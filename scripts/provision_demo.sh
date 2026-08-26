#!/usr/bin/env bash
# One-time demo provisioning on VPS sm (212.73.150.149)
# Run as root: bash scripts/provision_demo.sh
set -euo pipefail

DEMO_DOMAIN="watphou.smbistro.duckdns.org"
WEB_ROOT="/var/www/watphou-demo"
MEDIA_ORIG="/var/www/watphou-media/originals"
DB_NAME="watphou_demo"
DB_USER="watphou_demo"
WP_USER="watphou"
PHP_VER="8.1"
NGINX_SITE="watphou-demo"
BASIC_AUTH_FILE="/etc/nginx/.htpasswd-watphou-demo"
BACKUP_DIR="/var/backups/watphou-demo"
TS=$(date +%Y%m%d_%H%M%S)

echo "=== Preflight: backup nginx ==="
tar -czf "/root/nginx-backup-${TS}.tar.gz" /etc/nginx/
mkdir -p "$BACKUP_DIR"

echo "=== Baseline health smbistro ==="
SMB_STATUS=$(curl -s -o /dev/null -w '%{http_code}' https://smbistro.duckdns.org/ || echo "000")
echo "smbistro status: $SMB_STATUS"
echo "$SMB_STATUS" > "$BACKUP_DIR/smbistro_baseline_${TS}.txt"

echo "=== Install PHP ${PHP_VER} if missing ==="
if ! dpkg -l | grep -q "php${PHP_VER}-fpm"; then
  apt-get update -qq
  DEBIAN_FRONTEND=noninteractive apt-get install -y \
    "php${PHP_VER}-fpm" "php${PHP_VER}-mysql" "php${PHP_VER}-curl" \
    "php${PHP_VER}-gd" "php${PHP_VER}-mbstring" "php${PHP_VER}-xml" \
    "php${PHP_VER}-zip" "php${PHP_VER}-intl" "php${PHP_VER}-imagick" \
    nginx-extras apache2-utils certbot python3-certbot-nginx unzip curl
fi

echo "=== Create system user ==="
id "$WP_USER" &>/dev/null || useradd -r -s /usr/sbin/nologin -d "$WEB_ROOT" "$WP_USER"

echo "=== Database ==="
DB_PASS=$(openssl rand -base64 24 | tr -d '/+=' | head -c 24)
mysql -e "CREATE DATABASE IF NOT EXISTS \`${DB_NAME}\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
mysql -e "CREATE USER IF NOT EXISTS '${DB_USER}'@'localhost' IDENTIFIED BY '${DB_PASS}';"
mysql -e "GRANT ALL PRIVILEGES ON \`${DB_NAME}\`.* TO '${DB_USER}'@'localhost';"
mysql -e "FLUSH PRIVILEGES;"
echo "DB password saved to ${BACKUP_DIR}/db_credentials_${TS}.txt (root only)"
echo "DB_USER=${DB_USER}" > "${BACKUP_DIR}/db_credentials_${TS}.txt"
echo "DB_PASS=${DB_PASS}" >> "${BACKUP_DIR}/db_credentials_${TS}.txt"
chmod 600 "${BACKUP_DIR}/db_credentials_${TS}.txt"

echo "=== WP-CLI ==="
if ! command -v wp &>/dev/null; then
  curl -sO https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
  chmod +x wp-cli.phar
  mv wp-cli.phar /usr/local/bin/wp
fi

echo "=== WordPress core ==="
mkdir -p "$WEB_ROOT" "$MEDIA_ORIG"
if [ ! -f "$WEB_ROOT/wp-config.php" ]; then
  sudo -u "$WP_USER" wp core download --path="$WEB_ROOT" --locale=en_US
  sudo -u "$WP_USER" wp config create \
    --path="$WEB_ROOT" \
    --dbname="$DB_NAME" \
    --dbuser="$DB_USER" \
    --dbpass="$DB_PASS" \
    --dbhost=localhost \
    --dbcharset=utf8mb4 \
    --extra-php <<'PHPEOF'
define('DISALLOW_FILE_EDIT', true);
define('WP_ENVIRONMENT_TYPE', 'staging');
define('WATPHOU_DEMO', true);
PHPEOF
  ADMIN_PASS=$(openssl rand -base64 16 | tr -d '/+=' | head -c 16)
  sudo -u "$WP_USER" wp core install \
    --path="$WEB_ROOT" \
    --url="https://${DEMO_DOMAIN}" \
    --title="Watphou Travels Demo" \
    --admin_user=admin \
    --admin_password="$ADMIN_PASS" \
    --admin_email=sales.watphoutravel@gmail.com \
    --skip-email
  echo "WP admin password: ${ADMIN_PASS}" >> "${BACKUP_DIR}/db_credentials_${TS}.txt"
fi

chown -R "$WP_USER:$WP_USER" "$WEB_ROOT"

echo "=== PHP-FPM pool ==="
cat > "/etc/php/${PHP_VER}/fpm/pool.d/${NGINX_SITE}.conf" <<EOF
[${NGINX_SITE}]
user = ${WP_USER}
group = ${WP_USER}
listen = /run/php/php${PHP_VER}-fpm-${NGINX_SITE}.sock
listen.owner = www-data
listen.group = www-data
listen.mode = 0660
pm = ondemand
pm.max_children = 5
pm.process_idle_timeout = 10s
php_admin_value[upload_max_filesize] = 64M
php_admin_value[post_max_size] = 64M
php_admin_value[memory_limit] = 256M
EOF

echo "=== HTTP Basic Auth (disabled — public demo site) ==="
# Public website must be viewable. Manager edits via WordPress login at /wp-login.php
BASIC_AUTH_FILE="/etc/nginx/.htpasswd-watphou-demo"

echo "=== Nginx vhost (HTTP first) ==="
cat > "/etc/nginx/sites-available/${NGINX_SITE}" <<EOF
server {
    listen 80;
    server_name ${DEMO_DOMAIN};

    root ${WEB_ROOT};
    index index.php index.html;

    access_log /var/log/nginx/${NGINX_SITE}-access.log;
    error_log /var/log/nginx/${NGINX_SITE}-error.log;

    add_header X-Robots-Tag "noindex, nofollow" always;

    location / {
        try_files \$uri \$uri/ /index.php?\$args;
    }

    location ~ \\.php\$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php${PHP_VER}-fpm-${NGINX_SITE}.sock;
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~* /(?:uploads|files)/.*\\.(php|phps|phtml)\$ {
        deny all;
    }

    location ~ /\\.ht {
        deny all;
    }
}
EOF

ln -sf "/etc/nginx/sites-available/${NGINX_SITE}" "/etc/nginx/sites-enabled/${NGINX_SITE}"

echo "=== Test and reload nginx ==="
nginx -t
systemctl reload nginx
systemctl restart "php${PHP_VER}-fpm"

echo "=== SSL certificate ==="
certbot --nginx -d "$DEMO_DOMAIN" --non-interactive --agree-tos -m sales.watphoutravel@gmail.com --redirect || \
  certbot certonly --nginx -d "$DEMO_DOMAIN" --non-interactive --agree-tos -m sales.watphoutravel@gmail.com

echo "=== Post-install WP config ==="
sudo -u "$WP_USER" wp option update blog_public 0 --path="$WEB_ROOT"
sudo -u "$WP_USER" wp rewrite structure '/%postname%/' --path="$WEB_ROOT"
sudo -u "$WP_USER" wp rewrite flush --path="$WEB_ROOT"

echo "=== Verify smbistro still healthy ==="
SMB_AFTER=$(curl -s -o /dev/null -w '%{http_code}' https://smbistro.duckdns.org/ || echo "000")
echo "smbistro after: $SMB_AFTER"
if [ "$SMB_AFTER" != "200" ] && [ "$SMB_STATUS" = "200" ]; then
  echo "ERROR: smbistro degraded! Rolling back nginx reload may be needed."
  exit 1
fi

echo ""
echo "=== PROVISION COMPLETE ==="
echo "Demo URL: https://${DEMO_DOMAIN}"
echo "Basic auth credentials in: ${BACKUP_DIR}/db_credentials_${TS}.txt"
echo "Deploy theme/plugins with: python scripts/deploy_demo.py"
