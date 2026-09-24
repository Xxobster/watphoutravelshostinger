# Test Log — Watphou Travels



Append-only. Include date, command, result, screenshot path if applicable.



---



## 2026-08-26 — Environment audit (plan phase)



| Check | Result |

|-------|--------|

| SSH to sm (212.73.150.149) | OK |

| nginx active on sm | OK |

| MySQL 8.0.46 on sm | OK |

| smbistro.duckdns.org HTTPS | OK |

| watphou.smbistro.duckdns.org DNS | Resolves to 212.73.150.149 |

| GitHub watphoutravelshostinger.git | Reachable (renamed from watphoutravelsbluehost) |

| Backup ZIP readable | 639 entries, 1.8 GB |

| Live sitemap fetch | 36 pages |



## 2026-08-26 — verify_demo.py (post-deploy)



| Check | Result |

|-------|--------|

| demo HTTPS + Basic Auth | 200 |

| smbistro.duckdns.org | 200 |

| nginx | active |

| Node port 5000 | present |

| Tours imported | 24 |

| Plugins active | polylang, wordpress-seo, watphou-core, watphou-bookings |

| Theme | watphou-travels active |



Commands: `python scripts/verify_demo.py`



## 2026-08-26 — public site + manager WordPress login



| Check | Result |

|-------|--------|

| Homepage without Basic Auth | 200, "Log in to edit" present |

| POST wp-login.php manager/000000 | Redirects to Watphou dashboard |

| smbistro.duckdns.org | 200 |



## 2026-08-26T20:32:21 — verify_demo.py



```json

{

  "checks": [

    {

      "name": "smbistro_https",

      "status": 200,

      "ok": true

    },

    {

      "name": "demo_https",

      "status": 200,

      "ok": true

    },

    {

      "name": "nginx",

      "status": "active",

      "ok": true

    },

    {

      "name": "smbistro_node_5000",

      "ok": true

    }

  ],

  "php_log_tail": ""

}

```



## 2026-08-26T20:47:40 — verify_demo.py



```json

{

  "checks": [

    {

      "name": "smbistro_https",

      "status": 200,

      "ok": true

    },

    {

      "name": "demo_https",

      "status": 200,

      "ok": true

    },

    {

      "name": "wp_login",

      "status": 200,

      "ok": true

    },

    {

      "name": "nginx",

      "status": "active",

      "ok": true

    },

    {

      "name": "smbistro_node_5000",

      "ok": true

    }

  ],

  "php_log_tail": "2026/08/26 20:39:43 [error] 892485#892485: *108 user \"manager\" was not found in \"/etc/nginx/.htpasswd-watphou-demo\", client: 86.104.249.179, server: watphou.smbistro.duckdns.org, request: \"GET / HTTP/1.1\", host: \"watphou.smbistro.duckdns.org\"\n2026/08/26 20:39:56 [error] 892485#892485: *108 user \"manager\" was not found in \"/etc/nginx/.htpasswd-watphou-demo\", client: 86.104.249.179, server: watphou.smbistro.duckdns.org, request: \"GET / HTTP/1.1\", host: \"watphou.smbistro.duckdns.org\""

}

```



## 2026-08-26T22:05:35 — verify_demo.py



```json

{

  "checks": [

    {

      "name": "smbistro_https",

      "status": 200,

      "ok": true

    },

    {

      "name": "demo_https",

      "status": 200,

      "ok": true

    },

    {

      "name": "wp_login",

      "status": 200,

      "ok": true

    },

    {

      "name": "nginx",

      "status": "active",

      "ok": true

    },

    {

      "name": "smbistro_node_5000",

      "ok": true

    }

  ],

  "php_log_tail": "2026/08/26 20:39:43 [error] 892485#892485: *108 user \"manager\" was not found in \"/etc/nginx/.htpasswd-watphou-demo\", client: 86.104.249.179, server: watphou.smbistro.duckdns.org, request: \"GET / HTTP/1.1\", host: \"watphou.smbistro.duckdns.org\"\n2026/08/26 20:39:56 [error] 892485#892485: *108 user \"manager\" was not found in \"/etc/nginx/.htpasswd-watphou-demo\", client: 86.104.249.179, server: watphou.smbistro.duckdns.org, request: \"GET / HTTP/1.1\", host: \"watphou.smbistro.duckdns.org\""

}

```



## 2026-08-26 — WT-019 Vietnam Discovery layout on demo



| Check | Result |

|-------|--------|

| Homepage HTML | Contains hero, Discover Southern Laos Your Way, interest cards, Get Started Today, Tad Fane photo |

| Old Gutenberg cover | Absent |

| Logo / CSS / hero image | HTTP 200 |

| Tours archive | HTTP 200 with new header/footer |

| Demo HTTPS | 200 |

| smbistro.duckdns.org | 200 |

| nginx / Node port 5000 | active / present |

| Playwright screenshots | work/screenshots/home_375px.png, home_768px.png, home_1440px.png |



Photos are Watphou Wix/backup assets. Vietnam Discovery code and photos were not copied.



## 2026-09-10T14:26:21 — verify_demo.py (Hostinger staging + VPS deprovision)



```json

{

  "checks": [

    {

      "name": "hostinger_https",

      "status": 200,

      "ok": true

    },

    {

      "name": "wp_login",

      "status": 200,

      "ok": true

    },

    {

      "name": "vps_nginx",

      "status": "active",

      "ok": true

    },

    {

      "name": "vps_watphou_vhost_removed",

      "ok": true

    },

    {

      "name": "vps_smbistro_vhost_present",

      "ok": true

    },

    {

      "name": "vps_watphou_webroot_removed",

      "ok": true

    }

  ]

}

```


## 2026-09-10 � Hostinger staging smoke (WT-204�WT-205)

