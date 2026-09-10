# Demo Deployment and Rollback — Watphou Travels

**Status:** SUPERSEDED 2026-09-10. Watphou is not on Virtual Private Server (VPS) `sm`. Staging is Hostinger: https://darkslategray-snake-182151.hostingersite.com

Removal already ran: `scripts/deprovision_demo_vps.sh`. Offline backup on the VPS: `/var/backups/watphou-demo-final-20260910_142038/`

**Do not** remove or edit nginx sites `smbistro`, `cirlapp-duckdns`, or `cirl-ip`.

## Verify

```bash
python scripts/verify_demo.py
```
