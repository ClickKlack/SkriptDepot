# SkriptDepot

Internes Portal zur Ausgabe von Tampermonkey-Userscripts an einen kleinen, festen Nutzerkreis.

Jeder Nutzer sieht und erhält nur die Skripte, für die er freigeschaltet ist. Installierte Skripte
aktualisieren sich über den Update-Mechanismus von Tampermonkey, solange die Freischaltung besteht.
Jede ausgelieferte Kopie trägt ein nutzerspezifisches Wasserzeichen, sodass sich bei einer Weitergabe
der ursprüngliche Bezieher bestimmen lässt.

Die vollständige fachliche Spezifikation liegt in [`Spec/spec.md`](Spec/spec.md).

## Funktionsweise

- **Auth über Token in der URL.** Tampermonkey führt Update-Checks ohne Cookies oder eigene Header aus.
  Deshalb erhält jeder Nutzer pro Skript personalisierte Update- und Download-URLs mit einem opaken Token.
- **Auslieferungsrouten.** `GET /s/{token}/{slug}.meta.js` liefert nur den Metadaten-Block für den
  Versionsvergleich, `GET /s/{token}/{slug}.user.js` das vollständige Skript. Ungültige oder deaktivierte
  Tokens erhalten einheitlich `403`.
- **Widerruf.** Freischaltung deaktivieren genügt. Bereits installierte Kopien laufen bewusst weiter,
  erhalten aber keine Updates mehr.
- **Wasserzeichen.** Pro Nutzer, Skript und Version wird ein deterministischer Build-Hash berechnet
  und an mehreren Stellen in das Skript eingefügt. Die Zuordnung wird bei der Auslieferung gespeichert
  und ist zusätzlich jederzeit nachrechenbar.
- **Master-Skripte mit Platzhaltern.** `{{VERSION}}`, `{{BASE}}`, `{{TOKEN}}`, `{{SLUG}}` und `{{BUILD}}`
  werden bei der Auslieferung per String-Ersetzung befüllt.
- **Automatische Wasserzeichen.** Ein rohes Userscript, wie Tampermonkey es exportiert, wird beim Anlegen
  einer Version automatisch vorbereitet: Header-Platzhalter, ein Build-Kommentar nach dem Header, eine
  harmlose Zuweisung am Anfang und je nach Länge weitere Build-Kommentare an Anweisungsgrenzen. Die
  Grenzen liefert ein JavaScript-Parser, damit kein Marker in Strings oder Kommentaren landet. Der Code
  selbst wird nicht umgeschrieben.

## Stack

- PHP 8.4 oder neuer, Laravel 13
- Filament 5 mit zwei Panels: `/admin` für die Verwaltung, `/portal` für Nutzer
- MariaDB für Entwicklung und Produktion, SQLite im Speicher für Tests
- Pest als Testframework

## Lokale Einrichtung

Voraussetzungen: PHP 8.4+, Composer, ein laufender MariaDB-Server.

```sh
composer install
cp .env.example .env
php artisan key:generate
```

In der `.env` die Datenbankzugangsdaten eintragen, dann:

```sh
php artisan migrate --seed
php artisan serve
```

Der Seeder legt einen Administrator (`admin@example.test`), eine Nutzerin (`erika@example.test`),
jeweils mit Passwort `geheim123`, sowie ein Beispielskript mit Freischaltung an. Die Installations-URL
des Beispielskripts steht nach dem Login im Portal.

## Tests

```sh
php artisan test --compact
```

Die Tests laufen gegen eine SQLite-Datenbank im Speicher und benötigen keinen Datenbankserver.

## Deployment

Zielplattform ist klassisches Shared-Hosting. Die Anwendung kommt ohne Queue-Worker, Redis oder
Node-Build-Schritte aus. Die Auslieferungsrouten dürfen ausschließlich über HTTPS erreichbar sein.
Bei Betrieb hinter einem Reverse-Proxy müssen die Trusted Proxies konfiguriert sein, damit im
Auslieferungsprotokoll die echten Client-Adressen landen.

## Lizenz

MIT, siehe [`LICENSE`](LICENSE).
