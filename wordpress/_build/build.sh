#!/bin/sh
# Builds the LSC site content via WP-CLI. Idempotent: re-running updates pages
# in place rather than duplicating them. Run from the wpcli container:
#   docker compose run --rm --entrypoint sh wpcli /var/www/html/_build/build.sh
#
# Visual design is a pixel-clone of marys.org.uk (see docs/adr/0001); the page
# set was redefined to Home/Who Are We/Get Involved/Events/Media/Get in Touch
# (see docs/adr/0002). Terms of Use lives in the footer menu only.
set -e

export HOME=/tmp
export WP_CLI_CACHE_DIR=/tmp/.wp-cli/cache
WP="wp --path=/var/www/html"
BUILD=/var/www/html/_build
THEME_IMG=/var/www/html/wp-content/themes/lsc-child/assets/images
MEDIA=/var/www/html/_build/media

echo "[build] Activating lsc-child theme..."
$WP theme activate lsc-child

echo "[build] Site identity..."
$WP option update blogname "Lewisham Sports Consortium"
$WP option update blogdescription "Sport, recreation and opportunity for the Lewisham community"

# --- Media: logo (import once, reuse by title) -------------------------------
import_media() {
  # $1 = file path, $2 = title. Echoes attachment ID.
  existing=$($WP post list --post_type=attachment --title="$2" --field=ID --posts_per_page=1 2>/dev/null | head -n1)
  if [ -n "$existing" ]; then echo "$existing"; return; fi
  $WP media import "$1" --title="$2" --porcelain
}

echo "[build] Importing logo into the media library..."
LOGO_ID=$(import_media "$THEME_IMG/lsc-logo.png" "LSC logo")
echo "[build]   logo=$LOGO_ID"
$WP theme mod set custom_logo "$LOGO_ID"

# --- Site photos -------------------------------------------------------------
# Real club photos live (committed) in _build/media/ and are re-imported here so
# they survive a `docker compose down -v` reset. Each page template references
# them via __NAME_ID__ / __NAME_URL__ tokens, substituted by render() below.
media_url() { $WP eval "echo wp_get_attachment_url($1);"; }

echo "[build] Importing site photos into the media library..."
TROPHIES_ID=$(import_media "$MEDIA/youth-team-trophies.jpg" "Youth team with trophies"); TROPHIES_URL=$(media_url "$TROPHIES_ID")
ACTIVITY_ID=$(import_media "$MEDIA/youth-activity.jpg" "Youth activity session");        ACTIVITY_URL=$(media_url "$ACTIVITY_ID")
PARTNER_ID=$(import_media "$MEDIA/partnership-meeting.jpg" "Partnership meeting");        PARTNER_URL=$(media_url "$PARTNER_ID")
HERITAGE_ID=$(import_media "$MEDIA/heritage-display.jpg" "Heritage display");             HERITAGE_URL=$(media_url "$HERITAGE_ID")
COMMITTEE_ID=$(import_media "$MEDIA/committee-meeting.jpg" "Committee meeting");           COMMITTEE_URL=$(media_url "$COMMITTEE_ID")
GROUNDS_ID=$(import_media "$MEDIA/grounds-maintenance.jpg" "Grounds maintenance");        GROUNDS_URL=$(media_url "$GROUNDS_ID")
TRACTOR_ID=$(import_media "$MEDIA/grounds-tractor.jpg" "Grounds tractor");                TRACTOR_URL=$(media_url "$TRACTOR_ID")

# --- Booking Hire Agreement form ---------------------------------------------
# The booking form is a Forminator form built in wp-admin (see docs/adr/0003) and
# restored from the DB snapshot (import-db.sh), so book-the-grounds.html embeds
# its [forminator_form id="…"] shortcode literally — nothing to bootstrap here.

# Substitute the media tokens in a page template, emitting block markup with the
# freshly-imported attachment IDs/URLs (which differ after every DB reset).
render() {
  sed \
    -e "s|__TROPHIES_ID__|$TROPHIES_ID|g"   -e "s|__TROPHIES_URL__|$TROPHIES_URL|g" \
    -e "s|__ACTIVITY_ID__|$ACTIVITY_ID|g"   -e "s|__ACTIVITY_URL__|$ACTIVITY_URL|g" \
    -e "s|__PARTNER_ID__|$PARTNER_ID|g"     -e "s|__PARTNER_URL__|$PARTNER_URL|g" \
    -e "s|__HERITAGE_ID__|$HERITAGE_ID|g"   -e "s|__HERITAGE_URL__|$HERITAGE_URL|g" \
    -e "s|__COMMITTEE_ID__|$COMMITTEE_ID|g" -e "s|__COMMITTEE_URL__|$COMMITTEE_URL|g" \
    -e "s|__GROUNDS_ID__|$GROUNDS_ID|g"     -e "s|__GROUNDS_URL__|$GROUNDS_URL|g" \
    -e "s|__TRACTOR_ID__|$TRACTOR_ID|g"     -e "s|__TRACTOR_URL__|$TRACTOR_URL|g" \
    "$1"
}

