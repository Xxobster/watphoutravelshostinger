#!/usr/bin/env python3
"""Inventory Wix backup ZIP: hash assets, quarantine sensitive files, generate manifest."""

from __future__ import annotations

import csv
import hashlib
import re
import shutil
import zipfile
from collections import defaultdict
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
ZIP_PATH = ROOT / "bkp" / "site files - 1657fdf4-40dc-40b1-99f2-ed231b1446ae.zip"
EXTRACT_DIR = ROOT / "work" / "backup_extract"
QUARANTINE_DIR = ROOT / "work" / "quarantine"
MANIFEST_PATH = ROOT / "content" / "media_manifest.csv"
INVENTORY_MD = ROOT / "docs" / "BACKUP_INVENTORY.md"
SENSITIVE_MD = ROOT / "docs" / "project_memory" / "SENSITIVE_ASSETS.md"

SENSITIVE_PATTERNS = [
    re.compile(r"(?i)^invoice"),
    re.compile(r"(?i)^deposit\s*invoice"),
    re.compile(r"(?i)^rest\s*payment"),
    re.compile(r"(?i)^payment\s*group"),
    re.compile(r"(?i)bank\s*account"),
    re.compile(r"(?i)wpt\s*bank\s*account"),
]

IMAGE_EXT = {".jpg", ".jpeg", ".png", ".gif", ".webp", ".avif"}
VIDEO_EXT = {".mp4", ".mov", ".avi", ".mkv"}
DOC_EXT = {".pdf", ".doc", ".docx", ".xlsx"}


def sha256_bytes(data: bytes) -> str:
    return hashlib.sha256(data).hexdigest()


def is_sensitive(name: str) -> bool:
    base = Path(name).name
    return any(p.search(base) for p in SENSITIVE_PATTERNS)


def file_category(name: str) -> str:
    ext = Path(name).suffix.lower()
    if ext in IMAGE_EXT or (not ext and not name.endswith("/")):
        return "image"
    if ext in VIDEO_EXT:
        return "video"
    if ext in DOC_EXT:
        return "document"
    if ext == ".zip":
        return "archive"
    return "other"


