# Manager Guide — Watphou Travels

Plain-language guide for the person who updates tours (Tour Manager).

## Logging in

1. Open **https://darkslategray-snake-182151.hostingersite.com/wp-login.php** (not the public homepage “Edit website” link — that link is removed).
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

## Change the long text (itinerary, highlights)

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

English is the default public language (no `/en/` in the address). French (`/fr/`) and Thai (`/th/`) open the same pages in English with a banner until a **human** translator replaces the text. Do **not** machine-translate Thai.

To replace a language later: **Tours** or **Pages** → filter by French or Thai → edit that copy → Update. Empty drafts 404 for visitors, so placeholders stay **Published** until the real translation is ready.

## What you cannot do

- Install plugins or themes
- Edit code
- Create administrator users

## Need help?

Contact your webmaster or email sales.watphoutravel@gmail.com
