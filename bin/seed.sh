#!/usr/bin/env bash
# Seed the local site with EN + AR sample content.
#   bin/seed.sh            first run
#   SEES_RESEED=1 bin/seed.sh   delete previous sample content and seed again
#
# Uses the Docker WP-CLI container by default. On a server with WP-CLI installed:
#   WP="wp --path=/path/to/wordpress" bin/seed.sh
set -euo pipefail
cd "$(dirname "$0")/.."

if [[ -z "${WP:-}" ]]; then
  WP_CMD=(docker compose run --rm -T -e SEES_RESEED="${SEES_RESEED:-}" wpcli wp)
else
  read -r -a WP_CMD <<< "$WP"
fi
wp() { "${WP_CMD[@]}" "$@"; }

echo "→ Plugins"
wp plugin install polylang advanced-custom-fields contact-form-7 --activate
wp theme activate sees

echo "→ Arabic language packs"
wp language core install ar
for p in polylang advanced-custom-fields contact-form-7; do wp language plugin install "$p" ar || true; done

echo "→ Permalinks"
wp rewrite structure '/%postname%/'

echo "→ Content"
wp eval-file - < bin/seed.php

wp rewrite flush
echo "Done. Open http://localhost:${WP_PORT:-8080}/en/ and /ar/"
