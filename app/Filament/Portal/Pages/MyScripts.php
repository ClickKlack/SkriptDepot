<?php

namespace App\Filament\Portal\Pages;

use App\Models\Entitlement;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Collection;

/**
 * Startseite des Portals: alle aktiven Freischaltungen des angemeldeten Nutzers mit Installations-Link.
 */
class MyScripts extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static ?string $slug = 'scripts';

    protected string $view = 'filament.portal.pages.my-scripts';

    public static function getNavigationLabel(): string
    {
        return __('skriptdepot.portal.my_scripts');
    }

    public function getTitle(): string|Htmlable
    {
        return __('skriptdepot.portal.my_scripts');
    }

    /**
     * @return Collection<int, Entitlement>
     */
    public function getEntitlements(): Collection
    {
        return auth()->user()
            ->entitlements()
            ->active()
            ->with('script.latestVersion')
            ->get()
            ->sortBy(fn (Entitlement $entitlement): string => $entitlement->script->name)
            ->values();
    }
}
