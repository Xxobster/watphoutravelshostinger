# Watphou Travels — Agent Entry Point

Any AI agent working on this project **must** read this file first.

## Read in this order

1. [docs/project_memory/AGENT_ONBOARDING.md](docs/project_memory/AGENT_ONBOARDING.md)
2. [docs/project_memory/PROJECT_PROFILE.md](docs/project_memory/PROJECT_PROFILE.md)
3. [docs/project_memory/CURRENT_STATE.md](docs/project_memory/CURRENT_STATE.md)
4. [docs/project_memory/TASKS.md](docs/project_memory/TASKS.md)

## Project summary

Rebuild **watphou-travels.com** from Wix to WordPress with a custom block theme and two custom plugins. Demo runs on VPS `sm` (`212.73.150.149`) at `https://watphou.smbistro.duckdns.org`. Production will later deploy to Bluehost.

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
7. Deploy with `python scripts/deploy_demo.py` then verify with `python scripts/verify_demo.py`.
8. Use `From $XX` placeholders until real prices are supplied.
9. Thai translations must be flagged drafts — no machine translation.
10. BCEL payment stays mock/stub until real credentials exist.

## End-of-task ritual

After every meaningful change:

1. Update `docs/project_memory/CURRENT_STATE.md`
2. Update `docs/project_memory/TASKS.md` (mark completed items)
3. Append to `docs/project_memory/DECISIONS.md` if a new decision was made
4. Log test results in `docs/project_memory/TEST_LOG.md` if tests were run
5. Commit with a descriptive message
6. Deploy and verify if code changed
