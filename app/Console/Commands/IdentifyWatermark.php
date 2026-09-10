<?php

namespace App\Console\Commands;

use App\Services\WatermarkResolver;
use App\Support\WatermarkMatch;
use Illuminate\Console\Command;

/**
 * Leak-Prüfung auf der Kommandozeile: Build-Hash, Token oder Datei einem Nutzer zuordnen.
 */
class IdentifyWatermark extends Command
{
    protected $signature = 'wm:identify {hash? : Build-Hash oder Skript-Text} {--file= : Pfad zu einer geleakten Skript-Datei}';

    protected $description = 'Ordnet einen Build-Hash oder eine Skript-Datei dem ursprünglichen Bezieher zu';

    public function handle(WatermarkResolver $resolver): int
    {
        $input = $this->readInput();

        if ($input === null) {
            $this->error('Bitte einen Build-Hash angeben oder --file verwenden.');

            return self::INVALID;
        }

        $matches = $resolver->resolve($input);

        if ($matches->isEmpty()) {
            $this->warn('Kein Treffer: weder Build-Hash noch Token konnten zugeordnet werden.');

            return self::FAILURE;
        }

        $this->table(
            ['Merkmal', 'Methode', 'Nutzer', 'E-Mail', 'Skript', 'Version', 'Freischaltung aktiv'],
            $matches->map(fn (WatermarkMatch $match) => [
                $match->identifier,
                $match->method->value,
                $match->entitlement->user->name,
                $match->entitlement->user->email,
                $match->entitlement->script->name,
                $match->scriptVersion?->version ?? '-',
                $match->entitlement->is_active ? 'ja' : 'nein',
            ])->all(),
        );

        return self::SUCCESS;
    }

    private function readInput(): ?string
    {
        $file = $this->option('file');

        if (is_string($file) && $file !== '') {
            if (! is_readable($file)) {
                $this->error("Datei nicht lesbar: {$file}");

                return null;
            }

            return (string) file_get_contents($file);
        }

        $hash = $this->argument('hash');

        return is_string($hash) && $hash !== '' ? $hash : null;
    }
}
