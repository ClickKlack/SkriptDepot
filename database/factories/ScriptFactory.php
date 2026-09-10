<?php

namespace Database\Factories;

use App\Models\Script;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Script>
 */
class ScriptFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'slug' => fake()->unique()->slug(2),
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
        ];
    }
}
