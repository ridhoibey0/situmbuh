<?php

namespace Tests\Feature;

use App\Models\Child;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChildPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_parent_can_only_see_own_children_and_can_have_several(): void
    {
        $parent = User::factory()->create();
        $other = User::factory()->create();
        $mine = Child::factory()->count(2)->create(['parent_id' => $parent->id]);
        $theirs = Child::factory()->create(['parent_id' => $other->id]);

        $this->assertCount(2, Child::visibleTo($parent)->get());
        $this->assertTrue($parent->can('view', $mine[0]));
        $this->assertTrue($parent->can('recordMeasurement', $mine[1]));
        $this->assertFalse($parent->can('view', $theirs));
        $this->assertFalse($parent->can('recordMeasurement', $theirs));
    }

    public function test_kader_sees_assigned_and_self_registered_children_only(): void
    {
        $kader = User::factory()->kader()->create();
        $assigned = Child::factory()->create();
        $assigned->staff()->attach($kader->id, ['role' => 'kader']);
        $registered = Child::factory()->create(['parent_id' => null, 'registered_by' => $kader->id]);
        $unrelated = Child::factory()->create();

        $visible = Child::visibleTo($kader)->pluck('id');

        $this->assertEqualsCanonicalizing([$assigned->id, $registered->id], $visible->all());
        $this->assertTrue($kader->can('recordMeasurement', $assigned));
        $this->assertFalse($kader->can('view', $unrelated));
        $this->assertTrue($kader->can('create', Child::class));
    }

    public function test_nakes_can_review_assigned_child_but_not_edit(): void
    {
        $nakes = User::factory()->nakes()->create();
        $child = Child::factory()->create();
        $child->staff()->attach($nakes->id, ['role' => 'nakes']);

        $this->assertTrue($nakes->can('view', $child));
        $this->assertFalse($nakes->can('update', $child));
        $this->assertFalse($nakes->can('recordMeasurement', $child));
    }

    public function test_admin_sees_everything_and_only_admin_can_delete(): void
    {
        $admin = User::factory()->admin()->create();
        $child = Child::factory()->create();
        $parent = $child->parent;

        $this->assertTrue($admin->can('view', $child));
        $this->assertTrue($admin->can('delete', $child));
        $this->assertFalse($parent->can('delete', $child));
    }

    public function test_role_middleware_blocks_other_roles(): void
    {
        \Illuminate\Support\Facades\Route::middleware(['web', 'auth', 'role:kader'])->get('/_kader-only', fn() => 'ok');

        $this->actingAs(User::factory()->create())->get('/_kader-only')->assertForbidden();
        $this->actingAs(User::factory()->kader()->create())->get('/_kader-only')->assertOk();
        $this->actingAs(User::factory()->admin()->create())->get('/_kader-only')->assertOk();
    }
}
