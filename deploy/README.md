# Deployment auf Shared-Hosting (Plesk)

Das Deployment läuft vom Entwicklungsrechner aus per `deploy/deploy.sh`. Auf dem Server werden weder
Composer noch Git gebraucht. Geheimnisse und Pfade liegen ausschließlich in `deploy/deploy.env`
(lokal) und in der `.env` auf dem Server; beide sind vom Git ausgeschlossen.

## Einmalige Einrichtung

### Lokal

1. `deploy/deploy.env.example` nach `deploy/deploy.env` kopieren und SSH-Alias, Zielpfad und
   PHP-Binary eintragen.
2. Der SSH-Zugang muss ohne Passwortabfrage funktionieren (Schlüssel in `~/.ssh/config`).

### In Plesk

1. **Hosting-Einstellungen:** Dokumentroot der Domain auf `<Zielpfad>/public` stellen,
   PHP-Version mindestens 8.4 als FPM.
2. **SSL/TLS-Zertifikate:** Let's-Encrypt-Zertifikat für die Domain ausstellen. Anschließend in den
   Hosting-Einstellungen „Permanente SEO-sichere 301-Weiterleitung von HTTP zu HTTPS" aktivieren.
   Ohne gültiges Zertifikat bricht Tampermonkey den Update-Check ab.
3. **Datenbanken:** Datenbank und Datenbanknutzer anlegen (MariaDB, Zeichensatz utf8mb4).
4. **Mail:** Postfach für die Absenderadresse anlegen, etwa `noreply@<domain>`. SMTP-Host, Port und
   Zugangsdaten notieren.

### Erstes Deployment

1. `deploy/deploy.sh` ausführen. Beim ersten Lauf wird auf dem Server `.env` aus der Vorlage angelegt
   und das Skript hält an.
2. Per SSH `.env` ausfüllen: `APP_URL`, `DB_*`, `MAIL_*`.
3. Auf dem Server: `php artisan key:generate --force`
4. `deploy/deploy.sh` erneut ausführen. Jetzt laufen Migration und Caches.
5. Ersten Administrator anlegen, auf dem Server:
   `php artisan admin:create "Name" admin@example.de`
   Das verschickt die Einladungsmail. Falls Mail noch nicht eingerichtet ist:
   `php artisan admin:create "Name" admin@example.de --password="..."`

## Jedes weitere Deployment

```sh
deploy/deploy.sh
```

Das Skript verweigert uncommittete Änderungen, lässt die Tests laufen, baut aus dem letzten Commit,
synchronisiert per rsync, schaltet kurz in den Wartungsmodus, migriert und erneuert die Caches.
`SKIP_TESTS=1 deploy/deploy.sh` überspringt die Tests.

## Was auf dem Server liegt

- Anwendung im Zielpfad, Dokumentroot ist `public/`.
- `.env` mit allen Geheimnissen, wird vom Deployment nie überschrieben.
- `storage/` mit Logs (täglich rotiert, 14 Tage), Sessions und Cache in der Datenbank.
- Keine Queue-Worker, keine Cronjobs nötig.
