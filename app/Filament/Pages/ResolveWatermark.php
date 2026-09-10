<?php

namespace App\Filament\Pages;

use App\Services\WatermarkResolver;
use App\Support\WatermarkMatch;
use BackedEnum;
use Filament\Forms\Components\Textarea;
use Filament\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use UnitEnum;

/**
 * Leak-Prüfung: ordnet einen Build-Hash, Token oder Skript-Text dem ursprünglichen Bezieher zu.
 */
class ResolveWatermark extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFingerPrint;

    protected static ?int $navigationSort = 31;

    protected string $view = 'filament.pages.resolve-watermark';

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    /**
     * @var list<array<string, mixed>>
     */
    public array $matches = [];

    public bool $searched = false;

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return __('skriptdepot.nav.forensics');
    }

    public static function getNavigationLabel(): string
    {
        return __('skriptdepot.resolve.navigation');
    }

    public function getTitle(): string|Htmlable
    {
        return __('skriptdepot.resolve.title');
    }

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Textarea::make('input')
                    ->label(__('skriptdepot.resolve.input_label'))
                    ->helperText(__('skriptdepot.resolve.input_help'))
                    ->rows(12)
                    ->required(),
            ])
            ->statePath('data');
    }

    public function resolve(WatermarkResolver $resolver): void
    {
        $input = (string) $this->form->getState()['input'];

        $this->matches = $resolver->resolve($input)
            ->map(fn (WatermarkMatch $match): array => [
                'identifier' => $match->identifier,
                'method' => __("skriptdepot.resolve.methods.{$match->method->value}"),
                'user' => $match->entitlement->user->name,
                'email' => $match->entitlement->user->email,
                'script' => $match->entitlement->script->name,
                'version' => $match->scriptVersion?->version,
                'is_active' => $match->entitlement->is_active,
            ])
            ->all();

        $this->searched = true;
    }
}
