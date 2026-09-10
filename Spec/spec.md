# Projektbeschreibung: Userscript-Portal mit Auth, Update-Prozess und Wasserzeichen

> Spezifikation für eine KI-gestützte Umsetzung. Umsetzung bitte gegen die
> Akzeptanzkriterien am Ende prüfen. Wo „ANNAHME" steht, ist eine Entscheidung
> vorbelegt, die vor Umsetzung bestätigt oder geändert werden kann.

## 1. Ziel

Ein internes Portal, über das Tampermonkey-/Userscripts an einen kleinen,
festen Nutzerkreis ausgegeben werden. Drei Kernfunktionen:

1. **Authentifizierung + individuelle Freischaltung**: Jeder Nutzer sieht und
   erhält nur die Skripte, für die er freigeschaltet ist.
2. **Update-Prozess**: Installierte Skripte aktualisieren sich automatisch über
   Tampermonkeys Update-Mechanismus, solange die Freischaltung besteht.
3. **Wasserzeichen (Einzeltäter-Tracing)**: Jede ausgelieferte Kopie ist pro
   Nutzer eindeutig markiert. Taucht ein Skript bei jemandem auf, der es nicht
   von uns bezogen hat, lässt sich der ursprüngliche Bezieher bestimmen.

## 2. Rahmenbedingungen

- **Stack**: PHP 8.3+, Laravel 11, Filament 3 (Admin-Backend), MariaDB.
- **Größenordnung**: ~5 Skripte pro Jahr, ~5 Nutzer. Bewusst klein — keine
  Skalierungs-, Cache- oder Queue-Architektur nötig.
- **ANNAHME**: Eigenständige kleine Laravel-App (nicht an ein bestehendes
  Projekt angehängt). Domain z. B. `portal.example.de`.
- Auslieferung ausschließlich über HTTPS.

## 3. Fachliche Kernkonzepte (wichtig — bitte nicht wegoptimieren)

### 3.1 Auth läuft über Token in der URL, nicht über Header

Tampermonkey führt Update-Checks **selbst im Hintergrund** aus. Diese Requests
tragen **keine von uns setzbaren HTTP-Header und keine Session-Cookies** des
Browsers. Klassische Header-Authentifizierung ist für den Update-Weg daher
**nicht möglich**.

Konsequenz: **Das Credential steckt in der URL.** Jeder Nutzer bekommt pro
Skript personalisierte `@updateURL`/`@downloadURL`, die einen opaken,
zufälligen Token enthalten. Der Server validiert diesen Token und die
Freischaltung. Der Token wird in die installierte Kopie eingebacken.

### 3.2 Tampermonkey-Update-Mechanik

- `@version` ist Pflicht, sonst findet **kein** Update-Check statt. Für ein
  Update muss die Versionsnummer der neuen Datei höher sein als die installierte.
- `@updateURL` → schlanke `.meta.js` (nur der Metadaten-Block). Wird häufig
  abgerufen; dient nur dem Versionsvergleich.
- `@downloadURL` → volle `.user.js`. Wird nur geladen, wenn ein Update erkannt
  wurde.
- Eine URL, die auf `.user.js` endet und mit Content-Type `text/javascript`
  (bzw. `application/javascript`) ausgeliefert wird, löst in Tampermonkey den
  Installations-Dialog aus.

### 3.3 Widerruf (Revocation)

Freischaltung entziehen = Token in der DB deaktivieren → Update-/Download-Route
antwortet mit **403**. Das stoppt künftige Updates und Downloads.

**Wichtig und bewusst akzeptiert**: Eine bereits installierte Kopie läuft
weiter. Es gibt **keinen Kill-Switch** und **kein Phone-Home** — das Skript
deaktiviert sich nicht selbst. Das ist so gewollt (siehe Nicht-Ziele).

### 3.4 Wasserzeichen = Abschreckung + Forensik, nicht Prävention

Die Skripte sind **reine Client-/DOM-Manipulation**. Der gesamte Wert liegt im
ausgelieferten Klartext-JavaScript. Kopieren lässt sich technisch **nicht
verhindern**. Ziel ist ausschließlich **Rückverfolgbarkeit** eines einzelnen
Weitergebenden.

Umsetzung als **String-Injektion** (kein AST-Rewriting, keine Obfuskation):

- Jeder Nutzer hat einen stabilen, geheimen `seed` (Zufallswert in der DB).
- Pro Auslieferung wird ein **opaker Build-Hash** berechnet, der wie eine
  harmlose Build-/Cache-Kennung aussieht, z. B.
  `substr(hash('sha256', seed . '|' . script_id . '|' . version), 0, 8)`.
  → 8 Hex-Zeichen, deterministisch, pro (Nutzer, Skript, Version) eindeutig.
