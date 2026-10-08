<?php

namespace App\Http\Controllers\Kader;

use App\Enums\FollowUpAction;
use App\Enums\FollowUpStatus;
use App\Http\Controllers\Controller;
use App\Models\Child;
use App\Models\FollowUp;
use App\Services\FollowUp\FollowUpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class FollowUpController extends Controller
{
    public function __construct(private FollowUpService $service)
    {
    }

    /** Daftar tindak lanjut pada anak yang dapat dilihat pengguna, yang paling mendesak lebih dulu. */
    public function index(Request $request)
    {
        $user = Auth::user();
        $status = $request->input('status', 'active');
        $mine = $request->boolean('mine');

        $followUps = FollowUp::query()
            ->whereIn('child_id', Child::visibleTo($user)->select('children.id'))
            ->with('child:id,name', 'assignee:id,name')
            ->when($status === 'active', fn($q) => $q->active())
            ->when(in_array($status, ['done', 'cancelled'], true), fn($q) => $q->where('status', $status))
            ->when($mine, fn($q) => $q->where('assigned_to', $user->id))
            ->orderBy('due_date')
            ->paginate(20)
            ->withQueryString();

        return view('pages.kader.follow-ups.index', compact('followUps', 'status', 'mine'));
    }

    public function store(Request $request, Child $child)
    {
        $this->authorize('create', [FollowUp::class, $child]);

        $assignable = $child->staff()->pluck('users.id')->push(Auth::id())->unique()->all();

        $data = $request->validate([
            'action_type' => ['required', Rule::enum(FollowUpAction::class)],
            'due_date' => ['required', 'date', 'after_or_equal:today'],
            'assigned_to' => ['nullable', Rule::in($assignable)],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->service->create($child, Auth::user(), $data);

        return redirect()->route('kader.children.show', $child)->with('success', 'Tindak lanjut dibuat.');
    }

    public function update(Request $request, FollowUp $followUp)
    {
        $this->authorize('update', $followUp);

        $data = $request->validate([
            'status' => ['required', Rule::enum(FollowUpStatus::class)],
            'note' => ['nullable', 'string', 'max:1000'],
            'result_notes' => ['nullable', 'string', 'max:1000'],
        ]);

        if (!$followUp->status->isActive()) {
            return back()->withErrors(['status' => 'Tindak lanjut ini sudah ditutup.']);
        }

        $this->service->changeStatus(
            $followUp,
            Auth::user(),
            FollowUpStatus::from($data['status']),
            $data['note'] ?? null,
            $data['result_notes'] ?? null,
        );

        return back()->with('success', 'Status tindak lanjut diperbarui.');
    }
}
