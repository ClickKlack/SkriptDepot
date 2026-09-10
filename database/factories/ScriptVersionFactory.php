<?php

namespace Database\Factories;

use App\Models\Script;
use App\Models\ScriptVersion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ScriptVersion>
 */
class ScriptVersionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'script_id' => Script::factory(),
            'version' => '1.0.0',
            'source' => file_get_contents(base_path('tests/Fixtures/master-script.user.js')),
            'notes' => null,
        ];
    }

    public function version(string $version): static
    {
        return $this->state(fn (array $attributes) => [
            'version' => $version,
        ]);
    }
}
