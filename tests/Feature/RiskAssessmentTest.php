<?php

namespace Tests\Feature;

use App\Models\Child;
use App\Models\KpspResult;
use App\Models\User;
use App\Models\WhoGrowthStandard;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RiskAssessmentTest extends TestCase
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

    private function child(array $attributes = []): Child
    {
        return Child::factory()->create($attributes + ['gender' => 'male', 'bod' => '2026-01-27']);
    }

    public function test_recording_a_measurement_creates_an_assessment_with_explainable_factors(): void
    {
        $child = $this->child();

        $child->measurements()->create(['weight' => 8.6, 'height' => 62.0, 'measured_at' => '2026-10-04']);

        $assessment = $child->latestAssessment;
        $this->assertNotNull($assessment);
        $this->assertSame('height_severe', $assessment->factors[0]['code']);
        $this->assertSame(40, $assessment->score);
        $this->assertSame('sedang', $assessment->level);
        $this->assertSame(-3.31, $assessment->z_tb);
    }

    public function test_new_kpsp_result_triggers_reassessment(): void
    {
        $child = $this->child();
        $child->measurements()->create(['weight' => 8.6, 'height' => 70.6, 'measured_at' => '2026-10-04']);
        $this->assertSame(0, $child->latestAssessment()->first()->score);

        KpspResult::create([
            'child_id' => $child->id, 'age_category_id' => 1, 'yes_count' => 3,
            'interpretation' => 'Ada kemungkinan penyimpangan', 'intervensi' => 'Rujuk ke RS rujukan tumbuh kembang level 1.',
        ]);

        $latest = $child->assessments()->latest('id')->first();
        $this->assertSame(20, $latest->score);
        $this->assertSame('kpsp_referral', $latest->factors[0]['code']);
        $this->assertSame(2, $child->assessments()->count());
    }

    public function test_reassess_command_detects_missed_monitoring_and_skips_unchanged(): void
    {
        $child = $this->child();
        $child->measurements()->create(['weight' => 8.6, 'height' => 70.6, 'measured_at' => '2026-10-04']);
        $this->assertSame(1, $child->assessments()->count());

        $this->artisan('risk:reassess')->assertSuccessful();
        $this->assertSame(1, $child->assessments()->count(), 'hasil sama tidak boleh menambah riwayat');

        Carbon::setTestNow('2026-12-20 08:00:00'); // 77 hari setelah pengukuran
        $this->artisan('risk:reassess')->assertSuccessful();

        $latest = $child->assessments()->latest('id')->first();
        $this->assertSame(2, $child->assessments()->count());
        $this->assertSame('monitoring_long_overdue', $latest->factors[0]['code']);
    }

    public function test_kader_list_is_sorted_by_priority_and_can_filter_by_level(): void
    {
        $kader = User::factory()->kader()->create();
        $low = $this->child(['name' => 'Aaa Sehat', 'registered_by' => $kader->id]);
        $high = $this->child(['name' => 'Zzz Berisiko', 'registered_by' => $kader->id]);
        $low->measurements()->create(['weight' => 8.6, 'height' => 70.6, 'measured_at' => '2026-10-04']);
        $high->measurements()->create(['weight' => 5.0, 'height' => 62.0, 'measured_at' => '2026-10-04']);

        $this->actingAs($kader)->get(route('kader.children.index'))
            ->assertOk()
            ->assertSeeInOrder(['Zzz Berisiko', 'Aaa Sehat']);

        $this->get(route('kader.children.index', ['level' => 'rendah']))
            ->assertOk()
            ->assertSee('Aaa Sehat')
            ->assertDontSee('Zzz Berisiko');
    }

    public function test_child_detail_explains_why_it_is_a_priority(): void
    {
        $kader = User::factory()->kader()->create();
        $child = $this->child(['registered_by' => $kader->id]);
        $child->measurements()->create(['weight' => 8.6, 'height' => 62.0, 'measured_at' => '2026-10-04']);

        $this->actingAs($kader)->get(route('kader.children.show', $child))
            ->assertOk()
            ->assertSee('Mengapa prioritas ini?')
            ->assertSee('Tinggi badan sangat pendek untuk usianya')
            ->assertSee('TB/U -3.31 SD')
            ->assertSee('bukan diagnosis');
    }
}
