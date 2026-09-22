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
