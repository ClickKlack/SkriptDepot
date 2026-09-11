<?php

namespace App\Enums;

/**
 * Stand eines Nutzerkontos, wie er im Admin angezeigt wird.
 */
enum AccountStatus: string
{
    // Noch keine Einladung verschickt.
    case None = 'none';
    // Einladung verschickt, Passwort noch nicht gesetzt.
    case Pending = 'pending';
    // Passwort gesetzt, E-Mail damit bestätigt.
    case Accepted = 'accepted';
    // Vom Admin gesperrt: kein Login, keine Auslieferung.
    case Blocked = 'blocked';
}
