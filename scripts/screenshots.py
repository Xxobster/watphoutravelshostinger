#!/usr/bin/env python3
"""Capture desktop/mobile screenshots via Playwright (optional local venv)."""

from __future__ import annotations

import os
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
OUT = ROOT / "work" / "screenshots"
DEMO_URL = os.environ.get("DEMO_URL", "https://watphou.smbistro.duckdns.org")
BASIC_USER = os.environ.get("DEMO_BASIC_AUTH_USER", "")
BASIC_PASS = os.environ.get("DEMO_BASIC_AUTH_PASSWORD", "")

PAGES = ["", "/wp-admin/"]
WIDTHS = [375, 768, 1440]


def main() -> int:
    try:
        from playwright.sync_api import sync_playwright
    except ImportError:
        print("Install: pip install playwright && playwright install chromium")
        return 1

    OUT.mkdir(parents=True, exist_ok=True)
    auth = None
    if BASIC_USER and BASIC_PASS:
        auth = {"username": BASIC_USER, "password": BASIC_PASS}

    with sync_playwright() as p:
        browser = p.chromium.launch()
        for page_path in PAGES:
            slug = page_path.strip("/").replace("/", "_") or "home"
            for w in WIDTHS:
                context = browser.new_context(viewport={"width": w, "height": 900}, http_credentials=auth)
                page = context.new_page()
                url = DEMO_URL.rstrip("/") + page_path
                page.goto(url, wait_until="networkidle", timeout=60000)
                out = OUT / f"{slug}_{w}px.png"
                page.screenshot(path=str(out), full_page=True)
                print(f"Saved {out}")
                context.close()
        browser.close()
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
