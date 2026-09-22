# Decisions — Watphou Travels

Append-only log. One entry per decision.

---

## 2026-08-26 — Demo host: sm instead of 185.203.119.52

**Decision:** Run WordPress demo on VPS `sm` (`212.73.150.149`) at `https://watphou.smbistro.duckdns.org`.

**Alternatives considered:**
- `185.203.119.52` (ld-vps) — specified in original prompt
- Non-standard port on ld-vps
- Wait for client subdomain

**Reason:** ld-vps has no web stack; Node `remoteandroid.service` owns ports 80/443 with no reverse proxy; only 4.8 GB free. sm already has nginx, MySQL 8, certbot, DuckDNS; wildcard DNS resolves `watphou.smbistro.duckdns.org`; adding a vhost is zero-risk to existing smbistro app.

**Impact:** All deploy scripts use SSH host alias `sm`. Document in RUNBOOK.

---

## 2026-08-26 — Development mode: server-only

**Decision:** No local PHP/MySQL/Docker. Edit repo on Windows, deploy to demo VPS, verify over HTTPS.

**Alternatives:** Docker Desktop, Laragon, Local WP.

**Reason:** User chose server-only; local machine lacks PHP/MySQL/Node.

**Impact:** All testing via `scripts/deploy_demo.py` + `scripts/verify_demo.py` + Playwright screenshots.

---

## 2026-08-26 — Repository ships code only (no WordPress core)

**Decision:** Git tracks `wp-content/themes`, `wp-content/plugins`, `wp-content/mu-plugins` only. Core installed on server via WP-CLI.

**Reason:** Bluehost managed WordPress owns core; avoids bloating repo; safe deploy without overwriting uploads/DB.

---

## 2026-08-26 — Itinerary as Gutenberg blocks, not ACF repeaters

**Decision:** Day-by-day itinerary, highlights, inclusions use custom blocks (`watphou/itinerary-day`, etc.). Numeric/filterable tour fields use registered post meta.

**Alternatives:** Advanced Custom Fields Pro (paid repeaters).

**Reason:** Free ACF has no repeater; blocks keep editing native and avoid paid dependency.

---

## 2026-08-26 — Bookings in custom DB tables, not CPT

**Decision:** `wp_watphou_bookings` and `wp_watphou_booking_events` custom tables.

**Reason:** Personal data must not appear in REST API or public queries.

---

## 2026-08-26 — Production hosting: Bluehost (not Kinsta)

**Decision:** Production targets Bluehost managed WordPress. PDF Kinsta recommendation superseded.

**Reason:** User/client decision per master prompt.

---

## 2026-08-26 — No page builder

**Decision:** Custom lightweight block theme. No Elementor or similar.

**Reason:** Performance, maintainability, manager-safe locked patterns.

---

## 2026-08-26 — Polylang Free for multilingual

**Decision:** Polylang Free with `/en/`, `/fr/`, `/th/` URL structure.

**Alternatives:** WPML (paid).

**Reason:** Brief allows Polylang; free tier sufficient for three languages.

---

## 2026-08-26 — Sensitive backup assets quarantined

**Decision:** Invoice PDFs, deposit/rest payment docs, and BCEL bank account images go to gitignored `work/quarantine/`. Never deploy or commit.

**Reason:** GDPR/privacy; not website content.

---

## 2026-08-26 — Public demo site, WordPress login for editing

**Decision:** Remove nginx HTTP Basic Authentication from the demo. The website is publicly viewable. The Tour Manager logs in at `/wp-login.php` (header link **Log in to edit**) to edit content.

**Reason:** The outer browser login wall hid the website and conflicted with WordPress login (`manager` / `000000`). The client wants visitors to see the site, then a login option to edit.

**Kept:** `noindex`, demo banner, mock payments, restricted Tour Manager role.

**Impact:** `verify_demo.py` no longer requires Basic Auth credentials.

---

## 2026-08-26 — Classic PHP theme, Vietnam Discovery layout

