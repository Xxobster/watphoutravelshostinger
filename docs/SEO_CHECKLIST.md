# SEO Checklist — Watphou Travels

**Backlog:** WT-107 in `docs/project_memory/TASKS.md` — complete Search Engine Optimization (SEO) for the highest possible Google ranking. This checklist is the evidence list for that task. Requirement R23 stays pending until production items below are done.

## Staging (must stay noindex until the real domain is attached)

- [x] Hostinger temporary domain `https://darkslategray-snake-182151.hostingersite.com` returns `noindex, nofollow` (checked 2026-09-10)
- [x] Former VPS demo `watphou.smbistro.duckdns.org` removed (WT-108)
- [ ] Yoast noindex confirmed on Hostinger after Yoast is installed

## Production launch

- [ ] Remove demo noindex settings
- [ ] Enable XML sitemap (Yoast)
- [ ] Submit sitemap to Google Search Console
- [ ] Unique title + meta description per page/tour
- [ ] Canonical URLs
- [ ] hreflang EN/FR/TH (Polylang + Yoast)
- [ ] Open Graph images per tour
- [ ] Breadcrumbs
- [ ] 301 redirects from Wix URLs (`content/redirects.csv`)
- [ ] 410 for retired store products
- [ ] Descriptive image filenames + alt text
- [ ] Internal links between related tours
- [ ] Schema: TravelAgency, Organization (homepage)
- [ ] Schema: TouristTrip on tours (when structured)
- [ ] Schema: Offer only when real price visible
- [ ] Schema: Review only with genuine reviews
- [ ] Google Analytics 4 (GA4) placeholder configured
- [ ] Google Search Console verification
- [ ] Google Business Profile aligned with production Uniform Resource Locator (URL), Pakse address, and phone
- [ ] Lighthouse performance ≥ 80 (target); Core Web Vitals in the “good” range
- [ ] One `h1` per page and a clear heading hierarchy
- [ ] Mobile-friendly layout verified

## Content SEO

- [ ] Travel Guide / Blog category for long-tail keywords
- [ ] Destination archive pages with unique intro text
- [ ] Legal pages: Privacy, Terms, Cancellation
- [ ] Keyword-focused copy on homepage, tour pages, About, and Contact (no invented facts)
