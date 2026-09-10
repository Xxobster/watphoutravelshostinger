# Watphou Travels — English / French / Thai review pack

**Status:** automatic drafts for **review only**. Not published on the website.

| File | Use |
|------|-----|
| [Watphou_EN_FR_TH_review.xlsx](Watphou_EN_FR_TH_review.xlsx) | **Edit this.** Columns: English (source), French (auto draft), Thai (auto draft), Reviewer notes, Status (dropdown). |
| [Watphou_EN_FR_TH_review.pdf](Watphou_EN_FR_TH_review.pdf) | Readable snapshot of the same rows (English, then French, then Thai). |

Machine source: Google Translate (MyMemory as fallback), with place names and `$XX` protected. Thai is **not** a professional translation. Do not put it on the public site until a human has approved the row (set Status to `Approved` in Excel).

Rebuild: `python scripts/build_translation_review.py` (needs `deep-translator`, `openpyxl`, `reportlab`).

JSON source of truth: `content/translations/review_en_fr_th.json`.
