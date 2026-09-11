<?php

return [
    // Wählbare Oberflächensprachen; der Schlüssel muss zu lang/{locale} passen.
    'locales' => ['de', 'en'],

    // Gültigkeit des Einladungslinks in Tagen.
    'invitation_valid_days' => 7,

    // Automatische Wasserzeichen: je angefangene lines_per_marker Zeilen ein zusätzlicher Build-Kommentar,
    // begrenzt auf min_markers bis max_markers. Feste Stellen (Header-Kommentar, Zuweisung) kommen dazu.
    'watermark' => [
        'lines_per_marker' => 40,
        'min_markers' => 1,
        'max_markers' => 8,
    ],
];
