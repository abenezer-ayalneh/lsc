#!/bin/sh
# Restores the WordPress database from the git-tracked dump produced by
# export-db.sh, then rewrites the __LSC_SITE_URL__ token back to this
# environment's site URL. Use this on a fresh clone or after `down -v` instead
# of (or in addition to) build.sh when you want the exact admin-panel state.
#
# Run from the wpcli container:
#   docker compose run --rm --entrypoint sh wpcli /var/www/html/_build/import-db.sh
set -e

export HOME=/tmp
WP="wp --path=/var/www/html"
DUMP=/var/www/html/_build/db/lsc-db.sql
SITE_URL=${WP_SITE_URL:-http://localhost:8080}

[ -f "$DUMP" ] || { echo "[import] No dump at $DUMP — run export-db.sh first."; exit 1; }

echo "[import] Importing $DUMP ..."
$WP db import "$DUMP"
echo "[import] Rewriting __LSC_SITE_URL__ -> $SITE_URL ..."
$WP search-replace "__LSC_SITE_URL__" "$SITE_URL" --all-tables >/dev/null
echo "[import] Seeding editable footer widget if needed ..."
$WP eval-file /var/www/html/_build/scripts/install-footer-widget.php
echo "[import] Flushing rewrite rules ..."
$WP rewrite flush --hard >/dev/null 2>&1 || true
echo "[import] ✅ Done. Visit $SITE_URL"
