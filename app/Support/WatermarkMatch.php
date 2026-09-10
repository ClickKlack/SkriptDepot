<?php

namespace App\Support;

use App\Models\Entitlement;
use App\Models\ScriptVersion;

/**
 * Ergebnis einer Wasserzeichen-Auflösung: welches Merkmal auf welche Freischaltung zeigt.
 */
final readonly class WatermarkMatch
{
    public function __construct(
        public string $identifier,
        public WatermarkMatchMethod $method,
        public Entitlement $entitlement,
        public ?ScriptVersion $scriptVersion,
    ) {}
}
