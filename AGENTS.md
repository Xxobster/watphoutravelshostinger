# Watphou Travels — Agent Entry Point

Any AI agent working on this project **must** read this file first.

## Read in this order

1. [docs/project_memory/AGENT_ONBOARDING.md](docs/project_memory/AGENT_ONBOARDING.md)
2. [docs/project_memory/PROJECT_PROFILE.md](docs/project_memory/PROJECT_PROFILE.md)
3. [docs/project_memory/CURRENT_STATE.md](docs/project_memory/CURRENT_STATE.md)
4. [docs/project_memory/TASKS.md](docs/project_memory/TASKS.md)

## Project summary

Rebuild **watphou-travels.com** from Wix to WordPress with a custom block theme and two custom plugins. Staging runs on Hostinger at `https://darkslategray-snake-182151.hostingersite.com`. The old Virtual Private Server (VPS) demo `watphou.smbistro.duckdns.org` on `sm` was removed on 2026-09-10. Production stays on Hostinger when the real domain is attached. Never touch `smbistro.duckdns.org` or other sites on VPS `sm`.

## Repository contains

- **Code only**: `wp-content/themes/watphou-travels`, `wp-content/plugins/watphou-core`, `wp-content/plugins/watphou-bookings`, `wp-content/mu-plugins/`
- **Content data**: `content/extracted/*.json`, `content/redirects.csv`
- **Scripts**: `scripts/` for deploy, scrape, inventory, verify
- **Documentation**: `docs/` including `project_memory/`

## Hard rules

1. Never modify the live Wix site or its DNS.
2. Never touch the existing `smbistro.duckdns.org` app on the demo VPS.
3. Never commit secrets, invoices, bank documents, or the backup ZIP.
4. Never invent prices, reviews, or business facts.
5. Never overwrite production database with staging data.
6. Update `docs/project_memory/CURRENT_STATE.md` and `TASKS.md` in the same commit as code changes.
7. Do not deploy to Virtual Private Server (VPS) `sm`. Verify staging with `python scripts/verify_demo.py`. Deploy theme/plugins to Hostinger, then confirm `https://darkslategray-snake-182151.hostingersite.com`.
8. Use `From $XX` placeholders until real prices are supplied.
9. Thai: do not machine-translate. French/Thai public pages may show English with a banner until a professional translation exists.
10. BCEL payment stays mock/stub until real credentials exist.

## End-of-task ritual

After every meaningful change:

1. Update `docs/project_memory/CURRENT_STATE.md`
2. Update `docs/project_memory/TASKS.md` (mark completed items)
3. Append to `docs/project_memory/DECISIONS.md` if a new decision was made
4. Log test results in `docs/project_memory/TEST_LOG.md` if tests were run
5. Commit with a descriptive message
6. Deploy theme/plugins to Hostinger if `wp-content/` changed; then `python scripts/verify_demo.py`. Do not deploy Watphou to VPS `sm`.
