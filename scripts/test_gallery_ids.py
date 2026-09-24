"""CLI check: Quick Edit stores tour gallery IDs (no PHP needed)."""
from __future__ import annotations

import re
import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
PHOTOS = ROOT / "wp-content" / "plugins" / "watphou-core" / "includes" / "photos.php"
DESK = ROOT / "wp-content" / "plugins" / "watphou-core" / "includes" / "admin-tour-desk.php"
PLUGIN = ROOT / "wp-content" / "plugins" / "watphou-core" / "watphou-core.php"


def unique_positive_ids(raw: str) -> list[int]:
    out: list[int] = []
    for part in re.split(r"[,\s]+", raw.strip()):
        if not part:
            continue
        try:
            value = int(part)
        except ValueError:
            continue
        if value > 0 and value not in out:
            out.append(value)
    return out


def main() -> int:
    photos = PHOTOS.read_text(encoding="utf-8")
    desk = DESK.read_text(encoding="utf-8")
    plugin = PLUGIN.read_text(encoding="utf-8")
    for needle, text, label in (
        ("tour_gallery", photos, "photos.php"),
        ("tour_gallery_managed", photos, "photos.php"),
        ("watphou_core_unique_positive_ids", photos, "photos.php"),
        ("watphou_core_maybe_seed_tour_galleries", photos, "photos.php"),
        ("get_the_post_thumbnail_url", photos, "photos.php"),
        ("Change top photo", desk, "admin-tour-desk.php"),
        ("Change bottom photos", desk, "admin-tour-desk.php"),
        ("gallery_dirty", desk, "admin-tour-desk.php"),
        ("watphou-desk-gallery-pick", desk, "admin-tour-desk.php"),
        ("WATPHOU_CORE_VERSION', '1.7.5'", plugin, "watphou-core.php"),
    ):
        if needle not in text:
            print(f"missing {needle!r} in {label}", file=sys.stderr)
            return 1
    if unique_positive_ids("12, 0, 12, abc, 7") != [12, 7]:
        print("unique_positive_ids should keep order and drop junk", file=sys.stderr)
        return 1
    if unique_positive_ids("") != []:
        print("empty gallery must parse to no IDs", file=sys.stderr)
        return 1
    if unique_positive_ids("  3 5 5 9 ") != [3, 5, 9]:
        print("space-separated IDs should work", file=sys.stderr)
        return 1
    print("tour gallery IDs: OK")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
