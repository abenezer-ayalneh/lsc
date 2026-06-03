#!/bin/sh
# Auto-installs WordPress + Kadence on first `docker compose up`.
# Safe to re-run: skips steps that are already done. Runs as www-data (33).
set -e

echo "[setup] Waiting for WordPress core files (wp-config.php)..."
i=0
while [ ! -f /var/www/html/wp-config.php ]; do
  i=$((i+1))
  if [ "$i" -gt 60 ]; then
    echo "[setup] ERROR: wp-config.php never appeared. Is the wordpress service healthy?"
    exit 1
  fi
  sleep 2
done

echo "[setup] Waiting for database to accept connections..."
until wp db check --path=/var/www/html >/dev/null 2>&1; do
  sleep 2
done

if wp core is-installed --path=/var/www/html >/dev/null 2>&1; then
  echo "[setup] WordPress already installed — skipping core install."
else
  echo "[setup] Installing WordPress..."
  wp core install \
    --path=/var/www/html \
    --url="${WP_SITE_URL}" \
    --title="${WP_SITE_TITLE}" \
    --admin_user="${WP_ADMIN_USER}" \
    --admin_password="${WP_ADMIN_PASSWORD}" \
    --admin_email="${WP_ADMIN_EMAIL}" \
    --skip-email
fi

echo "[setup] Setting pretty permalinks..."
wp rewrite structure '/%postname%/' --path=/var/www/html >/dev/null 2>&1 || true

echo "[setup] Installing Kadence parent theme..."
wp theme install kadence --path=/var/www/html || \
  echo "[setup] WARN: could not install Kadence (check internet access). Continuing."

# Activate the LSC child theme if it's present (it's bind-mounted from the repo);
# otherwise fall back to the Kadence parent so the site still renders.
if wp theme is-installed lsc-child --path=/var/www/html >/dev/null 2>&1; then
  echo "[setup] Activating LSC child theme..."
  wp theme activate lsc-child --path=/var/www/html || \
    echo "[setup] WARN: could not activate lsc-child. Continuing."
else
  echo "[setup] lsc-child not found — activating Kadence parent instead."
  wp theme activate kadence --path=/var/www/html || true
fi

# Tidy default content so the dev starts clean
echo "[setup] Removing default plugins (Hello Dolly / Akismet)..."
wp plugin delete hello akismet --path=/var/www/html >/dev/null 2>&1 || true

echo ""
echo "[setup] ✅ Done."
echo "[setup]   Site:  ${WP_SITE_URL}"
echo "[setup]   Admin: ${WP_SITE_URL}/wp-admin  (user: ${WP_ADMIN_USER} / pass: ${WP_ADMIN_PASSWORD})"
