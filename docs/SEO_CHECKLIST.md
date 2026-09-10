# SEO Checklist — Watphou Travels

**Backlog:** WT-107 in `docs/project_memory/TASKS.md` — complete Search Engine Optimization (SEO) for the highest possible Google ranking. This checklist is the evidence list for that task. Requirement R23 stays pending until production items below are done.

## Staging (must stay noindex until the real domain is attached)

- [x] Hostinger temporary domain `https://darkslategray-snake-182151.hostingersite.com` returns `noindex, nofollow` (checked 2026-09-10)
- [x] Former VPS demo `watphou.smbistro.duckdns.org` removed (WT-108)
- [x] Yoast noindex confirmed on Hostinger after Yoast is installed (`X-Robots-Tag: noindex, nofollow`; sitemap also `noindex`)

## Production launch

- [ ] Remove demo noindex settings
- [ ] Enable XML sitemap (Yoast)
- [ ] Submit sitemap to Google Search Console
- [x] Unique title + meta description per page/tour (staging; copy is English until translations exist)
- [x] Canonical Uniform Resource Locators (URLs) (Yoast)
- [x] hreflang EN/FR/TH (Polylang + Yoast on published placeholders)
- [x] Open Graph images per tour (featured image; default image = logo / Tad Fane)
- [x] Breadcrumbs (Yoast on pages, tours, archives)
- [x] 301 redirects from Wix URLs (`content/redirects.csv`)
- [x] 410 for retired store products
- [x] Descriptive image alternative (alt) text fallback from tour title
- [x] Internal links between related tours (same destination)
- [x] Schema: TravelAgency, Organization, LocalBusiness (homepage)
- [x] Schema: TouristTrip on tours
- [x] Schema: Offer only when real price visible (skipped for `XX`)
- [x] Schema: Review only with genuine reviews (none yet)
- [x] Google Analytics 4 (GA4) placeholder configured (Settings field; not fired on staging)
- [ ] Google Search Console verification (needs production domain + real code)
- [ ] Google Business Profile aligned with production Uniform Resource Locator (URL), Pakse address, and phone
- [ ] Lighthouse performance ≥ 80 (target); Core Web Vitals in the “good” range
- [ ] One `h1` per page and a clear heading hierarchy
- [ ] Mobile-friendly layout verified

## Content SEO

- [x] Travel Guide stub (`/travel-guide/`) for long-tail keywords (expand later)
- [x] Destination archive pages with unique intro text
- [x] Legal pages: Privacy, Terms, Cancellation
- [x] Keyword-focused copy on homepage, tour pages, About, and Contact (no invented facts)
