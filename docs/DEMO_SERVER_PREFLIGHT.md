# Demo Server Preflight — Watphou Travels

**Date:** 2026-08-26  
**Host:** `sm` / `212.73.150.149`

## Baseline (before changes)

| Check | Result |
|-------|--------|
| OS | Ubuntu 22.04.5 LTS |
| RAM | 2.9 GB |
| Disk free | 5.5 GB |
| nginx | active |
| MySQL | 8.0.46 active |
| PHP | not installed → install 8.1-fpm |
| smbistro.duckdns.org | HTTP 200 |
| Node PM2 port 5000 | active |
| certbot | available (smbistro cert valid) |

## Isolation plan

| Resource | Value |
|----------|-------|
| Demo domain | watphou.smbistro.duckdns.org |
| Web root | /var/www/watphou-demo |
| DB | watphou_demo |
| DB user | watphou_demo |
| PHP pool | watphou-demo |
| Unix user | watphou |
| Content data | /var/www/watphou-content |

## Not touched

- smbistro nginx vhost
- rtrs_db database
- PM2 Node app on 127.0.0.1:5000
- smbistro.duckdns.org certificate

## Provisioning command

```bash
scp scripts/provision_demo.sh sm:/root/
ssh sm "bash /root/provision_demo.sh"
```

Credentials written to `/var/backups/watphou-demo/db_credentials_*.txt` on server (not in git).
