#!/bin/sh
# Unattended deploy, run ON the server by GitHub Actions over SSH
# (see .github/workflows/deploy-staging.yml and deploy-production.yml).
#
# It force-syncs the server's checkout to the latest commit of its tracked
# branch (main on staging, release/prod on production), brings the Docker stack
# up, and restores the committed DB snapshot. Media rides along in git
# (wp-content/uploads/ is tracked — see docs/adr/0004), so `git reset` alone
# restores working images; no rsync needed. Idempotent and safe to re-run.
#
# Manual use on the server:
#   LSC_REPO_DIR=/opt/lsc-wordpress sh /opt/lsc-wordpress/wordpress/_build/deploy.sh
set -e

REPO_DIR="${LSC_REPO_DIR:-/opt/lsc-wordpress}"
COMPOSE="docker compose -f docker-compose.yml -f docker-compose.prod.yml"
WPCLI_RUN="$COMPOSE run --rm --entrypoint"

cd "$REPO_DIR"

echo "[deploy] Backing up the current database (rollback safety net)..."
if $WPCLI_RUN wp wpcli core is-installed --path=/var/www/html 2>/dev/null; then
  $WPCLI_RUN wp wpcli db export - --path=/var/www/html > "backup-$(date +%F-%H%M%S).sql"
  echo "[deploy]   saved backup-$(date +%F-%H%M%S).sql"
else
  echo "[deploy]   no installed DB yet — first deploy, skipping backup."
fi

echo "[deploy] Fetching and force-syncing to latest commit on tracked branch..."
git fetch --prune origin
git reset --hard "@{u}"
echo "[deploy]   now at $(git rev-parse --short HEAD) on $(git rev-parse --abbrev-ref HEAD)"

echo "[deploy] Bringing the stack up..."
$COMPOSE up -d

echo "[deploy] Waiting for WordPress core to be installed/ready..."
i=0
until $WPCLI_RUN wp wpcli core is-installed --path=/var/www/html 2>/dev/null; do
  i=$((i + 1))
  if [ "$i" -gt 45 ]; then
    echo "[deploy] ERROR: WordPress not ready after ~90s. Check: $COMPOSE logs wordpress"
    exit 1
  fi
  sleep 2
done

echo "[deploy] Restoring DB snapshot (pages, menus, Forminator booking form)..."
$WPCLI_RUN sh wpcli /var/www/html/_build/import-db.sh

echo "[deploy] Pruning old local DB backups (keeping the 10 most recent)..."
ls -1t backup-*.sql 2>/dev/null | tail -n +11 | xargs -r rm -f || true

echo "[deploy] ✅ Done — $(git rev-parse --short HEAD) is live."