# --- Pages -------------------------------------------------------------------
# Creates or updates a page by slug. $1=slug $2=title $3=content-file $4=parent-slug(optional)
upsert_page() {
  slug="$1"; title="$2"; file="$3"; parent_slug="${4:-}"
  parent_id=0
  if [ -n "$parent_slug" ]; then
    parent_id=$($WP post list --post_type=page --name="$parent_slug" --field=ID --posts_per_page=1 2>/dev/null | head -n1)
    parent_id=${parent_id:-0}
  fi
  content=$(render "$file")
  id=$($WP post list --post_type=page --name="$slug" --field=ID --posts_per_page=1 2>/dev/null | head -n1)
  if [ -n "$id" ]; then
    echo "$content" | $WP post update "$id" --post_title="$title" --post_status=publish --post_parent="$parent_id" - >/dev/null
    echo "[build]   updated $slug (#$id)"
  else
    id=$(echo "$content" | $WP post create - --post_type=page --post_status=publish --post_title="$title" --post_name="$slug" --post_parent="$parent_id" --porcelain)
    echo "[build]   created $slug (#$id)"
  fi
  echo "$id"
}

echo "[build] Building pages..."
HOME_ID=$(upsert_page home "Home" "$BUILD/pages/home.html" | tail -n1)
upsert_page who-are-we   "Who Are We"   "$BUILD/pages/who-are-we.html" >/dev/null
upsert_page get-involved "Get Involved" "$BUILD/pages/get-involved.html" >/dev/null
# Booking Hire Agreement (LSC-000) — child of Get Involved. See docs/adr/0003.
upsert_page book-the-grounds "Book the grounds" "$BUILD/pages/book-the-grounds.html" get-involved >/dev/null
upsert_page events       "Events"       "$BUILD/pages/events.html" >/dev/null
upsert_page media        "Media"        "$BUILD/pages/media.html" >/dev/null
upsert_page get-in-touch "Get in Touch" "$BUILD/pages/get-in-touch.html" >/dev/null
upsert_page terms        "Terms of Use" "$BUILD/pages/terms.html" >/dev/null

echo "[build] Setting static front page..."
$WP option update show_on_front page
$WP option update page_on_front "$HOME_ID"

# Retire pages from the previous (superseded) page set if they still exist.
for old in about facilities pricing contact; do
  oid=$($WP post list --post_type=page --name="$old" --field=ID --posts_per_page=1 2>/dev/null | head -n1)
  [ -n "$oid" ] && $WP post delete "$oid" --force >/dev/null 2>&1 && echo "[build]   removed stale page $old (#$oid)" || true
done

# Remove the default "Sample Page" and "Hello world!" post if present.
$WP post delete $($WP post list --post_type=page --name=sample-page --field=ID 2>/dev/null) --force 2>/dev/null || true
$WP post delete $($WP post list --post_type=post --name=hello-world --field=ID 2>/dev/null) --force 2>/dev/null || true

# --- Menus -------------------------------------------------------------------
build_menu() {
  name="$1"; location="$2"; shift 2
  $WP menu delete "$name" >/dev/null 2>&1 || true
  $WP menu create "$name" >/dev/null
  for slug in "$@"; do
    pid=$($WP post list --post_type=page --name="$slug" --field=ID --posts_per_page=1 | head -n1)
    [ -n "$pid" ] && $WP menu item add-post "$name" "$pid" >/dev/null
  done
  $WP menu location assign "$name" "$location" >/dev/null 2>&1 || true
}

echo "[build] Building menus..."
build_menu "Primary" primary home who-are-we get-involved events media get-in-touch
build_menu "Footer"  footer  get-in-touch terms

echo "[build] Flushing rewrite rules..."
$WP rewrite flush --hard >/dev/null 2>&1 || true

echo "[build] ✅ Done. Visit ${WP_SITE_URL:-http://localhost:8080}"
