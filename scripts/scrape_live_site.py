#!/usr/bin/env python3
"""Scrape live Wix site pages and extract structured content for WordPress migration."""

from __future__ import annotations

import csv
import json
import re
import time
import urllib.error
import urllib.request
from html import unescape
from pathlib import Path
from urllib.parse import urljoin, urlparse

ROOT = Path(__file__).resolve().parents[1]
BASE = "https://www.watphou-travels.com"
SITEMAP_URL = f"{BASE}/pages-sitemap.xml"
HTML_DIR = ROOT / "work" / "live_html"
EXTRACT_DIR = ROOT / "content" / "extracted"
REDIRECTS_CSV = ROOT / "content" / "redirects.csv"
MIGRATION_MD = ROOT / "docs" / "CONTENT_MIGRATION_MAP.md"

URL_LIST = ROOT / "content" / "urls.txt"
USER_AGENT = "Mozilla/5.0 (compatible; WatphouMigration/1.0; +https://www.watphou-travels.com)"
DELAY_SEC = 8.0
MAX_RETRIES = 8
RETRY_BASE_SEC = 30

# Pages to 410
STORE_URLS = [
    f"{BASE}/product-page/i-m-a-product",
    *[f"{BASE}/product-page/i-m-a-product-{i}" for i in range(1, 12)],
    f"{BASE}/category/all-products",
]

PAGE_TYPE_HINTS = {
    "day-tours": "listing",
    "2-day-tours": "listing",
    "long-tours": "listing",
    "destinations": "listing",
    "about-us": "page",
    "contact-us": "page",
    "book-online": "page",
    "pakse-city": "destination",
    "bolaven-plateau-1": "destination",
    "champasak": "destination",
    "4000-islands": "destination",
}


def fetch(url: str) -> str:
    last_err: Exception | None = None
    for attempt in range(MAX_RETRIES):
        try:
            req = urllib.request.Request(url, headers={"User-Agent": USER_AGENT})
            with urllib.request.urlopen(req, timeout=45) as resp:
                return resp.read().decode("utf-8", errors="replace")
        except urllib.error.HTTPError as e:
            last_err = e
            if e.code == 429 and attempt < MAX_RETRIES - 1:
                wait = RETRY_BASE_SEC * (2**attempt)
                print(f"  429 rate limit - waiting {wait}s (retry {attempt + 1}/{MAX_RETRIES})")
                time.sleep(wait)
                continue
            raise
        except urllib.error.URLError as e:
            last_err = e
            if attempt < MAX_RETRIES - 1:
                time.sleep(RETRY_BASE_SEC)
                continue
            raise
    raise last_err or RuntimeError(f"Failed to fetch {url}")


def strip_html(html: str) -> str:
    html = re.sub(r"(?is)<script.*?>.*?</script>", " ", html)
    html = re.sub(r"(?is)<style.*?>.*?</style>", " ", html)
    text = re.sub(r"<[^>]+>", " ", html)
    text = unescape(text)
    return re.sub(r"\s+", " ", text).strip()


def extract_title(html: str) -> str:
    m = re.search(r"(?is)<title[^>]*>(.*?)</title>", html)
    if m:
        t = unescape(re.sub(r"\s+", " ", m.group(1))).strip()
        return re.sub(r"\s*\|\s*Watphoutravels.*$", "", t, flags=re.I)
    return ""


def extract_meta_desc(html: str) -> str:
    m = re.search(r'(?is)<meta[^>]+name=["\']description["\'][^>]+content=["\'](.*?)["\']', html)
    if not m:
        m = re.search(r'(?is)<meta[^>]+content=["\'](.*?)["\'][^>]+name=["\']description["\']', html)
    return unescape(m.group(1)).strip() if m else ""


def extract_wix_images(html: str) -> list[str]:
    urls = set()
    for m in re.finditer(r"https://static\.wixstatic\.com/media/[A-Za-z0-9_~%\./\-]+", html):
        url = m.group(0)
        # Original resolution: strip /v1/... transforms
        base = re.sub(r"/v1/.*$", "", url)
        if "~" in base or ".jpg" in base.lower() or ".png" in base.lower():
            urls.add(base)
    return sorted(urls)


def slug_from_url(url: str) -> str:
    path = urlparse(url).path.strip("/")
    return path or "home"


def guess_type(slug: str) -> str:
    if slug in PAGE_TYPE_HINTS:
        return PAGE_TYPE_HINTS[slug]
    if slug == "" or slug == "home":
        return "home"
    # Heuristic: most other slugs are tours
    tour_keywords = (
        "tour", "day", "trek", "bolaven", "vatphu", "4000", "island",
        "champasak", "pakse", "journey", "exploring", "weaving", "cycling",
    )
    if any(k in slug for k in tour_keywords):
        return "tour"
    return "page"


