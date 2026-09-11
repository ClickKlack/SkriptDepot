<?php

namespace App\Http\Controllers;

use App\Enums\DeliveryType;
use App\Models\Entitlement;
use App\Models\ScriptVersion;
use App\Services\ScriptBuilder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

/**
 * Öffentliche Auslieferungsrouten; die Authentifizierung läuft ausschließlich über den Token in der URL.
 *
 * Jeder Fehlerfall antwortet einheitlich mit 403, damit gültige Tokens nicht enumerierbar sind.
 */
class ScriptDeliveryController extends Controller
{
    public function __construct(private readonly ScriptBuilder $builder) {}

    public function meta(Request $request, string $token, string $slug): Response
    {
        [$entitlement, $scriptVersion] = $this->resolve($token, $slug);

        $this->logDelivery($request, $entitlement, $scriptVersion, DeliveryType::Meta);

        return $this->javascriptResponse($this->builder->buildMeta($entitlement, $scriptVersion));
    }

    public function user(Request $request, string $token, string $slug): Response
    {
        [$entitlement, $scriptVersion] = $this->resolve($token, $slug);

        $entitlement->watermarks()->firstOrCreate(
            ['script_version_id' => $scriptVersion->id],
            [
                'build_hash' => $this->builder->buildHashFor($entitlement, $scriptVersion),
                'first_delivered_at' => now(),
            ],
        );

        $this->logDelivery($request, $entitlement, $scriptVersion, DeliveryType::User);

        return $this->javascriptResponse($this->builder->build($entitlement, $scriptVersion));
    }

    /**
     * Löst Token und Slug zu einer aktiven Freischaltung mit aktueller Version auf, sonst 403.
     *
     * @return array{0: Entitlement, 1: ScriptVersion}
     */
    private function resolve(string $token, string $slug): array
    {
        $entitlement = Entitlement::query()
            ->active()
            ->where('token', $token)
            ->whereHas('script', fn ($query) => $query->where('slug', $slug))
            // Gesperrte Nutzer bekommen nichts mehr, auch wenn die Freischaltung selbst aktiv ist.
            ->whereHas('user', fn ($query) => $query->whereNull('blocked_at'))
            ->with(['user', 'script.latestVersion'])
            ->first();

        $scriptVersion = $entitlement?->script->latestVersion;

        abort_if($entitlement === null || $scriptVersion === null, 403);

        return [$entitlement, $scriptVersion];
    }

    private function logDelivery(Request $request, Entitlement $entitlement, ScriptVersion $scriptVersion, DeliveryType $type): void
    {
        $entitlement->deliveries()->create([
            'script_version_id' => $scriptVersion->id,
            'type' => $type,
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }

    private function javascriptResponse(string $content): Response
    {
        // no-store, damit weder Browser noch Proxy eine veraltete Version zwischenspeichern.
        return response($content, 200, [
            'Content-Type' => 'text/javascript; charset=utf-8',
            'Cache-Control' => 'no-store',
        ]);
    }
}
