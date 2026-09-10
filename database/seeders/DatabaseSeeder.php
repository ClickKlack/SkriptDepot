<?php

namespace Database\Seeders;

use App\Models\Entitlement;
use App\Models\Script;
use App\Models\ScriptVersion;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Lokale Entwicklungsdaten: ein Admin, ein normaler Nutzer, ein Beispielskript mit Freischaltung.
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::factory()->admin()->create([
            'name' => 'Admin',
            'email' => 'admin@example.test',
            'password' => 'geheim123',
        ]);

        $member = User::factory()->create([
            'name' => 'Erika Muster',
            'email' => 'erika@example.test',
            'password' => 'geheim123',
        ]);

        $script = Script::factory()->create([
            'slug' => 'beispiel',
            'name' => 'Beispiel-Skript',
            'description' => 'Demonstriert Installation, Update und Wasserzeichen.',
        ]);

        ScriptVersion::factory()->for($script)->version('1.0.0')->create();

        Entitlement::factory()->for($admin)->for($script)->create();
        Entitlement::factory()->for($member)->for($script)->create();
    }
}