| Check | Result |
|-------|--------|
| Unauthenticated front | 401 Basic Auth |
| Authenticated homepage | 200, 4 bestsellers (1.1/2.1/2.2/3.1) |
| X-Robots-Tag | noindex, nofollow |
| Staging banner | Present |
| Pages about-us, contact-us, day-tours, book-online, destinations, tailor-made | 200 |
| Nested destinations | 200 |
| Tour single (Bolaven classic full-day) | 200 |
| 301 old Wix tour root | ? /tours/... |
| 410 dummy shop | product-page / category/all-products |
| Theme + watphou-core + watphou-bookings | active |
| Polylang + Yoast SEO | active |
| No smbistro / wixstatic in HTML | OK |
| package.json | Not added (WordPress path) |

Credentials: `work/staging_basic_auth.txt`, `work/wp_admin_pass.txt` (gitignored).




## 2026-09-10 — WT-110 Polylang + remove site password

| Check | Result |
|-------|--------|
| Unauthenticated homepage | 200 (no HTTP Basic Auth) |
| X-Robots-Tag | noindex, nofollow |
| Staging banner | Present |
| Bestsellers | 4 (1.1 / 2.1 / 2.2 / 3.1) |
| /en/about-us/ | 301 to /about-us/ |
| Language switcher | EN only (FR/TH unpublished drafts hidden) |
| Polylang languages | en, fr, th |
| Empty FR/TH drafts | 50 (unpublished, not machine-translated) |
| verify_demo.py Hostinger HTTPS | 200 (log append then failed on TEST_LOG encoding; checks themselves passed) |

## 2026-09-10 — FR/TH placeholders + Yoast SEO (WT-107 staging)

| Check | Result |
|-------|--------|
| `/` | 200, title Watphou Travels private tours, noindex, favicon, no Edit website |
| `/fr/` `/th/` | 200, language banner, html lang fr-FR / th |
| `/fr/about-us/` `/fr/day-tours/` | 200 (redirect to language slug), banner on |
| Yoast first-time notice text | Absent on wp-admin index after login |
| `/privacy-policy/` `/terms/` `/cancellation/` `/travel-guide/` | 200 |
| TouristTrip schema | Present on tour singles; Offer omitted (price XX) |
| Sitemap | 200, X-Robots-Tag noindex, follow |
| watphou-core | 1.3.0 active |
## 2026-09-10 — WT-115 translation review pack

| Check | Result |
|-------|--------|
| Unique English strings | 400 (494 rows including repeats by field) |
| French / Thai empty | 0 / 0 |
| Excel | `docs/translations/Watphou_EN_FR_TH_review.xlsx` |
| PDF | `docs/translations/Watphou_EN_FR_TH_review.pdf` |
| Published on WordPress | No |



## 2026-09-10T15:40:24 — verify_demo.py (Hostinger staging + VPS deprovision)

```json
{
  "checks": [
    {
      "name": "hostinger_https",
      "status": 200,
      "ok": true
    },
    {
      "name": "wp_login",
      "status": 200,
      "ok": true
    },
    {
      "name": "vps_nginx",
      "status": "active",
      "ok": true
    },
    {
      "name": "vps_watphou_vhost_removed",
      "ok": true
    },
    {
      "name": "vps_smbistro_vhost_present",
      "ok": true
    },
    {
      "name": "vps_watphou_webroot_removed",
      "ok": true
    }
  ]
}
```

## 2026-09-10 — Excel tour import on staging (WT-116)

| Check | Result |
|-------|--------|
| Old `admin.php?page=watphou-import` | HTTP 403 (Hostinger blocks the word “import” in that address) |
| New `admin.php?page=watphou-package-sheet` | HTTP 200, title “Add tours (Excel)”, upload form present |
| Example workbook download | HTTP 200, Excel type, 11 629 bytes, slug `3-day-classic-experience-in-southern-laos` |
| Public homepage | HTTP 200 |
| `python scripts/verify_demo.py` | Hostinger 200, wp-login 200, Virtual Private Server (VPS) watphou demo gone |

## 2026-09-10T20:05:13 — verify_demo.py (Hostinger staging + VPS deprovision)

```json
{
  "checks": [
    {
      "name": "hostinger_https",
      "status": 200,
      "ok": true
    },
    {
      "name": "wp_login",
      "status": 200,
      "ok": true
    },
    {
      "name": "vps_nginx",
      "status": "active",
      "ok": true
    },
    {
      "name": "vps_watphou_vhost_removed",
      "ok": true
    },
    {
      "name": "vps_smbistro_vhost_present",
      "ok": true
    },
    {
      "name": "vps_watphou_webroot_removed",
      "ok": true
    }
  ]
}
```

## 2026-09-11T18:35:49 — verify_demo.py (Hostinger staging + VPS deprovision)

```json
{
  "checks": [
    {
      "name": "hostinger_https",
      "status": 200,
      "ok": true
    },
    {
      "name": "wp_login",
      "status": 200,
      "ok": true
    },
    {
      "name": "vps_nginx",
      "status": "active",
      "ok": true
    },
    {
      "name": "vps_watphou_vhost_removed",
      "ok": true
    },
    {
      "name": "vps_smbistro_vhost_present",
      "ok": true
    },
    {
      "name": "vps_watphou_webroot_removed",
      "ok": true
    }
  ]
}
```

## 2026-09-11 — WT-117 reviewed French / Thai live on Hostinger staging

Source: `docs/translations/Watphou_EN_FR_TH_reviewed.xlsx` (494 rows, status Revised, 0 empty French/Thai). Plugin `watphou-core` 1.5.0.

