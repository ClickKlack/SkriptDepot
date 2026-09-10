<?php

return [
    'brand' => 'SkriptDepot',

    'nav' => [
        'administration' => 'Verwaltung',
        'scripts' => 'Skripte',
        'forensics' => 'Forensik',
    ],

    'resources' => [
        'user' => ['label' => 'Nutzer', 'plural' => 'Nutzer'],
        'script' => ['label' => 'Skript', 'plural' => 'Skripte'],
        'script_version' => ['label' => 'Skriptversion', 'plural' => 'Skriptversionen'],
        'entitlement' => ['label' => 'Freischaltung', 'plural' => 'Freischaltungen'],
        'delivery' => ['label' => 'Auslieferung', 'plural' => 'Auslieferungen'],
    ],

    'fields' => [
        'name' => 'Name',
        'email' => 'E-Mail-Adresse',
        'password' => 'Passwort',
        'is_admin' => 'Administrator',
        'locale' => 'Sprache',
        'slug' => 'Slug',
        'description' => 'Beschreibung',
        'version' => 'Version',
        'source' => 'Master-Quelltext',
        'notes' => 'Notizen',
        'script' => 'Skript',
        'user' => 'Nutzer',
        'token' => 'Token',
        'is_active' => 'Aktiv',
        'type' => 'Typ',
        'ip' => 'IP-Adresse',
        'user_agent' => 'User-Agent',
        'created_at' => 'Angelegt am',
        'build_hash' => 'Build-Hash',
        'latest_version' => 'Aktuelle Version',
        'versions_count' => 'Versionen',
        'entitlements_count' => 'Freischaltungen',
        'install_url' => 'Installations-Link',
    ],

    'hints' => [
        'password_edit' => 'Leer lassen, um das Passwort beizubehalten.',
        'slug' => 'Nur Kleinbuchstaben, Ziffern und Bindestriche; Teil der Auslieferungs-URL.',
        'source' => 'Muss die Platzhalter {{VERSION}}, {{BASE}}, {{TOKEN}}, {{SLUG}} und mindestens zweimal {{BUILD}} enthalten.',
        'version_immutable' => 'Quelltext und Versionsnummer sind nach dem Anlegen unveränderlich. Für Änderungen eine neue, höhere Version anlegen.',
        'entitlement_immutable' => 'Nutzer und Skript sind nach dem Anlegen fest. Der Widerruf erfolgt über „Aktiv".',
    ],

    'locales' => [
        'de' => 'Deutsch',
        'en' => 'English',
    ],

    'delivery_types' => [
        'meta' => 'Metadaten',
        'user' => 'Skript',
    ],

    'actions' => [
        'open_install_link' => 'Installations-Link öffnen',
        'copy_install_link' => 'Installations-Link kopieren',
        'copied' => 'Kopiert',
    ],

    'validation' => [
        'version_not_higher' => 'Die Version muss höher sein als die aktuelle Version :latest.',
        'entitlement_exists' => 'Dieser Nutzer ist für dieses Skript bereits freigeschaltet.',
        'master_script' => [
            'header' => 'Der Quelltext enthält keinen // ==UserScript== … // ==/UserScript== Block.',
            'version' => 'Im Header muss „@version {{VERSION}}" stehen.',
            'update_url' => 'Im Header muss „@updateURL {{BASE}}/s/{{TOKEN}}/{{SLUG}}.meta.js" stehen.',
            'download_url' => 'Im Header muss „@downloadURL {{BASE}}/s/{{TOKEN}}/{{SLUG}}.user.js" stehen.',
            'build' => 'Der Platzhalter {{BUILD}} muss mindestens zweimal vorkommen.',
        ],
    ],

    'resolve' => [
        'title' => 'Wasserzeichen auflösen',
        'navigation' => 'Wasserzeichen auflösen',
        'input_label' => 'Build-Hash, Token oder kompletter Skript-Text',
        'input_help' => 'Einen 8-stelligen Build-Hash eingeben oder den gesamten Inhalt einer geleakten Skript-Datei einfügen.',
        'submit' => 'Auflösen',
        'results' => 'Ergebnis',
        'no_match' => 'Kein Treffer. Weder ein bekannter Build-Hash noch ein Token wurde gefunden.',
        'identifier' => 'Merkmal',
        'method' => 'Methode',
        'methods' => [
            'token' => 'Token aus Update-URL',
            'watermark' => 'Wasserzeichen-Tabelle',
            'computed' => 'Nachgerechnet',
        ],
        'active' => 'Freischaltung aktiv',
        'inactive' => 'Freischaltung widerrufen',
    ],

    'portal' => [
        'my_scripts' => 'Meine Skripte',
        'install' => 'Installieren',
        'version' => 'Version :version',
        'no_version' => 'Noch keine Version veröffentlicht.',
        'empty' => 'Für dich sind derzeit keine Skripte freigeschaltet.',
        'hint_title' => 'Hinweis zur Weitergabe',
        'hint' => 'Jede Kopie ist an dein Konto gebunden und enthält ein individuelles Wasserzeichen. Eine Weitergabe an Dritte ist nachverfolgbar.',
    ],
];
