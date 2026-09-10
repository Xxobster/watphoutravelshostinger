#!/usr/bin/env python3
"""Build EN / FR / TH review pack (Excel + PDF). Auto-translate for review only — do not publish Thai to the live site until a human signs off."""

from __future__ import annotations

import hashlib
import json
import re
import time
from pathlib import Path

from deep_translator import GoogleTranslator, MyMemoryTranslator
from openpyxl import Workbook
from openpyxl.styles import Alignment, Font, PatternFill, Border, Side
from openpyxl.utils import get_column_letter
from openpyxl.worksheet.datavalidation import DataValidation
from reportlab.lib import colors
from reportlab.lib.pagesizes import A4, landscape
from reportlab.lib.styles import ParagraphStyle, getSampleStyleSheet
from reportlab.lib.units import mm
from reportlab.pdfbase import pdfmetrics
from reportlab.pdfbase.ttfonts import TTFont
from reportlab.platypus import (
    KeepTogether,
    PageBreak,
    Paragraph,
    SimpleDocTemplate,
    Spacer,
    Table,
    TableStyle,
)

ROOT = Path(__file__).resolve().parents[1]
OUT_DIR = ROOT / "docs" / "translations"
CACHE = ROOT / "content" / "translations" / "auto_cache.json"
PACKAGES = ROOT / "content" / "packages"

PROTECT = sorted(
    [
        "Watphou Travels",
        "WhatsApp",
        "UNESCO",
        "Banque Pour Le Commerce Exterieur Lao",
        "BCEL",
        "Google Search Console",
        "Google Analytics 4",
        "Street N°5, Ban Vat Luang",
        "Ban Vat Luang",
        "sales.watphoutravel@gmail.com",
        "watphoutravel.of@gmail.com",
        "+85620 9949 5858",
        "Ban Kok Phung Tai",
        "Ban Hang Khone",
        "Ban Nakasang",
        "Ban Kandone",
        "Khone Phapheng",
        "Khonepasoi",
        "Tad Champee",
        "Tad Yuang",
        "Tad Fane",
        "Tad Lo",
        "Lak 40 Café",
        "Lak 40",
        "Mr. Vieng",
        "Jhai Coffee House",
        "Thateng",
        "Dan Sinxai",
        "Dan Yai",
        "Don Khone",
        "Don Det",
        "Don Daeng",
        "Si Phan Don",
        "4000 Islands",
        "4,000 Islands",
        "Vat Phou",
        "Vat Phu",
        "Champasak",
        "Bolaven Plateau",
        "Bolaven",
        "Pakse",
        "Li Phi",
        "Liphi",
        "Mekong",
        "Angkor Wat",
        "Ubon",
        "Ngae",
        "Katu",
        "Baci",
        "From $XX",
        "$XX",
    ],
    key=len,
    reverse=True,
)

