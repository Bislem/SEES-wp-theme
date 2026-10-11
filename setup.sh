#!/usr/bin/env bash
# Start the stack and install WordPress (idempotent).
set -euo pipefail
cd "$(dirname "$0")"
[[ -f .env ]] || cp .env.example .env
set -a; source .env; set +a

docker compose up -d

wp() { docker compose run --rm wpcli wp "$@"; }

echo "Waiting for WordPress files..."
until docker compose exec -T wordpress test -f /var/www/html/wp-config.php; do sleep 2; done

if ! wp core is-installed 2>/dev/null; then
  wp core install \
    --url="http://localhost:${WP_PORT}" \
    --title="${WP_TITLE}" \
    --admin_user="${WP_ADMIN_USER}" \
    --admin_password="${WP_ADMIN_PASSWORD}" \
    --admin_email="${WP_ADMIN_EMAIL}" \
    --skip-email
  wp rewrite structure '/%postname%/' --hard
  wp plugin install theme-check query-monitor --activate
  wp plugin delete hello akismet || true
fi

wp theme activate "${THEME_SLUG}"

echo
echo "Site:       http://localhost:${WP_PORT}"
echo "Admin:      http://localhost:${WP_PORT}/wp-admin  (${WP_ADMIN_USER} / ${WP_ADMIN_PASSWORD})"
echo "phpMyAdmin: http://localhost:${PMA_PORT}"
