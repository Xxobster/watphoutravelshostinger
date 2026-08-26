# Security — Watphou Travels

## Demo protections

| Control | Implementation |
|---------|----------------|
| HTTPS | Let's Encrypt via certbot |
| Outer access gate | nginx HTTP Basic Auth |
| noindex | mu-plugin + X-Robots-Tag |
| DISALLOW_FILE_EDIT | wp-config constant |
| XML-RPC disabled | mu-plugin filter |
| Login rate limiting | mu-plugin (5 attempts / 15 min) |
| Separate DB/user | watphou_demo |
| Mock payments only | WATPHOU_DEMO + MockProvider |
| Manager weak password | Demo only — change for production |

## Application security

- Nonces on all admin booking actions
- Capability checks: `manage_watphou_bookings`, `edit_tours`
- Booking data in custom tables — not REST-exposed
- Prepared SQL via `$wpdb->prepare`
- Input sanitization on all form fields
- Output escaping in templates
- Payment callback signature verification
- Amount/reference mismatch rejected

## Secrets policy

- Never commit `.env`, passwords, or BCEL keys
- Server credentials in `/var/backups/watphou-demo/` (root only)
- Basic auth password reported in chat only

## Privacy

- Invoice/bank PDFs quarantined locally
- Privacy consent on booking form
- Booking data retention: define policy before production
- GDPR: export/delete on request (implement before EU marketing)

## Production additional

- Strong manager password
- 2FA for admin accounts
- Daily off-site backups
- Wordfence or equivalent (evaluate on Bluehost)
- Remove demo banner and noindex

## Update policy

- WordPress core: auto minor updates on Bluehost
- Plugins: test on demo before production
- Never use nulled/pirated plugins
