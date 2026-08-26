#!/usr/bin/env python3
"""Generate minimal extracted JSON from urls.txt when live scrape is incomplete."""

from __future__ import annotations

import json
import re
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
URLS = ROOT / "content" / "urls.txt"
OUT = ROOT / "content" / "extracted"

PAGE_TYPE_HINTS = {
    "day-tours": "listing", "2-day-tours": "listing", "long-tours": "listing",
    "destinations": "listing", "about-us": "page", "contact-us": "page", "book-online": "page",
    "pakse-city": "destination", "bolaven-plateau-1": "destination",
    "champasak": "destination", "4000-islands": "destination",
}


def slug_from_url(url: str) -> str:
    path = url.rstrip("/").split("/")[-1]
    return path or "home"


def guess_type(slug: str) -> str:
    if slug in PAGE_TYPE_HINTS:
        return PAGE_TYPE_HINTS[slug]
    if slug in ("", "home"):
        return "home"
    tour_kw = ("tour", "day", "trek", "bolaven", "vatphu", "4000", "island", "champasak", "pakse", "journey", "weaving", "cycling")
    return "tour" if any(k in slug for k in tour_kw) else "page"


def new_url(slug: str, page_type: str) -> str:
    if slug in ("", "home"):
        return "/en/"
    if page_type == "destination":
        dest_map = {"pakse-city": "pakse", "bolaven-plateau-1": "bolaven-plateau", "champasak": "champasak", "4000-islands": "4000-islands"}
        return f"/en/destinations/{dest_map.get(slug, slug)}/"
    if page_type == "listing":
        return {"day-tours": "/en/day-tours/", "2-day-tours": "/en/multi-day-tours/", "long-tours": "/en/multi-day-tours/", "destinations": "/en/destinations/"}.get(slug, f"/en/{slug}/")
    if page_type == "tour":
        return f"/en/tours/{slug}/"
    page_map = {"about-us": "/en/about-us/", "contact-us": "/en/contact/", "book-online": "/en/booking-request/"}
    return page_map.get(slug, f"/en/{slug}/")


def main() -> None:
    OUT.mkdir(parents=True, exist_ok=True)
    urls = [u.strip() for u in URLS.read_text(encoding="utf-8").splitlines() if u.strip()]
    for url in urls:
        slug = slug_from_url(url)
        out = OUT / f"{slug}.json"
        if out.exists() and out.stat().st_size > 500:
            continue
        page_type = guess_type(slug)
        title = slug.replace("-", " ").title() if slug != "home" else "Watphou Travels"
        data = {
            "source_url": url,
            "slug": slug,
            "page_type": page_type,
            "language": "en",
            "title": title,
            "meta_description": "",
            "body_text": f"Content migrated from {url}. Full text pending scrape or manual edit.",
            "image_urls": [],
            "content_source": "urls_fallback",
            "new_url": new_url(slug, page_type),
            "migration_status": "placeholder",
        }
        out.write_text(json.dumps(data, indent=2, ensure_ascii=False), encoding="utf-8")
        print(f"Created {out.name}")
    print("Done.")


if __name__ == "__main__":
    main()
