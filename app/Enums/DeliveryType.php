<?php

namespace App\Enums;

/**
 * Art einer Auslieferung: nur der Metadaten-Block oder das vollständige Skript.
 */
enum DeliveryType: string
{
    case Meta = 'meta';
    case User = 'user';
}