UI_STRINGS = [
    ("ui", "header", "Southern Laos"),
    ("ui", "header", "Home"),
    ("ui", "header", "Day Tours"),
    ("ui", "header", "Tour Packages"),
    ("ui", "header", "2-Day Tours"),
    ("ui", "header", "3-Day Tours"),
    ("ui", "header", "4–6 Day Tours"),
    ("ui", "header", "View all tours"),
    ("ui", "header", "Destinations"),
    ("ui", "header", "Tailor-made"),
    ("ui", "header", "About Us"),
    ("ui", "header", "About us"),
    ("ui", "header", "Why travel with us"),
    ("ui", "header", "Reviews"),
    ("ui", "header", "Contact"),
    ("ui", "header", "Customize trip"),
    ("ui", "header", "Search"),
    ("ui", "home", "Discover Southern Laos Your Way"),
    ("ui", "home", "Your private journeys with a local Pakse team and European standards"),
    ("ui", "home", "Get Started Today"),
    ("ui", "home", "Explore Southern Laos like Never Before"),
    ("ui", "home", "Welcome to Southern Laos, a captivating region where the mighty Mekong River carves its path through lush landscapes, ancient histories whisper from forgotten temples, and life unfolds at an incredibly gentle pace."),
    ("ui", "home", "Far from the bustling crowds, this enchanting part of Laos offers a truly authentic Southeast Asian experience. Discover Pakse, the Bolaven Plateau with its waterfalls and coffee farms, the UNESCO-listed Vat Phou temple, and the peaceful 4000 Islands."),
    ("ui", "home", "As a local agency based in Pakse, Watphou Travels is uniquely positioned to help you uncover hidden gems. Whether you seek a one-day waterfall escape or a week-long private journey, every itinerary is 100% private and crafted around you."),
    ("ui", "home", "Tailored Tours for Your Interest"),
    ("ui", "home", "from"),
    ("ui", "home", "/Person"),
    ("ui", "home", "Explore"),
    ("ui", "home", "Top Adventures Selected for You"),
    ("ui", "home", "These handpicked tours promise you unforgettable memories."),
    ("ui", "home", "Flexible"),
    ("ui", "home", "Travel Routes"),
    ("ui", "home", "Private departure from Pakse — flexible itinerary"),
    ("ui", "home", "View tour"),
    ("ui", "home", "The Most Popular Destinations in Southern Laos"),
    ("ui", "home", "Here are just a few trip ideas to get you started!"),
    ("ui", "home", "Enjoy Amazing Holidays in 5 Easy Steps"),
    ("ui", "home", "A seamless journey from planning to adventure."),
    ("ui", "home", "Choose Your Tour"),
    ("ui", "home", "Browse our day tours and multi-day packages across Southern Laos."),
    ("ui", "home", "Customize Your Itinerary"),
    ("ui", "home", "Tell us your dates, pace, and interests — every tour is 100% private."),
    ("ui", "home", "Confirm Your Booking"),
    ("ui", "home", "We send a clear quote. Prices stay as From $XX until confirmed."),
    ("ui", "home", "Prepare for Your Adventure"),
    ("ui", "home", "Receive practical tips, meeting points, and what to pack."),
    ("ui", "home", "Enjoy Your Tour"),
    ("ui", "home", "Travel with a local driver-guide from Pakse and create lasting memories."),
    ("ui", "home", "Why Us!"),
    ("ui", "home", "Local expertise + European standards"),
    ("ui", "home", "Lao team based in Pakse with professional service standards."),
    ("ui", "home", "100% private tours"),
    ("ui", "home", "Your own vehicle and driver — no shared groups."),
    ("ui", "home", "Authentic experiences"),
    ("ui", "home", "Real villages, coffee plantations, waterfalls, and hidden corners."),
    ("ui", "home", "Transparent & flexible"),
    ("ui", "home", "Clear starting prices with easy comfort upgrades."),
    ("ui", "home", "Hear from Our Happy Travelers"),
    ("ui", "home", "Genuine Google reviews will appear here once imported. We do not display invented reviews."),
    ("ui", "home", "Read more reviews on Google"),
    ("ui", "home", "Ready to plan your Southern Laos journey?"),
    ("ui", "home", "WhatsApp us now"),
    ("ui", "footer", "Your local expert in Southern Laos — private tours with European standards."),
    ("ui", "footer", "All tours"),
    ("ui", "footer", "Tailor-made tours"),
    ("ui", "footer", "Travel guide"),
    ("ui", "footer", "Privacy"),
    ("ui", "footer", "Terms"),
    ("ui", "footer", "Cancellation"),
    ("ui", "tour", "Request this tour"),
    ("ui", "tour", "Related tours"),
    ("ui", "tour", "View Details →"),
    ("ui", "misc", "No tours found."),
    ("ui", "misc", "No results."),
    ("ui", "banner", "French translation is in progress. This page is currently in English."),
    ("ui", "banner", "Thai translation is in progress. This page is currently in English. We do not use machine translation."),
    ("ui", "price", "From $XX per person — Standard accommodation. Comfort upgrades available"),
]

