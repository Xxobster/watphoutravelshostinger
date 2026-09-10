# Bluehost Deployment — Watphou Travels

**Status:** SUPERSEDED 2026-09-10. Production/staging is **Hostinger**, not Bluehost. Current URL: https://darkslategray-snake-182151.hostingersite.com

Keep this file only as historical notes. Do not follow the Bluehost steps below unless the host is changed again.


## Target

Bluehost managed WordPress hosting for production `watphou-travels.com`.

## Deployment separation

| Layer | Method | Notes |
|-------|--------|-------|
| Theme + plugins | Git → SSH/SFTP | Same repo `watphoutravelshostinger` |
| wp-config.php | Manual on server | Never in git |
| Media/uploads | SFTP or migration plugin | One-time + incremental |
| Database content | WP-CLI export/import | **Never overwrite live DB after bookings** |
| Secrets | Bluehost panel / env | BCEL, SMTP, GA4 |

## Recommended workflow

1. Create Bluehost staging subdomain
2. Add SSH key for developer (not owner billing login)
3. Clone repo on staging
4. Deploy `wp-content/themes` and `wp-content/plugins` via `scripts/deploy_demo.py` adapted for Bluehost paths
5. Run `wp plugin install polylang wordpress-seo --activate`
6. Import content via `wp watphou import-content` (staging only, before go-live)
7. Manager tests on staging
8. DNS cutover from Wix when approved
9. Apply 301 redirects at launch

## Production checklist

- [ ] Strong manager password (not `000000`)
- [ ] Remove demo/noindex settings
- [ ] Real SMTP for booking emails
- [ ] BCEL sandbox tested then production
- [ ] GA4 + Search Console
- [ ] SSL via Bluehost
- [ ] Cloudflare optional (DNS plan)
- [ ] Daily backup (Bluehost + off-site)

## Critical rule

After production receives real bookings or manager edits:

**Never replace the entire production database with an old staging dump.**

Deploy code and controlled migrations separately.

## Tooling

```bash
# Example production deploy (adapt paths)
export DEMO_SSH_HOST=bluehost-staging
export DEMO_WEB_ROOT=/home/user/public_html
python scripts/deploy_demo.py
```

Create `scripts/deploy_production.sh` when Bluehost SSH details are available.

## Information needed from client

- Bluehost SSH hostname, user, port
- Staging URL
- Production URL confirmation
- SMTP credentials
- BCEL production credentials (after sandbox)
