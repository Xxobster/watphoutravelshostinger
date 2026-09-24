# Secrets Inventory — Watphou Travels

**Names and locations only. Never store values in git.**

| Secret | Location | Status |
|--------|----------|--------|
| Demo DB password | Server `/var/backups/watphou-demo/db_credentials_20260826_202651.txt` | provisioned |
| Demo DB user | `watphou_demo` | provisioned |
| HTTP Basic Auth | Removed 2026-08-26 | retired |
| HTTP Basic Auth password | n/a | retired |
| WordPress admin (dev) | user `admin` — password in server cred file | provisioned |
| WordPress manager password | Demo: `000000` (documented, not secret for demo) | known |
| WordPress salts | wp-config.php on server | pending provision |
| SMTP host/user/password | Optional `wp-content/watphou-smtp-secrets.php` or `wp-config.php` constants `WATPHOU_SMTP_*` (not in git). Staging uses Hostinger PHP mail + Sender Policy Framework (SPF); no Hostinger Email mailbox product on this account. | staging live 2026-09-24 |
| BCEL merchant ID | .env on server | UNRESOLVED |
| BCEL signing key | .env on server | UNRESOLVED |
| BCEL callback secret | .env on server | UNRESOLVED |
| Bluehost SSH key | User machine ~/.ssh/ | UNRESOLVED |
| GA4 measurement ID | WordPress options | UNRESOLVED |
| GSC verification token | WordPress options | UNRESOLVED |
| Google Places Application Programming Interface key | `WATPHOU_GOOGLE_PLACES_KEY` in server `wp-config.php`, or WordPress option `watphou_google_places_key` (Settings → Watphou Travels). Name only — never store the value in git. | UNRESOLVED |

## Rotation policy

- Change manager password before production launch.
- BCEL keys: production-only, never on demo with real payments.
