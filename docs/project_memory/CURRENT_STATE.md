# Current State — Watphou Travels

**Last updated:** 2026-08-26 (Vietnam Discovery layout + Wix photos)

## Summary

Demo site is **public** at `https://watphou.smbistro.duckdns.org`. The theme is now a classic PHP theme that follows the vietnamdiscovery.com homepage layout (hero, interest cards, adventure rows, destination tiles, steps, why-us, reviews, contact). Photos and logo come from Watphou’s Wix site / backup — not from Vietnam Discovery. HTTP Basic Authentication was removed. Managers click **Log in to edit** and use WordPress user `manager` / `000000`.

## Completed

- [x] WT-001 Repository bootstrap (git, README, AGENTS, .gitignore, remote)
- [x] WT-002 Project memory (12 files in `docs/project_memory/`)
- [x] WT-003 Backup inventory (639 files, 21 quarantined, `BACKUP_INVENTORY.md`)
- [x] WT-004 Content extraction (fallback JSON for 36 URLs + live scrape partial; `CONTENT_MIGRATION_MAP.md`)
- [x] WT-005 Requirements matrix + design reference
- [x] WT-006 Demo VPS provisioned on `sm` (212.73.150.149), smbistro verified 200
- [x] WT-007 deploy_demo.py, verify_demo.py, screenshots.py, RUNBOOK
- [x] WT-008 Block theme `watphou-travels`
- [x] WT-009 Plugin `watphou-core` (tours, taxonomies, blocks, Tour Manager role)
- [x] WT-010 Content import (36 items, 24 tours via WP-CLI)
- [x] WT-011 Polylang EN/FR/TH languages registered
- [x] WT-012 Plugin `watphou-bookings` (tables, state machine, admin UI)
- [x] WT-013 Payment layer (MockProvider, Bcel stub, BCEL doc)
- [x] WT-014 Manager dashboard + MANAGER_GUIDE.md
- [x] WT-015 SEO/security docs + demo hardening (mu-plugin)
- [x] WT-016 verify_demo.py passed (demo 200, smbistro 200, nginx active)
- [x] WT-017 BLUEHOST_DEPLOYMENT.md + deploy_production.sh stub
- [x] WT-018 Public demo (no Basic Auth) + working manager WordPress login
- [x] WT-019 Classic theme matching Vietnam Discovery layout, using Watphou Wix photos

## Demo access (not in git)

| Item | Value |
|------|-------|
| URL | https://watphou.smbistro.duckdns.org (public website) |
| HTTP Basic Auth | Removed 2026-08-26 — site is viewable |
| WP editor login | `/wp-login.php` or header **Log in to edit** |
| WP manager | user `manager` / `000000` (demo only) |
| WP admin (dev) | user `admin` — password on server cred file |

## Blocked / client input needed

| Item | Status |
|------|--------|
| Real tour prices | UNRESOLVED |
| Google review import | UNRESOLVED |
| Professional Thai translations | UNRESOLVED |
| BCEL merchant API | UNRESOLVED |
| Bluehost SSH | UNRESOLVED |
| SMTP for booking emails | sendmail not on demo VPS |
| Full live scrape | Wix rate-limited; fallback placeholders used |

## Next actions

1. Visual check of demo vs vietnamdiscovery.com on desktop and mobile; tweak spacing if needed
2. Attach real Wix tour photos as featured images in the media library (WT-102)
3. Client supplies real prices → update tour meta
4. Import genuine Google reviews into testimonial CPT
5. Bluehost staging deploy when credentials available
6. BCEL sandbox integration when contract ready

## Evidence

- `python scripts/verify_demo.py` → demo HTTP 200, smbistro OK
- `wp post list --post_type=tour --format=count` → 24
- SSL cert: watphou.smbistro.duckdns.org expires 2026-11-24
