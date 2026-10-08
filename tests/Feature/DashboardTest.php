<?php

namespace Tests\Feature;

use App\Models\Child;
use App\Models\User;
use App\Models\WhoGrowthStandard;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
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

    private function child(User $kader, string $name, float $weight, float $height, string $measuredAt): Child
    {
        $child = Child::factory()->create(['name' => $name, 'gender' => 'male', 'bod' => '2026-01-27', 'registered_by' => $kader->id]);
        $child->measurements()->create(['weight' => $weight, 'height' => $height, 'measured_at' => $measuredAt]);

        return $child;
    }

    public function test_kader_summary_counts_levels_missed_monitoring_and_overdue_follow_ups(): void
    {
        $kader = User::factory()->kader()->create();
        $high = $this->child($kader, 'Tinggi Satu', 5.0, 62.0, '2026-10-04');   // 70 -> tinggi
        $this->child($kader, 'Rendah Satu', 8.6, 70.6, '2026-10-04');            // 0 -> rendah
        $this->child($kader, 'Terlewat Satu', 8.6, 70.6, '2026-08-01');          // lama tak diukur
        $high->followUps()->create(['action_type' => 'remeasure', 'due_date' => '2026-10-01', 'status' => 'open', 'assigned_to' => $kader->id]);

        $response = $this->actingAs($kader)->get(route('kader.children.index'))->assertOk();
        $summary = $response->viewData('summary');

        $this->assertSame(1, $summary['tinggi']);
        $this->assertSame(1, $summary['missed']);
        $this->assertSame(1, $summary['overdue_followups']);
        $this->assertSame(0, $summary['sedang']);
        // 'Terlewat Satu' hanya mendapat 20 poin (terlewat panjang): masih level rendah.
        $this->assertSame(2, $summary['rendah']);
    }

    public function test_missed_filter_lists_only_children_with_overdue_monitoring(): void
    {
        $kader = User::factory()->kader()->create();
        $this->child($kader, 'Sehat Rutin', 8.6, 70.6, '2026-10-04');
        $this->child($kader, 'Lama Hilang', 8.6, 70.6, '2026-08-01');

        $this->actingAs($kader)->get(route('kader.children.index', ['missed' => 1]))
            ->assertOk()
            ->assertSee('Lama Hilang')
            ->assertDontSee('Sehat Rutin');
    }

    public function test_summary_only_counts_visible_children(): void
    {
        $kader = User::factory()->kader()->create();
        $other = User::factory()->kader()->create();
        $this->child($kader, 'Milik Saya', 5.0, 62.0, '2026-10-04');
        $this->child($other, 'Milik Lain', 5.0, 62.0, '2026-10-04');

        $summary = $this->actingAs($kader)->get(route('kader.children.index'))->viewData('summary');

        $this->assertSame(1, $summary['tinggi']);
    }

    public function test_parent_sees_plain_language_summary_without_scores(): void
    {
        $parent = User::factory()->create();
        $child = Child::factory()->create(['parent_id' => $parent->id, 'gender' => 'male', 'bod' => '2026-01-27']);
        $child->measurements()->create(['weight' => 5.0, 'height' => 62.0, 'measured_at' => '2026-10-04']);
        $child->followUps()->create(['action_type' => 'home_visit', 'due_date' => '2026-10-14', 'status' => 'open']);

        $this->actingAs($parent)->get(route('growth.index'))
            ->assertOk()
            ->assertSee('Perlu perhatian segera')
            ->assertSee('Tinggi badan sangat pendek untuk usianya')
            ->assertSee('Kunjungan rumah')
            ->assertSee('bukan diagnosis')
            ->assertDontSee('Skor');
    }

    public function test_parent_without_assessment_gets_neutral_message(): void
    {
        $parent = User::factory()->create();
        Child::factory()->create(['parent_id' => $parent->id]);

        $this->actingAs($parent)->get(route('growth.index'))->assertOk()->assertSee('Belum ada penilaian');
    }
}
