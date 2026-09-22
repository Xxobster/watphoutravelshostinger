#!/usr/bin/env python3
"""Resize Nang September tour photos and selected homepage hero images into the theme."""

from __future__ import annotations

import hashlib
import json
import re
import shutil
from concurrent.futures import ThreadPoolExecutor, as_completed
from pathlib import Path

from PIL import Image, ImageOps

ROOT = Path(__file__).resolve().parents[1]
NANG = Path(
    r"C:\Users\xxobs\OneDrive\20 - PROJECTS\PAKSE HOTEL\WATPHOU TRAVELS"
    r"\pictures\Pictures for the new website on September 2026 update by Nang"
)
HERO_SRC = Path(
    r"C:\Users\xxobs\OneDrive\20 - PROJECTS\PAKSE HOTEL\WATPHOU TRAVELS"
    r"\pictures\Home Page\Home page hero Picture selected"
)
THEME_IMG = ROOT / "wp-content" / "themes" / "watphou-travels" / "assets" / "images"
HERO_OUT = THEME_IMG / "home-hero"
TOURS_OUT = THEME_IMG / "tours"
MANIFEST = ROOT / "wp-content" / "plugins" / "watphou-core" / "data" / "september_photos.json"

CODE_TO_SLUG = {
    "1.1": "bolaven-plateau-classic-full-day-tour",
    "1.2": "vatphu-riverside-gems-full-day",
    "1.3": "4000islands-full-day",
    "1.4": "pakse-cultural-riverside-exploration-full-day",
    "1.5": "dan-sinxai-eco-adventure-trek-full-day",
    "1.6": "champasak-cycling-experience",
    "1.7": "full-day-trekking-at-dan-yai-tiger-falls",
    "2.1": "bolaven-plateau-classic-2-day",
    "2.2": "4000islands-vatphu-temple-2-day-1-night",
    "2.3": "vatphu-champasak-discovery-2days-1night",
    "3.1": "3-day-classic-experience-in-southern-laos",
    "3.2": "3-day-highlights-of-southern-laos",
    "4.1": "4-day-southern-laos-escape",
    "5.1": "5-day-exploring-southern-laos",
    "6.1": "6-day-journey-to-the-heart-of-southern-laos",
}

FEATURED_HINTS = {
    "1.1": ["Tad Yuang waterfall"],
    "1.2": ["Vatphou temple", "Watphou temple", "Vat Phu"],
    "1.3": ["Liphi with sunset"],
    "1.4": ["Phou Salao"],
    "1.5": ["Trekking in Bolaven"],
    "1.6": ["Cycling in Champasak.jpg", "Cycling in Champasak 1"],
    "1.7": ["Tad Khamued waterfall"],
    "2.1": ["Tad Fane"],
    "2.2": ["Liphi with sunset"],
    "2.3": ["Vatphou temple"],
    "3.1": ["4000 islands.jpg", "4000 islands Laos"],
    "3.2": ["Tad Fane"],
    "4.1": ["Tad Fane"],
    "5.1": ["Liphi with sunset"],
    "6.1": ["Vatphou temple"],
}

HERO_ORDER = [
    "Tad Fan Hero.jpg",
    "Sunrise and fisherman on the mekong hero.jpg",
    "Vat Phou Temple hero.jpg",
    "Khone Phapheng Waterfall  hero.jpg",
    "Don Khon Champasak Laos hero.jpg",
    "Phou Salao Pakse Champasak hero.jpg",
    "Tad Yuang Hero2.jpg",
    "Wat Phou Temple Hero.jpg",
    "Temple Buddha in Khong-island hero.jpg",
    "Sunrise and fisherman on the mekong hero 2.jpg",
    "Phou Salao Pakse Champasak hero (2).jpg",
]

HERO_ALTS = [
    "Tad Fane waterfall on the Bolaven Plateau",
    "Sunrise and a fisherman on the Mekong River",
    "Vat Phou temple near Champasak",
    "Khone Phapheng waterfall",
    "Don Khon in Champasak",
    "Phou Salao viewpoint above Pakse",
    "Tad Yuang waterfall",
    "Wat Phou temple",
    "Temple Buddha on Khong Island",
    "Fishermen at sunrise on the Mekong River",
    "Phou Salao mountain above Pakse",
]