def main() -> None:
    if not ZIP_PATH.exists():
        raise SystemExit(f"Backup ZIP not found: {ZIP_PATH}")

    EXTRACT_DIR.mkdir(parents=True, exist_ok=True)
    QUARANTINE_DIR.mkdir(parents=True, exist_ok=True)
    MANIFEST_PATH.parent.mkdir(parents=True, exist_ok=True)

    entries: list[dict] = []
    hash_to_paths: dict[str, list[str]] = defaultdict(list)
    ext_counts: dict[str, int] = defaultdict(int)
    ext_bytes: dict[str, int] = defaultdict(int)
    sensitive_rows: list[dict] = []
    quarantined = 0

    with zipfile.ZipFile(ZIP_PATH, "r") as zf:
        for info in zf.infolist():
            if info.is_dir():
                continue
            name = info.filename
            data = zf.read(info)
            digest = sha256_bytes(data)
            cat = file_category(name)
            ext = Path(name).suffix.lower() or "(none)"
            ext_counts[ext] += 1
            ext_bytes[ext] += len(data)
            hash_to_paths[digest].append(name)

            rel_extract = EXTRACT_DIR / name
            rel_extract.parent.mkdir(parents=True, exist_ok=True)

            sensitive = is_sensitive(name)
            if sensitive:
                qpath = QUARANTINE_DIR / Path(name).name
                # Handle duplicate basenames in quarantine
                if qpath.exists():
                    qpath = QUARANTINE_DIR / f"{digest[:8]}_{Path(name).name}"
                qpath.write_bytes(data)
                quarantined += 1
                sensitive_rows.append(
                    {"file": name, "reason": "invoice/bank/customer PII", "sha256": digest}
                )
                publish = "quarantine"
            else:
                rel_extract.write_bytes(data)
                publish = "yes" if cat in ("image", "video", "document") else "review"

            entries.append(
                {
                    "backup_path": name,
                    "category": cat,
                    "extension": ext,
                    "size_bytes": len(data),
                    "sha256": digest,
                    "sensitive": "yes" if sensitive else "no",
                    "publishable": publish,
                    "duplicate_of": "",
                    "target_tour": "",
                    "alt_text": "",
                }
            )

    # Mark duplicates (keep first path as canonical)
    seen_hash: dict[str, str] = {}
    for row in entries:
        h = row["sha256"]
        if h in seen_hash and row["sensitive"] == "no":
            row["duplicate_of"] = seen_hash[h]
        elif row["sensitive"] == "no":
            seen_hash[h] = row["backup_path"]

    dup_groups = sum(1 for paths in hash_to_paths.values() if len(paths) > 1)

    with MANIFEST_PATH.open("w", newline="", encoding="utf-8") as f:
        writer = csv.DictWriter(
            f,
            fieldnames=[
                "backup_path",
                "category",
                "extension",
                "size_bytes",
                "sha256",
                "sensitive",
                "publishable",
                "duplicate_of",
                "target_tour",
                "alt_text",
            ],
        )
        writer.writeheader()
        writer.writerows(entries)

    total_size = sum(r["size_bytes"] for r in entries)
    images = sum(1 for r in entries if r["category"] == "image")
    videos = sum(1 for r in entries if r["category"] == "video")
    docs = sum(1 for r in entries if r["category"] == "document")

    md_lines = [
        "# Backup Inventory — Watphou Travels",
        "",
        f"**Source:** `{ZIP_PATH.name}` (read-only, not modified)",
        f"**Generated:** inventory_backup.py",
        "",
        "## Summary",
        "",
        f"| Metric | Value |",
        f"|--------|-------|",
        f"| Total entries | {len(entries)} |",
        f"| Total uncompressed | {total_size / (1024**3):.2f} GB |",
        f"| Images | {images} |",
        f"| Videos | {videos} |",
        f"| Documents (PDF etc.) | {docs} |",
        f"| Duplicate hash groups | {dup_groups} |",
        f"| Quarantined sensitive files | {quarantined} |",
        "",
        "## By extension",
        "",
        "| Extension | Count | Size (MB) |",
        "|-----------|-------|-----------|",
    ]
    for ext, count in sorted(ext_counts.items(), key=lambda x: -x[1])[:20]:
        md_lines.append(f"| `{ext}` | {count} | {ext_bytes[ext] / (1024**2):.1f} |")

    md_lines.extend(
        [
            "",
            "## Content type",
            "",
            "This ZIP is a **Wix media-library export only**. It contains no HTML, JSON, or page content.",
            "Tour text must be scraped from the live site. Itinerary PDFs (non-invoice) are supplementary sources.",
            "",
            "## Useful tour PDFs (non-sensitive)",
            "",
        ]
    )
    for row in sorted(entries, key=lambda r: r["backup_path"]):
        if row["category"] == "document" and row["sensitive"] == "no":
            if any(
                k in row["backup_path"].lower()
                for k in ("bolaven", "vatphou", "vat phou", "4000", "island", "programme", "tour", "day")
            ):
                md_lines.append(f"- `{row['backup_path']}` ({row['size_bytes'] // 1024} KB)")

    md_lines.extend(
        [
            "",
            "## Quarantined files",
            "",
            f"See `{SENSITIVE_MD}` and `{QUARANTINE_DIR}` ({quarantined} files).",
            "",
            f"Full manifest: `{MANIFEST_PATH.relative_to(ROOT)}`",
        ]
    )
    INVENTORY_MD.write_text("\n".join(md_lines), encoding="utf-8")

    # Update SENSITIVE_ASSETS.md table
    sens_lines = [
        "# Sensitive Assets — Quarantine List",
        "",
        "Populated by `scripts/inventory_backup.py`. **Never publish or commit these files.**",
        "",
        "| File | Reason | SHA256 |",
        "|------|--------|--------|",
    ]
    for s in sensitive_rows:
        sens_lines.append(f"| `{s['file']}` | {s['reason']} | `{s['sha256'][:16]}…` |")
    SENSITIVE_MD.write_text("\n".join(sens_lines), encoding="utf-8")

    print(f"Inventory complete: {len(entries)} files, {quarantined} quarantined")
    print(f"Wrote {INVENTORY_MD}")
    print(f"Wrote {MANIFEST_PATH}")


if __name__ == "__main__":
    main()
