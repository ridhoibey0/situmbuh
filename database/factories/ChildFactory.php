<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Child>
 */
class ChildFactory extends Factory
{
    public function definition(): array
    {
        return [
            'parent_id' => User::factory(),
            'name' => fake()->firstName(),
            'gender' => fake()->randomElement(['male', 'female']),
            'bod' => now()->subMonths(fake()->numberBetween(1, 59))->toDateString(),
        ];
    }
}