**Decision:** Switch `watphou-travels` from a Gutenberg block theme to classic PHP templates (`header.php`, `front-page.php`, etc.). Homepage structure follows vietnamdiscovery.com (two-bar header, hero, interest cards, adventure rows, destination tiles). CSS/HTML is original. Images are Watphou Wix/backup photos.

**Reason:** The block-theme homepage did not resemble the agreed design target. WordPress ignores `front-page.php` while `templates/index.html` exists (`wp_is_block_theme()`).

**Impact:** Deleted `templates/*.html` and `parts/*.html`. Theme version 2.0.0. Orange CTAs `#f15a24`. Prices remain `$XX` until the client supplies figures.

---

## 2026-09-10 — GitHub repo rename to watphoutravelshostinger

**Decision:** Rename GitHub origin from `Xxobster/watphoutravelsbluehost` to `Xxobster/watphoutravelshostinger`.

**Reason:** Hosting target moved from Bluehost naming to Hostinger; keep repository name aligned with deployment plan.

**Impact:** Local `origin` remote updated. Old GitHub URL redirects. Docs (`README`, `PROJECT_PROFILE`, requirements, Bluehost deploy note) updated to the new name.

---

## 2026-09-10 — Staging moves to Hostinger; VPS demo removed

**Decision:** Delete the Watphou WordPress demo from Virtual Private Server (VPS) `sm` and use Hostinger temporary domain `https://darkslategray-snake-182151.hostingersite.com` as staging.

**Removed on `sm` only:** nginx site `watphou-demo`, PHP-FPM pool `watphou-demo`, database `watphou_demo`, user `watphou`, web roots `/var/www/watphou-demo` `/var/www/watphou-media` `/var/www/watphou-content`, Let’s Encrypt cert `watphou.smbistro.duckdns.org`.

**Not touched:** nginx sites `smbistro`, `cirlapp-duckdns`, `cirl-ip`; Let’s Encrypt certs for those hosts.

**Reason:** The DuckDNS demo URL was already offline (NXDOMAIN). Hosting and WordPress now run on Hostinger. Keeping a dead vhost on `sm` wasted disk and risked confusion.

**Impact:** `scripts/deploy_demo.py` refuses VPS deploys. `scripts/verify_demo.py` checks Hostinger HTTPS plus absence of `watphou-demo` on `sm`. Offline backup left at `/var/backups/watphou-demo-final-20260910_142038/` on the VPS (not in git).

---

## 2026-09-10 — Hostinger staging: WordPress deploy, not Git static import

**Decision:** Install WordPress on `darkslategray-snake-182151.hostingersite.com` and deploy the theme/plugins via Hostinger WordPress tools / TUS file upload. Do **not** add a fake `package.json` or use Hostinger’s static/Node Git importer.

**Reason:** The GitHub repo is WordPress `wp-content` code. Hostinger’s static importer expects Node/static projects and would overwrite `public_html` without a working WordPress stack.

**Also decided for this build:** Public prices stay `From $XX`; homepage bestsellers are Portfolio packages 1.1 / 2.1 / 2.2 / 3.1; primary future domain `https://www.watphou-travels.com`.

**Impact:** Documented in `docs/HOSTINGER_DEPLOYMENT.md`. Content import uses admin/one-shot PHP (no WP-CLI on Hostinger).

---

## 2026-09-10 — No HTTP Basic Auth on the Hostinger temporary domain

**Decision:** Do not require a site login/password to view `https://darkslategray-snake-182151.hostingersite.com`. Keep `noindex, nofollow`, `blog_public=0`, and the staging banner. WordPress admin remains password-protected (`wptadmin`).

**Reason:** Reviewers need to open the temporary domain like a normal website. Search-engine protection comes from `noindex`, not from HTTP Basic Auth. Hostinger LiteSpeed also ignored `.htaccess` Auth in some cases.

**Impact:** `watphou-staging-gate.php` stays in the repo as an opt-in (only runs if `WATPHOU_STAGING_USER` / `WATPHOU_STAGING_PASS` are defined in `wp-config.php`). Polylang English is default with `/en/` hidden; French and Thai exist as empty drafts only.