- Der Hash wird an **mehreren unauffälligen Stellen** injiziert (Redundanz gegen
  versehentliches Abschneiden, nicht gegen gezieltes Entfernen):
  1. als Kommentar im Header-Bereich, z. B. `// build: a3f9c1e2`
  2. als scheinbar funktionale Konstante, die auch tatsächlich (harmlos)
     verwendet wird, z. B. in einem Log-Präfix oder Cache-Key
  3. implizit über den Token in `@updateURL`/`@downloadURL`
- Die Zuordnung `build_hash → Nutzer` wird bei Auslieferung in der DB
  festgehalten (Tabelle `watermarks`), zusätzlich ist sie durch Nachrechnen
  über die 5 Nutzer jederzeit reproduzierbar.

**Determinismus ist Pflicht**: Der Hash darf **nicht** pro Request neu gewürfelt
werden. Gleicher (Nutzer, Skript, Version) muss immer denselben Output ergeben —
sonst ist weder eindeutige Zuordnung noch stabiles Tampermonkey-Update möglich
(wechselnder Inhalt bei gleicher Version verwirrt den Updater).

**Die User-ID selbst niemals im Klartext einbetten** (kein `const USER = 5`).
Nur der opake Hash.

## 4. Nicht-Ziele (bewusst ausgeklammert — bitte nicht bauen)

- Kein serverseitiges Verstecken der Skript-Logik (nicht möglich, da reines DOM).
- Kein Schutz gegen Kollusion (mehrere Nutzer diffen ihre Kopien). Nur
  Einzeltäter-Tracing.
- Kein Kill-Switch / kein Selbst-Deaktivieren / kein Phone-Home zur Laufzeit.
- Keine Obfuskation, kein Minifier, kein Node-Build-Step. Skripte bleiben lesbar.
- Keine öffentliche Registrierung, kein Self-Service-Signup. Nutzer werden vom
  Admin angelegt.

## 5. Datenmodell

| Tabelle        | Felder (Kern)                                                                 |
|----------------|-------------------------------------------------------------------------------|
| `users`        | Standard-Auth-Felder; zusätzlich `seed` (string, zufällig, geheim, immutable) |
| `scripts`      | `id`, `slug` (url-safe, z. B. `foo`), `name`, `description`                    |
| `script_versions` | `id`, `script_id`, `version` (semver-artig, string), `source` (das Master-Skript mit Platzhaltern), `notes`, `created_at` |
| `entitlements` | `id`, `user_id`, `script_id`, `token` (opak, zufällig, unique), `is_active` (bool), `created_at` |
| `watermarks`   | `id`, `entitlement_id`, `script_version_id`, `build_hash`, `first_delivered_at` |
| `deliveries`   | `id`, `entitlement_id`, `script_version_id`, `type` (`meta`\|`user`), `ip`, `user_agent`, `created_at` |

Hinweise:
- `entitlements`: eine Zeile pro (Nutzer × Skript). Der `token` identifiziert
  diese Zeile eindeutig; darüber sind Nutzer, Skript und Aktiv-Status auflösbar.
- „Aktuelle Version" eines Skripts = höchste `version` in `script_versions`.
- `watermarks` wird bei erster Auslieferung einer (Entitlement, Version)-Kombi
  per `firstOrCreate` befüllt.

## 6. Master-Skript: Platzhalter-Schema

Das Master-Skript wird pro Version in `script_versions.source` gehalten und
enthält definierte Platzhalter. Die Injektion beim Ausliefern ist ein einfaches
`str_replace`.

Platzhalter:

- `{{VERSION}}` → Versionsnummer
- `{{BASE}}` → Basis-URL des Portals (aus Config)
- `{{TOKEN}}` → Entitlement-Token des Nutzers
- `{{SLUG}}` → Skript-Slug
- `{{BUILD}}` → Build-Hash (Wasserzeichen)

Beispiel-Kopf eines Master-Skripts:

```javascript
// ==UserScript==
// @name         Beispiel-Skript
// @namespace    portal.example.de
// @version      {{VERSION}}
// @description  ...
// @match        https://ziel-seite.de/*
// @run-at       document-idle
// @updateURL    {{BASE}}/s/{{TOKEN}}/{{SLUG}}.meta.js
// @downloadURL  {{BASE}}/s/{{TOKEN}}/{{SLUG}}.user.js
// ==/UserScript==
/* build: {{BUILD}} */
(function () {
  'use strict';
  const BUILD = '{{BUILD}}'; // wird real verwendet, z. B. im Log-Präfix
  const log = (...a) => console.debug(`[${BUILD}]`, ...a);
  // ... eigentliche Skript-Logik ...
})();
```

Die `.meta.js` besteht **nur** aus dem `==UserScript==`-Block (inkl. bereits
ersetztem `{{VERSION}}` und den personalisierten URLs).

## 7. Funktionale Anforderungen

### 7.1 Auslieferungs-Routen (öffentlich, Auth über Token)

- `GET /s/{token}/{slug}.meta.js`
  → Token + Skript auflösen; `is_active` prüfen. Ungültig/inaktiv → **403**.
  → Nur Metadaten-Block der aktuellen Version, Platzhalter ersetzt.
  → Content-Type `text/javascript`. Delivery mit `type=meta` loggen.
