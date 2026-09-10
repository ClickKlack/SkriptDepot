<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'is_admin', 'locale'])]
#[Hidden(['password', 'remember_token', 'seed'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Der Seed ist geheim und unveränderlich; er wird genau einmal beim Anlegen erzeugt.
     */
    protected static function booted(): void
    {
        static::creating(function (User $user): void {
            $user->seed ??= bin2hex(random_bytes(32));
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }

    /**
     * Das Admin-Panel ist Administratoren vorbehalten, das Portal steht jedem Nutzer offen.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $panel->getId() !== 'admin' || $this->is_admin;
    }

    public function entitlements(): HasMany
    {
        return $this->hasMany(Entitlement::class);
    }
}
