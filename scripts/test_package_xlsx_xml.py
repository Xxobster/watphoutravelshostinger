"""Parse the example workbook the same way the PHP reader does."""
from __future__ import annotations

import re
import zipfile
from pathlib import Path
from xml.etree import ElementTree as ET

ROOT = Path(__file__).resolve().parents[1]
XLSX = ROOT / "wp-content" / "plugins" / "watphou-core" / "data" / "watphou-package-import-example.xlsx"


def strip_ns(xml: str) -> str:
    xml = re.sub(r'xmlns(?::[A-Za-z0-9]+)?="[^"]*"', "", xml)
    xml = re.sub(r'\s[a-zA-Z0-9]+:[a-zA-Z0-9]+="[^"]*"', "", xml)
    return xml


def col_index(ref: str) -> int:
    letters = re.sub(r"[^A-Z]", "", ref.upper())
    n = 0
    for ch in letters:
        n = n * 26 + (ord(ch) - 64)
    return max(1, n)


def main() -> None:
    z = zipfile.ZipFile(XLSX)
    ss_xml = z.read("xl/sharedStrings.xml").decode("utf-8") if "xl/sharedStrings.xml" in z.namelist() else ""
    strings: list[str] = []
    if ss_xml:
        root = ET.fromstring(strip_ns(ss_xml))
        for si in root.findall("si"):
            strings.append("".join((t.text or "") for t in si.iter("t")))

    wb = z.read("xl/workbook.xml").decode("utf-8")
    rels = z.read("xl/_rels/workbook.xml.rels").decode("utf-8")
    rid_to: dict[str, str] = {}
    for m in re.finditer(r'Id="([^"]+)"[^>]*Target="([^"]+)"', rels):
        rid_to[m.group(1)] = m.group(2).lstrip("/")
    for m in re.finditer(r'Target="([^"]+)"[^>]*Id="([^"]+)"', rels):
        rid_to[m.group(2)] = m.group(1).lstrip("/")

    sheets: dict[str, str] = {}
    for tag in re.findall(r"<sheet\b[^>]*>", wb):
        name_m = re.search(r'name="([^"]+)"', tag)
        rid_m = re.search(r'r:id="([^"]+)"', tag, re.I)
        if not name_m or not rid_m:
            raise SystemExit(f"bad sheet tag: {tag}")
        target = rid_to[rid_m.group(1)]
        if not target.startswith("xl/"):
            target = "xl/" + target
        sheets[name_m.group(1)] = target

    assert "Tours" in sheets, sheets
    assert "Itinerary" in sheets, sheets

    xml = strip_ns(z.read(sheets["Tours"]).decode("utf-8"))
    sheet = ET.fromstring(xml)
    rows: list[dict[int, str]] = []
    for row in sheet.find("sheetData").findall("row"):
        cells: dict[int, str] = {}
        for c in row.findall("c"):
            ref = c.get("r") or ""
            t = c.get("t")
            v = c.find("v")
            inline = c.find("is")
            if t == "s" and v is not None:
                val = strings[int(v.text)]
            elif inline is not None:
                val = "".join((node.text or "") for node in inline.iter("t"))
            elif v is not None:
                val = v.text or ""
            else:
                val = ""
            cells[col_index(ref)] = val
        rows.append(cells)

    headers = rows[0]
    assert headers[1] == "slug"
    assert rows[1][1] == "3-day-classic-experience-in-southern-laos"
    assert "Classic Experience" in rows[1][2]
    assert "Vat Phou" in rows[1][11]
    assert "\n" in rows[1][12]
    print("xml-reader OK")
    print("sheets", list(sheets))
    print("slug", rows[1][1])


if __name__ == "__main__":
    main()