---

## 2026-09-10 — French/Thai are published English placeholders, not empty drafts

**Decision:** Keep one WordPress page/tour **per language** (Polylang Free requirement). Publish the French and Thai copies with the **same English text**, status `publish`, meta `_watphou_translation_status=english-placeholder`, and a public banner. Do **not** machine-translate Thai (or tour bodies).

**Alternatives considered:**
- Leave 50 empty drafts — visitors clicking FR/TH get “page not found”
- Auto-translate plugin — forbidden for Thai; Polylang Free does not translate
- Hide FR/TH until a translator finishes — language switcher still 404s

**Reason:** Empty drafts have no public Uniform Resource Locator (URL). The user needs FR/TH to open a page. English with a banner is honest and indexable later when the real domain is live.

**Impact:** `/fr/` and `/th/` return HTTP 200. A human translator edits the French or Thai copy in admin (filter by language) and removes the placeholder meta. Language home slugs may be `/fr/home-2/` because WordPress unique slugs; `/fr/` still resolves.

---

## 2026-09-10 — Yoast first-time configuration done in code, not the wizard

**Decision:** Apply Yoast Search Engine Optimization (Yoast SEO) company name, titles, meta description templates, breadcrumbs, sitemap-on, tracking-off, `environment_type=staging`, and dismiss the first-time notice via options. Unique per-page titles come from verified Watphou copy (Pakse, Bolaven, Vat Phou, 4000 Islands). Cancellation page does **not** invent percentage fees.

**Reason:** The admin wizard is slow and easy to skip. Staging must stay `noindex`. We cannot invent Google Analytics 4 (GA4) or Google Search Console codes.

**Impact:** Settings fields exist for GA4 and Search Console. Those stay empty until the client sends real IDs. Do not submit `sitemap_index.xml` for the Hostinger temporary domain.

---

## 2026-09-10 — Auto-translate French and Thai for a review pack only

**Decision:** Generate automatic French and Thai drafts (Google Translate, MyMemory fallback) of all public website strings into `docs/translations/Watphou_EN_FR_TH_review.xlsx` (editable) and `.pdf` (readable). Do **not** publish those drafts on WordPress until a human marks rows Approved.

**Reason:** The client asked for automatic FR/TH so they can review on paper/Excel. The live `/fr/` and `/th/` pages stay English + banner. Thai still needs a human before it goes public.

**Impact:** WT-115 done. Rebuild with `python scripts/build_translation_review.py`. WT-113 (Google Analytics 4) and WT-114 (Google Search Console, real domain only) stay TODO.

---

## 2026-09-10 — Manager adds tours with Excel, photos with Quick edit

**Decision:** Replace the broken **Import packages** admin page with **Add tours (Excel)**. The manager downloads an example `.xlsx` filled with the live **3-Day Classic Experience** package, edits or copies rows, uploads the workbook, then sets the photo on **Quick edit tours**. The Excel file never changes pictures. JSON folder import stays as a collapsed webmaster tool.

**Reason:** The old page slug `watphou-import` 404’d (WordPress menu used a stricter permission than Quick edit, and the word “import” is often blocked). An Excel sheet matches how the manager already works. Real prices stay `XX` until supplied.

**Impact:** Plugin `watphou-core` 1.4.0. Example file: `wp-content/plugins/watphou-core/data/watphou-package-import-example.xlsx`. Rebuild with `python scripts/build_package_import_example.py`.

---

## 2026-09-11 — Publish reviewed French and Thai; Excel has FR and TH sheets

**Decision:** Use `docs/translations/Watphou_EN_FR_TH_reviewed.xlsx` as the live French and Thai copy (all 494 rows marked **Revised**). Do not overwrite English. Hide the English-placeholder banner once those texts are stored. Future wording changes: replace that Excel file, re-export JSON, deploy the plugin JSON. The tour-import example workbook now has a **FR** sheet and a **TH** sheet (same slug as English; day titles/bodies as extra columns). Uploading that file updates `/fr/` and `/th/` for those tours.

