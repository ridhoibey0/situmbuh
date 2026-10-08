<?php

namespace Tests\Feature;

use App\Models\Child;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParentChildFlowTest extends TestCase
{
    use RefreshDatabase;

    private function childPayload(array $overrides = []): array
    {
        return $overrides + [
            'name' => 'Budi',
            'gender' => 'male',
            'bod' => now()->subMonths(6)->toDateString(),
            'weight' => 3.2,
            'height' => 49,
            'head_circumference' => 34,
            'arm_circumference' => 11,
        ];
    }

    public function test_parent_without_child_is_redirected_to_add_child(): void
    {
        $parent = User::factory()->create();

        $this->actingAs($parent)->get(route('growth.index'))->assertRedirect(route('children.create'));
    }

    public function test_parent_can_register_multiple_children_with_birth_measurement(): void
    {
        $parent = User::factory()->create();

        $this->actingAs($parent)->post(route('children.store'), $this->childPayload())
            ->assertRedirect(route('children.index'));
        $this->actingAs($parent)->post(route('children.store'), $this->childPayload(['name' => 'Sari', 'gender' => 'female']))
            ->assertRedirect(route('children.index'));

        $this->assertSame(2, $parent->children()->count());
        $this->assertSame(2, \App\Models\UserMeasurement::whereIn('child_id', $parent->children()->pluck('id'))->count());
    }

    public function test_store_child_rejects_invalid_values(): void
    {
        $parent = User::factory()->create();

        $this->actingAs($parent)
            ->post(route('children.store'), $this->childPayload(['weight' => 900, 'bod' => now()->addDay()->toDateString()]))
            ->assertSessionHasErrors(['weight', 'bod']);

        $this->assertSame(0, Child::count());
    }

    public function test_measurement_is_recorded_for_active_child_only(): void
    {
        $parent = User::factory()->create();
        $first = Child::factory()->create(['parent_id' => $parent->id, 'bod' => now()->subMonths(10)]);
        $second = Child::factory()->create(['parent_id' => $parent->id, 'bod' => now()->subMonths(10)]);

        $this->actingAs($parent)->post(route('children.select', $second))->assertRedirect(route('growth.index'));

        $this->post(route('measurement.store'), [
            'weight' => 8.5, 'height' => 72, 'head_circumference' => 45, 'arm_circumference' => 14,
            'measured_at' => now()->toDateString(),
        ])->assertRedirect(route('growth.index'));

        $this->assertSame(0, $first->measurements()->count());
        $this->assertSame(1, $second->measurements()->count());
        $this->assertSame($parent->id, $second->measurements()->first()->measured_by);
    }

    public function test_parent_cannot_select_another_familys_child(): void
    {
        $parent = User::factory()->create();
        Child::factory()->create(['parent_id' => $parent->id]);
        $foreign = Child::factory()->create();

        $this->actingAs($parent)->post(route('children.select', $foreign))->assertForbidden();
    }

    public function test_growth_page_loads_for_active_child(): void
    {
        $parent = User::factory()->create();
        $child = Child::factory()->create(['parent_id' => $parent->id, 'bod' => now()->subMonths(3)]);
        $child->measurements()->create([
            'weight' => 5, 'height' => 60, 'head_circumference' => 40, 'arm_circumference' => 13,
            'measured_at' => now()->subMonth(),
        ]);

        $this->actingAs($parent)->get(route('growth.index'))->assertOk()->assertSee($child->name);
    }
}
