#!/bin/sh
# Exports the WordPress database — everything you changed in wp-admin (pages,
# posts, menus, widgets, options, Customizer, Forminator forms) — into a git-tracked
# SQL file, so admin-panel work survives a `docker compose down -v` reset and
# can be pushed to GitHub. This is the inverse of build.sh.
#
# Run from the wpcli container:
#   docker compose run --rm --entrypoint sh wpcli /var/www/html/_build/export-db.sh
#
# Or export + commit + push in one go (from the host repo root):
#   docker compose run --rm --entrypoint sh wpcli /var/www/html/_build/export-db.sh \
#     && git add wordpress/_build/db/lsc-db.sql \
#     && git commit -m "Snapshot wp-admin content" && git push
#
# The dump is environment-neutral: this site's URL is replaced with the
# __LSC_SITE_URL__ token (search-replace fixes serialized data correctly), so
# the same dump restores cleanly on localhost or behind an ngrok tunnel.
# Restore it with import-db.sh.
#
# NOTE: media files (wp-content/uploads/) are NOT in this dump — they're
# gitignored. Photos referenced by the build live in _build/media/ and are
# re-imported by build.sh; anything you uploaded ad-hoc via the Media Library
# won't be tracked by this script.
set -e

export HOME=/tmp
WP="wp --path=/var/www/html"
OUT=/var/www/html/_build/db/lsc-db.sql
SITE_URL=$($WP option get home)

mkdir -p "$(dirname "$OUT")"
echo "[export] Site URL: $SITE_URL"
echo "[export] Writing dump (URL -> __LSC_SITE_URL__) ..."
$WP search-replace "$SITE_URL" "__LSC_SITE_URL__" --all-tables --export="$OUT" >/dev/null
echo "[export] ✅ Done. Tracked at wordpress/_build/db/lsc-db.sql"
echo "[export]    Commit & push it:"
echo "[export]    git add wordpress/_build/db/lsc-db.sql && git commit -m 'Snapshot wp-admin content' && git push"