PAGES = [
    ("page", "privacy-policy", "Privacy Policy"),
    ("page", "privacy-policy", "Watphou Travels (Pakse, Laos) collects only the information you send through our enquiry form, email, or WhatsApp so we can answer your tour request."),
    ("page", "privacy-policy", "Typical fields: name, email, phone, travel dates, group size, and your message. We do not sell this information. We do not invent or publish traveller reviews."),
    ("page", "privacy-policy", "This page will be reviewed with a lawyer before the public domain launch if you need extra clauses (cookies, analytics identifiers)."),
    ("page", "terms", "Terms of Use"),
    ("page", "terms", "Watphou Travels offers 100% private tours in Southern Laos, departing from Pakse. Information on this website describes itineraries; your confirmed quote is the booking contract."),
    ("page", "terms", "Public prices currently show as From $XX until the company confirms real rates. Do not treat placeholder prices as a payable amount."),
    ("page", "terms", "Bookings are requested by form or WhatsApp. Banque Pour Le Commerce Exterieur Lao (BCEL) online payment is not live yet."),
    ("page", "cancellation", "Cancellation"),
    ("page", "cancellation", "Cancellation and payment terms are written on your personal quote. We do not publish a generic percentage here until the company confirms the official policy."),
    ("page", "cancellation", "To change or cancel a request, write to sales.watphoutravel@gmail.com or WhatsApp +85620 9949 5858."),
    ("page", "travel-guide", "Southern Laos Travel Guide"),
    ("page", "travel-guide", "A short, factual guide to the places we operate from Pakse. Use it to choose a private day tour or a multi-day journey. We do not pad this page with invented history or prices."),
    ("page", "travel-guide", "Pakse is the gateway to Southern Laos and the office of Watphou Travels (Street N°5, Ban Vat Luang). Most private tours start here."),
    ("page", "travel-guide", "Highlands east of Pakse known for Tad Fane and Tad Yuang waterfalls and coffee farms. A classic full-day private tour from Pakse."),
    ("page", "travel-guide", "UNESCO-listed Vat Phou sits near Champasak town, south of Pakse, on the Mekong. Visit as a day trip or combined with the 4000 Islands."),
    ("page", "travel-guide", "A Mekong archipelago near the Cambodian border: Don Khone, Don Det, and the Liphi waterfalls. Often a two-day private journey from Pakse."),
    ("dest", "bolaven-plateau", "Private tours on the Bolaven Plateau from Pakse: Tad Fane and Tad Yuang waterfalls, highland coffee farms, and villages. 100% private departures with Watphou Travels."),
    ("dest", "4000-islands", "Private tours to Si Phan Don (the 4000 Islands) on the Mekong: Don Khone, Don Det, and the Liphi waterfalls, organised from Pakse."),
    ("dest", "champasak", "Private tours to UNESCO-listed Vat Phou and Champasak town, south of Pakse, with a local driver-guide."),
    ("dest", "pakse", "Pakse is our home and the starting point for private journeys in Southern Laos: riverside walks, nearby villages, and easy links to the Bolaven Plateau and Vat Phou."),
]


def protect(text: str) -> tuple[str, list[str]]:
    held: list[str] = []
    out = text
    for name in PROTECT:
        if name in out:
            token = f"⟦P{len(held)}⟧"
            out = out.replace(name, token)
            held.append(name)
    return out, held


def unprotect(text: str, held: list[str]) -> str:
    out = text
    for i, name in enumerate(held):
        out = out.replace(f"⟦P{i}⟧", name)
        out = out.replace(f"[P{i}]", name)
    return out


