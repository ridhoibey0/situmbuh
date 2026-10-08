<?php

namespace Tests\Feature;

use App\Models\Child;
use App\Models\User;
use App\Models\WhoGrowthStandard;
use App\Services\Risk\ChildTimeline;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KaderDetailTest extends TestCase
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

    private function childFor(User $kader, array $attrs = []): Child
    {
        $child = Child::factory()->create($attrs + ['gender' => 'male', 'bod' => '2026-01-27', 'registered_by' => $kader->id]);
        $child->staff()->attach($kader->id, ['role' => 'kader']);

        return $child;
    }

    public function test_detail_shows_whatsapp_link_to_parent_with_prefilled_neutral_message(): void
    {
        $kader = User::factory()->kader()->create(['name' => 'Kader Sari']);
        $parent = User::factory()->create(['phone' => '081234567890']);
        $child = $this->childFor($kader, ['parent_id' => $parent->id, 'name' => 'Budi']);
        $child->measurements()->create(['weight' => 8.6, 'height' => 70.6, 'measured_at' => '2026-08-01']); // terlewat

        $html = $this->actingAs($kader)->get(route('kader.children.show', $child))->assertOk()->getContent();

        $this->assertStringContainsString('https://wa.me/6281234567890?text=', $html);
        $this->assertStringContainsString(rawurlencode('sudah waktunya diukur kembali'), $html);
        $this->assertStringContainsString('Hubungi orang tua', $html);
    }

    public function test_whatsapp_uses_pending_parent_phone_and_is_hidden_without_any_number(): void
    {
        $kader = User::factory()->kader()->create();
        $pending = $this->childFor($kader, ['parent_id' => null, 'parent_phone' => '081200001111']);
        $none = $this->childFor($kader, ['parent_id' => null, 'parent_phone' => null]);

        $this->actingAs($kader)->get(route('kader.children.show', $pending))->assertSee('wa.me/6281200001111', false);
        $this->get(route('kader.children.show', $none))->assertDontSee('Hubungi orang tua');
    }

    public function test_chart_only_appears_with_at_least_two_measurements(): void
    {
        $kader = User::factory()->kader()->create();
        $child = $this->childFor($kader);
        $child->measurements()->create(['weight' => 8.0, 'height' => 68.0, 'measured_at' => '2026-09-01']);

        $this->actingAs($kader)->get(route('kader.children.show', $child))->assertDontSee('chartWeight', false);

        $child->measurements()->create(['weight' => 8.5, 'height' => 70.0, 'measured_at' => '2026-10-01']);

        $this->get(route('kader.children.show', $child))->assertSee('chartWeight', false)->assertSee('Grafik pertumbuhan');
    }

    public function test_timeline_lists_measurements_level_changes_kpsp_and_followups_newest_first(): void
    {
        $kader = User::factory()->kader()->create();
        $child = $this->childFor($kader);

        Carbon::setTestNow('2026-09-01 08:00:00');
        $child->measurements()->create(['weight' => 8.6, 'height' => 70.6, 'measured_at' => '2026-09-01']);

        Carbon::setTestNow('2026-10-04 08:00:00');
        $child->measurements()->create(['weight' => 5.0, 'height' => 62.0, 'measured_at' => '2026-10-04']); // naik ke tinggi

        $this->actingAs($kader)->post(route('kader.children.follow-ups.store', $child), [
            'action_type' => 'home_visit', 'due_date' => '2026-10-14', 'notes' => 'Kunjungan',
        ]);

        $events = app(ChildTimeline::class)->for($child->fresh());
        $titles = $events->pluck('title')->all();

        $this->assertContains('Pengukuran dicatat', $titles);
        $this->assertContains('Penilaian prioritas pertama: Rendah', $titles);
        $this->assertContains('Prioritas berubah dari rendah ke tinggi', $titles);
        $this->assertContains('Kunjungan rumah: belum dikerjakan', $titles);

        $times = $events->map(fn($e) => $e['at']->getTimestamp())->all();
        $sorted = $times;
        rsort($sorted);
        $this->assertSame($sorted, $times, 'urutan terbaru lebih dulu');

        $this->get(route('kader.children.show', $child))->assertSee('Riwayat aktivitas')->assertSee('Prioritas berubah dari rendah ke tinggi');
    }
}
