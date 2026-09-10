# Current State — Watphou Travels

**Last updated:** 2026-09-10 (EN/FR/TH review pack; Google Analytics 4 and Search Console still TODO)

## Summary

Staging is live on Hostinger temporary domain **https://darkslategray-snake-182151.hostingersite.com** with the classic PHP theme. September 2026 package texts are imported (15 tours + tailor-made page). Public prices stay **From $XX**. Search engines see `noindex, nofollow`. The front end is **open**; WordPress admin still requires a login.

French (`/fr/`) and Thai (`/th/`) public pages are **English placeholders** with a banner (not machine-translated on the website). A **review pack** of automatic French and Thai drafts lives in `docs/translations/` (Excel to edit, PDF to read). Those drafts are **not** published yet.

The old DuckDNS demo on Virtual Private Server (VPS) `sm` was removed; `smbistro` was not modified. Live Wix and Joker Domain Name System (DNS) are untouched.

## Staging access (not in git)

| Item | Value |
|------|-------|
| URL | https://darkslategray-snake-182151.hostingersite.com |
| Host | Hostinger (username `u916301613`, order `1009997687`) |
| Front end | Open URL — no site password |
| WordPress login | `/wp-login.php` — user `wptadmin` — password in `work/wp_admin_pass.txt` (gitignored) |
| Search engines | `noindex, nofollow`; `WATPHOU_ENV=staging` in server `wp-config.php` |
| Docs | `docs/HOSTINGER_DEPLOYMENT.md` |
| Translation review | `docs/translations/Watphou_EN_FR_TH_review.xlsx` (edit) and `.pdf` (read) |

## Completed

- [x] WT-001 through WT-019, WT-108 (see git history)
- [x] WT-201 through WT-206 Hostinger staging, packages, redirects
- [x] WT-110 Polylang English default; FR/TH public English placeholders
- [x] WT-107 staging Search Engine Optimization (SEO); temporary domain still `noindex`
- [x] WT-112 Quick edit tours; no “Edit website”; Wix favicon
- [x] WT-115 EN/FR/TH review pack (494 strings, Google Translate drafts, Excel + PDF)

## Blocked / client input needed

| Item | Status |
|------|--------|
| Real tour prices | UNRESOLVED |
| Final photography | UNRESOLVED |
| Google review import | UNRESOLVED |
| Approve FR/TH drafts in the Excel review pack | OPEN (WT-104) |
| **Google Analytics 4 (GA4) measurement ID** (`G-XXXXXXXX`) | TODO (WT-113) — Settings → Watphou Travels; do not use on staging |
| **Google Search Console HTML verification** | TODO (WT-114) — **real domain only**, never the Hostinger temporary address |
| Facebook / Instagram / TripAdvisor URLs | Optional |
| Official cancellation percentages | UNRESOLVED |
| BCEL merchant API | UNRESOLVED |
| Production domain + Joker cutover | UNRESOLVED |
| SMTP for booking emails | UNRESOLVED |

## Next actions

1. Review French and Thai in `docs/translations/Watphou_EN_FR_TH_review.xlsx`; mark rows Approved
2. Send Google Analytics 4 (GA4) ID and Google Search Console code for `www.watphou-travels.com` only
3. After approval: apply translations to Polylang copies; then production cutover
