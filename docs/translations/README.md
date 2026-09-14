# Watphou Travels — English / French / Thai review pack

**Status:** the reviewed workbook is the live source for `/fr/` and `/th/` (plugin `watphou-core` 1.5.0).

| File | Use |
|------|-----|
| [Watphou_EN_FR_TH_reviewed.xlsx](Watphou_EN_FR_TH_reviewed.xlsx) | **Current public French and Thai.** Replace this file and re-export when wording changes. |
| [Watphou_EN_FR_TH_review.xlsx](Watphou_EN_FR_TH_review.xlsx) | Older automatic-draft pack. Do not publish this one. |
| [Watphou_EN_FR_TH_review.pdf](Watphou_EN_FR_TH_review.pdf) | Readable snapshot of the older automatic drafts. |

Export into WordPress: `python scripts/export_reviewed_translations.py` (writes `content/translations/reviewed_en_fr_th.json` and `wp-content/plugins/watphou-core/data/reviewed_en_fr_th.json`). The plugin applies French and Thai on the next few page loads after the JSON hash changes.

Tour import example (English + **FR** + **TH** sheets): `python scripts/build_package_import_example.py`.