def collect_rows() -> list[dict]:
    rows: list[dict] = []

    def add(kind: str, loc: str, field: str, en: str) -> None:
        en = (en or "").strip()
        if not en:
            return
        rows.append({"kind": kind, "location": loc, "field": field, "en": en})

    for kind, loc, en in UI_STRINGS:
        add(kind, loc, "string", en)
    for kind, loc, en in PAGES:
        add(kind, loc, "copy", en)

    for path in sorted(PACKAGES.glob("*.json")):
        if path.name == "index.json":
            continue
        data = json.loads(path.read_text(encoding="utf-8"))
        loc = f"{data.get('code', '')} {data.get('slug', path.stem)}".strip()
        for key in ("title", "headline", "duration", "departure", "price_note", "dream", "cta"):
            add("package", loc, key, str(data.get(key) or ""))
        for key in ("highlights", "included", "excluded", "upgrades"):
            for i, item in enumerate(data.get(key) or [], start=1):
                add("package", loc, f"{key}[{i}]", str(item))
        for day in data.get("itinerary") or []:
            n = day.get("day", "?")
            add("package", loc, f"itinerary.day{n}.title", str(day.get("title") or ""))
            add("package", loc, f"itinerary.day{n}.body", str(day.get("body") or ""))
    return rows


def load_cache() -> dict:
    if CACHE.exists():
        return json.loads(CACHE.read_text(encoding="utf-8"))
    return {"fr": {}, "th": {}}


def save_cache(cache: dict) -> None:
    CACHE.parent.mkdir(parents=True, exist_ok=True)
    CACHE.write_text(json.dumps(cache, ensure_ascii=False, indent=2), encoding="utf-8")


def key_for(text: str) -> str:
    return hashlib.sha1(text.encode("utf-8")).hexdigest()


def translate_one(text: str, lang: str, google: GoogleTranslator, memory: MyMemoryTranslator) -> str:
    if len(text) <= 1:
        return text
    if re.fullmatch(r"[\d\s$Xx.,/+\-–—:·•]+", text):
        return text
    protected, held = protect(text)
    attempts = [protected]
    if len(protected) < 48:
        attempts = [f"Travel website text: {protected}", protected]
    last_err = None
    for src in attempts:
        try:
            out = google.translate(src)
            if out:
                out = re.sub(
                    r"^(Travel website text:\s*|Texte du site de voyage\s*:\s*|ข้อความเว็บไซต์ท่องเที่ยว\s*:\s*)",
                    "",
                    out,
                    flags=re.I,
                )
                return unprotect(out.strip(), held)
        except Exception as exc:
            last_err = exc
            time.sleep(0.8)
    try:
        out = memory.translate(protected)
        if out:
            return unprotect(out.strip(), held)
    except Exception as exc:
        last_err = exc
    print(f"WARN leave-EN {lang}: {text[:60]!r} ({last_err})", flush=True)
    return text


def fill_translations(rows: list[dict]) -> list[dict]:
    cache = load_cache()
    fr_google = GoogleTranslator(source="en", target="fr")
    th_google = GoogleTranslator(source="en", target="th")
    fr_mem = MyMemoryTranslator(source="english", target="french")
    th_mem = MyMemoryTranslator(source="english", target="thai")
    unique = []
    seen = set()
    for row in rows:
        k = key_for(row["en"])
        if k not in seen:
            seen.add(k)
            unique.append(row["en"])

    n = len(unique)
    for i, en in enumerate(unique, start=1):
        k = key_for(en)
        if k not in cache["fr"]:
            cache["fr"][k] = translate_one(en, "fr", fr_google, fr_mem)
            time.sleep(0.15)
        if k not in cache["th"]:
            cache["th"][k] = translate_one(en, "th", th_google, th_mem)
            time.sleep(0.15)
        if i % 25 == 0 or i == n:
            save_cache(cache)
            print(f"translated {i}/{n}", flush=True)

    for row in rows:
        k = key_for(row["en"])
        row["fr"] = cache["fr"].get(k, "")
        row["th"] = cache["th"].get(k, "")
        row["status"] = "AI draft — review"
        row["notes"] = ""
    save_cache(cache)
    return rows