- `GET /s/{token}/{slug}.user.js`
  → wie oben, aber vollständiges Skript inkl. Wasserzeichen.
  → `watermarks`-Eintrag sicherstellen (`firstOrCreate`).
  → Content-Type `text/javascript`. Delivery mit `type=user` loggen.

Ein zentraler **Build-Service** (`app/Services/ScriptBuilder.php` o. Ä.) kapselt:
Version laden → Build-Hash berechnen → Platzhalter ersetzen → String zurückgeben.
Von beiden Routen genutzt.

### 7.2 Nutzer-Bereich (login-geschützt)

- Login (Laravel-Standard-Auth, keine Registrierung).
- Übersichtsseite: Liste der freigeschalteten Skripte des Nutzers, je Skript ein
  **Installations-Link** (`.user.js`-URL mit dem eigenen Token). Klick öffnet
  den Tampermonkey-Installationsdialog.
- Kurzhinweis, dass die Skripte an den Nutzer gebunden und Weitergabe
  nachverfolgbar ist.

### 7.3 Admin-Backend (Filament 3)

- **Users**: anlegen/bearbeiten; `seed` wird beim Anlegen automatisch erzeugt und
  ist nicht editierbar.
- **Scripts**: CRUD auf Skripte (Slug, Name, Beschreibung).
- **ScriptVersions**: neue Version zu einem Skript anlegen (Master-Quelltext als
  Textarea/Code-Feld, Versionsnummer, Notizen). Anlegen einer höheren Version =
  Rollout des Updates.
- **Entitlements**: Nutzer × Skript freischalten (Token wird automatisch
  generiert), aktiv/inaktiv schalten (= Widerruf).
- **Deliveries**: read-only Log-Ansicht (wer, wann, welche Version, welcher Typ,
  IP, User-Agent).
- **Leak-Prüfung**: eine Aktion/Seite „Wasserzeichen auflösen", die einen
  eingegebenen `build_hash` (oder einen eingefügten Skript-Text, aus dem der Hash
  extrahiert wird) dem Nutzer + Skript + Version zuordnet. Auflösung primär über
  `watermarks`-Tabelle, Fallback über Nachrechnen aller Nutzer-Seeds.

### 7.4 Leak-Erkennung (Kommando, optional zusätzlich zur Filament-Aktion)

Ein Artisan-Command `wm:identify {hash}` bzw. `wm:identify --file=pfad`, das den
Build-Hash aus einer Datei liest und den zugehörigen Nutzer ausgibt.

## 8. Sicherheit / Robustheit

- Tokens und Seeds kryptografisch zufällig (`Str::random(40)` bzw. `random_bytes`).
- Alle Auslieferungs-Routen ausschließlich HTTPS.
- 403 statt 404 bei inaktivem Token ist ok; kein Enumerieren gültiger Tokens
  ermöglichen (keine unterscheidbaren Fehlermeldungen zwischen „Token existiert
  nicht" und „Token inaktiv").
- Rate-Limiting auf den Auslieferungs-Routen (großzügig; dient nur dem
  Missbrauchsschutz, Update-Checks sind selten).

## 9. Akzeptanzkriterien

1. Ein freigeschalteter Nutzer kann sein Skript über den Installations-Link in
   Tampermonkey installieren.
2. Nach Anlegen einer höheren Version erkennt Tampermonkey beim Update-Check das
   Update und lädt die neue `.user.js`.
3. Zwei verschiedene Nutzer erhalten für dasselbe Skript+Version **unterschiedliche**
   `{{BUILD}}`-Werte; derselbe Nutzer erhält bei wiederholtem Abruf denselben Wert.
4. Wird ein Entitlement auf inaktiv gesetzt, liefern `.meta.js` und `.user.js`
   für diesen Token **403**; ein zuvor installiertes Skript erhält keine Updates
   mehr.
5. Aus einer geleakten Skript-Datei (bzw. deren `build_hash`) bestimmt die
   Leak-Prüfung korrekt den ursprünglichen Nutzer.
6. Jeder Abruf von `.meta.js`/`.user.js` erzeugt einen `deliveries`-Eintrag.
7. Die installierte Datei enthält an keiner Stelle eine klартext-lesbare
   Nutzer-ID — nur den opaken Build-Hash.

## 10. Offene Entscheidungen (vor/bei Umsetzung klären)

- **Deployment-Ziel**: eigenständige App (ANNAHME) oder Integration in eine
  bestehende Anwendung.
- **Exakte Injektionsstellen** des Build-Hashes im Skript-Body — abhängig von der
  Struktur der realen Skripte; Schema aus Abschnitt 6 ist der Startvorschlag.
- **Versionsschema** (`1.0.0` vs. einfacher Zähler) — für Tampermonkey egal,
  solange monoton steigend.