# Current State — Watphou Travels

**Last updated:** 2026-09-14 (public staging hostname `https://watphoutravels.site` — Hostinger preview name does not resolve for some visitors)

## Summary

Staging is live on **https://watphoutravels.site** (also **https://www.watphoutravels.site**) with the classic PHP theme. The Hostinger preview name `darkslategray-snake-182151.hostingersite.com` still serves the same WordPress install, but it is **not** the URL to send customers: some networks return NXDOMAIN for `*.hostingersite.com`. September 2026 package texts are imported (15 tours + tailor-made page). Public prices stay **From $XX**. Search engines see `noindex, nofollow`. The front end is **open**; WordPress admin still requires a login. Live Wix (`watphou-travels.com`) is unchanged.

Managers add or update tour **text** with an Excel workbook (**Watphou → Add tours (Excel)**). The example file has English **Tours** / **Itinerary** plus **FR** and **TH** sheets. Photos stay on **Quick edit tours**.

French (`/fr/`) and Thai (`/th/`) now use the reviewed workbook `docs/translations/Watphou_EN_FR_TH_reviewed.xlsx` (all 494 rows status **Revised**). The English-placeholder banner is hidden after those texts are applied. Later Excel updates re-apply when the JSON file hash changes.

The old DuckDNS demo on Virtual Private Server (VPS) `sm` was removed; `smbistro` was not modified. Live Wix and Joker Domain Name System (DNS) are untouched.

## Staging access (not in git)

| Item | Value |
|------|-------|
| URL (send this to reviewers) | https://watphoutravels.site or https://www.watphoutravels.site |
| Hostinger preview (owner fallback only) | https://darkslategray-snake-182151.hostingersite.com |
| Host | Hostinger (username `u916301613`, order `1009997687`) |
| Front end | Open URL — no site password |
| WordPress login | `/wp-login.php` — user `wptadmin` — password in `work/wp_admin_pass.txt` (gitignored) |
| Search engines | `noindex, nofollow`; `WATPHOU_ENV=staging` in server `wp-config.php` |
| Docs | `docs/HOSTINGER_DEPLOYMENT.md` |
| Live FR/TH source | `docs/translations/Watphou_EN_FR_TH_reviewed.xlsx` |
| Tour Excel example | `wp-content/plugins/watphou-core/data/watphou-package-import-example.xlsx` |

## Completed

- [x] WT-001 through WT-019, WT-108 (see git history)
- [x] WT-201 through WT-206 Hostinger staging, packages, redirects
- [x] WT-110 Polylang English default; FR/TH public English placeholders
- [x] WT-107 staging Search Engine Optimization (SEO); temporary domain still `noindex`
- [x] WT-112 Quick edit tours; no “Edit website”; Wix favicon
- [x] WT-115 EN/FR/TH review pack (494 strings, Google Translate drafts, Excel + PDF)
- [x] WT-116 Excel tour import + example sheet (3-Day Classic); photos still via Quick edit tours
- [x] WT-117 Publish reviewed FR/TH on `/fr/` and `/th/`; FR and TH sheets on the tour-import example Excel
- [x] WT-118 Public staging hostname `watphoutravels.site` (Hostinger free domain) after preview URL NXDOMAIN for the customer

## Blocked / client input needed

| Item | Status |
|------|--------|
| Real tour prices | UNRESOLVED |
| Final photography | UNRESOLVED |
| Google review import | UNRESOLVED |
| Further FR/TH wording tweaks | OPEN — send an updated `Watphou_EN_FR_TH_reviewed.xlsx` |
| **Google Analytics 4 (GA4) measurement ID** (`G-XXXXXXXX`) | TODO (WT-113) — Settings → Watphou Travels; do not use on staging |
| **Google Search Console HTML verification** | TODO (WT-114) — **real domain only**, never the Hostinger temporary address |
| Facebook / Instagram / TripAdvisor URLs | Optional |
| Official cancellation percentages | UNRESOLVED |
| BCEL merchant API | UNRESOLVED |
| Production domain + Joker cutover | UNRESOLVED |
| SMTP for booking emails | UNRESOLVED |

## Next actions

1. Send the customer **https://watphoutravels.site** (not the Hostinger preview URL)
2. Spot-check `/fr/` and `/th/` (home, one tour, privacy). Send any wording fixes in a new reviewed Excel file
3. Production cutover when `watphou-travels.com` is ready (Joker Domain Name System still untouched)
