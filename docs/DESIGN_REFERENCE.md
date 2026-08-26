# Design Reference — Watphou Travels

**Inspiration:** [vietnamdiscovery.com](https://vietnamdiscovery.com/)

**Client request (2026-08-26):** the demo must *look like* vietnamdiscovery.com (same page rhythm and component layout). Photos, logo, phone, and copy remain Watphou’s own (from the live Wix site and backup). We write original CSS/HTML — we do not copy their source code, JavaScript, photographs, or logo.

## Page layout to match (homepage)

1. White sticky top bar: logo left, location, search, WhatsApp phone, language, hamburger
2. Second nav: Home / packages mega-menu / destinations / About + orange outline **Customize trip**
3. Full-bleed hero photo, large H1, quoted subtitle, orange **Get Started Today**
4. Centered intro (“Explore … like Never Before” + quote mark)
5. Horizontal **interest cards** (photo, duration badge, from $XX /Person, Explore)
6. **Top Adventures** rows: photo + duration, title, excerpt, Travel Routes, from $XX /Person, **View tour**
7. Popular destination tiles with overlay titles
8. Five easy steps, Why us, reviews, contact/WhatsApp, dark footer

## Adopted principles

| Principle | Application in Watphou design |
|-----------|-------------------------------|
| Hero presentation | Full-width emotional photography, clear headline + subheadline + dual CTA |
| Navigation hierarchy | Max 6–7 top items; destinations dropdown; language switcher top-right |
| Tour card design | Large image, duration, starting price, one-line benefit, dual CTAs |
| Page rhythm | Alternating content blocks; whitespace; clear section headings |
| Trust elements | Why-us icons, verified contact, reviews section (genuine only) |
| Destination presentation | Dedicated destination archives with related tours |
| Calls to action | Primary booking + secondary WhatsApp on every tour page |
| Mobile behavior | Mobile-first; hamburger menu; sticky WhatsApp button |
| Typography scale | Clear H1→H3 hierarchy; readable body at 16–18px base |
| Image presentation | Responsive srcset; lazy load below fold; hero preloaded |

## Originality boundaries

**Do:**
- Use Watphou-owned photos from backup and live site
- Use brief-supplied copy and scraped Wix content
- Apply similar *layout patterns* (hero → cards → trust → contact)
- Use orange call-to-action buttons in the same *role* as Vietnam Discovery (primary action), not their exact CSS file

**Do not:**
- Copy Vietnam Discovery source code, CSS, or JavaScript
- Hotlink or reuse their photographs
- Reuse their text, logo, or proprietary UI components

## Watphou-specific differentiators

- Southern Laos focus (Bolaven, Vat Phou, 4000 Islands) — not Vietnam
- Private tours only — no group tour positioning
- European standards + local Lao team messaging
- Coffee expertise on Bolaven Plateau
- Pakse Hotel connection (when verified in content)
- French-speaking capacity
- WhatsApp-first contact culture

## Colour and type (theme.json)

- **Primary (CTA orange):** `#f15a24`
- **Secondary:** `#d9480f`
- **Neutral:** Charcoal `#212529`, white bars, dark footer `#1a1d21`
- **Fonts:** System stack (Segoe UI / Roboto / Helvetica) — no Google Fonts CDN

## Component patterns

1. **Hero pattern** — locked structure, editable headline/image/CTAs
2. **Best seller cards** — 4 featured tours with meta-driven duration/price
3. **Why us** — 4 icon columns from brief arguments
4. **Reviews** — testimonial CPT loop (genuine reviews only)
5. **Contact zone** — WhatsApp + short form
6. **Tour single** — price bar → dream paragraph → highlights → itinerary blocks → included/excluded → CTAs

## Screenshots

Internal reference screenshots (if captured) stored in `work/screenshots/design-ref/` — not committed if large.
