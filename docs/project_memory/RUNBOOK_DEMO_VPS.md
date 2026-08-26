# Demo VPS Runbook — Watphou Travels

SSH host alias: **`sm`** (`212.73.150.149`)

## Health check (always run after changes)

```bash
# From Windows project root
python scripts/verify_demo.py
```

Manual checks on server:

```bash
ssh sm "curl -s -o /dev/null -w '%{http_code}' https://smbistro.duckdns.org/"
ssh sm "curl -s -o /dev/null -w '%{http_code}' https://watphou.smbistro.duckdns.org/"
ssh sm "systemctl is-active nginx mysql php8.1-fpm"
```

Expected: smbistro returns 200; watphou demo returns 200 (public, no HTTP Basic Auth). Manager edits via `/wp-login.php`.

## Deploy theme and plugins

```bash
python scripts/deploy_demo.py
```

Syncs only:
- `wp-content/themes/watphou-travels/`
- `wp-content/plugins/watphou-core/`
- `wp-content/plugins/watphou-bookings/`
- `wp-content/mu-plugins/`

Then on server:

```bash
ssh sm "cd /var/www/watphou-demo && sudo -u watphou wp cache flush && sudo -u watphou wp rewrite flush"
```

## Reload nginx (after config change)

```bash
ssh sm "nginx -t && systemctl reload nginx"
```

**Never** `systemctl restart nginx` unless reload fails.

## Restart PHP-FPM pool (watphou only)

```bash
ssh sm "systemctl restart php8.1-fpm"
```

## Rollback nginx config

```bash
ssh sm "ls -t /root/nginx-backup-*.tar.gz | head -1"
# Extract and restore specific file, then nginx -t && systemctl reload nginx
```

See `docs/DEMO_DEPLOYMENT_AND_ROLLBACK.md` for full rollback.

## Renew SSL certificate

```bash
ssh sm "certbot renew --dry-run"
ssh sm "certbot certonly --nginx -d watphou.smbistro.duckdns.org"
```

## WordPress CLI examples

```bash
ssh sm "cd /var/www/watphou-demo && sudo -u watphou wp plugin list"
ssh sm "cd /var/www/watphou-demo && sudo -u watphou wp post list --post_type=tour"
```

## Paths on server

| Item | Path |
|------|------|
| Web root | `/var/www/watphou-demo` |
| Nginx site | `/etc/nginx/sites-available/watphou-demo` |
| PHP pool | `/etc/php/8.1/fpm/pool.d/watphou-demo.conf` |
| Basic auth | retired (file may still exist unused) |
| Media originals (outside web root) | `/var/www/watphou-media/originals` |
| Backups | `/var/backups/watphou-demo/` |

## Verify existing site untouched

After any server change:

```bash
ssh sm "curl -sI https://smbistro.duckdns.org/ | head -5"
ssh sm "ss -tlnp | grep 5000"
```

Port 5000 must still show PM2 Node process.