**Reason:** The client asked to publish these reviewed texts now, and to keep French and Thai editable in the same manager Excel file as the English example. Thai on the website is this human-reviewed pack, not a fresh machine translation at request time.

**Impact:** Plugin `watphou-core` 1.5.0. Rebuild JSON with `python scripts/export_reviewed_translations.py`. Rebuild the example with `python scripts/build_package_import_example.py`.

---

## 2026-09-14 — Public staging URL is watphoutravels.site, not the Hostinger preview name

**Decision:** Claim Hostinger’s free domain `watphoutravels.site`, park it on the existing WordPress site, point Domain Name System (DNS) at Hostinger, and tell reviewers to use **https://watphoutravels.site**. Do **not** change `watphou-travels.com` (still on Wix / Joker). Keep the preview hostname as a fallback for people whose DNS already resolves it. A must-use plugin rewrites home, site, and HTML URLs to the hostname the visitor used, so WordPress does not 301 them onto `*.hostingersite.com`.

**Alternatives considered:**
- Keep only `darkslategray-snake-182151.hostingersite.com` — fails with NXDOMAIN on some visitor networks (the customer’s screenshot).
- Another `*.hostingersite.com` preview — same Domain Name System (DNS) problem.
- Attach `watphou-travels.com` now — forbidden until production cutover.

**Reason:** Hostinger preview names CNAME to `free.cdn.hstgr.net`. That name is missing from some resolvers, so the site looks “down” even while it answers HTTP 200 for the owner. A normal registered domain on Hostinger nameservers plus a real Secure Sockets Layer (SSL) certificate is visible worldwide. Apex uses A records (not ALIAS) so resolvers that mishandle ALIAS still work.

**Impact:** Customer URL: `https://watphoutravels.site`. Must-use plugin `wp-content/mu-plugins/watphou-public-hosts.php`. Staging stays `noindex`. Apex A records: `88.222.222.65` and `2.57.91.249`. `www` CNAME to Hostinger CDN.

---

## 2026-09-18 — Top menu matches customer sheets; tours edited in wp-admin

**Decision:** Hard-code the public header to the Excel structure sheets (Home, Day Tour, 2-DAY Tours, 3-DAY Tours, 4-6 Day Tours, Destinations, Tailor-Made, About Us, Contact). Duration WordPress pages use a listing template that queries the `duration` taxonomy (with a duration-meta fallback). Destinations stay a hub plus four term pages. Do not import Price Braquette numbers. Homepage “Tailored Tours for Your Interest” is removed. Tour Gutenberg screens redirect to **Watphou → Manage tours**, which edits English / French / Thai Excel-equivalent fields, draft/preview/publish, hide, and delete. Administrators get the full `tour` capability set (`edit_published_tours` and related) so Quick edit **Edit text** works. HTTP requests on public hosts 301 to HTTPS; PHP trusts `X-Forwarded-Proto`; send Strict-Transport-Security.

**Alternatives considered:**
- WordPress menu Customizer — managers should not need Appearance → Customize
- Keep empty `page.php` listings — that is why Day Tours looked empty
- Leave tour editing in Gutenberg — customer asked for a wp-admin tool covering all Excel fields including French and Thai

**Reason:** Customer screenshots and the 20261009 “Website structures for Micah” workbook define the public information architecture. Empty listing pages were a template bug, not missing tours.

**Impact:** Theme 2.2.0. Plugin `watphou-core` 1.6.0. New file `includes/admin-tour-manager.php`.

---

## 2026-09-19 — Public catalog order is the structure Excel, not title sort

**Decision:** Tour listing order, destination membership, and homepage best sellers follow `docs/phr info for website/20261009 content info/Website structures for Micah 2026-09-01.xlsx`. Package codes 1.1–6.1 are the sort key (`menu_order` plus a PHP catalog sort). Home best sellers are Bolaven full day, 4,000 Islands & Vat Phu 2-Day, 3-Day Classic, 4 Days Southern Laos Escape — not Bolaven 2-day. Do not import Price Braquette numbers. Destination YES table (section 5) is the membership source; Pakse lists 1.4 plus a Tailor-made (T.1) link.