DEST_COPIES = {
    "bolaven.jpg": ("1.1", "Tad Yuang waterfall"),
    "liphi.jpg": ("1.3", "Liphi with sunset"),
    "vatphou.jpg": ("1.2", "Vatphou temple"),
    "donkhone.jpg": ("1.4", "Phou Salao"),
}

IMAGE_EXT = {".jpg", ".jpeg", ".png", ".webp", ".bmp"}
MAX_TOUR = 1600
MAX_HERO = 1920
QUALITY = 78
MAX_GALLERY = 10


def slugify(name: str) -> str:
    stem = Path(name).stem
    stem = re.sub(r"[^\w\s-]", "", stem, flags=re.UNICODE)
    stem = re.sub(r"[-\s]+", "-", stem).strip("-").lower()
    return stem or "image"


def file_hash(path: Path) -> str:
    h = hashlib.md5()
    with path.open("rb") as fh:
        for chunk in iter(lambda: fh.read(1024 * 1024), b""):
            h.update(chunk)
    return h.hexdigest()


def unique_images(files: list[Path]) -> list[Path]:
    seen: set[str] = set()
    out: list[Path] = []
    for path in files:
        digest = file_hash(path)
        if digest in seen:
            continue
        seen.add(digest)
        out.append(path)
    return out


def collect_images(folder: Path) -> list[Path]:
    files: list[Path] = []
    for path in folder.iterdir():
        if not path.is_file():
            continue
        if path.suffix.lower() not in IMAGE_EXT:
            continue
        files.append(path)
    files.sort(key=lambda p: p.name.lower())
    return unique_images(files)


def pick_featured(files: list[Path], hints: list[str]) -> Path:
    lowered = {p: p.name.lower() for p in files}
    for hint in hints:
        h = hint.lower()
        for path, name in lowered.items():
            if name == h:
                return path
        for path, name in lowered.items():
            if h.replace(".jpg", "") in name:
                return path
    return min(files, key=lambda p: p.name.lower())


def open_rgb(path: Path) -> Image.Image:
    im = Image.open(path)
    im = ImageOps.exif_transpose(im)
    if im.mode in ("RGBA", "LA") or (im.mode == "P" and "transparency" in im.info):
        rgba = im.convert("RGBA")
        bg = Image.new("RGB", rgba.size, (255, 255, 255))
        bg.paste(rgba, mask=rgba.split()[-1])
        return bg
    if im.mode != "RGB":
        return im.convert("RGB")
    return im


def save_jpeg(im: Image.Image, dest: Path, max_edge: int) -> tuple[int, int]:
    w, h = im.size
    longest = max(w, h)
    if longest > max_edge:
        scale = max_edge / float(longest)
        im = im.resize((max(1, int(w * scale)), max(1, int(h * scale))), Image.Resampling.LANCZOS)
    dest.parent.mkdir(parents=True, exist_ok=True)
    im.save(dest, format="JPEG", quality=QUALITY, optimize=True, progressive=True)
    return im.size


def process_one(src: Path, dest: Path, max_edge: int) -> dict:
    with open_rgb(src) as im:
        width, height = save_jpeg(im, dest, max_edge)
    return {
        "src": src.name,
        "file": dest.name,
        "width": width,
        "height": height,
        "bytes": dest.stat().st_size,
        "alt": Path(src.name).stem,
    }


def find_code_folders() -> dict[str, Path]:
    found: dict[str, Path] = {}
    for path in NANG.rglob("*"):
        if not path.is_dir():
            continue
        match = re.match(r"^(\d+\.\d+)\b", path.name)
        if not match:
            continue
        code = match.group(1)
        if code in CODE_TO_SLUG:
            found[code] = path
    return found


def process_hero() -> list[dict]:
    if HERO_OUT.exists():
        shutil.rmtree(HERO_OUT)
    HERO_OUT.mkdir(parents=True, exist_ok=True)
    jobs = []
    for i, name in enumerate(HERO_ORDER, start=1):
        src = HERO_SRC / name
        if not src.is_file():
            raise FileNotFoundError(src)
        dest = HERO_OUT / f"{i:02d}-{slugify(name)}.jpg"
        jobs.append((src, dest, HERO_ALTS[i - 1]))
    rows = []
    with ThreadPoolExecutor(max_workers=6) as pool:
        futs = {
            pool.submit(process_one, src, dest, MAX_HERO): (src, dest, alt)
            for src, dest, alt in jobs
        }
        for fut in as_completed(futs):
            src, dest, alt = futs[fut]
            info = fut.result()
            info["alt"] = alt
            info["order"] = int(dest.name[:2])
            rows.append(info)
    rows.sort(key=lambda r: r["order"])
    return rows


