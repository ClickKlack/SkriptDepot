<?php

namespace App\Support;

/**
 * Über welchen Weg ein Treffer zustande kam.
 */
enum WatermarkMatchMethod: string
{
    // Token aus den Update-URLs des Skripts, eindeutigster Weg.
    case Token = 'token';
    // Build-Hash in der Tabelle watermarks gefunden.
    case Watermark = 'watermark';
    // Build-Hash durch Nachrechnen über alle Nutzer-Seeds ermittelt.
    case Computed = 'computed';
}
