<?php

namespace App\Models;

use Database\Factories\WatermarkFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['entitlement_id', 'script_version_id', 'build_hash', 'first_delivered_at'])]
class Watermark extends Model
{
    /** @use HasFactory<WatermarkFactory> */
    use HasFactory;

    // Nur der Zeitpunkt der ersten Auslieferung ist relevant, keine Standard-Timestamps.
    public $timestamps = false;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'first_delivered_at' => 'datetime',
        ];
    }

    public function entitlement(): BelongsTo
    {
        return $this->belongsTo(Entitlement::class);
    }

    public function scriptVersion(): BelongsTo
    {
        return $this->belongsTo(ScriptVersion::class);
    }
}
