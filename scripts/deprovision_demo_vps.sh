#!/usr/bin/env bash
# Remove Watphou demo from VPS sm. Do not touch smbistro, cirlapp, or other vhosts.
# Run as root: CONFIRM=yes bash /tmp/deprovision_demo_vps.sh
set -euo pipefail

if [[ "${CONFIRM:-}" != "yes" ]]; then
  echo "Refusing to run without CONFIRM=yes"
  exit 1
fi

TS=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/var/backups/watphou-demo-final-${TS}"
SMBISTRO_SITE="/etc/nginx/sites-enabled/smbistro"
mkdir -p "$BACKUP_DIR"

echo "=== Preflight: never touch these ==="
test -e "$SMBISTRO_SITE"
test -e /etc/nginx/sites-enabled/cirlapp-duckdns
test -e /etc/nginx/sites-enabled/cirl-ip
cp -a "$SMBISTRO_SITE" "$BACKUP_DIR/smbistro.nginx.sites-enabled"
cp -a /etc/nginx/sites-available/smbistro "$BACKUP_DIR/smbistro.nginx.sites-available" 2>/dev/null || true
sha256sum "$SMBISTRO_SITE" > "$BACKUP_DIR/smbistro.sha256"
mysql -N -e "SHOW DATABASES;" | grep -qx rtrs_db && echo "rtrs_db present" || echo "WARN: rtrs_db not found (left untouched)"

echo "=== Backup nginx + watphou data ==="
tar -czf "/root/nginx-backup-watphou-deprovision-${TS}.tar.gz" /etc/nginx/
if mysql -N -e "SHOW DATABASES LIKE 'watphou_demo';" | grep -q watphou_demo; then
  mysqldump --single-transaction --routines --triggers watphou_demo | gzip > "$BACKUP_DIR/watphou_demo.sql.gz"
fi
if [[ -d /var/www/watphou-demo ]]; then
  tar -czf "$BACKUP_DIR/watphou-demo-webroot.tar.gz" -C /var/www watphou-demo
fi
ls -la /etc/nginx/sites-enabled/ > "$BACKUP_DIR/sites-enabled.before.txt"

echo "=== Disable watphou nginx vhost ==="
rm -f /etc/nginx/sites-enabled/watphou-demo
nginx -t
systemctl reload nginx
echo "nginx reloaded after disabling watphou-demo"

echo "=== Remove watphou site file, htpasswd, cert ==="
rm -f /etc/nginx/sites-available/watphou-demo
rm -f /etc/nginx/.htpasswd-watphou-demo
if [[ -d /etc/letsencrypt/live/watphou.smbistro.duckdns.org ]]; then
  certbot delete --cert-name watphou.smbistro.duckdns.org --non-interactive
fi

echo "=== Remove watphou PHP-FPM pool only ==="
rm -f /etc/php/8.1/fpm/pool.d/watphou-demo.conf
if systemctl is-active --quiet php8.1-fpm; then
  systemctl reload php8.1-fpm
fi
rm -f /run/php/php8.1-fpm-watphou-demo.sock

echo "=== Drop watphou database and user only ==="
mysql -e "DROP DATABASE IF EXISTS watphou_demo;"
mysql -e "DROP USER IF EXISTS 'watphou_demo'@'localhost';"
mysql -e "FLUSH PRIVILEGES;"

echo "=== Remove watphou web files ==="
rm -rf /var/www/watphou-demo
rm -rf /var/www/watphou-media
rm -rf /var/www/watphou-content
rm -f /var/log/nginx/watphou-demo-access.log*
rm -f /var/log/nginx/watphou-demo-error.log*

if id watphou &>/dev/null; then
  userdel watphou
fi

echo "=== Post-check: smbistro nginx unchanged ==="
sha256sum -c "$BACKUP_DIR/smbistro.sha256"
test -e "$SMBISTRO_SITE"
test -e /etc/nginx/sites-enabled/cirlapp-duckdns
test -e /etc/nginx/sites-enabled/cirl-ip
test ! -e /etc/nginx/sites-enabled/watphou-demo
test ! -e /etc/nginx/sites-available/watphou-demo
test ! -d /var/www/watphou-demo
nginx -t
systemctl is-active nginx
systemctl is-active mysql
if mysql -N -e "SHOW DATABASES;" | grep -qx rtrs_db; then
  echo "rtrs_db still present"
else
  echo "WARN: rtrs_db not found (was not dropped by this script)"
fi
ls -la /etc/nginx/sites-enabled/ > "$BACKUP_DIR/sites-enabled.after.txt"
ls -la /etc/nginx/sites-enabled/
echo "Backup kept at $BACKUP_DIR"
echo DEPROVISION_OK
