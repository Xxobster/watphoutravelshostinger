#!/usr/bin/env python3
"""Download Watphou-owned Wix images into the theme assets folder."""

from __future__ import annotations

import urllib.request
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
OUT = ROOT / "wp-content" / "themes" / "watphou-travels" / "assets" / "images"
OUT.mkdir(parents=True, exist_ok=True)

UA = "Mozilla/5.0 (compatible; WatphouMigration/1.0)"

# Original-resolution Wix media from the live Watphou site (owner content).
IMAGES = {
    "logo.png": "https://static.wixstatic.com/media/42d50c_59b944da102d4ef2a264dfffe523e27b~mv2.png",
    "logo-white.png": "https://static.wixstatic.com/media/42d50c_dc72d24054e4467ba8f322558f5b3e96~mv2.png",
    "hero-islands.jpg": "https://static.wixstatic.com/media/42d50c_4bd796c219ce44c3a1dfa91f81d071f1~mv2_d_2048_1363_s_2.jpg",
    "hero-temple.jpg": "https://static.wixstatic.com/media/42d50c_c1025180fe9b4c4887c0d4ed1ae435c3~mv2.jpg",
    "bolaven.jpg": "https://static.wixstatic.com/media/42d50c_12baf73eaecf463ea24a2afacdb7625d~mv2.jpg",
    "waterfall.jpg": "https://static.wixstatic.com/media/42d50c_9d4dc28c9c614345b9b00844125f6531~mv2.jpg",
    "islands-2.jpg": "https://static.wixstatic.com/media/42d50c_202131bf3b8a472bb109b966489723e7~mv2.jpg",
    "champasak.jpg": "https://static.wixstatic.com/media/42d50c_3b0e3f8f8dd74ebe853dff9d0eec40e2~mv2.jpg",
    "fisherman.jpg": "https://static.wixstatic.com/media/42d50c_4d8482bc68e4483da072575d688331ef~mv2.jpg",
    "coffee.jpg": "https://static.wixstatic.com/media/42d50c_c1025180fe9b4c4887c0d4ed1ae435c3~mv2.jpg",
}


def main() -> None:
    for name, url in IMAGES.items():
        dest = OUT / name
        print(f"Fetching {name}...")
        req = urllib.request.Request(url, headers={"User-Agent": UA})
        try:
            with urllib.request.urlopen(req, timeout=60) as resp:
                dest.write_bytes(resp.read())
            print(f"  {dest.stat().st_size} bytes")
        except Exception as e:
            print(f"  FAIL {e}")


if __name__ == "__main__":
    main()
