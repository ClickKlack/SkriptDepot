#!/usr/bin/env bash
#
# Deployment auf Shared-Hosting (Plesk, ohne Composer auf dem Server).
#
# Ablauf: Tests lokal -> Build aus dem letzten Commit ohne Dev-Pakete -> rsync auf den Server
#         -> Migration und Caches auf dem Server. Die .env auf dem Server wird nie angefasst.
#
# Zugangsdaten und Pfade kommen ausschließlich aus deploy/deploy.env (nicht im Git).
#
set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
CONFIG="$ROOT/deploy/deploy.env"
BUILD="$ROOT/deploy/build"

if [[ ! -f "$CONFIG" ]]; then
    echo "Fehlt: deploy/deploy.env (Vorlage: deploy/deploy.env.example)" >&2
    exit 1
fi

# shellcheck source=/dev/null
source "$CONFIG"
: "${DEPLOY_SSH_HOST:?DEPLOY_SSH_HOST fehlt in deploy/deploy.env}"
: "${DEPLOY_REMOTE_PATH:?DEPLOY_REMOTE_PATH fehlt in deploy/deploy.env}"
DEPLOY_PHP="${DEPLOY_PHP:-php}"

log() { printf '\n\033[1;34m== %s\033[0m\n' "$*"; }

# 1. Nur committete, getestete Stände verlassen den Rechner.
if [[ -n "$(git -C "$ROOT" status --porcelain)" ]]; then
    echo "Das Arbeitsverzeichnis hat uncommittete Änderungen. Bitte erst committen." >&2
    exit 1
fi

if [[ "${SKIP_TESTS:-0}" != "1" ]]; then
    log "Tests"
    (cd "$ROOT" && php artisan test --compact)
fi

# 2. Build: sauberer Export des letzten Commits plus Produktiv-Abhängigkeiten.
log "Build aus $(git -C "$ROOT" rev-parse --short HEAD)"
rm -rf "$BUILD"
mkdir -p "$BUILD"
git -C "$ROOT" archive --format=tar HEAD | tar -x -C "$BUILD"
(cd "$BUILD" && composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader --classmap-authoritative --quiet)

# 3. Dateien synchronisieren. Ausgenommen: Geheimnisse, Laufzeitdaten, Entwicklungs- und Dokumentationsdateien.
log "Synchronisieren nach $DEPLOY_SSH_HOST:$DEPLOY_REMOTE_PATH"
RSYNC_EXCLUDES=(
    --exclude '.env' --exclude '.env.*'
    --exclude 'storage/' --exclude 'bootstrap/cache/*'
    --exclude 'tests/' --exclude 'Spec/' --exclude 'deploy/'
    --exclude '.claude/' --exclude '.mcp.json' --exclude 'boost.json'
    --exclude 'AGENTS.md' --exclude 'CLAUDE.md' --exclude 'phpunit.xml'
    --exclude '.git*' --exclude 'node_modules/'
)
rsync -az --delete "${RSYNC_EXCLUDES[@]}" "$BUILD/" "$DEPLOY_SSH_HOST:$DEPLOY_REMOTE_PATH/"
# Verzeichnisstruktur unter storage einmalig anlegen, vorhandene Dateien nie überschreiben.
rsync -az --ignore-existing "$BUILD/storage/" "$DEPLOY_SSH_HOST:$DEPLOY_REMOTE_PATH/storage/"
scp -q "$ROOT/deploy/env.production.example" "$DEPLOY_SSH_HOST:$DEPLOY_REMOTE_PATH/.env.production.example"

# 4. Auf dem Server: Wartungsmodus, Migration, Caches.
log "Auf dem Server"
ssh "$DEPLOY_SSH_HOST" "DEPLOY_PHP='$DEPLOY_PHP' REMOTE_PATH='$DEPLOY_REMOTE_PATH' bash -s" <<'REMOTE'
set -euo pipefail
cd "$REMOTE_PATH"

if [[ ! -f .env ]]; then
    cp .env.production.example .env
    echo
    echo "Erstes Deployment: .env wurde aus der Vorlage angelegt, ist aber noch leer."
    echo "Bitte per SSH ausfüllen (Datenbank, Mail, APP_URL), danach:"
    echo "  cd $REMOTE_PATH && $DEPLOY_PHP artisan key:generate --force"
    echo "und das Deployment erneut starten."
    exit 0
fi

if ! grep -qE '^APP_KEY=base64:' .env; then
    echo "APP_KEY fehlt in .env. Bitte ausführen: $DEPLOY_PHP artisan key:generate --force" >&2
    exit 1
fi

"$DEPLOY_PHP" artisan down --retry=10 || true
"$DEPLOY_PHP" artisan package:discover --ansi
"$DEPLOY_PHP" artisan migrate --force
"$DEPLOY_PHP" artisan optimize
"$DEPLOY_PHP" artisan up
echo "Fertig: $("$DEPLOY_PHP" artisan --version)"
REMOTE
