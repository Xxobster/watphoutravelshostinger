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