| Check | Result |
|-------|--------|
| `https://darkslategray-snake-182151.hostingersite.com/fr/` | HTTP 200. Heading: Découvrez le sud du Laos à votre façon. Menu: Accueil. Bestseller: Circuit classique de 3 jours. No English-placeholder banner. |
| `https://darkslategray-snake-182151.hostingersite.com/th/` | HTTP 200. Heading: เที่ยวลาวใต้ในแบบของคุณ. Menu: หน้าแรก. Bestseller: ทัวร์คลาสสิก 3 วันในลาวใต้. No banner. |
| French 3-Day Classic tour | HTTP 200. Heading: Circuit classique de 3 jours dans le sud du Laos |
| Thai 3-Day Classic tour | HTTP 200. Heading: ทัวร์คลาสสิก 3 วันในลาวใต้ |
| French privacy page | HTTP 200. Heading: Politique de confidentialité |
| Thai privacy page | HTTP 200. Heading: นโยบายความเป็นส่วนตัว |
| English home | HTTP 200. Heading still Discover Southern Laos Your Way |
| Example Excel sheets | How to use, Tours, Itinerary, **FR**, **TH**, Column meanings. Header comments include slug and cta (Call To Action). |
| `python scripts/verify_demo.py` | Hostinger 200, wp-login 200, Virtual Private Server (VPS) Watphou demo gone, smbistro present |

## 2026-09-14 — WT-118 public staging hostname (customer NXDOMAIN on preview URL)

Cause: `darkslategray-snake-182151.hostingersite.com` CNAME → `free.cdn.hstgr.net`. Some visitor resolvers return NXDOMAIN (`DNS_PROBE_FINISHED_NXDOMAIN`) while the owner still gets HTTP 200.

Fix: Hostinger free domain `watphoutravels.site` parked on the same WordPress root. Apex A `88.222.222.65` / `2.57.91.249`. `www` CNAME to Hostinger CDN. SSL certificate covers apex and www. Must-use plugin `watphou-public-hosts.php` keeps URLs on the hostname the visitor used.

| Check | Result |
|-------|--------|
| `https://watphoutravels.site/` via Hostinger CDN | HTTP 200. Heading: Discover Southern Laos Your Way. `noindex`. |
| `https://watphoutravels.site/fr/` | HTTP 200. Heading: Découvrez le sud du Laos à votre façon. |
| `https://watphoutravels.site/th/` | HTTP 200. Heading: เที่ยวลาวใต้ในแบบของคุณ. |
| `https://www.watphoutravels.site/` | HTTP 200. Same English home. |
| WordPress canonical | After cache purge, no 301 to `*.hostingersite.com` (`x-redirect-by: WordPress` gone). |
| Live Wix `watphou-travels.com` | Untouched. |
| `python scripts/verify_demo.py` | HTTP 200 on `https://watphoutravels.site/`, wp-login HTTP 200, Virtual Private Server (VPS) Watphou demo still gone |



## 2026-09-14T16:06:35 — verify_demo.py (Hostinger staging + VPS deprovision)

```json
{
  "checks": [
    {
      "name": "hostinger_https",
      "status": 200,
      "ok": true
    },
    {
      "name": "wp_login",
      "status": 200,
      "ok": true
    },
    {
      "name": "vps_nginx",
      "status": "active",
      "ok": true
    },
    {
      "name": "vps_watphou_vhost_removed",
      "ok": true
    },
    {
      "name": "vps_smbistro_vhost_present",
      "ok": true
    },
    {
      "name": "vps_watphou_webroot_removed",
      "ok": true
    }
  ]
}
```

## 2026-09-14T16:07:46 — verify_demo.py (Hostinger staging + VPS deprovision)

```json
{
  "checks": [
    {
      "name": "hostinger_https",
      "status": 200,
      "ok": true
    },
    {
      "name": "wp_login",
      "status": 200,
      "ok": true
    },
    {
      "name": "vps_nginx",
      "status": "active",
      "ok": true
    },
    {
      "name": "vps_watphou_vhost_removed",
      "ok": true
    },
    {
      "name": "vps_smbistro_vhost_present",
      "ok": true
    },
    {
      "name": "vps_watphou_webroot_removed",
      "ok": true
    }
  ]
}
```

## 2026-09-18T16:22:11 — verify_demo.py (Hostinger staging + VPS deprovision)

```json
{
  "checks": [
    {
      "name": "hostinger_https",
      "status": 200,
      "ok": true
    },
    {
      "name": "wp_login",
      "status": 200,
      "ok": true
    },
    {
      "name": "vps_nginx",
      "status": "active",
      "ok": true
    },
    {
      "name": "vps_watphou_vhost_removed",
      "ok": true
    },
    {
      "name": "vps_smbistro_vhost_present",
      "ok": true
    },
    {
      "name": "vps_watphou_webroot_removed",
      "ok": true
    }
  ]
}
```

## 2026-09-18 — WT-119 public listing pages, HTTPS, menu

Checked `https://watphoutravels.site` in the browser and with Python:

- Top menu: Home, Day Tour, 2-DAY Tours, 3-DAY Tours, 4-6 Day Tours, Destinations, Tailor-Made, About Us, Contact
- Homepage does not contain “Tailored Tours for Your Interest”
- `/day-tours/` lists 7 tours with Book this tour / View tour; `/2-day-tours/` 3; `/3-day-tours/` 2; `/4-6-day-tours/` 3
- `/destinations/` hub: Pakse & Surroundings, Bolaven Plateau, Vat Phou & Champasak, 4000 Islands
- Bolaven full-day tour page has itinerary, included, From $XX, and Request this tour (`id=booking`, two forms)
- HTTP 301 to HTTPS for apex and www
- Certificate subject alternative names: `watphoutravels.site` and `www.watphoutravels.site` (valid until 2026-12-13)
- After cache bypass: Strict-Transport-Security `max-age=31536000; includeSubDomains`, Content-Security-Policy `upgrade-insecure-requests`, X-Robots-Tag noindex
- `/wp-admin/admin.php?page=watphou-tours` redirects to WordPress login (the Manage tours page exists)

## 2026-09-19 — WT-120 Excel catalog order

