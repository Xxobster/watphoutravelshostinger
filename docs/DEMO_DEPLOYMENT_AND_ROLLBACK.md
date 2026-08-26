# Demo Deployment and Rollback — Watphou Travels

## Deploy code (theme + plugins)

From Windows project root:

```bash
python scripts/deploy_demo.py
python scripts/verify_demo.py
```

## Post-provision (first time only)

```bash
scp scripts/post_provision.sh sm:/root/
ssh sm "bash /root/post_provision.sh"
```

## Rollback nginx

```bash
ssh sm "ls -t /root/nginx-backup-*.tar.gz | head -1"
# Example restore:
ssh sm "tar -xzf /root/nginx-backup-TIMESTAMP.tar.gz -C / && nginx -t && systemctl reload nginx"
```

## Rollback demo site only

1. Disable vhost: `rm /etc/nginx/sites-enabled/watphou-demo && nginx -t && systemctl reload nginx`
2. Drop database (optional): `mysql -e 'DROP DATABASE watphou_demo;'`
3. Remove web root (optional): `rm -rf /var/www/watphou-demo`

**Do not** remove smbistro vhost or rtrs_db.

## Verify existing site after rollback

```bash
curl -s -o /dev/null -w '%{http_code}' https://smbistro.duckdns.org/
```

Must return `200`.

## SSL renewal

```bash
ssh sm "certbot renew --dry-run"
```
