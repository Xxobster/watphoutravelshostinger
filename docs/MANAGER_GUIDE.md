# Manager Guide — Watphou Travels

Plain-language guide for the person who updates tours (Tour Manager).

## Logging in

1. Open **https://watphoutravels.site/wp-login.php** (not the public homepage “Edit website” link — that link is removed). The Hostinger preview hostname is a fallback only; some visitor networks cannot resolve it.
2. Use the WordPress user you were given (`wptadmin` or a Tour Manager account). The password is **not** stored in git.
3. You land in WordPress admin.

Visitors never see a login link. Search engines should stay on `noindex` while this is the Hostinger temporary domain.

## Do not use Appearance → Customize

The Customizer (the left-hand panel that says “You are customizing”) **cannot** change tour prices or tour photos. It only changes site-wide theme options (logo area, extra CSS). Close it.

## Change tour text, add a tour, hide or preview (main tool)

1. Left menu → **Watphou** → **Manage tours**
2. Click a tour title or **Edit texts**. You see **English**, **French**, and **Thai** tabs with the same fields as the Excel package sheets (slug, title, headline, duration, destinations, introduction, highlights, itinerary days, included / not included, upgrades, call to action)
3. **Add new tour** creates a draft. Fill the English tab (required). Fill French and Thai when you have professional text — do not machine-translate Thai
4. **Save as draft (hide + preview first)** — visitors do not see it. Click **Preview this tour** while you are logged in
5. **Save and publish on the website** — the tour appears in the matching menu (Day Tour, 2-DAY Tours, 3-DAY Tours, 4-6 Day Tours) and destination pages
6. **Hide from website** / **Show on website** on the tour list turn a tour off or on without deleting it
7. **Delete** moves the tour (and its French/Thai copies) to the trash

You do **not** need to edit the public website or the block editor for this. **Edit text** on Quick edit tours opens this same screen.

## Change prices and photos (easiest)

1. Left menu → **Watphou** → **Quick edit tours**
2. You see every English tour in one table (published and drafts): top photo, bottom photos, price, duration, homepage bestseller
3. **Top photo:** click **Change top photo** → pick or upload in the Media Library → **Use this photo**. This is the large picture at the top of the tour page and on listing cards
4. **Bottom photos:** click **Change bottom photos** → select every picture you want in the Media Library (hold Ctrl on Windows or Command on Mac to pick several) → **Use these photos**. This is the picture grid under the itinerary. **Remove all** clears that grid. The first time you open this page, WordPress may copy the current bottom photos into the Media Library — refresh if you see a notice
5. **Price:** type a number (for example `95`) in **From $ (USD)**. Until real prices are confirmed, leave `XX` (the site shows “From $XX”)
6. Click **Save all changes** at the top or bottom. Photos also apply to the French and Thai copies of the same tour

You can also open **Tours → All Tours** and click the blue **Quick edit prices & photos** button.

## Add or update a tour with Excel (easiest for new packages)

1. Left menu → **Watphou** → **Add tours (Excel)**
2. Click **Download example Excel (3-Day Classic)** — it is filled with the live *3-Day Classic Experience in Southern Laos* package so you can copy the layout
3. To **update** that tour: keep the same `slug`, change the text, save as `.xlsx`
4. To **add** a new tour: copy the example row, give it a **new** `slug` (lowercase, hyphens, for example `bolaven-sunset-day-tour`) and a new title. Add one row per day on the **Itinerary** sheet using the same slug
5. French text goes on the **FR** sheet. Thai text goes on the **TH** sheet. Keep the **same slug**. Hover the yellow header cells (or open **Column meanings**) to read what `slug`, `cta` (Call To Action), and the other columns mean
6. Leave `price_from` as `XX` until the real price is confirmed
7. Upload the file on the same WordPress page and click **Upload and save tours**
8. Then open **Quick edit tours** and click **Change top photo** / **Change bottom photos** — the Excel file does not upload pictures

Allowed destination names (comma-separated): `bolaven-plateau`, `4000-islands`, `champasak`, `pakse`. Duration menu: `1-day`, `2-day`, `3-day`, `4-6-day`.

## Change the long text in the editor (optional)

Prefer **Watphou → Manage tours**. The old **Tours → All Tours** list still exists; opening a tour there sends you to Manage tours so the block editor is not required.

## Contact details (phone, email, address)

**Settings → Watphou Travels** (administrators only). The homepage Google reviews can refresh automatically if the webmaster pastes a Google Places Application Programming Interface key there (or sets `WATPHOU_GOOGLE_PLACES_KEY` in `wp-config.php`). The site then shows the newest 4–5 star quotes, most recent on the left. Do not invent reviews.

## Booking requests

1. **Bookings** in the left menu
2. Open a reference → notes → status buttons

## Languages

English is the default public language (no `/en/` in the address). French (`/fr/`) and Thai (`/th/`) use the reviewed texts from `docs/translations/Watphou_EN_FR_TH_reviewed.xlsx`. Later changes can go in that Excel file again, or on the **FR** / **TH** sheets of the tour import workbook.

To replace a language later: edit the **French** or **Thai** tab in **Manage tours**, or edit the **FR** / **TH** sheet and upload the Excel file.

## What you cannot do

- Install plugins or themes
- Edit code
- Create administrator users

## Need help?

Contact your webmaster or email sales.watphoutravel@gmail.com