def write_xlsx(rows: list[dict], path: Path) -> None:
    wb = Workbook()
    cover = wb.active
    cover.title = "HOW TO REVIEW"
    cover["A1"] = "Watphou Travels — English / French / Thai text review"
    cover["A1"].font = Font(bold=True, size=16, color="FFFFFF")
    cover["A1"].fill = PatternFill("solid", fgColor="F15A24")
    cover.merge_cells("A1:F1")
    cover["A3"] = (
        "This workbook is the editable review file. French and Thai columns are automatic drafts "
        "(Google Translate), not a professional translation. Do not publish Thai to the website until a human has approved it. "
        "Proper names (Pakse, Vat Phou, Tad Fane, Watphou Travels, $XX, WhatsApp, emails, phone) were protected where possible. "
        "Edit columns D (French), E (Thai) and F (your notes). Set column G to Approved when a row is final."
    )
    cover.merge_cells("A3:F6")
    cover["A3"].alignment = Alignment(wrap_text=True, vertical="top")
    cover["A8"] = "Google Analytics 4 (GA4) measurement ID and Google Search Console verification are still TODO — not in this file."
    cover.column_dimensions["A"].width = 28
    for col in "BCDEF":
        cover.column_dimensions[col].width = 22
    cover.row_dimensions[3].height = 80

    ws = wb.create_sheet("Texts")
    headers = ["ID", "Type", "Location", "Field", "English (source)", "French (auto draft)", "Thai (auto draft)", "Reviewer notes", "Status"]
    header_fill = PatternFill("solid", fgColor="1A1D21")
    alt = PatternFill("solid", fgColor="FFF8F3")
    wrap = Alignment(wrap_text=True, vertical="top")
    thin = Border(
        left=Side(style="thin", color="DDDDDD"),
        right=Side(style="thin", color="DDDDDD"),
        top=Side(style="thin", color="DDDDDD"),
        bottom=Side(style="thin", color="DDDDDD"),
    )
    for i, h in enumerate(headers, start=1):
        cell = ws.cell(1, i, h)
        cell.font = Font(bold=True, color="FFFFFF")
        cell.fill = header_fill
        cell.alignment = Alignment(wrap_text=True, vertical="center")
    widths = [8, 12, 42, 22, 55, 55, 55, 28, 18]
    for i, w in enumerate(widths, start=1):
        ws.column_dimensions[get_column_letter(i)].width = w
    ws.freeze_panes = "A2"
    ws.auto_filter.ref = f"A1:I{len(rows)+1}"
    dv = DataValidation(type="list", formula1='"AI draft — review,Approved,Needs rewrite"', allow_blank=True)
    ws.add_data_validation(dv)
    dv.add(f"I2:I{len(rows)+1}")

    for idx, row in enumerate(rows, start=2):
        values = [
            idx - 1,
            row["kind"],
            row["location"],
            row["field"],
            row["en"],
            row["fr"],
            row["th"],
            row["notes"],
            row["status"],
        ]
        for c, val in enumerate(values, start=1):
            cell = ws.cell(idx, c, val)
            cell.alignment = wrap
            cell.border = thin
            if idx % 2 == 0:
                cell.fill = alt
        ws.row_dimensions[idx].height = min(90, 18 + 12 * (max(len(row["en"]), len(row["fr"]), len(row["th"])) // 70))

    unlock_fill = PatternFill("solid", fgColor="E8F5E9")
    for r in range(2, len(rows) + 2):
        for col in (6, 7, 8, 9):
            ws.cell(r, col).fill = unlock_fill

    path.parent.mkdir(parents=True, exist_ok=True)
    wb.save(path)


def write_pdf(rows: list[dict], path: Path) -> None:
    font_path = Path(r"C:\Windows\Fonts\LeelawUI.ttf")
    if not font_path.exists():
        font_path = Path(r"C:\Windows\Fonts\tahoma.ttf")
    pdfmetrics.registerFont(TTFont("Leela", str(font_path)))
    styles = getSampleStyleSheet()
    cover_title = ParagraphStyle("CoverTitle", parent=styles["Title"], fontName="Leela", fontSize=18, textColor=colors.HexColor("#f15a24"), leading=22)
    body = ParagraphStyle("Body", parent=styles["Normal"], fontName="Leela", fontSize=8, leading=11)
    h = ParagraphStyle("H", parent=styles["Heading2"], fontName="Leela", fontSize=11, textColor=colors.HexColor("#1a1d21"), spaceBefore=8, spaceAfter=4)
    small = ParagraphStyle("Small", parent=body, fontSize=7, leading=9, textColor=colors.HexColor("#444444"))
    label = ParagraphStyle("Label", parent=body, fontSize=7, textColor=colors.HexColor("#f15a24"), leading=9)

    doc = SimpleDocTemplate(
        str(path),
        pagesize=landscape(A4),
        leftMargin=12 * mm,
        rightMargin=12 * mm,
        topMargin=12 * mm,
        bottomMargin=12 * mm,
        title="Watphou Travels EN / FR / TH review (auto-translated draft)",
        author="Watphou Travels",
    )
    story = []
    story.append(Paragraph("Watphou Travels — English / French / Thai review pack", cover_title))
    story.append(Spacer(1, 6))
    story.append(
        Paragraph(
            "Automatic drafts (Google Translate) for review only. Thai must be checked by a human before it goes on the public website. "
            "To edit the wording, open the Excel file with the same name (columns French, Thai, Reviewer notes, Status). "
            "This PDF is a readable snapshot. Prices stay From $XX. Do not invent reviews.",
            body,
        )
    )
    story.append(Paragraph(f"{len(rows)} text rows. Generated for internal review, 10 September 2026.", small))
    story.append(PageBreak())

    def esc(text: str) -> str:
        return (
            (text or "")
            .replace("&", "&amp;")
            .replace("<", "&lt;")
            .replace(">", "&gt;")
            .replace("\n", "<br/>")
        )

    current_loc = None
    for i, row in enumerate(rows, start=1):
        loc = f"{row['kind']} · {row['location']}"
        if loc != current_loc:
            current_loc = loc
            story.append(Paragraph(esc(loc), h))
        block = [
            Paragraph(f"#{i} · {esc(row['field'])}", label),
            Paragraph(f"<b>EN</b> — {esc(row['en'])}", body),
            Paragraph(f"<b>FR</b> — {esc(row['fr'])}", body),
            Paragraph(f"<b>TH</b> — {esc(row['th'])}", body),
            Spacer(1, 4),
        ]
        story.append(KeepTogether(block))
    doc.build(story)


def main() -> None:
    print("collecting strings…")
    rows = collect_rows()
    print(f"{len(rows)} rows")
    print("auto-translating (Google Translate)…")
    rows = fill_translations(rows)
    OUT_DIR.mkdir(parents=True, exist_ok=True)
    json_path = ROOT / "content" / "translations" / "review_en_fr_th.json"
    json_path.parent.mkdir(parents=True, exist_ok=True)
    json_path.write_text(json.dumps(rows, ensure_ascii=False, indent=2), encoding="utf-8")
    xlsx = OUT_DIR / "Watphou_EN_FR_TH_review.xlsx"
    pdf = OUT_DIR / "Watphou_EN_FR_TH_review.pdf"
    print("writing Excel…")
    write_xlsx(rows, xlsx)
    print("writing PDF…")
    write_pdf(rows, pdf)
    print("done")
    print(xlsx)
    print(pdf)
    print(json_path)


if __name__ == "__main__":
    main()
