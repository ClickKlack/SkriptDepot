<?php

namespace Database\Factories;

use App\Enums\DeliveryType;
use App\Models\Delivery;
use App\Models\Entitlement;
use App\Models\ScriptVersion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Delivery>
 */
class DeliveryFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'entitlement_id' => Entitlement::factory(),
            'script_version_id' => ScriptVersion::factory(),
            'type' => DeliveryType::User,
            'ip' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'created_at' => now(),
        ];
    }
}
