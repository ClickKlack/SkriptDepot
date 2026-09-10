<?php

namespace App\Models;

use Database\Factories\ScriptVersionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use InvalidArgumentException;

#[Fillable(['script_id', 'version', 'source', 'notes'])]
class ScriptVersion extends Model
{
    /** @use HasFactory<ScriptVersionFactory> */
    use HasFactory;

    /**
     * Eine neue Version muss höher als die bisherige sein, sonst erkennt Tampermonkey kein Update.
     * Versionen sind unveränderlich, weil Wasserzeichen und Auslieferungen darauf verweisen.
     */
    protected static function booted(): void
    {
        static::creating(function (ScriptVersion $scriptVersion): void {
            if (! $scriptVersion->script->acceptsVersion($scriptVersion->version)) {
                throw new InvalidArgumentException(
                    "Version {$scriptVersion->version} ist nicht höher als die aktuelle Version des Skripts."
                );
            }
        });

        static::updating(function (ScriptVersion $scriptVersion): void {
            if ($scriptVersion->isDirty(['script_id', 'version', 'source'])) {
                throw new InvalidArgumentException('Quelltext und Versionsnummer einer Version sind unveränderlich.');
            }
        });
    }

    public function script(): BelongsTo
    {
        return $this->belongsTo(Script::class);
    }

    public function watermarks(): HasMany
    {
        return $this->hasMany(Watermark::class);
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(Delivery::class);
    }
}
