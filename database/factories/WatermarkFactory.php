<?php

namespace Database\Factories;

use App\Models\Entitlement;
use App\Models\ScriptVersion;
use App\Models\Watermark;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Watermark>
 */
class WatermarkFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'entitlement_id' => Entitlement::factory(),
            'script_version_id' => ScriptVersion::factory(),
            'build_hash' => fake()->regexify('[0-9a-f]{8}'),
            'first_delivered_at' => now(),
        ];
    }
}
