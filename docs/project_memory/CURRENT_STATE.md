# Current State — Watphou Travels

**Last updated:** 2026-09-24 (homepage hero: crossfade, no white flash)

## Summary

Staging is live on **https://watphoutravels.site** (also **https://www.watphoutravels.site**) with the classic PHP theme. The Hostinger preview name `darkslategray-snake-182151.hostingersite.com` still serves the same WordPress install, but it is **not** the URL to send customers: some networks return NXDOMAIN for `*.hostingersite.com`. September 2026 package texts are imported (15 tours + tailor-made page). Public prices stay **From $XX**. Search engines see `noindex, nofollow`. The front end is **open**; WordPress admin still requires a login. Live Wix (`watphou-travels.com`) is unchanged.

Tour listings and tour pages use Nang’s September 2026 photos mapped by package code (1.1–6.1). The homepage hero is a slideshow of the selected Home Page hero pictures (not the `1.Home (Hero pic)` folder). It shows the first picture immediately, keeps only the current and next picture decoded, and changes about every **2 seconds** on a fast link, **5 seconds** on a moderate link or a computer with 4 gigabytes (GB) of memory or less, and **10 seconds** if the link is slow, “save data” is on, or memory is about 2 GB. Phones can use a 960-pixel-wide hero file. Language follows the Uniform Resource Locator (URL): English stays English, French stays on `/fr/`, Thai stays on `/th/`, unless the visitor clicks EN / FR / TH.

French and Thai public chrome that was still English (What’s included, From $XX per person, booking-form labels, footer Phone/WhatsApp and Email, Tailor-made) now comes from the reviewed workbook plus extra user-interface strings. Destination names stay English, except French **4000 îles**. WordPress File Manager stays **active** on staging so we can overwrite plugin files when Hostinger uploads fail. Deactivate it when the real domain goes live (security; WT-133).

The top menu matches the customer sheet order with consistent capitalization: **Home, Day Tours, 2-Day Tours, 3-Day Tours, 4-6 Day Tours, Destinations, Tailor-made, About Us, Contact**. Duration listing pages (`/day-tours/` and the other duration pages) list tours in the Excel package-code order (1.1, 1.2, 1.3…). Destination membership follows Excel section 5. Homepage Popular Private Tours uses the Quick Edit homepage flag (Excel Home four packages if none are ticked). The homepage block “Tailored Tours for Your Interest” is removed. **About Us** uses the live Wix page text and photos (`https://www.watphou-travels.com/about-us`), as the Excel About Us sheet required. The former Book Online page is titled **Request a quote**.

Managers add, edit (English / French / Thai), preview, hide, publish, or delete tours from **Watphou → Manage tours** without the block editor. **Edit text** on Quick edit tours opens that screen. Photos stay on **Quick edit tours**: **Change top photo** (hero and listing card) and **Change bottom photos** (the grid under the itinerary). Bulk text still works from **Add tours (Excel)**. Quick Edit price, photos, duration, and homepage flag update the listing, the tour page, the request form, French/Thai copies, and the homepage Popular Private Tours block. **Request a quote** saves a booking in WordPress admin **Bookings** and emails **both** office inboxes (`sales.watphoutravel@gmail.com` and `watphoutravel.of@gmail.com`) from `bookings@watphoutravels.site`. The guest does not get an email; they see a confirmation on the page. Missing required fields (Name, Email, Preferred date, privacy agreement) show as a red list next to the button plus a line under each field. The booking row records whether the office email was sent or failed.

HTTPS is forced on the public hosts (HTTP → HTTPS, forwarded-protocol, Strict Transport Security). Certificates stay on Hostinger Let’s Encrypt for `watphoutravels.site` and `www`.