Checked `https://watphoutravels.site` after deploying `watphou-core` 1.6.2 and theme 2.2.2:

- `/day-tours/` order: 1.1 Bolaven Plateau Classic Full-Day, 1.2 Vat Phou & Riverside Gems Full-Day, 1.3 4000 Islands Full-Day, 1.4 Pakse Cultural, 1.5 Dan Sinxai, 1.6 Champasak Cycling, 1.7 Dan Yai & Tiger Falls. Second card opens `/tours/vatphu-riverside-gems-full-day/`.
- `/2-day-tours/` 2.1, 2.2, 2.3
- `/3-day-tours/` 3.1, 3.2
- `/4-6-day-tours/` headings 4-DAY TOURS (3 nights) / 5-DAY TOURS (4 nights) / 6-DAY TOURS (5 nights) then 4.1, 5.1, 6.1
- `/tours/` full catalog 1.1 through 6.1
- Destinations hub: Bolaven, 4000 Islands, Vat Phou & Champasak, Pakse
- Bolaven destination page: 1.1, 1.5, 1.7, 2.1, 3.1, 3.2, 4.1, 5.1, 6.1
- Home best sellers: Bolaven full day, 4,000 Islands & Vat Phu 2-Day, 3-Day Classic, 4 Days Southern Laos Escape
- Public prices stay From $XX; Price Braquette not imported

## 2026-09-19 — WT-122 booking form pre-fill

Checked `https://watphoutravels.site/tours/vatphu-riverside-gems-full-day/#booking` after deploying `watphou-bookings` 1.1.0 and theme 2.2.3:

- Your tour block is pre-filled: name Vat Phou & Riverside Gems Full-Day, code 1.2, duration, destinations, departure, From $XX, headline
- Your details fields (name, email, dates, adults, pickup, message) stay empty for the guest
- Home contact form has a tour dropdown that fills the same tour fields
- **View tour** (listings and Home) opens the tour page at `#booking`; the same pre-filled **Your tour** block sits under the hero
- Public prices stay From $XX

## 2026-09-19 — WT-123 About Us matches live Wix

Checked Excel sheet **About Us**: cell A3 “Same as our old website”, A4 `https://www.watphou-travels.com/about-us`. Staging `/about-us/` before this change was only “Your local expert in Southern Laos — private tours with European standards.”

After theme 2.2.5 deploy, `https://watphoutravels.site/about-us/` is checked in the browser for:

- Heading About Watphou Travels and the 2008 establishment paragraph
- Hospitality: Pakse Hotel & Restaurant, Le Panorama Rooftop, Pakse Burger 66 (hours, phones, Facebook)
- Partnerships: University of Calgary / Meuang Kang, Les Colchés d’Asie
- Office, hotel, rooftop, burger, and school photo rows
- Closing Phone/WhatsApp +85620 9949 5858 and both sales emails

## 2026-09-19 — WT-124 text-review PDF (our copy only)

After theme 2.2.6 / `watphou-core` 1.6.4 / `watphou-bookings` 1.1.1, checked live on `https://watphoutravels.site`:

- All 7 `/day-tours/` price bars: no “accommodation” (package notes such as “Standard.” / “Hard-trek option”)
- Overnight example Bolaven 2-day still: “From $XX per person — Standard accommodation. Comfort upgrades available.”
- Home: Popular Private Tours; Plan Your Private Tour in 5 Simple Steps; no Google-review developer note; header **Request a quote**
- Listing: 7× Request this tour, 0× Book this tour; tour form button **Send tour request**
- Privacy / Terms / Cancellation / Travel Guide / Contact / Book-online / About: no “Genuine Google”, “invented reviews”, “do not pad”, or “until the company confirms”
- Contact intro: “Planning a trip in Southern Laos? Contact our Pakse team…”
- About Us still the Wix page; menus still Excel labels (Day Tour / 2-DAY Tours / …); Palateu / Angkor / three icons / Pakse same-day / Dan Yai picnic unchanged
- `/fr/` and `/th/` return 200 (language buttons no longer 404)

## 2026-09-19 — WT-125 Terms of Use without placeholder-price / BCEL notes

Checked `https://watphoutravels.site/terms/` in the browser after `watphou-core` 1.6.6:

- Heading Terms of Use
- Remaining body: private tours from Pakse; the written quotation is the contract; contact email / phone / Pakse address
- Absent: “From $XX”, “placeholder prices as a payable amount”, “BCEL”, “online payment is not live yet”, “Starting prices on the website are indications”, “Send a request by the form or WhatsApp”
- Staging banner still says mock payments (site-wide, not this page’s body)
- `python scripts/verify_demo.py`: Hostinger staging HTTP 200

## 2026-09-19 — WT-126 photo lightbox

Checked live on `https://watphoutravels.site` (theme 2.2.8):

- About Us: 18 gallery photos are zoom-in links; first office photo opens a dark viewer with caption “Watphou Travels office”
- Next arrow loads `office-2.jpg` in the same group
- Escape and Close hide the viewer; WhatsApp float and top bar stay hidden while it is open
- Day Tour listing photo (Bolaven) also opens the large viewer; destination tiles stay ordinary links

## 2026-09-19 — WT-127 View tour opens itinerary first

Checked live after theme 2.2.9:

- `/day-tours/`: listing has **View tour** only (no Request this tour under the excerpt)
- Clicking the Bolaven title and Home **View tour** open `/tours/bolaven-plateau-classic-full-day-tour/` on the hero and itinerary, not the form
- Form **Request this tour** is below Related tours; no extra Request this tour button after What’s included
- `python scripts/verify_demo.py`: Hostinger staging HTTP 200


## 2026-09-19T16:53:53 — verify_demo.py (Hostinger staging + VPS deprovision)

