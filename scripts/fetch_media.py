#!/usr/bin/env python3
"""Download media from manifest or scraped image URLs (stub for batch fetch)."""

from __future__ import annotations

import csv
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
MANIFEST = ROOT / "content" / "media_manifest.csv"
OUT = ROOT / "work" / "media_downloads"


def main() -> None:
    OUT.mkdir(parents=True, exist_ok=True)
    if not MANIFEST.exists():
        print("Run inventory_backup.py first")
        return
    count = 0
    with MANIFEST.open(encoding="utf-8") as f:
        for row in csv.DictReader(f):
            if row.get("publishable") != "yes" or row.get("category") != "image":
                continue
            count += 1
    print(f"Ready to fetch {count} publishable images to {OUT}")
    print("Full download runs on server during media migration phase.")


if __name__ == "__main__":
    main()
