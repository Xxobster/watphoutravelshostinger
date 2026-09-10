# Watphou Travels — WordPress Website

Custom WordPress rebuild for [Watphou Travels](https://www.watphou-travels.com/), migrating from Wix to a lightweight theme with booking workflow and multilingual support (English, French, Thai).

## Quick start for developers

```bash
# Clone
git clone git@github.com:Xxobster/watphoutravelshostinger.git
cd watphoutravelshostinger

# Read agent onboarding
cat docs/project_memory/AGENT_ONBOARDING.md

# Verify Hostinger staging and that the old VPS demo is gone
python scripts/verify_demo.py
```

## Staging site (Hostinger temporary domain)

- **URL**: https://darkslategray-snake-182151.hostingersite.com
- **WordPress admin**: `/wp-login.php` — user `wptadmin` (password not in git)
- **Environment**: Temporary Hostinger domain, `noindex`, mock payments only
- **Retired**: `https://watphou.smbistro.duckdns.org` was removed from Virtual Private Server (VPS) `sm` on 2026-09-10

## Repository structure

| Path | Purpose |
|------|---------|
| `wp-content/themes/watphou-travels/` | Custom theme |
| `wp-content/plugins/watphou-core/` | Tours, taxonomies, settings, blocks |
| `wp-content/plugins/watphou-bookings/` | Booking requests and payments |
| `wp-content/mu-plugins/` | Environment hardening, noindex |
| `content/extracted/` | Scraped page/tour JSON |
| `scripts/` | Deploy, scrape, inventory, verify |
| `docs/project_memory/` | Living project state for any agent |

## Current status

See [docs/project_memory/CURRENT_STATE.md](docs/project_memory/CURRENT_STATE.md).

## Production

Hostinger WordPress. Temporary domain now; attach the real domain when ready. Older Bluehost notes: [docs/BLUEHOST_DEPLOYMENT.md](docs/BLUEHOST_DEPLOYMENT.md) (superseded).
