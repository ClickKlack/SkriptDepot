<?php

namespace App\Enums;

/**
 * Stand der Einladung eines Nutzers.
 */
enum InvitationStatus: string
{
    // Noch keine Einladung verschickt.
    case None = 'none';
    // Einladung verschickt, Passwort noch nicht gesetzt.
    case Pending = 'pending';
    // Passwort gesetzt, E-Mail damit bestätigt.
    case Accepted = 'accepted';
}