```json
{
  "checks": [
    {
      "name": "hostinger_https",
      "status": 200,
      "ok": true
    },
    {
      "name": "wp_login",
      "status": 200,
      "ok": true
    },
    {
      "name": "vps_nginx",
      "status": "active",
      "ok": true
    },
    {
      "name": "vps_watphou_vhost_removed",
      "ok": true
    },
    {
      "name": "vps_smbistro_vhost_present",
      "ok": true
    },
    {
      "name": "vps_watphou_webroot_removed",
      "ok": true
    }
  ]
}
```

## 2026-09-19T17:41:57 — verify_demo.py (Hostinger staging + VPS deprovision)

```json
{
  "checks": [
    {
      "name": "hostinger_https",
      "status": 200,
      "ok": true
    },
    {
      "name": "wp_login",
      "status": 200,
      "ok": true
    },
    {
      "name": "vps_nginx",
      "status": "active",
      "ok": true
    },
    {
      "name": "vps_watphou_vhost_removed",
      "ok": true
    },
    {
      "name": "vps_smbistro_vhost_present",
      "ok": true
    },
    {
      "name": "vps_watphou_webroot_removed",
      "ok": true
    }
  ]
}
```

## 2026-09-19T17:55:33 — verify_demo.py (Hostinger staging + VPS deprovision)

```json
{
  "checks": [
    {
      "name": "hostinger_https",
      "status": 200,
      "ok": true
    },
    {
      "name": "wp_login",
      "status": 200,
      "ok": true
    },
    {
      "name": "vps_nginx",
      "status": "active",
      "ok": true
    },
    {
      "name": "vps_watphou_vhost_removed",
      "ok": true
    },
    {
      "name": "vps_smbistro_vhost_present",
      "ok": true
    },
    {
      "name": "vps_watphou_webroot_removed",
      "ok": true
    }
  ]
}
```

## 2026-09-19 — WT-129 booking form missing-field list

- Page: `https://watphoutravels.site/tours/bolaven-plateau-classic-full-day-tour/`
- Empty **Send tour request**: red box above the button lists **Name**, **Email**, **Preferred date**, **Agreement to the privacy policy**. Each of those fields also has a red line under it. No booking row created. No office email sent.
- Invalid email `not-an-email` with other required fields filled: list shows **Email**; field text is “please enter a valid email address (for example name@example.com).”
- Server empty/invalid post returns JSON `success:false` with `errors` (did not complete a real booking).
- Plugin `watphou-bookings` 1.1.4, theme 2.3.2. `form.js?ver=1.1.4` loaded.
- `python scripts/verify_demo.py`: Hostinger HTTP 200; Virtual Private Server (VPS) `watphou` gone; `smbistro` still present.

## 2026-09-19 — WT-128 Quick Edit price on listing, tour page, and form

- After Quick Edit **3-Day Classic** From $ = `120` and Save:
  - `/3-day-tours/` card shows `$120`
  - `/tours/3-day-classic-experience-in-southern-laos/` hero: `From $120 per person — Standard accommodation. Comfort upgrades available.`
  - Request form `tour_price` field: same sentence
  - Homepage Popular Private Tours card for Classic: `$120`
- Highlights and other tours still `$XX` (unchanged)
- Plugin `watphou-core` 1.6.8 on Hostinger (`watphou-core` active)
- First broken save showed `From 0 per person` because PHP `preg_replace` treated `$120` as a back-reference; fixed with `preg_replace_callback`


## 2026-09-19 — WT-130 menu capitalization, Request a quote, French/Thai links

- English menu: **Day Tours**, **2-Day Tours**, **3-Day Tours**, **4-6 Day Tours** (no mixed “Day Tour” / “2-DAY Tours”)
- French menu: **Excursions à la journée**, **Circuits de 2 jours**, **Circuits de 3 jours**, **Circuits de 4 à 6 jours**
- Thai menu: **ทัวร์แบบไปเช้าเย็นกลับ**, **ทัวร์ 2 วัน**, **ทัวร์ 3 วัน**, **ทัวร์ 4–6 วัน**
- Quote page `/book-online/`: heading, header button, and form button **Request a quote**; intro “You are requesting a private tour…”
- French `/fr/book-online/`: **Demander un devis** + French intro; Thai `/th/book-online/`: **ขอใบเสนอราคา** + Thai intro (reviewed-pack wording, not a new machine pass)
- Language switcher: `https://watphoutravels.site/fr/` and `/th/` return HTTP 200 (were 404). Child paths `/fr/day-tours/` and `/fr/book-online/` stay on those Uniform Resource Locators (URLs) with French copy
- Workbook `docs/translations/Watphou_EN_FR_TH_reviewed.xlsx` Texts rows 3, 5–7, 495–497
- Plugin `watphou-core` 1.6.12, `watphou-bookings` 1.1.5, theme 2.3.3
- `python scripts/verify_demo.py`: Hostinger HTTP 200; Virtual Private Server (VPS) `watphou` gone; `smbistro` still present

## 2026-09-19 — WT-130 quote-page browser titles (French / Thai)

- `/book-online/`: `<title>` **Request a quote | Watphou Travels**
- `/fr/book-online/`: `<title>` and Open Graph **Demander un devis | Watphou Travels** (matches heading)
- `/th/book-online/`: `<title>` and Open Graph **ขอใบเสนอราคา | Watphou Travels** (matches heading)
- Plugin `watphou-core` 1.6.13 on Hostinger

## 2026-09-19T17:57:29 — verify_demo.py (Hostinger staging + VPS deprovision)

```json
{
  "checks": [
    {
      "name": "hostinger_https",
      "status": 200,
      "ok": true
    },
    {
      "name": "wp_login",
      "status": 200,
      "ok": true
    },
    {
      "name": "vps_nginx",
      "status": "active",
      "ok": true
    },
    {
      "name": "vps_watphou_vhost_removed",
      "ok": true
    },
    {
      "name": "vps_smbistro_vhost_present",
      "ok": true
    },
    {
      "name": "vps_watphou_webroot_removed",
      "ok": true
    }
  ]
}
```