**Alternatives considered:**
- Keep `tour_priority` then A–Z title — that put Vat Phou last among equal-priority day tours
- Keep `index.json` bestseller list that still had Bolaven 2-day as #2

**Reason:** The customer asked for the Excel order exactly and to flag discrepancies. The title sort was a bug, not a content choice.

**Impact:** Theme 2.2.2. Plugin `watphou-core` 1.6.2. New file `includes/catalog.php`. One-shot option `watphou_catalog_apply=1.6.2` writes `menu_order`, `tour_code`, `tour_bestseller`, and destination terms.

---

## 2026-09-19 — Booking form is pre-filled from the tour; Google reviews wait for Places API

**Decision:** On a tour page (and `?tour=` slug), the booking form shows a locked **Your tour** block: name, package code, duration, destinations, departure (from package JSON), starting price, headline. The guest only fills personal fields. On Home / book-online, a tour dropdown fills the same fields. Server-side submit re-reads the tour from `tour_id` so posted names cannot be spoofed. Google reviews stay a placeholder until WT-121 has a Place Identifier and Places Application Programming Interface key. Do not scrape Google. Do not invent reviews.

**Alternatives considered:**
- Keep a hidden `tour_id` only — staff could not see which package was requested at a glance
- Third-party review widgets — extra vendor, weaker control of Google attribution

**Reason:** The customer asked for tour fields to be filled automatically, and for a plan to pull real Google reviews.

**Impact:** Plugin `watphou-bookings` 1.1.0. Theme 2.2.3 (form layout). Task WT-121 pending credentials. Task WT-122 done.

---

## 2026-09-19 — About Us is the live Wix page

**Decision:** The Excel About Us sheet says “Same as our old website” and links `https://www.watphou-travels.com/about-us`. The WordPress About page therefore uses that Wix copy and photos: agency intro (established 2008), office photos, Pakse Hotel & Restaurant, Le Panorama Rooftop, Pakse Burger 66, University of Calgary partnership, Les Colchés d’Asie, and the closing contact block. Theme template `template-about.php` is the display source (not the old one-line placeholder). French and Thai About pages show this English text until a reviewed translation exists. Do not machine-translate it.

**Alternatives considered:**
- Keep the one-line placeholder until the customer writes a new About — the Excel already named the live page as the source
- Paste the HTML into the WordPress editor only — a theme template survives imports and matches Destinations / listing pages

**Reason:** The customer asked for the Excel instruction to be followed. Established in 2008 is now a customer-published fact on the live site they pointed to.

**Impact:** Theme 2.2.5. Photos under `assets/images/about/`. Task WT-123 done.

---

## 2026-09-19 — Text-review PDF changes only our lines

**Decision:** Follow `Watphou_Travels_Text_Review_Recommendations.pdf` for wording we wrote (day-tour price bar, homepage headings, enquiry buttons, Google-review placeholder, Travel Guide / Privacy / Terms / Cancellation, Contact intro). Leave customer-chosen copy unchanged: package dreams and itineraries (Angkor, Palateu, three icons, Pakse same-day, Dan Yai picnic, Tailor-Made), Excel menu labels (Day Tour / 2-DAY Tours / …), mixed Vat Phou / Vat Phu, and the live Wix About Us page.

**Applied:** Day tours show the package `price_note` (no “accommodation”). Homepage: Popular Private Tours; Plan Your Private Tour in 5 Simple Steps; reviews block hidden until WT-121. Buttons: Request this tour / Send tour request / Request a quote. Legal pages rewritten as visitor rules, not staging notes. Contact gets the PDF intro sentence.

**Not applied (customer source):** PDF items 7–14, and extra “European standards” explanation beyond the Wix About text.

**Impact:** Theme 2.2.6. Plugin `watphou-core` 1.6.4 (`includes/copy-review.php`). Plugin `watphou-bookings` 1.1.1. Task WT-124.

---