def new_url(slug: str, page_type: str) -> str:
    if slug in ("", "home"):
        return "/en/"
    if page_type == "destination":
        dest_map = {
            "pakse-city": "pakse",
            "bolaven-plateau-1": "bolaven-plateau",
            "champasak": "champasak",
            "4000-islands": "4000-islands",
        }
        d = dest_map.get(slug, slug)
        return f"/en/destinations/{d}/"
    if page_type == "listing":
        listing_map = {
            "day-tours": "/en/day-tours/",
            "2-day-tours": "/en/multi-day-tours/",
            "long-tours": "/en/multi-day-tours/",
            "destinations": "/en/destinations/",
        }
        return listing_map.get(slug, f"/en/{slug}/")
    if page_type == "tour":
        return f"/en/tours/{slug}/"
    page_map = {
        "about-us": "/en/about-us/",
        "contact-us": "/en/contact/",
        "book-online": "/en/booking-request/",
    }
    return page_map.get(slug, f"/en/{slug}/")


def get_sitemap_urls() -> list[str]:
    if URL_LIST.exists():
        lines = [ln.strip() for ln in URL_LIST.read_text(encoding="utf-8").splitlines() if ln.strip()]
        if lines:
            return lines
    try:
        xml = fetch(SITEMAP_URL)
        return re.findall(r"<loc>(https://www\.watphou-travels\.com[^<]+)</loc>", xml)
    except (urllib.error.HTTPError, urllib.error.URLError):
        print("Sitemap fetch failed — using cached content/urls.txt only")
        return []


def main() -> None:
    HTML_DIR.mkdir(parents=True, exist_ok=True)
    EXTRACT_DIR.mkdir(parents=True, exist_ok=True)

    urls = sorted(set(get_sitemap_urls()))
    rows: list[dict] = []
    redirects: list[dict] = []

    for i, url in enumerate(urls):
        slug = slug_from_url(url)
        out_name = slug or "home"
        json_path = EXTRACT_DIR / f"{out_name}.json"
        if json_path.exists():
            print(f"[{i+1}/{len(urls)}] {slug} — skip (already extracted)")
            existing = json.loads(json_path.read_text(encoding="utf-8"))
            rows.append(existing)
            redirects.append(
                {
                    "old_url": urlparse(url).path or "/",
                    "new_url": existing["new_url"],
                    "http_code": "301",
                    "notes": existing["page_type"],
                }
            )
            continue
        print(f"[{i+1}/{len(urls)}] {slug}")
        try:
            html = fetch(url)
        except (urllib.error.URLError, urllib.error.HTTPError) as e:
            print(f"  ERROR: {e}")
            continue
        (HTML_DIR / f"{slug or 'home'}.html").write_text(html, encoding="utf-8")

        page_type = guess_type(slug)
        title = extract_title(html)
        meta = extract_meta_desc(html)
        text = strip_html(html)
        images = extract_wix_images(html)

        # First ~8000 chars of body text for migration
        body_sample = text[:8000] if len(text) > 8000 else text

        data = {
            "source_url": url,
            "slug": slug or "home",
            "page_type": page_type,
            "language": "en",
            "title": title,
            "meta_description": meta,
            "body_text": body_sample,
            "image_urls": images,
            "content_source": "live_site",
            "new_url": new_url(slug, page_type),
            "migration_status": "extracted",
        }
        out_name = slug or "home"
        (EXTRACT_DIR / f"{out_name}.json").write_text(
            json.dumps(data, indent=2, ensure_ascii=False), encoding="utf-8"
        )
        rows.append(data)
        redirects.append(
            {
                "old_url": urlparse(url).path,
                "new_url": data["new_url"],
                "http_code": "301",
                "notes": page_type,
            }
        )
        time.sleep(DELAY_SEC)

    for url in STORE_URLS:
        path = urlparse(url).path
        redirects.append({"old_url": path, "new_url": "", "http_code": "410", "notes": "dummy wix store"})

    with REDIRECTS_CSV.open("w", newline="", encoding="utf-8") as f:
        w = csv.DictWriter(f, fieldnames=["old_url", "new_url", "http_code", "notes"])
        w.writeheader()
        w.writerows(redirects)

    md = [
        "# Content Migration Map — Watphou Travels",
        "",
        f"**Source:** Live Wix site + sitemap",
        f"**Pages scraped:** {len(rows)}",
        "",
        "| Old URL | Title | Type | New URL | Images | Status |",
        "|---------|-------|------|---------|--------|--------|",
    ]
    for r in rows:
        old = f"/{r['slug']}" if r["slug"] != "home" else "/"
        md.append(
            f"| `{old}` | {r['title'][:50]} | {r['page_type']} | `{r['new_url']}` | {len(r['image_urls'])} | extracted |"
        )
    md.extend(["", "## Retired URLs (410)", ""])
    for d in redirects:
        if d["http_code"] == "410":
            md.append(f"- `{d['old_url']}`")
    md.append(f"\nRedirects: `{REDIRECTS_CSV.relative_to(ROOT)}`")
    md.append(f"Extracted JSON: `{EXTRACT_DIR.relative_to(ROOT)}/`")
    MIGRATION_MD.write_text("\n".join(md), encoding="utf-8")
    print(f"Done: {len(rows)} pages -> {EXTRACT_DIR}")


if __name__ == "__main__":
    main()
