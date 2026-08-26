#!/usr/bin/env bash
set -eu
DB_PASS="O79aAdDcpSoZx4d2o8n06mFu"
ADMIN_PASS="GJ5c3fYMRG4uSNJi"
WEB_ROOT="/var/www/watphou-demo"
PHP_VER="8.1"
NGINX_SITE="watphou-demo"
DEMO_DOMAIN="watphou.smbistro.duckdns.org"
BASIC_AUTH_FILE="/etc/nginx/.htpasswd-watphou-demo"

mysql -e "ALTER USER 'watphou_demo'@'localhost' IDENTIFIED BY '${DB_PASS}'; FLUSH PRIVILEGES;"

cd "$WEB_ROOT"
if [ ! -f wp-config.php ]; then
  sudo -u watphou wp config create \
    --dbname=watphou_demo \
    --dbuser=watphou_demo \
    --dbpass="${DB_PASS}" \
    --dbhost=localhost \
    --dbcharset=utf8mb4 \
    --extra-php <<'PHPEOF'
define('DISALLOW_FILE_EDIT', true);
define('WP_ENVIRONMENT_TYPE', 'staging');
define('WATPHOU_DEMO', true);
PHPEOF
fi

if ! sudo -u watphou wp core is-installed 2>/dev/null; then
  sudo -u watphou wp core install \
    --url="https://${DEMO_DOMAIN}" \
    --title="Watphou Travels Demo" \
    --admin_user=admin \
    --admin_password="${ADMIN_PASS}" \
    --admin_email=sales.watphoutravel@gmail.com \
    --skip-email
fi

# PHP pool
cat > "/etc/php/${PHP_VER}/fpm/pool.d/${NGINX_SITE}.conf" <<EOF
[${NGINX_SITE}]
user = watphou
group = watphou
listen = /run/php/php${PHP_VER}-fpm-${NGINX_SITE}.sock
listen.owner = www-data
listen.group = www-data
listen.mode = 0660
pm = ondemand
pm.max_children = 5
pm.process_idle_timeout = 10s
EOF

# Basic auth disabled — public website; manager uses WordPress login

# Nginx
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
    location ~ /\\.ht { deny all; }
}
EOF

ln -sf "/etc/nginx/sites-available/${NGINX_SITE}" "/etc/nginx/sites-enabled/${NGINX_SITE}"
nginx -t
systemctl reload nginx
systemctl restart php8.1-fpm

certbot --nginx -d "$DEMO_DOMAIN" --non-interactive --agree-tos -m sales.watphoutravel@gmail.com --redirect 2>/dev/null || true

sudo -u watphou wp option update blog_public 0 --path="$WEB_ROOT"
sudo -u watphou wp rewrite structure '/%postname%/' --path="$WEB_ROOT"
sudo -u watphou wp rewrite flush --path="$WEB_ROOT"

curl -s -o /dev/null -w "smbistro:%{http_code}\n" https://smbistro.duckdns.org/
echo "FIX_COMPLETE"