## 2026-09-19 — Terms page has no placeholder-price or BCEL notes

**Decision:** The public Terms of Use page must not mention `From $XX` placeholder prices or that Banque Pour Le Commerce Exterieur Lao (BCEL) payment is not live. Keep the visitor rule (the written quotation is the contract) and the contact line.

**Reason:** Those sentences were staging notes, not customer terms.

**Impact:** Plugin `watphou-core` 1.6.6. Live `/terms/` on watphoutravels.site. Task WT-125.

---

## 2026-09-19 — Click photos to open a large viewer

**Decision:** About Us gallery photos (and other content photos) open in a full-screen viewer. Logos, icons, header/footer chrome, and destination tiles stay ordinary links. Tour listing photos also enlarge; **View tour** / title still go to the tour page.

**Reason:** The About Us photos are cropped small in the three-column grid. Visitors should see the full picture without leaving the page.

**Impact:** Theme 2.2.8. Task WT-126.

---

## 2026-09-19 — View tour shows details, not the request form

**Decision:** Listing **View tour** and the tour title/card go to the tour page top (itinerary). The request form is under the details. The extra **Request this tour** button after the description is removed. The hero still has **Request this tour** to jump to the form.

**Reason:** `#booking` used to sit above the itinerary, so View tour skipped the programme.

**Impact:** Theme 2.2.9. Task WT-127.

---

## 2026-09-19 — Quick Edit fields apply on every public surface

**Decision:** Price, photo, duration, and homepage bestseller from **Quick edit tours** update the listing cards, the tour page sentence, the request-form starting price, French/Thai copies of the same tour, and the homepage Popular Private Tours block. The stored price sentence keeps its wording; only the `$` amount follows the Quick Edit number.

**Reason:** Listing cards already read `tour_price_from` (so `$120` showed), but the tour hero and booking form still showed a frozen `tour_price_note` with `$XX`.

**Impact:** Plugin `watphou-core` 1.6.8. Theme 2.3.0 (homepage price/bestsellers). Task WT-128.

A first save used PHP `preg_replace` with a `$120` replacement string, which PHP treats as capture group 12, so the tour page showed “From 0 per person”. Replacement now uses `preg_replace_callback`.

---

## 2026-09-19 — Send tour request lists every missing field

**Decision:** **Send tour request** saves a row in **Bookings** and emails the Pakse office. The guest is not emailed. If required fields are empty, the page shows a red list of every missing item next to the button (Name, Email, Preferred date, privacy agreement) and a red line under each field. The form does not send until those are filled. An invalid email shows an example such as `name@example.com`.

**Reason:** The browser used to show only the first empty field. Guests could not see the full list.

**Impact:** Plugin `watphou-bookings` 1.1.4. Theme 2.3.2. Task WT-129. Office mail still depends on Simple Mail Transfer Protocol (SMTP), which is unresolved.

---

## 2026-09-19 — Menu capitalization and Request a quote (not Book Online)

**Decision:** Main menu labels are **Day Tours**, **2-Day Tours**, **3-Day Tours**, **4-6 Day Tours** in English, with the reviewed French and Thai strings from the translation workbook. The old Book Online page title, browser title, and form button are **Request a quote** (French **Demander un devis**, Thai **ขอใบเสนอราคา**). The intro says the visitor is requesting a private tour and will receive a quote. French `/fr/` and Thai `/th/` links must open those languages, not a 404 page.

**Reason:** Text-review items 13 and 16. Language switcher Uniform Resource Locators (URLs) used Polylang copies such as `/fr/day-tours-2/` while `/fr/` itself returned 404.

**Impact:** Plugin `watphou-core` 1.6.13. Plugin `watphou-bookings` 1.1.5. Theme 2.3.3. Workbook `docs/translations/Watphou_EN_FR_TH_reviewed.xlsx` rows 3, 5–7 and 495–497. Task WT-130.

**Follow-up:** The quote-page browser tab (Yoast Search Engine Optimization title) is **Request a quote | Watphou Travels** in English, **Demander un devis | Watphou Travels** in French, and **ขอใบเสนอราคา | Watphou Travels** in Thai, matching the visible heading.