## 2026-09-19T18:18:04 — verify_demo.py (Hostinger staging + VPS deprovision)

```json
{
  "checks": [
    {
      "name": "hostinger_https",
      "status": 200,
      "ok": true
    },
    {
      "name": "wp_login",
      "status": 200,
      "ok": true
    },
    {
      "name": "vps_nginx",
      "status": "active",
      "ok": true
    },
    {
      "name": "vps_watphou_vhost_removed",
      "ok": true
    },
    {
      "name": "vps_smbistro_vhost_present",
      "ok": true
    },
    {
      "name": "vps_watphou_webroot_removed",
      "ok": true
    }
  ]
}
```

## 2026-09-22 — WT-131 September photos, hero slideshow, language lock

- Homepage hero: `home-hero/01-tad-fan-hero.jpg` through `11-…`; class `wpt-hero--home`; no `1.Home` folder. Slideshow interval 5 / 8 / 12 seconds from `navigator.connection` + first-image load time. First image `fetchpriority=high`; others `data-src`.
- Day Tours listing uses theme `…/tours/{slug}/featured.jpg` (example Bolaven: Tad Yuang waterfall), not leftover `uploads/2026/09/tad-fane-…` placeholders.
- English `/tours/bolaven-plateau-classic-full-day-tour/`: `html lang=en-US`, heading **Bolaven Plateau Classic Full-Day Tour**, body starts “Leave the heat of Pakse…”, breadcrumb in English.
- French `/fr/tours/bolaven-plateau-classic-full-day-tour/`: heading **Circuit classique d’une journée sur le plateau des Bolovens**, body “Quittez la chaleur de Pakse…”, menu **Excursions à la journée**.
- Thai `/th/tours/bolaven-plateau-classic-full-day-tour/`: heading **ทัวร์คลาสสิกเต็มวันที่ราบสูงโบลาเวน**, stays on `/th/`.
- After visiting `/fr/` then opening the English Bolaven Uniform Resource Locator (URL), the page stays English (cookie test in `work/probe_wt131.py`).
- French homepage destination links use `/fr/destinations/…`. `/fr/contact/` redirects to `/fr/contact-us/` (still French).
- `python work/probe_wt131.py`: ERRORS 0 (15 tours × English/French/Thai + listing/pages).
- Theme 2.4.2, plugin `watphou-core` 1.7.1 on https://watphoutravels.site

## 2026-09-22 — WT-132 booking form language + extras JSON

- Staging homepage, login, and French Bolaven tour return Hypertext Transfer Protocol (HTTP) 200 after extras.php was replaced with a JSON loader.
- French `/fr/tours/bolaven-plateau-classic-full-day-tour/` form: **Votre circuit**, **Nom du circuit**, **Code du forfait**, **Durée**, **Destinations visitées**, **Départ de Pakse à 8 h 00**, **Prix de départ**, **Vos informations**, **Date souhaitée**, **J’accepte la politique de confidentialité**, **Demander un devis**. Value **Bolaven Plateau** stays English.
- Thai `/th/tours/bolaven-plateau-classic-full-day-tour/` form: **ทัวร์ของคุณ**, **ชื่อทัวร์**, **รหัสแพ็กเกจ**, **จุดหมายปลายทาง**, **วันเดินทางที่ต้องการ**.
- English tour request form stays English (**Your tour**, **Request a quote**).
- French menu destination **4000 îles**.
- `python work/probe_booking_i18n.py`: ERRORS 0. Plugin `watphou-core` 1.7.3, `watphou-bookings` 1.1.8.

## 2026-09-22 — extras.php overwrite; File Manager left on

- Replaced `public_html/wp-content/plugins/watphou-core/includes/public-i18n-extras.php` in place (TUS `override=true`, 954 bytes, HTTP 204). Did not delete the file.
- Homepage `https://watphoutravels.site/` HTTP 200. WordPress login `https://watphoutravels.site/wp-login.php` HTTP 200 (Log In form).
- WordPress File Manager (`wp-file-manager/file_folder_manager.php`) is **active**. Leave it on until the real domain (WT-133).

## 2026-09-24 — WT-134 Simple Mail Transfer Protocol (SMTP) to both office Gmails

- Account has **no Hostinger Email mailbox order**. Mail is sent with Hostinger PHP mail From **`bookings@watphoutravels.site`**, not From Gmail (Sender Policy Framework).
- Domain Name System (DNS) TXT on `watphoutravels.site` only: `v=spf1 a include:_spf.mail.hostinger.com ~all`. Live `watphou-travels.com` Joker records were not changed.
- Recipients: **`sales.watphoutravel@gmail.com`** and **`watphoutravel.of@gmail.com`**. Reply-To is the guest.
- Live test booking **#1** name **SMTP wiring test 24 Sep 2026**: WordPress `wp_mail` returned success; booking history: **Office email sent to sales.watphoutravel@gmail.com, watphoutravel.of@gmail.com**.
- Homepage `https://watphoutravels.site/` HTTP 200 after deploy. `python scripts/test_office_emails.py`: OK.
- Plugins on staging: `watphou-core` 1.7.4, `watphou-bookings` 1.1.9, must-use `watphou-smtp.php`.
- Check both Gmail inboxes (and Spam) for From `bookings@watphoutravels.site`. WordPress reports the send; Gmail delivery cannot be read from here.

## 2026-09-24 — WT-136 homepage gap and decorative quote

- `.wpt-explore` top padding `4rem` → `1rem` (one line under the hero). Theme 2.4.4.
- Removed the orange decorative `“` (`.wpt-quote-mark`) under **Explore Southern Laos like Never Before**.
- Live check: gap 16px; no `wpt-quote-mark` on English or French home. Cascading Style Sheets (CSS) `theme.css?ver=2.4.4`.



