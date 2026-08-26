# SEO Checklist — Watphou Travels

## Demo (must be noindex)

- [x] `blog_public = 0`
- [x] `X-Robots-Tag: noindex, nofollow` (mu-plugin)
- [x] HTTP Basic Auth gate
- [ ] Yoast noindex on demo (verify after install)

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
- [ ] GA4 placeholder configured
- [ ] Search Console verification
- [ ] Lighthouse performance ≥ 80 (target)

## Content SEO

- [ ] Travel Guide / Blog category for long-tail keywords
- [ ] Destination archive pages with unique intro text
- [ ] Legal pages: Privacy, Terms, Cancellation
