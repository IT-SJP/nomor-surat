<?php

namespace Database\Factories;

use App\Models\LetterTarget;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LetterTarget>
 */
class LetterTargetFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->unique()->lexify('???')),
            'name' => fake()->words(2, true),
            'description' => fake()->sentence(),
            'is_active' => true,
        ];
    }
}
