<?php

namespace App\Models;

use App\Enums\DeliveryType;
use Database\Factories\DeliveryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['entitlement_id', 'script_version_id', 'type', 'ip', 'user_agent'])]
class Delivery extends Model
{
    /** @use HasFactory<DeliveryFactory> */
    use HasFactory;

    // Ein Protokolleintrag wird nie geändert, deshalb gibt es nur created_at.
    public const null UPDATED_AT = null;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => DeliveryType::class,
            'created_at' => 'datetime',
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
