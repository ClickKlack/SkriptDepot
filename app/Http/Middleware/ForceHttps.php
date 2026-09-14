<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Leitet produktiv jede unverschlüsselte Anfrage dauerhaft auf HTTPS um.
 *
 * Sicherheitsnetz unabhängig von der Hosting-Konfiguration: Tokens stehen in der URL und dürfen
 * nie im Klartext über die Leitung gehen.
 */
class ForceHttps
{
    public function handle(Request $request, Closure $next): Response
    {
        if (app()->isProduction() && ! $request->secure()) {
            return redirect()->to('https://'.$request->getHttpHost().$request->getRequestUri(), 301);
        }

        return $next($request);
    }
}
