<?php

namespace App\Providers\Filament;

use App\Filament\Auth\EditProfile;
use App\Filament\Auth\RequestPasswordReset;
use App\Filament\Portal\Pages\Auth\AcceptInvitation;
use App\Filament\Portal\Pages\MyScripts;
use App\Http\Middleware\SetLocale;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;
use Illuminate\View\Middleware\ShareErrorsFromSession;

/**
 * Nutzer-Portal: Standard-Panel, zeigt jedem angemeldeten Nutzer seine freigeschalteten Skripte.
 */
class PortalPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('portal')
            ->path('portal')
            ->brandName(fn (): string => __('skriptdepot.brand'))
            ->login()
            ->passwordReset(RequestPasswordReset::class)
            // Eine geänderte E-Mail-Adresse gilt erst nach Bestätigung über die neue Adresse.
            ->emailChangeVerification()
            // Profil im normalen Panel-Layout, damit Navigation und Nutzermenü erreichbar bleiben.
            ->profile(EditProfile::class, isSimple: false)
            ->colors([
                'primary' => Color::Sky,
            ])
            ->pages([
                MyScripts::class,
            ])
            // Gast-Route für die Einladung: nur mit gültiger Signatur erreichbar, kein Login nötig.
            ->routes(function (): void {
                Route::get('/invitation/{user}', AcceptInvitation::class)
                    ->middleware('signed')
                    ->name('invitation.accept');
            })
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                SetLocale::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
