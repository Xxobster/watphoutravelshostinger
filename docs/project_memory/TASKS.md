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
| WT-102 | Final photography into media library (placeholders used on staging) | pending |
| WT-103 | Client real prices | blocked |
| WT-104 | Professional TH translations | blocked |
| WT-105 | BCEL live integration | blocked |
| WT-106 | Hostinger production domain attach + Joker cutover | blocked |
| WT-107 | Complete Search Engine Optimization (SEO) for the highest possible Google ranking | pending |
| WT-109 | Deploy custom theme + plugins + content to Hostinger temporary domain | done |
| WT-110 | Finish Polylang admin wizard on staging (EN default hide URL; FR/TH empty drafts) | done |
| WT-111 | Production cutover checklist (DNS A/www only; SSL; remove noindex; SMTP test) | pending |

### WT-111 — Production cutover checklist (do not run until approved)

Full detail: `docs/HOSTINGER_DEPLOYMENT.md`.

- [ ] Export/screenshot all Joker Domain Name System (DNS) records; lower Time To Live (TTL) on A/`www` ~300s, 24–48h before
- [ ] Connect domain in Hostinger first so Secure Sockets Layer (SSL) exists
- [ ] Change **only** website A/`www` — never Mail Exchanger (MX) / Sender Policy Framework (SPF) / DomainKeys Identified Mail (DKIM) / Domain-based Message Authentication, Reporting and Conformance (DMARC)
- [ ] Primary `https://www.watphou-travels.com`; apex → www 301
- [ ] Serialized-safe search-replace staging domain → final domain
- [ ] `WATPHOU_ENV=production`; remove noindex (and any leftover staging auth constants)
- [ ] Submit Yoast sitemap (final Uniform Resource Locators (URLs) only) in Google Search Console — **no** Change of Address
- [ ] Keep Wix paid 2–4 weeks; Hostinger backup
- [ ] Test contact form off office network after Simple Mail Transfer Protocol (SMTP)

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

**Blocked until:** production domain is live (indexing), real prices (Offer schema), genuine reviews (Review schema), Google Analytics 4 / Google Search Console identifiers, professional Thai copy (Thai pages).

**Evidence:** `docs/SEO_CHECKLIST.md` all production items checked; sitemap accepted in Google Search Console; Lighthouse run logged in `docs/project_memory/TEST_LOG.md`.
