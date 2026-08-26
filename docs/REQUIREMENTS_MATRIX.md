# Requirements Matrix — Watphou Travels

Every requirement from the PDF brief and master prompt. Status: `pending` | `in_progress` | `done` | `blocked` | `n/a`

| ID | Requirement | Source | Implementation | Status | Test / evidence | Missing info |
|----|-------------|--------|----------------|--------|-----------------|--------------|
| R01 | Migrate from Wix to WordPress | PDF §1 | Full rebuild on demo VPS | in_progress | Demo URL when live | — |
| R02 | Languages EN / FR / TH | PDF §1 | Polylang Free, /en/ /fr/ /th/ | pending | hreflang check | Professional TH translator |
| R03 | Design inspired by vietnamdiscovery.com | PDF §1 | Custom block theme, DESIGN_REFERENCE.md | in_progress | Visual review | — |
| R04 | Show starting prices "From $XX" | PDF §2, §6 | Tour meta + template | pending | Tour page render | Real prices from client |
| R05 | Sales-focused homepage | PDF §5 | Hero + best sellers + why us + reviews + contact | pending | Homepage screenshot | Review text from Google |
| R06 | Menu: Day / Multi-day / Destinations / Tailor-made / About / Contact | PDF §4 | Registered nav menus + theme header | pending | Menu crawl | — |
| R07 | Language switcher + phone + WhatsApp in header | PDF §4 | Theme header partial | pending | Mobile/desktop check | — |
| R08 | Floating WhatsApp button | PDF §4, §10 | Theme JS + global setting | pending | All pages | — |
| R09 | Destinations dropdown (4 items) | PDF §4 | Nav menu structure | pending | Dropdown test | — |
| R10 | Hero copy from brief | PDF §5.1 | Homepage pattern | pending | Text match | — |
| R11 | 4 best seller cards | PDF §5.2 | Featured tour query | pending | 4 cards visible | Prices |
| R12 | Why travel with us (4 arguments) | PDF §5.3 | Pattern block | pending | Section visible | Verify "since 2008" |
| R13 | Real client reviews | PDF §5.4 | Testimonial CPT | blocked | — | Google review import |
| R14 | Contact zone + form | PDF §5.5 | Contact pattern + booking form | pending | Form submit | SMTP |
| R15 | Tour page structure (10 sections) | PDF §7 | Single tour template + blocks | pending | Sample tour page | — |
| R16 | 8 core packages featured | PDF §8 | tour_priority meta | pending | Homepage + menu | — |
| R17 | Migrate all existing tours | Prompt §3 | 24 tour CPT imports | in_progress | Post count = 24+ | Scrape completing |
| R18 | Tailor-made tours page | PDF §8 | New page | pending | Page exists | — |
| R19 | Booking request workflow | Prompt §8 | watphou-bookings plugin | pending | E2E booking test | — |
| R20 | BCEL payment architecture | Prompt §9 | Provider interface + stub | pending | Mock payment test | BCEL contract + API |
| R21 | Manager role (non-admin) | Prompt §10 | tour_manager role | pending | Capability test | — |
| R22 | Manager dashboard | Prompt §10 | Admin dashboard page | pending | Screenshot | — |
| R23 | SEO: Yoast, sitemap, hreflang, schema | Prompt §11 | Yoast + theme schema | pending | SEO_CHECKLIST | GA4/GSC IDs |
| R24 | 301 redirects from Wix URLs | Prompt §11 | redirects.csv + plugin | pending | Redirect test | — |
| R25 | Demo on VPS, isolated | Prompt §13 | sm host, separate DB/vhost | in_progress | verify_demo.py | — |
| R26 | Demo HTTPS + noindex (public site, WP login to edit) | Prompt §13 superseded by client | nginx HTTPS + mu-plugin noindex | done | curl 200 public; login works | — |
| R27 | Manager demo user manager/000000 | Prompt §1 | WP user, Tour Manager role | pending | Login test | — |
| R28 | Do not break existing VPS site | Prompt §13 | Isolated vhost on sm | pending | smbistro 200 | — |
| R29 | Bluehost production prep | Prompt §14 | BLUEHOST_DEPLOYMENT.md | pending | Doc review | SSH access |
| R30 | Privacy / terms / cancellation pages | Prompt §12 | Legal page templates | pending | Pages exist | Legal copy |
| R31 | Travel Guide / Blog (optional SEO) | PDF §3 | Post type `post` archive | pending | Archive URL | Content |
| R32 | Fast loading, optimized images | PDF §3 | WebP, lazy load, theme perf | pending | Lighthouse | — |
| R33 | Accessible navigation | Prompt §4 | Keyboard nav, focus, contrast | pending | a11y check | — |
| R34 | Production: Bluehost (not Kinsta) | Decision | BLUEHOST_DEPLOYMENT.md | pending | — | Bluehost credentials |
| R35 | Preserve backup ZIP read-only | Prompt §3 | bkp/ gitignored | done | .gitignore | — |
| R36 | Requirements matrix document | Prompt §3 | This file | done | File exists | — |
| R37 | Project memory for agents | User request | docs/project_memory/ | done | 12 files | — |
| R38 | Git repo watphoutravelsbluehost | User request | origin remote | done | git remote -v | — |

## PDF package templates (ready-to-use copy)

| Package | PDF section | Status | Notes |
|---------|-------------|--------|-------|
| Bolaven Full-Day | §9A | pending | Use brief copy + scraped live text |
| Bolaven 2D/1N | §9B | pending | Same |
| 3-Day Classic | §9C | pending | Same |

## Blocked items summary

1. **Real prices** — client to supply quotes
2. **Google reviews** — need genuine text + permission
3. **BCEL** — merchant contract, sandbox credentials, API documentation
4. **Bluehost** — SSH/WP-CLI access
5. **Thai translations** — professional translator required
6. **SMTP** — demo mail credentials optional
