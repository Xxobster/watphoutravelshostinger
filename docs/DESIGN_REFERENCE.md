# Design Reference — Watphou Travels

**Inspiration:** [vietnamdiscovery.com](https://vietnamdiscovery.com/) (principles only — no code, copy, images, or brand assets reused)

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
- Use Watphou brand colours derived from existing logo (greens, earth tones)

**Do not:**
- Copy Vietnam Discovery source code, CSS, or JavaScript
- Hotlink or reuse their photographs
- Reuse their text, logo, or proprietary UI components
- Clone their exact colour palette or typography pairing

## Watphou-specific differentiators

- Southern Laos focus (Bolaven, Vat Phou, 4000 Islands) — not Vietnam
- Private tours only — no group tour positioning
- European standards + local Lao team messaging
- Coffee expertise on Bolaven Plateau
- Pakse Hotel connection (when verified in content)
- French-speaking capacity
- WhatsApp-first contact culture

## Colour and type (theme.json)

- **Primary:** Deep forest green `#2D5016` (trust, nature)
- **Secondary:** Warm gold `#C4A035` (CTAs, accents)
- **Neutral:** Charcoal `#2C2C2C`, off-white `#FAFAF8`
- **Fonts:** System stack with optional local "Inter" or "Source Sans 3" (no Google Fonts CDN for privacy)

## Component patterns

1. **Hero pattern** — locked structure, editable headline/image/CTAs
2. **Best seller cards** — 4 featured tours with meta-driven duration/price
3. **Why us** — 4 icon columns from brief arguments
4. **Reviews** — testimonial CPT loop (genuine reviews only)
5. **Contact zone** — WhatsApp + short form
6. **Tour single** — price bar → dream paragraph → highlights → itinerary blocks → included/excluded → CTAs

## Screenshots

Internal reference screenshots (if captured) stored in `work/screenshots/design-ref/` — not committed if large.
