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

