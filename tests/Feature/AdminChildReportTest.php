<?php

namespace Tests\Feature;

use App\Models\Child;
use App\Models\User;
use App\Models\WhoGrowthStandard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminChildReportTest extends TestCase
{
    use RefreshDatabase;

    private function stuntedChild(): Child
    {
        WhoGrowthStandard::create([
            'gender' => 'male', 'parameter' => 'TB/U', 'age_in_months' => 12,
            'sd_3_negatif' => 67.6, 'sd_2_negatif' => 70.1, 'sd_1_negatif' => 72.6,
            'sd_median' => 75.7, 'sd_1_positif' => 78.6, 'sd_2_positif' => 81.5, 'sd_3_positif' => 84.4,
        ]);

        $child = Child::factory()->create([
            'gender' => 'male',
            'bod' => now()->subMonths(12)->toDateString(),
        ]);
        $child->measurements()->create(['weight' => 8, 'height' => 68, 'measured_at' => now()]);

        return $child;
    }

    public function test_stunting_list_shows_children_below_minus_two_sd(): void
    {
        $child = $this->stuntedChild();

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.stunting.index'))
            ->assertOk()
            ->assertSee($child->name)
            ->assertSee('Z-score TB/U')
            ->assertSee('-2.48');
    }

    public function test_stunting_detail_loads_for_child(): void
    {
        $child = $this->stuntedChild();

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.stunting.show', $child))
            ->assertOk()
            ->assertSee($child->name);
    }

    public function test_stunting_pages_are_admin_only(): void
    {
        $child = $this->stuntedChild();

        $this->actingAs(User::factory()->create())->get(route('admin.stunting.index'))->assertRedirect('/');
        $this->actingAs(User::factory()->kader()->create())->get(route('admin.stunting.show', $child))->assertRedirect('/');
    }

    public function test_admin_can_assign_kader_role_and_invalid_role_is_rejected(): void
    {
        $admin = User::factory()->admin()->create();
        $target = User::factory()->create();

        $this->actingAs($admin)->put(route('users.update', $target->id), ['roles' => 'kader'])->assertRedirect();
        $this->assertSame('kader', $target->fresh()->roles->value);

        $this->put(route('users.update', $target->id), ['roles' => 'root'])->assertSessionHasErrors('roles');
        $this->assertSame('kader', $target->fresh()->roles->value);
    }
}
