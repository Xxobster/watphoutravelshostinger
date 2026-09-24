# Tasks — Watphou Travels

| ID | Task | Status |
|----|------|--------|
| WT-001 | Repository bootstrap | done |
| WT-002 | Project memory (12 files) | done |
| WT-003 | Backup inventory script | done |
| WT-004 | Live site scraper + fallback content | done |
| WT-005 | Requirements matrix + design ref | done |
| WT-006 | Demo VPS provisioning | done |
| WT-007 | Deploy/verify/screenshot scripts | done |
| WT-008 | Block theme watphou-travels | done |
| WT-009 | Plugin watphou-core | done |
| WT-010 | Content import | done |
| WT-011 | Polylang EN/FR/TH | done |
| WT-012 | Plugin watphou-bookings | done |
| WT-013 | Payment layer + BCEL doc | done |
| WT-014 | Manager dashboard + guide | done |
| WT-015 | SEO + security docs | done |
| WT-016 | Test suite (verify_demo) | done |
| WT-017 | Bluehost deployment doc | done |
| WT-018 | Public demo site + working manager login | done |
| WT-019 | Match vietnamdiscovery.com layout with Watphou Wix photos | done |
| WT-108 | Remove Watphou demo from VPS sm; switch staging to Hostinger temporary domain | done |
| WT-201 | Convert 20261009 PDFs into content/packages JSON; prices stay XX; draft prices private | done |
| WT-202 | Rewrite redirects.csv (keep Wix slugs, 301 tours/destinations, 410 shop; no /en/) | done |
| WT-203 | Staging env flag, relative links, admin JSON importer, bestsellers, duration menus | done |
| WT-204 | Install WordPress on Hostinger temp domain; deploy theme + plugins + Polylang + Yoast | done |
| WT-205 | Import content, placeholder images, password-protect, noindex, smoke-test | done |
| WT-206 | Document Hostinger vs Git (no package.json); cutover/DNS/email/noindex in HOSTINGER_DEPLOYMENT.md | done |

## Follow-up backlog

