#!/usr/bin/env python3
"""Optimize images to WebP (requires Pillow)."""

from __future__ import annotations

from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
SRC = ROOT / "work" / "media_downloads"
OUT = ROOT / "work" / "media_optimized"


def main() -> None:
    try:
        from PIL import Image
    except ImportError:
        print("pip install Pillow")
        return
    OUT.mkdir(parents=True, exist_ok=True)
    n = 0
    for path in SRC.rglob("*"):
        if path.suffix.lower() not in {".jpg", ".jpeg", ".png"}:
            continue
        dest = OUT / (path.stem + ".webp")
        if dest.exists():
            continue
        Image.open(path).save(dest, "WEBP", quality=82)
        n += 1
    print(f"Optimized {n} images to {OUT}")


if __name__ == "__main__":
    main()