## 2026-09-24T12:30:13 — verify_demo.py (Hostinger staging + VPS deprovision)

```json
{
  "checks": [
    {
      "name": "hostinger_https",
      "status": 200,
      "ok": true
    },
    {
      "name": "wp_login",
      "status": 200,
      "ok": true
    },
    {
      "name": "vps_nginx",
      "status": "active",
      "ok": true
    },
    {
      "name": "vps_watphou_vhost_removed",
      "ok": true
    },
    {
      "name": "vps_smbistro_vhost_present",
      "ok": false
    },
    {
      "name": "vps_watphou_webroot_removed",
      "ok": true
    }
  ]
}
```

## 2026-09-24 — WT-137 homepage memory

- `python work/test_hero_960.py`: 11 pairs, 1920 and 960 wide.
- Live home: `theme.js?ver=2.4.5`. Hero keeps **2** of 11 slides decoded; the rest use a 1-pixel placeholder. Active slide has `srcset` including `-960w.jpg`.
- Popular tours images: `featured-*-600x400.jpg` (was `1024x…`).
- Hero still rotates; Home screenshot looks normal.
- `python scripts/verify_demo.py`: Hostinger HTTP 200; Virtual Private Server (VPS) `watphou` gone. `smbistro` nginx site was **missing** on this run (not changed here; do not recreate).

## 2026-09-24 — WT-138 Quick Edit top + bottom tour photos

