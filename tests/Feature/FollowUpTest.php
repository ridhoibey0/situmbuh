<?php

namespace Tests\Feature;

use App\Enums\FollowUpStatus;
use App\Models\Child;
use App\Models\FollowUp;
use App\Models\User;
use App\Models\WhoGrowthStandard;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FollowUpTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-10-07 08:00:00');

        foreach (['TB/U' => [70.6, 2.6], 'BB/U' => [8.6, 1.0]] as $parameter => [$median, $sd]) {
            WhoGrowthStandard::create([
                'gender' => 'male', 'parameter' => $parameter, 'age_in_months' => 8,
                'sd_3_negatif' => $median - 3 * $sd, 'sd_2_negatif' => $median - 2 * $sd, 'sd_1_negatif' => $median - $sd,
                'sd_median' => $median,
                'sd_1_positif' => $median + $sd, 'sd_2_positif' => $median + 2 * $sd, 'sd_3_positif' => $median + 3 * $sd,
            ]);
        }
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function riskyChild(User $kader): Child
    {
        $child = Child::factory()->create(['gender' => 'male', 'bod' => '2026-01-27', 'registered_by' => $kader->id]);
        $child->staff()->attach($kader->id, ['role' => 'kader']);
        $child->measurements()->create(['weight' => 5.0, 'height' => 62.0, 'measured_at' => '2026-10-04', 'measured_by' => $kader->id]);

        return $child;
    }

    public function test_full_closed_loop_scenario_from_detection_to_outcome(): void
    {
        $kader = User::factory()->kader()->create();
        $child = $this->riskyChild($kader);

        // Detect + Prioritize + Explain
        $before = $child->latestAssessment;
        $this->assertSame('tinggi', $before->level);
        $this->assertSame(70, $before->score);

        // Act: kader membuat tindak lanjut
        $this->actingAs($kader)->post(route('kader.children.follow-ups.store', $child), [
            'action_type' => 'home_visit',
            'due_date' => '2026-10-14',
            'notes' => 'Kunjungan rumah dan konseling gizi',
        ])->assertRedirect(route('kader.children.show', $child));

        $followUp = FollowUp::firstOrFail();
        $this->assertSame($before->id, $followUp->baseline_assessment_id);
        $this->assertSame($kader->id, $followUp->assigned_to);
        $this->assertSame(FollowUpStatus::Open, $followUp->status);

        // Follow-up terlambat menaikkan prioritas
        Carbon::setTestNow('2026-10-20 08:00:00');
        $this->artisan('risk:reassess')->assertSuccessful();
        $codes = array_column($child->fresh()->latestAssessment->factors, 'code');
        $this->assertContains('followup_overdue', $codes);

        // Follow-up diselesaikan: faktor tenggat hilang
        $this->patch(route('kader.follow-ups.update', $followUp), ['status' => 'done', 'result_notes' => 'Orang tua sudah dikonseling'])
            ->assertRedirect();
        $followUp->refresh();
        $this->assertSame(FollowUpStatus::Done, $followUp->status);
        $this->assertNotContains('followup_overdue', array_column($child->fresh()->latestAssessment->factors, 'code'));
        $this->assertNull($followUp->outcome_assessment_id, 'belum ada pemantauan sesudah selesai');
        $this->assertCount(2, $followUp->logs);

        // Re-monitoring: pengukuran berikutnya menjadi outcome
        Carbon::setTestNow('2026-10-25 08:00:00');
        $child->measurements()->create(['weight' => 8.4, 'height' => 69.0, 'measured_at' => '2026-10-25', 'measured_by' => $kader->id]);

        $followUp->refresh();
        $this->assertNotNull($followUp->outcome_assessment_id);
        $this->assertSame('rendah', $followUp->outcome->level);
        $this->assertSame(-70, $followUp->scoreChange());
        $this->assertSame('Membaik', $followUp->outcomeLabel());

        // Evaluate: terlihat pada halaman anak
        $this->get(route('kader.children.show', $child))
            ->assertOk()
            ->assertSee('Kunjungan rumah')
            ->assertSee('Orang tua sudah dikonseling')
            ->assertSee('Membaik');
    }

    public function test_nakes_cannot_create_but_assigned_nakes_can_update_status(): void
    {
        $kader = User::factory()->kader()->create();
        $nakes = User::factory()->nakes()->create();
        $child = $this->riskyChild($kader);
        $child->staff()->attach($nakes->id, ['role' => 'nakes']);

        $this->actingAs($nakes)->post(route('kader.children.follow-ups.store', $child), [
            'action_type' => 'refer_facility', 'due_date' => '2026-10-10',
        ])->assertForbidden();

        $this->actingAs($kader)->post(route('kader.children.follow-ups.store', $child), [
            'action_type' => 'refer_facility', 'due_date' => '2026-10-10', 'assigned_to' => $nakes->id,
        ])->assertRedirect();
        $followUp = FollowUp::firstOrFail();
        $this->assertSame($nakes->id, $followUp->assigned_to);

        $this->actingAs($nakes)->patch(route('kader.follow-ups.update', $followUp), ['status' => 'in_progress'])->assertRedirect();
        $this->assertSame(FollowUpStatus::InProgress, $followUp->fresh()->status);
    }

    public function test_unrelated_kader_cannot_see_create_or_update(): void
    {
        $kader = User::factory()->kader()->create();
        $stranger = User::factory()->kader()->create();
        $child = $this->riskyChild($kader);
        $followUp = $child->followUps()->create(['action_type' => 'remeasure', 'due_date' => '2026-10-10', 'status' => 'open', 'assigned_to' => $kader->id]);

        $this->actingAs($stranger)->post(route('kader.children.follow-ups.store', $child), [
            'action_type' => 'remeasure', 'due_date' => '2026-10-10',
        ])->assertForbidden();
        $this->patch(route('kader.follow-ups.update', $followUp), ['status' => 'done'])->assertForbidden();
        $this->get(route('kader.follow-ups.index'))->assertOk()->assertDontSee($child->name);
    }

    public function test_validation_rules(): void
    {
        $kader = User::factory()->kader()->create();
        $outsider = User::factory()->kader()->create();
        $child = $this->riskyChild($kader);

        $this->actingAs($kader)->post(route('kader.children.follow-ups.store', $child), [
            'action_type' => 'give_medicine', 'due_date' => '2026-10-01', 'assigned_to' => $outsider->id,
        ])->assertSessionHasErrors(['action_type', 'due_date', 'assigned_to']);

        $this->assertSame(0, FollowUp::count());
    }

    public function test_closed_follow_up_cannot_be_reopened(): void
    {
        $kader = User::factory()->kader()->create();
        $child = $this->riskyChild($kader);
        $followUp = $child->followUps()->create([
            'action_type' => 'remeasure', 'due_date' => '2026-10-10', 'status' => 'cancelled', 'assigned_to' => $kader->id,
        ]);

        $this->actingAs($kader)->patch(route('kader.follow-ups.update', $followUp), ['status' => 'open'])
            ->assertSessionHasErrors('status');
        $this->assertSame(FollowUpStatus::Cancelled, $followUp->fresh()->status);
    }

    public function test_follow_up_list_defaults_to_active_and_orders_by_due_date(): void
    {
        $kader = User::factory()->kader()->create();
        $child = $this->riskyChild($kader);
        $child->followUps()->create(['action_type' => 'remeasure', 'due_date' => '2026-10-30', 'status' => 'open', 'assigned_to' => $kader->id, 'notes' => 'NANTI']);
        $child->followUps()->create(['action_type' => 'remeasure', 'due_date' => '2026-10-09', 'status' => 'open', 'assigned_to' => $kader->id, 'notes' => 'SEGERA']);
        $child->followUps()->create(['action_type' => 'remeasure', 'due_date' => '2026-10-08', 'status' => 'done', 'assigned_to' => $kader->id, 'notes' => 'SUDAH', 'completed_at' => now()]);

        $this->actingAs($kader)->get(route('kader.follow-ups.index'))
            ->assertOk()
            ->assertSeeInOrder(['SEGERA', 'NANTI'])
            ->assertDontSee('SUDAH');
    }
}