---

## 2026-09-22 — September photos, hero slideshow, URL-locked language

**Decision:** Tour pictures come from Nang’s September 2026 folders (package codes 1.1–6.1 under Day Tour / 2-Day / 3-Day / 4-6 Day). The homepage hero uses only the selected Home Page hero pictures, never `1.Home (Hero pic)`. The hero changes about every 5 seconds on a fast link, 8 seconds on a moderate link, and 12 seconds on a slow link or when “save data” is on. Connection is judged with the browser Network Information Application Programming Interface (`navigator.connection`) plus how long the first picture takes to load — not an Internet Control Message Protocol (ICMP) ping, which browsers cannot run. The first picture is in the page immediately; the second is preloaded; the rest load in the background on a fast link.

**Language:** The Uniform Resource Locator (URL) is the only language switch. A previous visit to `/fr/` must not show French on unprefixed English pages that share the same slug. Polylang’s language cookie is disabled (`PLL_COOKIE` false). Browser language detection is off.

**Reason:** Customer supplied final photos and reported English Home → Day Tours → Bolaven Classic jumping to French.

**Impact:** Theme 2.4.2. Plugin `watphou-core` 1.7.1. Must-use plugin `watphou-env.php`. Task WT-131. WT-102 photography for this pack is done on staging.

---

## 2026-09-22 — Hero 2/5/10 seconds, leftover French/Thai labels, 4000 îles

**Decision:** Homepage hero interval is **2 seconds** on a fast link, **5 seconds** on a moderate link, and **10 seconds** on a slow link or when “save data” is on. Remaining public English labels on French and Thai pages (tour headings, price sentence, booking form, footer Phone/WhatsApp and Email, Tailor-made) use the reviewed workbook plus extra user-interface strings. Destination names stay English for now, except French **4000 îles**. WordPress File Manager is deactivated on staging after the last upload; it was only an emergency deploy workaround.

**Reason:** Customer asked for faster slides, full French/Thai chrome, French 4000 îles only, and File Manager off when emergency uploads were finished.

**Impact:** Theme 2.4.3. Plugin `watphou-core` 1.7.2. Plugin `watphou-bookings` 1.1.7. Task WT-132.

---

## 2026-09-22 — Booking form follows the selected language

**Decision:** Every visitor-facing label on the tour request form (and the tour-page chrome around it) must match the Uniform Resource Locator (URL) language. Extra French/Thai strings live in `wp-content/plugins/watphou-core/data/ui_extras_i18n.json`, not in a large Hypertext Preprocessor (PHP) array. `apply-reviewed-i18n.php` no longer includes `public-i18n-extras.php`, so a syntax error there cannot take the site down.

Destination **place names** stay English except French **4000 îles**. The form label is **Tour destinations** / **Destinations visitées** / **จุดหมายปลายทาง**. Product names such as WhatsApp stay as-is.

**Reason:** The French request form still mixed English labels with French tour copy. An earlier extras.php parse error also returned Hypertext Transfer Protocol (HTTP) 500 on all pages.

**Impact:** Plugin `watphou-core` 1.7.3. Plugin `watphou-bookings` 1.1.8. Staging restored.

---

## 2026-09-22 — WordPress File Manager stays on until the real domain

**Decision:** Keep WordPress File Manager **active** on Hostinger staging (`watphoutravels.site`) for overwrite/replace of plugin files when Hostinger Model Context Protocol (MCP) upload tools time out. Do **not** delete files on the server when replacing; overwrite in place. Deactivate File Manager at production cutover on the real domain (WT-133): it lets a logged-in administrator write files and is a common attack target, so it is a security issue on a public live site.

**Reason:** Staging still needs a reliable way to replace Hypertext Preprocessor (PHP) files after a parse error. The customer asked to keep File Manager until the website is live with the real domain.

**Impact:** Plugin `wp-file-manager` active on staging. Task WT-133. Production cutover checklist in WT-111.









