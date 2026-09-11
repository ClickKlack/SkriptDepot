<?php

namespace App\Models;

use App\Enums\AccountStatus;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Contracts\Translation\HasLocalePreference;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use LogicException;

#[Fillable(['name', 'email', 'password', 'is_admin', 'locale'])]
#[Hidden(['password', 'remember_token', 'seed'])]
class User extends Authenticatable implements FilamentUser, HasLocalePreference
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Der Seed ist geheim und unveränderlich; er wird genau einmal beim Anlegen erzeugt.
     * Ohne Passwort erhält ein neuer Nutzer ein unbrauchbares Zufallspasswort, bis er die Einladung annimmt.
     */
    protected static function booted(): void
    {
        static::creating(function (User $user): void {
            $user->seed ??= bin2hex(random_bytes(32));
            $user->password ??= Str::random(64);
        });

        // Löschen nur, solange nichts ausgeliefert wurde; dann sind auch die Freischaltungen entbehrlich.
        static::deleting(function (User $user): void {
            if ($user->hasDeliveryHistory()) {
                throw new LogicException('Nutzer mit Auslieferungen oder Wasserzeichen dürfen nicht gelöscht werden.');
            }

            $user->entitlements()->delete();
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'invited_at' => 'datetime',
            'blocked_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }

    /**
     * Zugang gibt es erst nach angenommener Einladung und nie für Gesperrte;
     * das Admin-Panel zusätzlich nur für Administratoren.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        if (! $this->hasVerifiedEmail() || $this->isBlocked()) {
            return false;
        }

        return $panel->getId() !== 'admin' || $this->is_admin;
    }

    public function preferredLocale(): string
    {
        return $this->locale;
    }

    /**
     * Gab es je eine Auslieferung oder ein Wasserzeichen? Dann muss der Nutzer für die Forensik erhalten bleiben.
     */
    public function hasDeliveryHistory(): bool
    {
        return $this->entitlements()
            ->where(fn ($query) => $query->whereHas('deliveries')->orWhereHas('watermarks'))
            ->exists();
    }

    public function isBlocked(): bool
    {
        return $this->blocked_at !== null;
    }

    public function accountStatus(): AccountStatus
    {
        if ($this->isBlocked()) {
            return AccountStatus::Blocked;
        }

        if ($this->hasVerifiedEmail()) {
            return AccountStatus::Accepted;
        }

        return $this->invited_at === null ? AccountStatus::None : AccountStatus::Pending;
    }

    public function entitlements(): HasMany
    {
        return $this->hasMany(Entitlement::class);
    }
}
