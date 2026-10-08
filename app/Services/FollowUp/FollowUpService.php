<?php

namespace App\Services\FollowUp;

use App\Enums\FollowUpStatus;
use App\Models\Child;
use App\Models\FollowUp;
use App\Models\User;
use App\Services\Risk\AssessChild;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class FollowUpService
{
    public function __construct(private AssessChild $assess, private OutcomeLinker $linker)
    {
    }

    /**
     * @param  array{action_type: string, due_date: string, assigned_to?: int|null, notes?: string|null}  $data
     */
    public function create(Child $child, User $creator, array $data): FollowUp
    {
        return DB::transaction(function () use ($child, $creator, $data) {
            // Simpan kondisi saat ini sebagai baseline evaluasi.
            $baseline = $this->assess->handle($child);

            $followUp = $child->followUps()->create([
                'baseline_assessment_id' => $baseline->id,
                'assigned_to' => $data['assigned_to'] ?? $creator->id,
                'created_by' => $creator->id,
                'action_type' => $data['action_type'],
                'notes' => $data['notes'] ?? null,
                'due_date' => $data['due_date'],
                'status' => FollowUpStatus::Open,
            ]);

            $followUp->logs()->create([
                'user_id' => $creator->id,
                'from_status' => null,
                'to_status' => FollowUpStatus::Open,
                'note' => 'Tindak lanjut dibuat',
            ]);

            $this->assess->handle($child);

            return $followUp;
        });
    }

    public function changeStatus(FollowUp $followUp, User $by, FollowUpStatus $to, ?string $note = null, ?string $resultNotes = null): FollowUp
    {
        $from = $followUp->status;

        if (!$from->isActive()) {
            throw new InvalidArgumentException('Tindak lanjut yang sudah selesai atau dibatalkan tidak dapat diubah.');
        }

        if ($to === $from) {
            return $followUp;
        }

        return DB::transaction(function () use ($followUp, $by, $from, $to, $note, $resultNotes) {
            $attributes = ['status' => $to];

            if ($to === FollowUpStatus::Done) {
                $attributes['completed_at'] = now();
                $attributes['result_notes'] = $resultNotes;
            }

            $followUp->update($attributes);

            $followUp->logs()->create([
                'user_id' => $by->id,
                'from_status' => $from,
                'to_status' => $to,
                'note' => $note ?? $resultNotes,
            ]);

            $child = $followUp->child;
            $this->assess->handle($child); // faktor "tenggat terlewat" dapat hilang
            $this->linker->link($child);

            return $followUp->fresh();
        });
    }
}
