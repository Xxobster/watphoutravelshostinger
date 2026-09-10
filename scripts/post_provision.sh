#!/usr/bin/env bash
echo "RETIRED 2026-09-10: Watphou is not on VPS sm. Do not run."
exit 1
# Post-provision: install plugins, theme, import content, Polylang
set -euo pipefail
WEB_ROOT="/var/www/watphou-demo"
CONTENT="/var/www/watphou-content"

cd "$WEB_ROOT"

echo "=== Install plugins ==="
sudo -u watphou wp plugin install polylang wordpress-seo --activate --path="$WEB_ROOT" || true

echo "=== Activate custom code ==="
sudo -u watphou wp theme activate watphou-travels --path="$WEB_ROOT" || true
sudo -u watphou wp plugin activate watphou-core watphou-bookings --path="$WEB_ROOT" || true

echo "=== Polylang languages ==="
sudo -u watphou wp pll lang create en English en_US 1 --path="$WEB_ROOT" 2>/dev/null || true
sudo -u watphou wp pll lang create fr Français fr_FR 2 --path="$WEB_ROOT" 2>/dev/null || true
sudo -u watphou wp pll lang create th ไทย th 3 --path="$WEB_ROOT" 2>/dev/null || true

echo "=== Import content ==="
if [ -d "$CONTENT/extracted" ]; then
  ln -sfn "$CONTENT" "$WEB_ROOT/../content" 2>/dev/null || true
  sudo -u watphou wp watphou import_content --path="$WEB_ROOT" || true
fi

echo "=== Create manager if missing ==="
sudo -u watphou wp user create manager manager-demo@watphou.local --role=tour_manager --user_pass=000000 --path="$WEB_ROOT" 2>/dev/null || \
  sudo -u watphou wp user update manager --user_pass=000000 --role=tour_manager --path="$WEB_ROOT" || true

sudo -u watphou wp rewrite flush --path="$WEB_ROOT"
echo "POST_PROVISION_OK"
