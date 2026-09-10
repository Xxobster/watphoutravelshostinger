# Staging Runbook — Watphou Travels

**Current staging:** https://darkslategray-snake-182151.hostingersite.com (Hostinger)

The Virtual Private Server (VPS) demo on `sm` (`watphou.smbistro.duckdns.org`) was **removed 2026-09-10**. Do not recreate it.

## Health check

```bash
# From Windows project root
python scripts/verify_demo.py
```

Expected:

- Hostinger staging HTTPS 200
- `/wp-login.php` 200 or 302
- On VPS `sm`: nginx active, `watphou-demo` **not** in sites-enabled, `smbistro` still present, `/var/www/watphou-demo` gone

## Deploy theme and plugins

Do **not** run `python scripts/deploy_demo.py` (it refuses VPS deploys).

Push `wp-content/themes/watphou-travels` and the two custom plugins to the Hostinger WordPress install (Hostinger panel or Hostinger deploy tools). Then re-run `python scripts/verify_demo.py`.

## Hostinger facts

| Item | Value |
|------|-------|
| Temporary domain | `darkslategray-snake-182151.hostingersite.com` |
| Hostinger username | `u916301613` |
| WordPress admin user | `wptadmin` (password not in git) |
| Search engines | Keep `noindex` until the real domain is attached |

## Retired VPS paths (gone)

| Item | Former path |
|------|-------------|
| Web root | `/var/www/watphou-demo` |
| Nginx site | `/etc/nginx/sites-available/watphou-demo` |
| PHP pool | `/etc/php/8.1/fpm/pool.d/watphou-demo.conf` |
| Offline backup (left on VPS) | `/var/backups/watphou-demo-final-20260910_142038/` |

Removal script: `scripts/deprovision_demo_vps.sh` (already run).

## Never touch on VPS sm

- nginx site `smbistro`
- nginx sites `cirlapp-duckdns` and `cirl-ip`
- Let’s Encrypt certificates other than the deleted Watphou one

If nginx config on `sm` must change for **non-Watphou** work: `nginx -t` then `systemctl reload nginx` — never restart unless reload fails.
