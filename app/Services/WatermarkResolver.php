<?php

namespace App\Services;

use App\Models\Entitlement;
use App\Models\Watermark;
use App\Support\WatermarkMatch;
use App\Support\WatermarkMatchMethod;
use Illuminate\Support\Collection;

/**
 * Ordnet einen Build-Hash, einen Token oder einen kompletten Skript-Text dem Bezieher zu.
 *
 * Primär über die Tabelle watermarks, als Fallback durch Nachrechnen des Hashes für alle
 * Freischaltungen. Tokens aus den Update-URLs werden ebenfalls erkannt.
 */
class WatermarkResolver
{
    private const string BUILD_HASH_PATTERN = '/^[0-9a-f]{8}$/i';

    private const string EMBEDDED_HASH_PATTERN = "/(?:build:\\s*|BUILD\\s*=\\s*['\"])([0-9a-f]{8})\\b/i";

    private const string TOKEN_PATTERN = '#/s/([A-Za-z0-9]{40})/#';

    public function __construct(private readonly ScriptBuilder $builder) {}

    /**
     * @return Collection<int, WatermarkMatch>
     */
    public function resolve(string $input): Collection
    {
        $matches = collect();

        foreach ($this->extractTokens($input) as $token) {
            $entitlement = Entitlement::query()->where('token', $token)->with(['user', 'script'])->first();

            if ($entitlement !== null) {
                $matches->push(new WatermarkMatch($token, WatermarkMatchMethod::Token, $entitlement, null));
            }
        }

        foreach ($this->extractBuildHashes($input) as $buildHash) {
            $matches = $matches->merge($this->resolveBuildHash($buildHash));
        }

        return $matches->values();
    }

    /**
     * @return list<string>
     */
    public function extractBuildHashes(string $input): array
    {
        $trimmed = trim($input);

        if (preg_match(self::BUILD_HASH_PATTERN, $trimmed) === 1) {
            return [strtolower($trimmed)];
        }

        preg_match_all(self::EMBEDDED_HASH_PATTERN, $input, $found);

        return array_values(array_unique(array_map('strtolower', $found[1])));
    }

    /**
     * @return list<string>
     */
    public function extractTokens(string $input): array
    {
        preg_match_all(self::TOKEN_PATTERN, $input, $found);

        return array_values(array_unique($found[1]));
    }

    /**
     * @return Collection<int, WatermarkMatch>
     */
    private function resolveBuildHash(string $buildHash): Collection
    {
        $recorded = Watermark::query()
            ->where('build_hash', $buildHash)
            ->with(['entitlement.user', 'entitlement.script', 'scriptVersion'])
            ->get()
            ->map(fn (Watermark $watermark) => new WatermarkMatch(
                $buildHash,
                WatermarkMatchMethod::Watermark,
                $watermark->entitlement,
                $watermark->scriptVersion,
            ));

        if ($recorded->isNotEmpty()) {
            return $recorded;
        }

        return $this->computeBuildHash($buildHash);
    }

    /**
     * Fallback: rechnet den Hash für jede Freischaltung und jede Version ihres Skripts nach.
     *
     * @return Collection<int, WatermarkMatch>
     */
    private function computeBuildHash(string $buildHash): Collection
    {
        $matches = collect();

        Entitlement::query()->with(['user', 'script.versions'])->each(function (Entitlement $entitlement) use ($buildHash, $matches): void {
            foreach ($entitlement->script->versions as $scriptVersion) {
                $candidate = $this->builder->buildHash($entitlement->user->seed, $entitlement->script_id, $scriptVersion->version);

                if ($candidate === $buildHash) {
                    $matches->push(new WatermarkMatch($buildHash, WatermarkMatchMethod::Computed, $entitlement, $scriptVersion));
                }
            }
        });

        return $matches;
    }
}
