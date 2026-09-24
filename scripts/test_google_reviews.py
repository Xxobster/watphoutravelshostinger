"""CLI check: homepage Google reviews stay newest-first and 4–5 star only."""
from __future__ import annotations

import json
import re
import sys
from datetime import datetime, timedelta
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
JSON_PATH = ROOT / "wp-content" / "plugins" / "watphou-core" / "data" / "google-reviews.json"
REVIEWS_PHP = ROOT / "wp-content" / "plugins" / "watphou-core" / "includes" / "reviews.php"
PLUGIN = ROOT / "wp-content" / "plugins" / "watphou-core" / "watphou-core.php"
NOW = datetime(2026, 9, 24)

EXPECTED_AUTHORS = [
    "Natalie Guignard",
    "尾崎行雄",
    "Reinhard Anton",
    "Anja Krause",
    "Simone Kuhl",
]


def sort_ts(row: dict) -> tuple[datetime, int]:
    date = str(row.get("date") or "")
    if re.match(r"^\d{4}-\d{2}-\d{2}", date):
        return datetime.fromisoformat(date[:10]), 0
    label = str(row.get("date_label") or "").lower()
    match = re.search(r"(\d+)\s*(year|month|week|day|hour)s?\s+ago", label)
    if match:
        amount = max(1, int(match.group(1)))
        unit = match.group(2)
        days = {"year": 365, "month": 30, "week": 7, "day": 1, "hour": 0}[unit] * amount
        hours = amount if unit == "hour" else 0
        return NOW - timedelta(days=days, hours=hours), 1
    return datetime.min, 2


def main() -> int:
    data = json.loads(JSON_PATH.read_text(encoding="utf-8"))
    php = REVIEWS_PHP.read_text(encoding="utf-8")
    plugin = PLUGIN.read_text(encoding="utf-8")
    reviews = data["reviews"]
    authors = [row["author"] for row in reviews]

    assert authors == EXPECTED_AUTHORS, authors
    assert all(int(row["rating"]) >= 4 for row in reviews)
    assert all(str(row["text"]).strip() for row in reviews)
    assert "Master blaster ironic" not in authors
    assert " mar" not in php
    assert "watphou_core_sort_reviews_newest_first" in php
    assert "reviewsSort" in php and "NEWEST" in php
    assert "WATPHOU_CORE_REVIEWS_MIN_RATING" in php
    assert "1.8.4" in plugin

    indexed = list(enumerate(reviews))
    resorted = sorted(indexed, key=lambda item: (sort_ts(item[1])[0], -item[0]), reverse=True)
    resorted_authors = [row["author"] for _, row in resorted]
    assert resorted_authors == EXPECTED_AUTHORS, resorted_authors
    print("ok", len(reviews), "reviews newest-first:", ", ".join(a.encode("ascii", "backslashreplace").decode("ascii") for a in authors))
    return 0


if __name__ == "__main__":
    sys.exit(main())