French (`/fr/`) and Thai (`/th/`) use the reviewed workbook `docs/translations/Watphou_EN_FR_TH_reviewed.xlsx`. The English-placeholder banner is hidden after those texts are applied.

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
- [x] WT-107 staging Search Engine Optimization (SEO); technical **100 / 100** on live pages (2026-09-24); Lighthouse-style **90 / 100** because staging stays `noindex`; customer report `docs/SEO_CUSTOMER_REPORT.md`
- [x] WT-112 Quick edit tours; no “Edit website”; Wix favicon
- [x] WT-115 EN/FR/TH review pack (494 strings, Google Translate drafts, Excel + PDF)
- [x] WT-116 Excel tour import + example sheet (3-Day Classic); photos still via Quick edit tours
- [x] WT-117 Publish reviewed FR/TH on `/fr/` and `/th/`; FR and TH sheets on the tour-import example Excel
- [x] WT-118 Public staging hostname `watphoutravels.site` (Hostinger free domain) after preview URL NXDOMAIN for the customer
- [x] WT-119 Customer menu + listing pages + Manage tours + HTTPS force
- [x] WT-120 Excel catalog order (Day Tour 1.1–1.7, destination pages, Home best sellers)
- [x] WT-122 Booking form pre-fills tour name, code, duration, destinations, departure, price, headline
- [x] WT-123 About Us page matches live Wix (`watphou-travels.com/about-us`) per Excel “Same as our old website”
- [x] WT-124 Text-review PDF: remove our developer/placeholder lines; keep customer package copy
- [x] WT-125 Terms of Use: remove From $XX / Banque Pour Le Commerce Exterieur Lao (BCEL) staging notes
- [x] WT-126 Click-to-enlarge lightbox for About Us galleries and other content photos
- [x] WT-127 View tour / tour click show itinerary first; request form sits below
- [x] WT-128 Quick Edit price, photo, duration, homepage flag update listing, tour page, and request form
- [x] WT-129 Send tour request: office email + clear list of missing required fields
- [x] WT-130 Menu Day Tours / 2-Day Tours / 3-Day Tours / 4-6 Day Tours; Book Online → Request a quote; French and Thai language links
- [x] WT-131 September 2026 tour photos; homepage hero slideshow; English/French/Thai stay on the clicked language
- [x] WT-132 Hero 2/5/10 seconds; leftover French/Thai labels; French 4000 îles; File Manager stays on until live domain
- [x] WT-134 Booking mail to both office Gmails via Hostinger (`bookings@watphoutravels.site`)
- [x] WT-136 Homepage: one-line space under the hero; remove decorative “ under Explore Southern Laos like Never Before
- [x] WT-137 Homepage hero keeps only two pictures in memory; phones can use 960-pixel files; listing cards use 600×400
- [x] WT-138 Quick Edit **Change top photo** and **Change bottom photos**; French/Thai copies follow
- [x] WT-139 Excel package code **4.1** on `4-day-southern-laos-escape-2`; homepage gap under **Popular Private Tours** halved; **The Most Popular Destinations** block removed; genuine Google Maps reviews carousel after the 5 steps; footer uses `logo-wpt.jpg` as a white mark; staging Search Engine Optimization (SEO) titles/meta/schema/geo completed (still `noindex`)
- [x] WT-140 Browser memory and loading audit. No JavaScript leak. About decoded photos cut from about 53 MB to about 17 MB. Homepage slideshow pauses when it is off screen. Report: `docs/performance/watphou_performance_audit.md`
- [x] WT-141 Quick edit “Change bottom photos” keeps every photo you click. The tour page shows that exact set. “Preview on the page” shows the grid before Save all changes.
- [x] WT-142 Homepage: gap between Popular Private Tours and Plan Your Private Tour in 5 Simple Steps cut in half (theme 2.5.3)
- [x] WT-143 Homepage Google reviews sorted newest-first (left); 4–5 star only; Places Application Programming Interface daily refresh when a key exists (`watphou-core` 1.8.4)
- [x] WT-144 Homepage hero crossfade: wait until the next photo is decoded; keep the current photo until the fade ends; dark backdrop instead of a transparent pixel (theme 2.5.5)

## Blocked / client input needed

| Item | Status |
|------|--------|
| Real tour prices | UNRESOLVED |
| Final photography | DONE for current pack (September 2026 Nang photos on staging; WT-131) |
| Google review import | Homepage shows the five newest 4–5 star Google Maps reviews, most recent on the left (WT-143). Daily Places Application Programming Interface refresh is coded and still needs a key (WT-121) |
| Further FR/TH wording tweaks | OPEN — send an updated `Watphou_EN_FR_TH_reviewed.xlsx` |
| **Google Analytics 4 (GA4) measurement ID** (`G-XXXXXXXX`) | TODO (WT-113) — Settings → Watphou Travels; do not use on staging |
| **Google Search Console HTML verification** | TODO (WT-114) — **real domain only**, never the Hostinger temporary address |
| Facebook / Instagram / TripAdvisor URLs | Optional |
| Official cancellation percentages | UNRESOLVED |
| BCEL merchant API | UNRESOLVED |
| Production domain + Joker cutover | UNRESOLVED |
| SMTP for booking emails | DONE 2026-09-24 — both office Gmails; From `bookings@watphoutravels.site` |

## Next actions

1. Ask the customer for a restricted Places Application Programming Interface key (WT-121). The Place Identifier is already stored.
2. Customer still needs real prices (photography for the current pack is on staging)
3. At real-domain cutover: deactivate WordPress File Manager (WT-133); sending From `bookings@watphou-travels.com` (WT-135, not `@www…`); keep Gmail as the office inbox.