| ID | Task | Status |
|----|------|--------|
| WT-101 | Re-scrape full Wix text when rate limit clears | pending |
| WT-102 | Final photography into media library (placeholders used on staging) | done (September 2026 Nang photos on listings, tour pages, and homepage hero; WT-131) |
| WT-103 | Client real prices | blocked |
| WT-104 | Professional TH translations | done for current pack (human-reviewed Excel published as WT-117; further edits welcome) |
| WT-105 | BCEL live integration | blocked |
| WT-106 | Hostinger production domain attach + Joker cutover | blocked |
| WT-107 | Complete Search Engine Optimization (SEO) for the highest possible Google ranking | staging technical SEO **100 / 100** (live 2026-09-24); Lighthouse-style **90 / 100** (only fail is intentional `noindex`); production indexing still blocked |
| WT-109 | Deploy custom theme + plugins + content to Hostinger temporary domain | done |
| WT-110 | Finish Polylang admin wizard on staging (EN default hide URL; FR/TH empty drafts) | done (superseded: FR/TH now published English placeholders, not empty drafts) |
| WT-111 | Production cutover checklist (DNS A/www only; SSL; remove noindex; SMTP test) | pending |
| WT-112 | Manager Quick edit tours + remove front “Edit website” + Wix favicon | done |
| WT-113 | Google Analytics 4 (GA4) measurement ID (`G-XXXXXXXX`) — paste in Settings → Watphou Travels | blocked (need client ID; do not invent) |
| WT-114 | Google Search Console HTML verification — **real domain only**, never the Hostinger temporary address | blocked (need client code + production domain) |
| WT-115 | EN/FR/TH review pack (Excel + PDF auto-drafts) | done |
| WT-116 | Manager Excel import for tours + example workbook (3-Day Classic); fix Import packages 404 | done |
| WT-117 | Publish reviewed FR/TH from `Watphou_EN_FR_TH_reviewed.xlsx`; add FR and TH sheets to the tour-import example Excel | done |
| WT-118 | Public staging hostname after Hostinger preview NXDOMAIN (`watphoutravels.site`) | done |
| WT-119 | Customer menu (Home / Day Tour / 2-DAY / 3-DAY / 4-6 / Destinations / Tailor-Made / About Us / Contact); fill listing pages; remove homepage interest row; HTTPS force; Manage tours EN/FR/TH with preview/draft/publish | done |
| WT-120 | Lock public tour order and destination membership to `Website structures for Micah 2026-09-01.xlsx` (codes 1.1–6.1, Home best sellers, destination YES table) | done |
| WT-121 | Pull genuine Google reviews onto Home (automatic cache via Google Places Application Programming Interface). Needs Place Identifier and Application Programming Interface key from the customer. Never invent reviews. | newest 4–5 star carousel live; daily Places Application Programming Interface refresh coded; still needs a key |
| WT-122 | Pre-fill the public booking form with the current tour (name, code, duration, destinations, departure, price, headline); guest fills the rest. Same form on View tour and Book this tour. | done |
| WT-123 | About Us page: same copy and photos as live Wix `https://www.watphou-travels.com/about-us` (Excel About Us sheet) | done |
| WT-124 | Apply `Watphou_Travels_Text_Review_Recommendations.pdf` only to our template/legal/button copy; leave customer package, menu, and About text | done |
| WT-125 | Terms of Use: remove From $XX and Banque Pour Le Commerce Exterieur Lao (BCEL) notes | done |
| WT-126 | Click-to-enlarge lightbox for About Us photo rows and other content photos (not logos, icons, or destination tiles) | done |
| WT-127 | View tour and tour-card clicks open itinerary details; request form moves below; remove extra Request this tour under the description | done |
| WT-128 | Quick Edit price/photo/duration/homepage apply on listing, tour page, request form, and French/Thai copies | done |
| WT-129 | Send tour request lists all missing required fields (Name, Email, Preferred date, privacy); save booking + email Pakse office | done |
| WT-130 | Standardize menu capitalization; rename Book Online to Request a quote; fix French/Thai language Uniform Resource Locators (URLs) | done |
| WT-131 | September 2026 tour photos + homepage hero slideshow; keep English/French/Thai when following in-language links | done |
| WT-132 | Drop hero intervals to 2/5/10 seconds; translate leftover public English on French/Thai pages; French destination label 4000 îles | done |
| WT-133 | Keep WordPress File Manager **on** while staging on `watphoutravels.site`. Deactivate it at real-domain cutover: it is a security issue on a public live site (file write as WordPress admin, often targeted). Until then leave it on for emergency overwrite of plugin files. | pending (leave on until live domain) |
| WT-134 | Simple Mail Transfer Protocol (SMTP): email both office Gmails from Hostinger `bookings@watphoutravels.site`; log send/fail on the booking | done |
| WT-136 | Homepage: one-line gap under the hero; remove the decorative quotation mark under **Explore Southern Laos like Never Before** | done |
| WT-137 | Reduce homepage browser memory: decode only current+next hero slides; 960-pixel hero files; listing photos `tour-card` 600×400 | done |
| WT-138 | Quick Edit can change the tour-page **top photo** and the **bottom photo grid**; French/Thai copies follow | done |
| WT-139 | Package code 4.1 on the 4-day booking form; homepage spacing; remove Destinations block; Google reviews carousel; white footer logo; staging Search Engine Optimization (SEO) completeness | done |
| WT-140 | Browser Random Access Memory (RAM), processor, and loading audit; shrink oversized photos without removing features | done |
| WT-141 | Quick edit bottom photos: select any number, preview the grid before save | done |
| WT-142 | Cut homepage gap before Plan Your Private Tour in 5 Simple Steps in half | done |
| WT-143 | Homepage Google reviews: newest 4–5 star first (left); Places Application Programming Interface refresh when a key exists | done (key still needed for live daily pull) |
| WT-144 | Smooth homepage hero change: no white flash | done |

### WT-111 — Production cutover checklist (do not run until approved)

Full detail: `docs/HOSTINGER_DEPLOYMENT.md`.

- [ ] Export/screenshot all Joker Domain Name System (DNS) records; lower Time To Live (TTL) on A/`www` ~300s, 24–48h before
- [ ] Connect domain in Hostinger first so Secure Sockets Layer (SSL) exists
- [ ] Change **only** website A/`www` — never Mail Exchanger (MX) / Sender Policy Framework (SPF) / DomainKeys Identified Mail (DKIM) / Domain-based Message Authentication, Reporting and Conformance (DMARC)
- [ ] Primary `https://www.watphou-travels.com`; apex → www 301
- [ ] Serialized-safe search-replace staging domain → final domain
- [ ] `WATPHOU_ENV=production`; remove noindex (and any leftover staging auth constants)
- [ ] **Deactivate WordPress File Manager** (WT-133) — leave it on during staging; it is a security issue on the public live domain
- [ ] Submit Yoast sitemap (final Uniform Resource Locators (URLs) only) in Google Search Console — **no** Change of Address. Needs WT-114 first.
- [ ] Add Google Analytics 4 (GA4) measurement ID (`G-XXXXXXXX`) in Settings → Watphou Travels (WT-113). Do not fire analytics on the Hostinger temporary domain.
- [ ] Keep Wix paid 2–4 weeks; Hostinger backup
- [ ] Test contact form off office network (staging Simple Mail Transfer Protocol is WT-134; repeat after live-domain cutover)
- [ ] **WT-135** Sending From `bookings@watphou-travels.com` (not `@www…`). Recipients stay the two Gmails. Do not change Joker Mail Exchanger (MX) until approved.

