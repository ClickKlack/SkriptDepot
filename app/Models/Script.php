<?php

namespace App\Models;

use Database\Factories\ScriptFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['slug', 'name', 'description'])]
class Script extends Model
{
    /** @use HasFactory<ScriptFactory> */
    use HasFactory;

    public function versions(): HasMany
    {
        return $this->hasMany(ScriptVersion::class);
    }

    /**
     * Die aktuelle Version ist die zuletzt angelegte; beim Anlegen wird erzwungen, dass sie höher ist.
     */
    public function latestVersion(): HasOne
    {
        return $this->hasOne(ScriptVersion::class)->latestOfMany();
    }

    public function entitlements(): HasMany
    {
        return $this->hasMany(Entitlement::class);
    }

    /**
     * Prüft, ob eine Versionsnummer höher als die aktuelle Version dieses Skripts ist.
     */
    public function acceptsVersion(string $version): bool
    {
        $latest = $this->versions()->latest('id')->value('version');

        return $latest === null || version_compare($version, $latest, '>');
    }
}
