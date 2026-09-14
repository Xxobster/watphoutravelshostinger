# Manager Guide — Watphou Travels

Plain-language guide for the person who updates tours (Tour Manager).

## Logging in

1. Open **https://watphoutravels.site/wp-login.php** (not the public homepage “Edit website” link — that link is removed). The Hostinger preview hostname is a fallback only; some visitor networks cannot resolve it.
2. Use the WordPress user you were given (`wptadmin` or a Tour Manager account). The password is **not** stored in git.
3. You land in WordPress admin.

Visitors never see a login link. Search engines should stay on `noindex` while this is the Hostinger temporary domain.

## Do not use Appearance → Customize

The Customizer (the left-hand panel that says “You are customizing”) **cannot** change tour prices or tour photos. It only changes site-wide theme options (logo area, extra CSS). Close it.

## Change prices and photos (easiest)

1. Left menu → **Watphou** → **Quick edit tours**
2. You see every published English tour in one table: photo, price, duration, homepage bestseller
3. **Photo:** click **Change photo** → pick or upload in the Media Library → **Use this photo**
4. **Price:** type a number (for example `95`) in **From $ (USD)**. Until real prices are confirmed, leave `XX` (the site shows “From $XX”)
5. Click **Save all changes** at the top or bottom

You can also open **Tours → All Tours** and click the blue **Quick edit prices & photos** button.

## Add or update a tour with Excel (easiest for new packages)

1. Left menu → **Watphou** → **Add tours (Excel)**
2. Click **Download example Excel (3-Day Classic)** — it is filled with the live *3-Day Classic Experience in Southern Laos* package so you can copy the layout
3. To **update** that tour: keep the same `slug`, change the text, save as `.xlsx`
4. To **add** a new tour: copy the example row, give it a **new** `slug` (lowercase, hyphens, for example `bolaven-sunset-day-tour`) and a new title. Add one row per day on the **Itinerary** sheet using the same slug
5. French text goes on the **FR** sheet. Thai text goes on the **TH** sheet. Keep the **same slug**. Hover the yellow header cells (or open **Column meanings**) to read what `slug`, `cta` (Call To Action), and the other columns mean
6. Leave `price_from` as `XX` until the real price is confirmed
7. Upload the file on the same WordPress page and click **Upload and save tours**
8. Then open **Quick edit tours** and click **Change photo** — the Excel file does not upload pictures

Allowed destination names (comma-separated): `bolaven-plateau`, `4000-islands`, `champasak`, `pakse`. Duration menu: `1-day`, `2-day`, `3-day`, `4-6-day`.

## Change the long text in the editor (optional)

1. **Tours → All Tours** → click the tour title
2. Edit the blocks (dream paragraph, itinerary days, included / excluded)
3. Right sidebar: **Featured image** is the main photo; **Tour Details** has price and duration if you prefer to edit them here
4. Click **Update**

## Contact details (phone, email, address)

**Settings → Watphou Travels** (administrators only).

## Booking requests

1. **Bookings** in the left menu
2. Open a reference → notes → status buttons

## Languages

English is the default public language (no `/en/` in the address). French (`/fr/`) and Thai (`/th/`) use the reviewed texts from `docs/translations/Watphou_EN_FR_TH_reviewed.xlsx`. Later changes can go in that Excel file again, or on the **FR** / **TH** sheets of the tour import workbook.

To replace a language later: edit the **FR** or **TH** sheet and upload the Excel file, or open **Tours** / **Pages**, filter by French or Thai, edit that copy, then **Update**.

## What you cannot do

- Install plugins or themes
- Edit code
- Create administrator users

## Need help?

Contact your webmaster or email sales.watphoutravel@gmail.com