### WT-107 — Complete Search Engine Optimization (SEO) for Google ranking

**Goal:** Implement Search Engine Optimization (SEO) fully so Watphou Travels ranks as high as possible in Google for relevant searches (Laos tours, Pakse, Wat Phou, Bolaven Plateau, and related queries). Requirement R23 in `docs/REQUIREMENTS_MATRIX.md` stays pending until this is done. The Hostinger temporary domain must stay `noindex` until the real domain is attached.

**Scope (do all of the following, not docs only):**

1. **Technical Search Engine Optimization (SEO)**
   - Unique title and meta description on every page, tour, destination, and language
   - Canonical Uniform Resource Locators (URLs)
   - XML sitemap via Yoast Search Engine Optimization (Yoast SEO); submit to Google Search Console on production
   - Robots: keep demo `noindex`; on production allow indexing and remove `X-Robots-Tag: noindex`
   - `hreflang` for English / French / Thai (Polylang + Yoast)
   - 301 redirects from Wix Uniform Resource Locators (URLs) (`content/redirects.csv`); 410 for retired store products
   - Fast pages: image compression, lazy load, Core Web Vitals; Lighthouse performance target ≥ 80
   - Mobile-friendly markup, breadcrumbs, clean heading hierarchy (one `h1` per page)

2. **On-page and content**
   - Keyword-focused copy on homepage, tour pages, destination archives, About, Contact
   - Descriptive image filenames and alternative (alt) text
   - Internal links between related tours and destinations
   - Travel Guide / Blog (requirement R31) for long-tail search queries
   - Legal pages: Privacy, Terms, Cancellation (also needed for trust and indexing)

3. **Structured data (schema)**
   - Organization / TravelAgency / LocalBusiness on the homepage
   - TouristTrip on tour pages
   - Offer schema only when a real price is visible (not `From $XX` placeholders)
   - Review schema only with genuine Google reviews (never invented)

4. **Local and launch Search Engine Optimization (SEO)**
   - Google Search Console verification and sitemap submit (needs production domain + IDs)
   - Google Analytics 4 (GA4) placeholder then real measurement identifier
   - Google Business Profile alignment (Pakse address, phone, website) when production Uniform Resource Locator (URL) is live
   - Open Graph images per tour for social and search previews

**Staging (2026-09-24):** Yoast company representation, unique titles/meta descriptions, **canonical tags printed by `watphou-core` 1.8.1** (Yoast omits them while `blog_public=0`), breadcrumbs, sitemap enabled but **not** submitted, `hreflang` EN/FR/TH plus `x-default`, TravelAgency + LocalBusiness (geo, opening hours, aggregate rating) + TouristTrip schema, genuine Review schema from Google Maps quotes, legal pages, travel-guide stub, image `loading=lazy`. Temporary domain stays `noindex`. Live scores: technical **100 / 100**, Lighthouse-style Search Engine Optimization **90 / 100**. Customer copy: `docs/SEO_CUSTOMER_REPORT.md`.

**Still blocked until production:** indexing, Google Search Console sitemap submit, Google Analytics 4 (GA4) ID, Google Business Profile website URL swap, Offer schema (real prices), professional French/Thai Search Engine Optimization titles if the office wants them.

**Evidence:** `docs/SEO_CHECKLIST.md`; `docs/SEO_CUSTOMER_REPORT.md`; Lighthouse-equivalent run in `docs/project_memory/TEST_LOG.md`.

### WT-121 — Genuine Google reviews on Home (automatic)

**Goal:** Show real Google reviews in the Home **What Our Clients Say** block. Never invent quotes.

**Done 2026-09-24 (WT-139 then WT-143):** Five genuine 4–5 star reviews from the public listing [Watphou Travels on Google Maps](https://maps.app.goo.gl/QUTghqefjS9LCZSA8) (Place Identifier `ChIJzWB4m7P4FDER1RsT6DS65oE`), **newest first** (leftmost). 1-star quotes are not shown. Rolling cards match IZITOUR. Each card opens that reviewer’s Maps contribution. “Read more on Google” opens the listing.

**Automatic when a key exists:** Google Places Application Programming Interface (Places API) Place Details, `reviewsSort=NEWEST`, cached in WordPress option `watphou_google_reviews_cache` for one day. Key from `WATPHOU_GOOGLE_PLACES_KEY` in `wp-config.php` or Settings → Watphou Travels. Google returns at most five reviews per request; we keep rating ≥ 4 and backfill from `data/google-reviews.json`. Do not scrape Google as the production updater. Need a restricted Application Programming Interface key from the customer.

