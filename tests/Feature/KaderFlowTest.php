<?php

namespace Tests\Feature;

use App\Models\Child;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KaderFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_kader_registers_child_linked_to_existing_parent_by_phone(): void
    {
        $kader = User::factory()->kader()->create();
        $parent = User::factory()->create(['phone' => '081234567890']);

        $response = $this->actingAs($kader)->post(route('kader.children.store'), [
            'name' => 'Ayu',
            'gender' => 'female',
            'bod' => now()->subMonths(8)->toDateString(),
            'parent_phone' => '081234567890',
        ]);

        $child = Child::firstOrFail();
        $response->assertRedirect(route('kader.children.show', $child));
        $this->assertSame($parent->id, $child->parent_id);
        $this->assertSame($kader->id, $child->registered_by);
        $this->assertTrue($child->staff->contains($kader));
    }

    public function test_kader_can_register_child_without_parent_account(): void
    {
        $kader = User::factory()->kader()->create();

        $this->actingAs($kader)->post(route('kader.children.store'), [
            'name' => 'Rafi', 'gender' => 'male', 'bod' => now()->subMonths(2)->toDateString(),
        ])->assertRedirect();

        $this->assertNull(Child::firstOrFail()->parent_id);
    }

    public function test_kader_records_measurement_and_sees_only_own_children(): void
    {
        $kader = User::factory()->kader()->create();
        $mine = Child::factory()->create(['registered_by' => $kader->id, 'bod' => now()->subMonths(5)]);
        $other = Child::factory()->create();

        $this->actingAs($kader)->post(route('kader.children.measurements.store', $mine), [
            'weight' => 6.5, 'height' => 63, 'measured_at' => now()->toDateString(),
        ])->assertRedirect(route('kader.children.show', $mine));

        $this->assertSame($kader->id, $mine->measurements()->firstOrFail()->measured_by);
        $this->get(route('kader.children.index'))->assertOk()->assertSee($mine->name)->assertDontSee($other->name);
        $this->get(route('kader.children.show', $other))->assertForbidden();
        $this->post(route('kader.children.measurements.store', $other), [
            'weight' => 6.5, 'height' => 63, 'measured_at' => now()->toDateString(),
        ])->assertForbidden();
    }

    public function test_nakes_can_view_assigned_child_but_not_write(): void
    {
        $nakes = User::factory()->nakes()->create();
        $child = Child::factory()->create();
        $child->staff()->attach($nakes->id, ['role' => 'nakes']);

        $this->actingAs($nakes)->get(route('kader.children.show', $child))->assertOk()->assertDontSee('Catat Pengukuran');
        $this->post(route('kader.children.measurements.store', $child), [
            'weight' => 6.5, 'height' => 63, 'measured_at' => now()->toDateString(),
        ])->assertForbidden();
        $this->post(route('kader.children.store'), ['name' => 'X', 'gender' => 'male', 'bod' => now()->toDateString()])
            ->assertForbidden();
    }

    public function test_parent_cannot_open_kader_area(): void
    {
        $this->actingAs(User::factory()->create())->get('/kader')->assertForbidden();
    }

    public function test_login_redirects_each_role_to_its_home(): void
    {
        foreach ([
            [User::factory()->admin(), '/admin/dashboard'],
            [User::factory()->kader(), '/kader'],
            [User::factory()->nakes(), '/kader'],
            [User::factory(), '/users'],
        ] as [$factory, $path]) {
            $user = $factory->create(['phone' => fake()->unique()->numerify('08##########'), 'password' => 'secret123']);

            auth()->logout();
            $this->flushSession();

            $this->post('/login', ['phone' => $user->phone, 'password' => 'secret123'])->assertRedirect($path);
        }
    }
}
