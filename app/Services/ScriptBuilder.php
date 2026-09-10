<?php

namespace App\Services;

use App\Models\Entitlement;
use App\Models\ScriptVersion;
use RuntimeException;

/**
 * Baut aus dem Master-Quelltext einer Version die personalisierte Kopie für eine Freischaltung.
 *
 * Alle Ersetzungen sind reine String-Operationen. Der Build-Hash ist deterministisch, damit
 * derselbe Nutzer bei gleicher Version immer denselben Inhalt erhält.
 */
class ScriptBuilder
{
    private const string HEADER_PATTERN = '#// ==UserScript==.*?// ==/UserScript==#s';

    /**
     * Vollständiges .user.js inklusive Wasserzeichen.
     */
    public function build(Entitlement $entitlement, ScriptVersion $scriptVersion): string
    {
        return $this->replacePlaceholders($scriptVersion->source, $entitlement, $scriptVersion);
    }

    /**
     * Nur der Metadaten-Block für .meta.js; enthält bewusst keinen Build-Hash.
     */
    public function buildMeta(Entitlement $entitlement, ScriptVersion $scriptVersion): string
    {
        $header = $this->extractHeader($scriptVersion->source);

        return $this->replacePlaceholders($header, $entitlement, $scriptVersion)."\n";
    }

    /**
     * Opaker Build-Hash: 8 Hex-Zeichen, eindeutig pro (Seed, Skript, Version), ohne Rückschluss auf den Nutzer.
     */
    public function buildHash(string $seed, int $scriptId, string $version): string
    {
        return substr(hash('sha256', $seed.'|'.$scriptId.'|'.$version), 0, 8);
    }

    public function buildHashFor(Entitlement $entitlement, ScriptVersion $scriptVersion): string
    {
        return $this->buildHash($entitlement->user->seed, $scriptVersion->script_id, $scriptVersion->version);
    }

    /**
     * Liefert den ==UserScript==-Block eines Quelltexts.
     *
     * @throws RuntimeException wenn der Quelltext keinen Metadaten-Block enthält
     */
    public function extractHeader(string $source): string
    {
        if (preg_match(self::HEADER_PATTERN, $source, $matches) !== 1) {
            throw new RuntimeException('Der Quelltext enthält keinen ==UserScript==-Block.');
        }

        return $matches[0];
    }

    private function replacePlaceholders(string $source, Entitlement $entitlement, ScriptVersion $scriptVersion): string
    {
        $replacements = [
            '{{VERSION}}' => $scriptVersion->version,
            '{{BASE}}' => rtrim((string) config('app.url'), '/'),
            '{{TOKEN}}' => $entitlement->token,
            '{{SLUG}}' => $entitlement->script->slug,
            '{{BUILD}}' => $this->buildHashFor($entitlement, $scriptVersion),
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $source);
    }
}
