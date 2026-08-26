# BCEL Integration Requirements — Watphou Travels

**Status:** Not available — mock provider only on demo.

## Required from BCEL (Banque pour le Commerce Extérieur Lao)

| Item | Status |
|------|--------|
| Merchant contract | UNRESOLVED |
| Sandbox credentials | UNRESOLVED |
| Production credentials | UNRESOLVED |
| Merchant identifier | UNRESOLVED |
| API documentation (REST/SOAP) | UNRESOLVED |
| Callback/webhook documentation | UNRESOLVED |
| Signing algorithm (HMAC/RSA) | UNRESOLVED |
| Test cards or QR payment flow | UNRESOLVED |
| Supported currencies (USD, LAK) | UNRESOLVED |
| Decimal/rounding rules | UNRESOLVED |
| Session/link expiration policy | UNRESOLVED |
| Payment status query API | UNRESOLVED |
| Refund API (if supported) | UNRESOLVED |
| Required legal/compliance pages | UNRESOLVED |
| IP allowlist / TLS requirements | UNRESOLVED |
| Production approval procedure | UNRESOLVED |

## Architecture (ready in code)

- `Watphou_Payment_Provider` interface
- `Watphou_Mock_Payment_Provider` — demo/testing
- `Watphou_Bcel_Payment_Provider` — stub throws until configured
- Token URL: `/pay/{secure-token}`
- REST callback: `/wp-json/watphou/v1/payment/callback`
- Idempotent callback with amount/reference verification

## Environment variables (names only)

See `.env.example`: `BCEL_MERCHANT_ID`, `BCEL_API_ENDPOINT`, `BCEL_SIGNING_KEY`, `BCEL_CALLBACK_SECRET`, `BCEL_MODE`

## Do not

- Invent API endpoints
- Store card numbers or CVV
- Enable live BCEL on demo without sandbox tests
