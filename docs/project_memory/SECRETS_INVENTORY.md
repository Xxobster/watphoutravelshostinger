# Secrets Inventory — Watphou Travels

**Names and locations only. Never store values in git.**

| Secret | Location | Status |
|--------|----------|--------|
| Demo DB password | Server `/var/backups/watphou-demo/db_credentials_20260826_202651.txt` | provisioned |
| Demo DB user | `watphou_demo` | provisioned |
| HTTP Basic Auth user | `watphou_demo` | provisioned |
| HTTP Basic Auth password | Server cred file only — not in git | provisioned |
| WordPress admin (dev) | user `admin` — password in server cred file | provisioned |
| WordPress manager password | Demo: `000000` (documented, not secret for demo) | known |
| WordPress salts | wp-config.php on server | pending provision |
| SMTP host/user/password | .env on server (not in git) | UNRESOLVED |
| BCEL merchant ID | .env on server | UNRESOLVED |
| BCEL signing key | .env on server | UNRESOLVED |
| BCEL callback secret | .env on server | UNRESOLVED |
| Bluehost SSH key | User machine ~/.ssh/ | UNRESOLVED |
| GA4 measurement ID | WordPress options | UNRESOLVED |
| GSC verification token | WordPress options | UNRESOLVED |

## Rotation policy

- Change manager password before production launch.
- Regenerate Basic Auth password if leaked.
- BCEL keys: production-only, never on demo with real payments.
