#!/usr/bin/env bash
echo "RETIRED 2026-09-10: Watphou is not on VPS sm. Do not run."
exit 1
set -eu
TS=$(date +%Y%m%d_%H%M%S)
cp /etc/nginx/sites-available/watphou-demo "/root/nginx-watphou-demo-backup-${TS}.conf"
sed -i '/auth_basic/d' /etc/nginx/sites-available/watphou-demo
nginx -t
systemctl reload nginx
curl -s -o /dev/null -w "demo:%{http_code}\n" https://watphou.smbistro.duckdns.org/
curl -s -o /dev/null -w "smbistro:%{http_code}\n" https://smbistro.duckdns.org/
echo NGINX_AUTH_REMOVED
