# Hostinger Deployment — Watphou Travels

**Staging URL:** https://darkslategray-snake-182151.hostingersite.com  
**Account:** Hostinger Business plan (order `1009997687`, username `u916301613`)  
**GitHub:** https://github.com/Xxobster/watphoutravelshostinger  

This is a **WordPress** theme/plugin project. It is **not** a Node.js (JavaScript runtime) or static site.

## Do not add `package.json`

Hostinger’s Git importer asks for `package.json` when it thinks the repo is a **static or Node.js** website. Importing that way would dump files over `public_html` and **would not run WordPress**.

**Correct path:**

1. Install WordPress on the temporary domain (or attach the real domain later).
2. Deploy `wp-content/themes/watphou-travels`, `wp-content/plugins/watphou-core`, `wp-content/plugins/watphou-bookings`, and `wp-content/mu-plugins/` with Hostinger WordPress deploy tools (or Secure File Transfer Protocol (SFTP) / TUS upload).
3. If you connect GitHub in hPanel later, point it at **WordPress / `wp-content` as a code backup**, not “import as static website”.

No fake `package.json`.

## Staging stack (current)

| Component | Status |
|-----------|--------|
| WordPress on temporary domain | Installed |
| Theme `watphou-travels` | Active (same design as former demo) |
| Plugins `watphou-core`, `watphou-bookings` | Active |
| Polylang + Yoast Search Engine Optimization (Yoast SEO) | Installed and active |
| Must-Use plugins (mu-plugins): `watphou-env.php`, `watphou-staging-gate.php` | Deployed |
| `WATPHOU_ENV` | `staging` in server `wp-config.php` (not in git) |
| Content | 16 tours + tailor-made page from September 2026 package texts; prices **From $XX** |
| Redirects | `content/redirects.csv` loaded into WordPress option |
| HTTP Basic Auth | **Off** — public staging URL; search engines still blocked with `noindex` |
| Search engines | `noindex, nofollow` + `blog_public=0` while not production |

## Deploy theme / plugins again

Preferred: Hostinger Model Context Protocol (MCP) / hPanel WordPress deploy tools:

- Theme zip or folder → activate
- Plugin folders → activate

Hostinger may store uploads under a **suffixed folder name** (for example `watphou-travels-xxxx`). Use the deploy/activate endpoints with that path, or rename to the clean slug.

Alternative: TUS upload Uniform Resource Locator (URL) from `hosting_generateUploadURLV1`, then upload files under `public_html/wp-content/...`.

After code changes: clear Hostinger website cache (and keep **cacheless mode** on while actively editing staging).

## Content import (no WP-CLI)

Hostinger web Hypertext Preprocessor (PHP) cannot rely on `exec` / WordPress Command Line Interface (WP-CLI).

1. Bundle JSON under `wp-content/plugins/watphou-core/data/packages/`
2. WordPress admin → **Watphou → Import packages** → Run import  
   Or use a one-shot PHP bootstrap that calls `watphou_core_import_packages()` (delete after use)

Public prices stay **From $XX**. Spreadsheet draft prices live only in gitignored `content/draft/prices_draft.json`.

## Staging hardening checklist

- [x] `define( 'WATPHOU_ENV', 'staging' );` in Hostinger `wp-config.php`
- [x] Mu-plugin noindex while not production
- [x] Strong WordPress admin password (not demo `000000`)
- [x] Polylang: English default, hide `/en/`; French/Thai published as English placeholders with a banner (no machine translation)
- [ ] Do **not** submit the temporary-domain sitemap to Google Search Console
- HTTP Basic Auth is **not** required: `noindex` + staging banner are enough while the temporary domain is public to reviewers

## Cutover / Domain Name System (DNS) / email (later — do not do now)

Recorded so nothing is forgotten at launch. **Do not change Joker Domain Name System (DNS) or Wix until approved.**

1. Export/screenshot all Joker records; lower Time To Live (TTL) on A / `www` to ~300 seconds, 24–48 hours before cutover.
2. Connect `watphou-travels.com` in Hostinger **before** switching Joker, so Secure Sockets Layer (SSL) exists when the first visitor arrives.
3. Change **only** website A / `www` records. Do **not** touch Mail Exchanger (MX), Sender Policy Framework (SPF), DomainKeys Identified Mail (DKIM), or Domain-based Message Authentication, Reporting and Conformance (DMARC).
4. Primary production Uniform Resource Locator (URL): `https://www.watphou-travels.com` (matches current live site). Redirect apex `https://watphou-travels.com` → `www`.
5. Search-replace staging domain → final domain in the database (serialized-safe).
6. Set `WATPHOU_ENV` to `production`; **remove** staging auth constants and noindex behaviour.
7. Submit Yoast sitemap (**final-domain Uniform Resource Locators (URLs) only**) in Google Search Console. Domain is unchanged → **do not** use Change of Address.
8. Keep Wix paid 2–4 weeks; keep a Hostinger backup.
9. Test the contact form from a phone that is **not** on the office network, after Hostinger email / Simple Mail Transfer Protocol (SMTP) is set.

### Highest-risk cutover mistakes

1. Leaving `noindex` on after launch  
2. Editing email Domain Name System (DNS)  
3. Dropping Wix Uniform Resource Locators (URLs) without 301 redirects  

## Out of scope until client supplies them

- Final photography swap  
- Public prices (and draft spreadsheet tables on the site)  
- Real Google reviews  
- Banque Pour Le Commerce Exterieur Lao (BCEL) live payments (stay mock)  
- Joker Domain Name System (DNS) / Wix shutdown  
