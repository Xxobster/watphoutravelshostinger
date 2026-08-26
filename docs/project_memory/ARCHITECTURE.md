# Architecture — Watphou Travels

## Repository map

```
wp-content/
  themes/watphou-travels/     Presentation (block theme)
  plugins/watphou-core/       Business data: tours, taxonomies, settings, blocks
  plugins/watphou-bookings/   Booking workflow, payments
  mu-plugins/watphou-env.php  Demo hardening, noindex banner

content/
  extracted/*.json            Scraped page/tour text (git-tracked)
  redirects.csv               Old Wix URL → new URL
  media_manifest.csv          Asset mapping and hashes

scripts/
  deploy_demo.py              tar-over-ssh sync wp-content
  verify_demo.py              HTTP health checks
  scrape_live_site.py         Live Wix content extraction
  inventory_backup.py         Backup ZIP analysis
  provision_demo.sh           One-time server setup (run on sm)
```

## Tour data model

**Post type:** `tour` (registered in watphou-core)

**Taxonomies:**
- `destination` — Bolaven Plateau, 4000 Islands, Vat Phou & Champasak, Pakse
- `tour_type` — Day, Multi-day, Tailor-made, etc.
- `duration_cat` — 1 day, 2D/1N, 3D/2N, etc.

**Post meta (filterable / numeric):**
- `tour_duration`, `tour_price_from`, `tour_currency`
- `tour_price_2pax`, `tour_price_3_4pax`, `tour_price_5_6pax`
- `tour_guide_supplement`, `tour_accommodation_standard`, `tour_comfort_upgrades`
- `tour_bestseller`, `tour_priority`, `tour_whatsapp_cta`

**Block content (long-form):**
- `watphou/itinerary-day` — day number, title, description, image
- `watphou/highlights` — bullet list
- `watphou/inclusions` / `watphou/exclusions` / `watphou/upgrades`

**Post type:** `testimonial` — star rating, quote, reviewer name, date, source URL

## Booking state machine

States: `requested` → `under_review` → `availability_confirmed` → `quote_sent` → `payment_pending` → `paid` → `confirmed`

Terminal/alternate: `rejected`, `cancelled`, `expired`, `payment_failed`, `refunded`

Each transition writes to `wp_watphou_booking_events` with user_id, timestamp, reason.

Invalid transitions rejected by guard table in `Watphou_Booking_State_Machine`.

## Payment abstraction

```php
interface Watphou_Payment_Provider {
    public function create_session( $booking, $amount, $currency, $reference );
    public function verify_callback( $payload, $signature );
    public function get_status( $reference );
    public function cancel( $reference );
}
```

Implementations:
- `MockProvider` — demo/testing, clearly labelled simulation
- `BcelProvider` — stub throws until credentials configured

Public URL: `/pay/{secure-token}` — expiring, unguessable, single-use.

## Deploy flow

```mermaid
flowchart LR
  A[Edit wp-content locally] --> B[git commit]
  B --> C[deploy_demo.py tar ssh]
  C --> D[/var/www/watphou-demo/wp-content]
  D --> E[wp cache flush]
  E --> F[verify_demo.py]
```

Never syncs: uploads, wp-config.php, database.

## Multilingual

Polylang links translated posts. English is source. French from backup PDFs where available. Thai = empty flagged drafts.

## Security layers (demo)

1. HTTPS
2. WordPress login for managers (`Log in to edit`)
3. `DISALLOW_FILE_EDIT`
4. XML-RPC disabled
5. `noindex` + X-Robots-Tag
6. Tour Manager role (no plugin/theme admin)
7. Booking tables not exposed via REST
