# Current State — Watphou Travels

**Last updated:** 2026-09-10 (WT-110 Polylang done; site password removed)

## Summary

Staging is live on Hostinger temporary domain **https://darkslategray-snake-182151.hostingersite.com** with the same classic PHP theme design as the former demo (vietnamdiscovery.com layout). September 2026 package texts are imported (15 tours + tailor-made page). Public prices stay **From $XX**. Search engines see `noindex, nofollow`. The front end is **open** (no HTTP Basic Auth); WordPress admin still requires a login.

The old DuckDNS demo on Virtual Private Server (VPS) `sm` was removed earlier; `smbistro` and other sites on `sm` were not modified. Live Wix and Joker Domain Name System (DNS) are untouched.

## Staging access (not in git)

| Item | Value |
|------|-------|
| URL | https://darkslategray-snake-182151.hostingersite.com |
| Host | Hostinger (username `u916301613`, order `1009997687`) |
| Front end | Open URL — no site password |
| WordPress login | `/wp-login.php` — user `wptadmin` — password in `work/wp_admin_pass.txt` (gitignored) |
| Search engines | `noindex, nofollow`; `WATPHOU_ENV=staging` in server `wp-config.php` |
| Docs | `docs/HOSTINGER_DEPLOYMENT.md` |

## Completed

- [x] WT-001 through WT-019, WT-108 (see git history / earlier entries)
- [x] WT-201 Package JSON from 20261009 PDFs; draft prices private under `content/draft/`
- [x] WT-202 `content/redirects.csv` rewritten (keep Wix slugs, 301 tours/destinations, 410 shop)
- [x] WT-203 Staging env mu-plugin, admin JSON importer, duration menus, homepage bestsellers 1.1/2.1/2.2/3.1
- [x] WT-204 WordPress + theme + plugins + Polylang + Yoast on Hostinger temporary domain
- [x] WT-205 Content import, placeholder media, password gate, noindex, smoke-test
- [x] WT-206 `docs/HOSTINGER_DEPLOYMENT.md` + cutover Domain Name System (DNS) / email / noindex checklist in `TASKS.md`
- [x] WT-110 Polylang English default (hide `/en/`); French/Thai empty drafts; site password removed

## Blocked / client input needed

| Item | Status |
|------|--------|
| Real tour prices | UNRESOLVED (draft spreadsheet not public) |
| Final photography | UNRESOLVED (theme placeholders in use) |
| Google review import | UNRESOLVED |
| Professional Thai translations | UNRESOLVED |
| BCEL merchant API | UNRESOLVED |
| Final production domain attach + Joker cutover | UNRESOLVED |
| SMTP for booking emails | UNRESOLVED |

## Next actions

1. WT-107 — Search Engine Optimization (SEO) polish while keeping temporary domain `noindex`
2. Client supplies real prices → update tour meta (never invent figures)
3. Swap placeholder photos for final client photography
4. When ready: Hostinger domain connect → Joker A/`www` only → remove noindex (see `HOSTINGER_DEPLOYMENT.md`)
5. Banque Pour Le Commerce Exterieur Lao (BCEL) sandbox when credentials exist

## Evidence (2026-09-10 Hostinger staging)

- Homepage (open, no site password): 4 bestsellers (Bolaven 1-day, Bolaven 2-day, 4000 Islands + Vat Phou 2-day, 3-day classic)
- Polylang: `en` / `fr` / `th`; `/en/about-us/` → 301 `/about-us/`; language switcher shows EN only until FR/TH are published
- Key pages 200: `/about-us/`, `/contact-us/`, `/day-tours/`, `/book-online/`, `/destinations/`, nested destinations, `/tailor-made-tours/`
- Redirects: `/bolaven-plateau-classic-full-day-tour` → `/tours/...` (301); shop dummies → 410
- Headers: `X-Robots-Tag: noindex, nofollow`; unauthenticated front → 200
- Plugins active: watphou-core, watphou-bookings, polylang, wordpress-seo
- No `package.json` added (WordPress deploy path, not Node/static Git import)