- `python scripts/test_gallery_ids.py`: unique IDs, plugin 1.7.5, desk buttons present.
- Live plugin `watphou-core.php` Version 1.7.5 (TUS overwrite of the real `watphou-core` folder).
- Public galleries still 10 pictures: [4-day](https://www.watphoutravels.site/tours/4-day-southern-laos-escape-2/), [5-day](https://www.watphoutravels.site/tours/5-day-exploring-southern-laos/), [6-day](https://www.watphoutravels.site/tours/6-day-journey-to-the-heart-of-southern-laos/).
- WordPress admin **Quick edit tours**: **Change top photo**, **Change bottom photos**, **Remove all**. Media Library modal title **Choose bottom photos** / **Use these photos**.
- Seed copied current bottom JPEGs into the Media Library (notice gone after a few refreshes).
- `python scripts/verify_demo.py`: Hostinger HTTP 200. Virtual Private Server (VPS) `smbistro` nginx site missing (unchanged; do not recreate).


## 2026-09-24T12:52:03 — verify_demo.py (Hostinger staging + VPS deprovision)

```json
{
  "checks": [
    {
      "name": "hostinger_https",
      "status": 200,
      "ok": true
    },
    {
      "name": "wp_login",
      "status": 200,
      "ok": true
    },
    {
      "name": "vps_nginx",
      "status": "active",
      "ok": true
    },
    {
      "name": "vps_watphou_vhost_removed",
      "ok": true
    },
    {
      "name": "vps_smbistro_vhost_present",
      "ok": false
    },
    {
      "name": "vps_watphou_webroot_removed",
      "ok": true
    }
  ]
}
```

## 2026-09-24 — WT-139 live on watphoutravels.site

| Check | Result |
|-------|--------|
| Excel 4-Day Southern Laos Escape code | 4.1 |
| Booking form Package code on /tours/4-day-southern-laos-escape-2/ | 4.1 |
| Homepage Destinations block | Removed |
| Explore to Popular Private Tours gap | About halved |
| What Our Clients Say carousel | Five genuine Google Maps reviews; 4.5 from 55 |
| Footer logo | White mark from logo-wpt.jpg, visible on dark bar |
| Staging robots | noindex still present |
| Schema | LocalBusiness geo + Review + aggregateRating |
| Versions | Theme 2.5.1, watphou-core 1.8.0, watphou-bookings 1.2.0 |

## 2026-09-24 — Search Engine Optimization (SEO) scores for the customer (WT-107)

- Live HTML (11 pages on `https://www.watphoutravels.site`): technical on-page **100 / 100** after canonical + `hreflang` `x-default` (`watphou-core` 1.8.1).
- Lighthouse-style Search Engine Optimization (SEO) category (10 equal-weight checks): **90 / 100** on every page. The only fail is `is-crawlable` (`noindex, nofollow` on purpose).
- Homepage in a real browser: title, one `h1`, canonical `https://www.watphoutravels.site/`, `hreflang` en/fr/th/`x-default`.
- `robots.txt` HTTP 200 (`Disallow:` empty; sitemap listed). Sitemap HTTP 200 with `X-Robots-Tag: noindex, follow`.
- `npx lighthouse` desktop against the homepage: HTTP **403** (Hostinger blocks headless Chrome). Google PageSpeed Insights Application Programming Interface: quota exceeded.
- `python scripts/verify_demo.py`: Hostinger HTTP 200. Virtual Private Server (VPS) `smbistro` nginx site missing (unchanged; do not recreate).
- Customer-facing copy: `docs/SEO_CUSTOMER_REPORT.md`.

## 2026-09-24 — Browser memory and loading (WT-140)

Before (Chrome, desktop 1366×768), decoded image bytes: Home 16.2 MB, About 53.2 MB, 4-day tour 21.0 MB, Contact 5.2 MB. JavaScript heap on the homepage stayed 1.8–2.1 MB across 5 idle minutes. Listeners stayed at 56. Ten navigation cycles did not climb (heap 2.9–5.0 MB, documents 4–8).

After theme 2.5.2 / core 1.8.2 / bookings 1.2.1: Home 11.6 MB (−28%), About 16.5 MB (−69%), 4-day tour 11.0 MB (−48%), Contact 0.56 MB (−89%). Mobile About 21.2 MB → 6.1 MB. Homepage Largest Contentful Paint 2.25 s desktop, 1.92 s mobile. Cumulative Layout Shift about 0.

After idle (homepage, hero still in view): heap 2.94 → 1.78 → 1.96 → 2.13 → 1.80 → 1.97 MB at 0/60/120/180/240/300 seconds. Listeners 106 then 56. Decoded images 11.60 MB and flat. Image transfer stopped at 3.39 MB after the first minute (54 requests).

After 10 navigation cycles, heap MB: 4.0, 3.5, 2.9, 5.0, 2.9, 4.8, 3.9, 2.9, 5.0, 3.9. Documents 8/6/4/8/4/8/6/4/8/6. Listeners 137/83/24/135/24/135/83/24/137/83. No climb.

Menu open/close plus resize plus scroll: heap 3.11 → 4.41 MB, listeners 56 → 69, nodes 1494 → 1778. A small rise from decoded lower-page photos, not a growing set of listeners.

Low-end simulation (Chrome, Slow 4G, processor slowed 4×): homepage DOMContentLoaded 11.3 s, load 13.4 s. Hero kept 2 of 11 frames decoded while on screen, then 1 frame after scrolling away. Mobile menu opened (`aria-expanded=true`) in about 0.7 s. At 6× the same page still opened the menu; load was about 39 s because the hero photographs are large on a slow link, not because a script loop froze the tab.

`python scripts/verify_demo.py`: Hostinger HTTP 200. Exit 1 only because the old Virtual Private Server (VPS) `smbistro` nginx site is gone. That site must stay gone.

Widths 360, 412, 768, 1366, and 1920 on the homepage, tours list, 4-day tour, and French homepage: no horizontal overflow and no page errors.

## 2026-09-24 — Quick edit bottom photos (WT-141)

Logged into staging Quick edit tours. A tour that shows “4 photos on the tour page” opened Choose bottom photos with those 4 already in the selected strip. Removing one with × and clicking two more photos changed the button to “Use these 5 photos”. Preview on the page showed 5 pictures in the same three-column grid as the tour page, with Desktop, Tablet, and Phone. The page was reloaded without Save all changes: the tour still said 4 photos, so the test did not publish.

Live checks: French homepage still French; tour gallery and request form present; empty submit lists required fields and does not send; mobile menu opens; `form.js` absent on `/tours/` and `/privacy-policy/`.

`python scripts/verify_demo.py` recorded below if run in this pass.

Theme CSS is Brotli (`content-encoding: br`, `max-age=31536000`).

## 2026-09-24T13:36:46 — verify_demo.py (Hostinger staging + VPS deprovision)

```json
{
  "checks": [
    {
      "name": "hostinger_https",
      "status": 200,
      "ok": true
    },
    {
      "name": "wp_login",
      "status": 200,
      "ok": true
    },
    {
      "name": "vps_nginx",
      "status": "active",
      "ok": true
    },
    {
      "name": "vps_watphou_vhost_removed",
      "ok": true
    },
    {
      "name": "vps_smbistro_vhost_present",
      "ok": false
    },
    {
      "name": "vps_watphou_webroot_removed",
      "ok": true
    }
  ]
}
```

## 2026-09-24T14:14:54 — verify_demo.py (Hostinger staging + VPS deprovision)

```json
{
  "checks": [
    {
      "name": "hostinger_https",
      "status": 200,
      "ok": true
    },
    {
      "name": "wp_login",
      "status": 200,
      "ok": true
    },
    {
      "name": "vps_nginx",
      "status": "active",
      "ok": true
    },
    {
      "name": "vps_watphou_vhost_removed",
      "ok": true
    },
    {
      "name": "vps_smbistro_vhost_present",
      "ok": false
    },
    {
      "name": "vps_watphou_webroot_removed",
      "ok": true
    }
  ]
}
```

## 2026-09-24 — Homepage gap before 5 Simple Steps (WT-142)

Before: 132 pixels from the last Popular Private Tours card to the heading Plan Your Private Tour in 5 Simple Steps (Popular section bottom padding 4 rem + Steps top padding 3 rem). After theme 2.5.3 on the live homepage: paddings 2 rem / 1.5 rem, measured gap **76 pixels**. The join (112 pixels) is now 56 pixels (half). Last-card margin stayed 20 pixels.

## 2026-09-24 — Homepage Google reviews newest-first (WT-143)

`python scripts/test_google_reviews.py`: **ok** — five 4–5 star quotes, order Natalie Guignard → 尾崎行雄 → Reinhard Anton → Anja Krause → Simone Kuhl. 1-star omitted.

Live https://watphoutravels.site/?v=184#what-clients-say: first original card is **Natalie Guignard** (9 June 2026). Plugin `watphou-core` **1.8.4** on Hostinger. Hostinger staging HTTP **200**. Places Application Programming Interface daily refresh is coded; no key on the server yet, so the snapshot file is what visitors see until a key is added.

## 2026-09-24 — Homepage hero crossfade (WT-144)

`python scripts/test_hero_fade.py`: **ok**. Live `theme.js?ver=2.5.5`. Over a 13-second homepage watch, two photos overlapped (for example opacity 0.55 / 0.45) while both still had real JPEG pixels. No active or visible slide used the 1-pixel placeholder during the fade (`visibleFlashAny: false`). Hero backdrop is `rgb(28, 25, 23)`.



## 2026-09-24T18:55:25 — verify_demo.py (Hostinger staging + VPS deprovision)

```json
{
  "checks": [
    {
      "name": "hostinger_https",
      "status": 200,
      "ok": true
    },
    {
      "name": "wp_login",
      "status": 200,
      "ok": true
    },
    {
      "name": "vps_nginx",
      "status": "active",
      "ok": true
    },
    {
      "name": "vps_watphou_vhost_removed",
      "ok": true
    },
    {
      "name": "vps_smbistro_vhost_present",
      "ok": false
    },
    {
      "name": "vps_watphou_webroot_removed",
      "ok": true
    }
  ]
}
```
