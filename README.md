# Watphou Travels — WordPress Website

Custom WordPress rebuild for [Watphou Travels](https://www.watphou-travels.com/), migrating from Wix to a lightweight block theme with booking workflow and multilingual support (English, French, Thai).

## Quick start for developers

```bash
# Clone
git clone git@github.com:Xxobster/watphoutravelsbluehost.git
cd watphoutravelsbluehost

# Read agent onboarding
cat docs/project_memory/AGENT_ONBOARDING.md

# Deploy theme/plugins to demo
python scripts/deploy_demo.py

# Verify demo and existing site health
python scripts/verify_demo.py
```

## Demo site

- **URL**: https://watphou.smbistro.duckdns.org (HTTP Basic Auth required)
- **WordPress admin**: `/wp-admin/` — user `manager` (demo password documented in deployment notes, not in git)
- **Environment**: Protected demo, `noindex`, mock payments only

## Repository structure

| Path | Purpose |
|------|---------|
| `wp-content/themes/watphou-travels/` | Custom block theme |
| `wp-content/plugins/watphou-core/` | Tours, taxonomies, settings, blocks |
| `wp-content/plugins/watphou-bookings/` | Booking requests and payments |
| `wp-content/mu-plugins/` | Demo hardening, noindex |
| `content/extracted/` | Scraped page/tour JSON |
| `scripts/` | Deploy, scrape, inventory, verify |
| `docs/project_memory/` | Living project state for any agent |

## Current status

See [PROJECT_STATE.md](PROJECT_STATE.md) → [docs/project_memory/CURRENT_STATE.md](docs/project_memory/CURRENT_STATE.md).

## Production

Future deployment to Bluehost managed WordPress — see [docs/BLUEHOST_DEPLOYMENT.md](docs/BLUEHOST_DEPLOYMENT.md).
