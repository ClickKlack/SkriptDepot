<?php

namespace App\Filament\Resources\Entitlements\Pages;

use App\Filament\Resources\Entitlements\EntitlementResource;
use App\Models\Entitlement;
use App\Models\Script;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\EmbeddedTable;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;

/**
 * Alle Nutzer für ein Skript mit Schalter: erstes Einschalten legt die Freischaltung samt Token an,
 * Ausschalten deaktiviert nur. Der Token bleibt erhalten, damit Wasserzeichen und Protokoll auflösbar bleiben.
 *
 * @property Script $record
 */
class ManageEntitlements extends Page implements HasTable
{
    use InteractsWithRecord;
    use InteractsWithTable;

    protected static string $resource = EntitlementResource::class;

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }

    public function getTitle(): string|Htmlable
    {
        return __('skriptdepot.entitlements.title', ['script' => $this->record->name]);
    }

    public function getBreadcrumb(): ?string
    {
        return $this->record->name;
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            EmbeddedTable::make(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder => User::query()
                ->with(['entitlements' => fn ($query) => $query->where('script_id', $this->record->id)]))
            ->columns([
                TextColumn::make('name')
                    ->label(__('skriptdepot.fields.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label(__('skriptdepot.fields.email'))
                    ->searchable(),
                ToggleColumn::make('is_active')
                    ->label(__('skriptdepot.fields.is_active'))
                    ->state(fn (User $record): bool => $this->entitlementFor($record)?->is_active ?? false)
                    ->updateStateUsing(fn (User $record, bool $state) => $this->setActive($record, $state)),
                TextColumn::make('token')
                    ->label(__('skriptdepot.fields.token'))
                    ->state(fn (User $record): ?string => $this->entitlementFor($record)?->token)
                    ->limit(12)
                    ->copyable()
                    ->copyMessage(__('skriptdepot.actions.copied'))
                    ->placeholder('-'),
                TextColumn::make('entitled_since')
                    ->label(__('skriptdepot.fields.entitled_since'))
                    ->state(fn (User $record) => $this->entitlementFor($record)?->created_at)
                    ->dateTime()
                    ->placeholder('-'),
            ])
            ->defaultSort('name')
            ->paginated(false)
            ->recordActions([
                Action::make('install')
                    ->label(__('skriptdepot.actions.open_install_link'))
                    ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                    ->visible(fn (User $record): bool => $this->entitlementFor($record)?->is_active ?? false)
                    ->url(fn (User $record): string => route('scripts.user', [
                        'token' => $this->entitlementFor($record)->token,
                        'slug' => $this->record->slug,
                    ]))
                    ->openUrlInNewTab(),
            ]);
    }

    private function entitlementFor(User $user): ?Entitlement
    {
        return $user->entitlements->first();
    }

    private function setActive(User $user, bool $isActive): bool
    {
        $entitlement = Entitlement::query()
            ->whereBelongsTo($user)
            ->whereBelongsTo($this->record)
            ->first();

        if ($entitlement === null) {
            if ($isActive) {
                Entitlement::create(['user_id' => $user->id, 'script_id' => $this->record->id, 'is_active' => true]);
            }

            return $isActive;
        }

        $entitlement->update(['is_active' => $isActive]);

        return $isActive;
    }
}
