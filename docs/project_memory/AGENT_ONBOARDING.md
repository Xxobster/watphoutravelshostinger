# Agent Onboarding — Watphou Travels

Read this file first. Then read the files in order below.

## Read order

1. `PROJECT_PROFILE.md` — identity, hosts, stack, frozen decisions
2. `CURRENT_STATE.md` — what is done, in progress, blocked
3. `TASKS.md` — backlog with WT-xxx IDs
4. `ARCHITECTURE.md` — code layout and data model
5. `CONTENT_INVENTORY.md` — URLs, tours, migration status
6. `RUNBOOK_DEMO_VPS.md` — retired VPS notes + current Hostinger staging URL

## 10 hard rules

1. **Never modify** the live Wix site (`watphou-travels.com`) or its DNS.
2. **Never touch** the existing `smbistro.duckdns.org` restaurant app on VPS `sm`.
3. **Never commit** secrets, `.env`, invoices, bank documents, or `bkp/*.zip`.
4. **Never invent** prices, reviews, certifications, or contact details.
5. **Never overwrite** production database with staging/demo data.
6. **Always update** `CURRENT_STATE.md` and `TASKS.md` in the same commit as code.
7. **Deploy loop**: Hostinger staging (not VPS `sm`). Verify with `python scripts/verify_demo.py`. `deploy_demo.py` refuses VPS deploys.
8. **Prices**: use `From $XX per person — Standard accommodation. Comfort upgrades available` until client supplies real figures.
9. **Thai**: do not machine-translate. French and Thai public Uniform Resource Locators (URLs) may show English with a banner until a professional translation exists.
10. **BCEL**: mock provider only until real merchant credentials and API docs exist.

## End-of-task ritual

After every meaningful change:

1. Update `CURRENT_STATE.md` (done / in progress / next)
2. Update `TASKS.md` statuses
3. Append to `DECISIONS.md` if you made an architectural choice
4. Append to `TEST_LOG.md` if you ran tests
5. Commit with a clear message
6. Deploy theme/plugins to Hostinger if `wp-content/` changed. Verify with `python scripts/verify_demo.py`. Never deploy Watphou to VPS `sm`.

## Key paths

| What | Where |
|------|-------|
| Theme | `wp-content/themes/watphou-travels/` |
| Core plugin | `wp-content/plugins/watphou-core/` |
| Bookings plugin | `wp-content/plugins/watphou-bookings/` |
| Scraped content | `content/extracted/*.json` |
| Requirements | `docs/REQUIREMENTS_MATRIX.md` |
| Staging runbook | `RUNBOOK_DEMO_VPS.md` (Hostinger URL; VPS demo retired) |

## Contact facts (verified from live site)

- Phone/WhatsApp: +85620 9949 5858
- Email: sales.watphoutravel@gmail.com, watphoutravel.of@gmail.com
- Address: Street N°5, Ban Vat Luang, Pakse, Laos

Do not add unverified claims (e.g. "since 2008") until confirmed in source materials.
