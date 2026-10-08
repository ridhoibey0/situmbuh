<?php

namespace Tests\Feature;

use App\Models\Child;
use App\Models\FollowUp;
use App\Models\User;
use App\Models\WhoGrowthStandard;
use App\Services\Admin\DashboardMetrics;
use App\Support\Phone;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPanelTest extends TestCase
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

    private function child(array $attrs = [], float $weight = 8.6, float $height = 70.6, string $at = '2026-10-04'): Child
    {
        $child = Child::factory()->create($attrs + ['gender' => 'male', 'bod' => '2026-01-27']);
        $child->measurements()->create(['weight' => $weight, 'height' => $height, 'measured_at' => $at]);

        return $child;
    }

    public function test_metrics_summarise_levels_followups_and_outcomes(): void
    {
        $kader = User::factory()->kader()->create();
        $risky = $this->child(['registered_by' => $kader->id], 5.0, 62.0);
        $this->child([], 8.6, 70.6);
        $this->child([], 8.6, 70.6, '2026-08-01'); // terlewat

        $this->actingAs($kader)->post(route('kader.children.follow-ups.store', $risky), [
            'action_type' => 'home_visit', 'due_date' => '2026-10-14',
        ]);
        $followUp = FollowUp::firstOrFail();

        Carbon::setTestNow('2026-10-20 08:00:00'); // terlambat dari tenggat 14 Okt
        $this->patch(route('kader.follow-ups.update', $followUp), ['status' => 'done']);
        Carbon::setTestNow('2026-10-25 08:00:00');
        $risky->measurements()->create(['weight' => 8.4, 'height' => 69.0, 'measured_at' => '2026-10-25']);

        $m = (new DashboardMetrics())->summary(6);

        $this->assertSame(3, $m['total_children']);
        $this->assertSame(1, $m['followups']['done']);
        $this->assertSame(100, $m['followups']['completion_rate']);
        $this->assertSame(0, $m['followups']['on_time_rate'], 'selesai setelah tenggat');
        $this->assertSame(['membaik' => 1, 'tetap' => 0, 'memburuk' => 0], $m['outcomes']);
        $this->assertSame(1, $m['missed']);
        $this->assertCount(6, $m['monthly']['labels']);
        $this->assertSame(1, array_sum($m['monthly']['done']));
        $this->assertSame(1, array_sum($m['monthly']['created']));
    }

    public function test_dashboard_renders_for_admin_and_is_forbidden_for_others(): void
    {
        $this->child([], 5.0, 62.0);

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Ringkasan layanan')
            ->assertSee('Perlu ditinjau lebih dulu')
            ->assertSee('Tinggi badan sangat pendek untuk usianya');

        $this->actingAs(User::factory()->kader()->create())->get(route('admin.dashboard'))->assertRedirect('/');
    }

    public function test_dashboard_handles_empty_database(): void
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Belum ada anak dengan skor prioritas');
    }

    public function test_admin_assigns_and_removes_staff(): void
    {
        $admin = User::factory()->admin()->create();
        $nakes = User::factory()->nakes()->create();
        $child = Child::factory()->create();

        $this->actingAs($admin)->post(route('admin.children.staff.assign', $child), ['user_id' => $nakes->id])->assertRedirect();
        $this->assertTrue($child->staff()->where('users.id', $nakes->id)->exists());
        $this->assertSame('nakes', $child->staff()->first()->pivot->role);

        // nakes kini dapat melihat anak tersebut
        $this->actingAs($nakes)->get(route('kader.children.show', $child))->assertOk();

        $this->actingAs($admin)->delete(route('admin.children.staff.remove', [$child, $nakes]))->assertRedirect();
        $this->assertFalse($child->staff()->where('users.id', $nakes->id)->exists());
    }

    public function test_admin_cannot_assign_a_parent_account_as_staff(): void
    {
        $admin = User::factory()->admin()->create();
        $parent = User::factory()->create();
        $child = Child::factory()->create();

        $this->actingAs($admin)->post(route('admin.children.staff.assign', $child), ['user_id' => $parent->id])
            ->assertSessionHasErrors('user_id');
    }

    public function test_admin_links_and_unlinks_parent_by_phone_ignoring_formatting(): void
    {
        $admin = User::factory()->admin()->create();
        $parent = User::factory()->create(['phone' => '081234567890']);
        $child = Child::factory()->create(['parent_id' => null, 'parent_phone' => '081234567890']);

        $this->actingAs($admin)->put(route('admin.children.parent.link', $child), ['phone' => '0812-3456-7890'])->assertRedirect();
        $this->assertSame($parent->id, $child->fresh()->parent_id);
        $this->assertNull($child->fresh()->parent_phone);

        $this->delete(route('admin.children.parent.unlink', $child))->assertRedirect();
        $this->assertNull($child->fresh()->parent_id);

        $this->put(route('admin.children.parent.link', $child), ['phone' => '000'])->assertSessionHasErrors('phone');
    }

    public function test_children_list_filters_unassigned(): void
    {
        $admin = User::factory()->admin()->create();
        $assigned = Child::factory()->create(['name' => 'Sudah Ditugaskan']);
        $assigned->staff()->attach(User::factory()->kader()->create()->id, ['role' => 'kader']);
        Child::factory()->create(['name' => 'Belum Ditugaskan']);

        $this->actingAs($admin)->get(route('admin.children.index', ['unassigned' => 1]))
            ->assertOk()
            ->assertSee('Belum Ditugaskan')
            ->assertDontSee('Sudah Ditugaskan');
    }

    public function test_kader_registration_without_parent_account_links_when_parent_registers(): void
    {
        $kader = User::factory()->kader()->create();

        $this->actingAs($kader)->post(route('kader.children.store'), [
            'name' => 'Anak Menunggu', 'gender' => 'female', 'bod' => '2026-08-01', 'parent_phone' => '0812-0000-1111',
        ])->assertRedirect();

        $child = Child::where('name', 'Anak Menunggu')->firstOrFail();
        $this->assertNull($child->parent_id);
        $this->assertSame('081200001111', $child->parent_phone);

        auth()->logout();
        $this->flushSession();
        $this->post('/register', [
            'name' => 'Ibu Baru', 'phone' => '081200001111', 'password' => 'secret123', 'password_confirmation' => 'secret123',
        ]);

        $parent = User::where('phone', '081200001111')->firstOrFail();
        $this->assertSame($parent->id, $child->fresh()->parent_id);
        $this->assertNull($child->fresh()->parent_phone);
    }

    public function test_phone_helper(): void
    {
        $this->assertSame('6281234567890', Phone::international('0812-3456-7890'));
        $this->assertSame('6281234567890', Phone::international('+62 812 3456 7890'));
        $this->assertSame('', Phone::international(null));
        $this->assertStringStartsWith('https://wa.me/6281234567890?text=', Phone::whatsappUrl('081234567890', 'Halo bu'));
        $this->assertNull(Phone::whatsappUrl(''));
    }
}
