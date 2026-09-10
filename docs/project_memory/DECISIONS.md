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



