#!/bin/sh
# Host-side helper for the DB-snapshot-first workflow.
#
# Run this before AI/code work whenever WP Admin may have changed:
#   scripts/snapshot-admin-content.sh
set -e

ROOT=$(git rev-parse --show-toplevel 2>/dev/null) || {
  echo "[snapshot] ERROR: run from inside the git repo."
  exit 1
}

cd "$ROOT"

echo "[snapshot] Exporting local WordPress DB snapshot..."
docker compose run --rm --entrypoint sh wpcli /var/www/html/_build/export-db.sh

echo
echo "[snapshot] Include media uploaded through WP Admin:"
echo "[snapshot]   git add wordpress/_build/db/lsc-db.sql wordpress/wp-content/uploads"
echo
echo "[snapshot] Current content-related git changes:"
git status --short -- wordpress/_build/db/lsc-db.sql wordpress/wp-content/uploads
echo
echo "[snapshot] Suggested commit:"
echo "[snapshot]   git commit -m 'Snapshot wp-admin content'"
