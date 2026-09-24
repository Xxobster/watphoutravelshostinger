# Watphou Travels — Search Engine Optimization (SEO) report

**Date:** 24 September 2026  
**Website tested:** [https://www.watphoutravels.site](https://www.watphoutravels.site) (new WordPress site, staging copy)  
**Live Wix site:** [https://www.watphou-travels.com](https://www.watphou-travels.com) — not changed

## Scores

| Measure | Score | What it means |
|---------|------:|---------------|
| Technical on-page Search Engine Optimization (SEO) | **100 / 100** | Every tested page has a title, meta description, one main heading, a canonical Uniform Resource Locator (URL), language tags, social preview tags, and structured data. |
| Google Lighthouse-style Search Engine Optimization (SEO) | **90 / 100** | Same checks as Google’s Lighthouse Search Engine Optimization category. The missing 10 points are **only** because staging tells Google “do not list this site yet”. |
| Google ranking today | **Not applicable** | Staging is hidden from Google on purpose so it does not compete with the current Wix website. |

After the real domain is attached and indexing is turned on, the Lighthouse-style Search Engine Optimization (SEO) score is expected to be **100 / 100** (same pages, without the “do not index” flag).

## How this was tested

- 11 public pages were fetched live (home in English, French and Thai; a 4-day tour; Day Tours; About; Contact; the tours list; Bolaven Plateau; Privacy; plus HTTP → HTTPS).
- `robots.txt` and the XML sitemap both respond HTTP 200.
- A real browser confirmed the homepage title, heading, canonical URL, and English / French / Thai language links.
- Google’s Lighthouse desktop tool could not load the host (the server returns HTTP 403 to automated Chrome). Google PageSpeed Insights was also over quota. Scores above use the **same weighted Lighthouse Search Engine Optimization checks** on the live HTML.

## What is already in place

- Unique titles and meta descriptions; one `h1` per page.
- Canonical URLs and `hreflang` for English, French, Thai, plus `x-default` pointing at English.
- Open Graph and Twitter preview tags; HTTPS on the final URL.
- Structured data: TravelAgency / LocalBusiness (Pakse address, map coordinates, opening hours, Google rating 4.5 from 55 reviews) on the homepage; TouristTrip on tour pages; genuine Review quotes from Google Maps (not invented).
- XML sitemap (Yoast Search Engine Optimization). Image alternative text attributes are present (decorative pictures may use an empty alternative text, which is correct).
- Staging robots: `noindex, nofollow` — this is required until cutover.

## What is not a Google ranking score yet

This report measures **technical readiness**, not position in Google. Ranking needs the real domain (`watphou-travels.com`), indexing turned on, Google Search Console, and time after launch.

## Pages in this test (all 100 / 100 technical)

| Page | Title (as Google would see it) |
|------|--------------------------------|
| Home (English) | Watphou Travels \| Private tours in Southern Laos |
| Home (French / Thai URLs) | Same English title for now (see note below) |
| 4-Day Southern Laos Escape | 4-Day Southern Laos Escape \| Private tour \| Watphou Travels |
| Day Tours, Tours, Bolaven Plateau, About, Contact, Privacy | Unique titles on each page |

**Note:** Visible French and Thai headings are translated. Browser-tab titles and meta descriptions on `/fr/` and `/th/` still use the English templates until the office supplies French and Thai Search Engine Optimization titles (we do not machine-translate).

## After the real domain goes live (not done on staging)

1. Remove `noindex` so Google may list the site.
2. Verify the site in Google Search Console and submit the sitemap.
3. Add a Google Analytics 4 (GA4) measurement ID (`G-XXXXXXXX`).
4. Point Google Business Profile at the production Uniform Resource Locator (URL).
5. Replace `From $XX` with real prices so Offer structured data can be added.

---

*Prepared for Watphou Travels from live tests on 24 September 2026. Send reviewers https://www.watphoutravels.site — not the Hostinger preview hostname.*
