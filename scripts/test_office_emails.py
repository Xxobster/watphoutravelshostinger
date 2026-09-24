"""CLI check: both office Gmail addresses stay unique and ordered (no PHP needed)."""
from __future__ import annotations

import re
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
MAIL_PHP = ROOT / "wp-content" / "plugins" / "watphou-core" / "includes" / "mail.php"


def unique_office_emails(primary: str, alt: str) -> list[str]:
    out: list[str] = []
    for raw in (primary, alt):
        email = raw.strip().lower()
        ok = bool(re.match(r"^[^@\s]+@[^@\s]+\.[^@\s]+$", email))
        if email and ok and email not in out:
            out.append(email)
    return out or ["sales.watphoutravel@gmail.com"]


def main() -> int:
    text = MAIL_PHP.read_text(encoding="utf-8")
    for needle in (
        "sales.watphoutravel@gmail.com",
        "watphoutravel.of@gmail.com",
        "watphou_unique_office_emails",
        "watphou_office_email_recipients",
    ):
        if needle not in text:
            print("missing", needle, file=sys.stderr)
            return 1
    got = unique_office_emails(
        "sales.watphoutravel@gmail.com",
        "watphoutravel.of@gmail.com",
    )
    want = ["sales.watphoutravel@gmail.com", "watphoutravel.of@gmail.com"]
    if got != want:
        print("expected both inboxes, got", got, file=sys.stderr)
        return 1
    dedupe = unique_office_emails(
        "sales.watphoutravel@gmail.com",
        "sales.watphoutravel@gmail.com",
    )
    if dedupe != ["sales.watphoutravel@gmail.com"]:
        print("duplicate emails were not collapsed", file=sys.stderr)
        return 1
    fallback = unique_office_emails("", "not-an-email")
    if fallback != ["sales.watphoutravel@gmail.com"]:
        print("empty list should fall back to sales inbox", file=sys.stderr)
        return 1
    print("office email recipients: OK")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