def process_tours(folders: dict[str, Path]) -> dict[str, dict]:
    if TOURS_OUT.exists():
        shutil.rmtree(TOURS_OUT)
    jobs: list[tuple[str, str, Path, Path, int]] = []
    plan: dict[str, dict] = {}
    for code, slug in CODE_TO_SLUG.items():
        folder = folders.get(code)
        if folder is None:
            raise FileNotFoundError(f"Missing folder for package {code}")
        files = collect_images(folder)
        if not files:
            raise FileNotFoundError(f"No images in {folder}")
        featured_src = pick_featured(files, FEATURED_HINTS.get(code, []))
        gallery_src = [p for p in files if p != featured_src][:MAX_GALLERY]
        dest_dir = TOURS_OUT / slug
        dest_dir.mkdir(parents=True, exist_ok=True)
        featured_dest = dest_dir / "featured.jpg"
        jobs.append((code, slug, featured_src, featured_dest, 0))
        gallery_files = []
        for i, src in enumerate(gallery_src, start=1):
            dest = dest_dir / f"g{i:02d}.jpg"
            jobs.append((code, slug, src, dest, i))
            gallery_files.append(dest.name)
        plan[slug] = {
            "code": code,
            "folder": folder.name,
            "featured": "featured.jpg",
            "featured_src": featured_src.name,
            "gallery": gallery_files,
        }
    results: dict[tuple[str, str], dict] = {}
    with ThreadPoolExecutor(max_workers=6) as pool:
        futs = {
            pool.submit(process_one, src, dest, MAX_TOUR): (code, slug, dest.name, src.name)
            for code, slug, src, dest, _order in jobs
        }
        for fut in as_completed(futs):
            code, slug, dest_name, src_name = futs[fut]
            info = fut.result()
            results[(slug, dest_name)] = info
    for slug, row in plan.items():
        feat = results[(slug, "featured.jpg")]
        row["featured_alt"] = Path(row["featured_src"]).stem
        row["featured_bytes"] = feat["bytes"]
        row["gallery_meta"] = []
        for name in row["gallery"]:
            meta = results[(slug, name)]
            row["gallery_meta"].append(
                {
                    "file": name,
                    "src": meta["src"],
                    "alt": Path(meta["src"]).stem,
                    "bytes": meta["bytes"],
                }
            )
    return plan


def copy_destinations(folders: dict[str, Path]) -> dict[str, str]:
    copied = {}
    for dest_name, (code, hint) in DEST_COPIES.items():
        folder = folders[code]
        files = collect_images(folder)
        src = pick_featured(files, [hint])
        process_one(src, THEME_IMG / dest_name, MAX_TOUR)
        copied[dest_name] = f"{code}/{src.name}"
    return copied


def main() -> None:
    if not NANG.is_dir():
        raise SystemExit(f"Missing Nang folder: {NANG}")
    if not HERO_SRC.is_dir():
        raise SystemExit(f"Missing hero folder: {HERO_SRC}")
    folders = find_code_folders()
    missing = [c for c in CODE_TO_SLUG if c not in folders]
    if missing:
        raise SystemExit(f"Missing package folders: {missing}")
    hero = process_hero()
    tours = process_tours(folders)
    dests = copy_destinations(folders)
    manifest = {
        "version": "1.7.0",
        "skip_home_hero_folder": "1.Home (Hero pic)",
        "home_hero": [
            {"file": r["file"], "alt": r["alt"], "bytes": r["bytes"]} for r in hero
        ],
        "tours": tours,
        "destinations": dests,
    }
    MANIFEST.parent.mkdir(parents=True, exist_ok=True)
    MANIFEST.write_text(json.dumps(manifest, indent=2, ensure_ascii=True) + "\n", encoding="utf-8")
    hero_bytes = sum(r["bytes"] for r in hero)
    tour_bytes = sum(
        Path(TOURS_OUT / slug / "featured.jpg").stat().st_size
        + sum((TOURS_OUT / slug / g).stat().st_size for g in row["gallery"])
        for slug, row in tours.items()
    )
    print(f"hero {len(hero)} files {hero_bytes} bytes")
    print(f"tours {len(tours)} packages {tour_bytes} bytes")
    print(f"dest {dests}")
    print(f"manifest {MANIFEST}")


if __name__ == "__main__":
    main()
