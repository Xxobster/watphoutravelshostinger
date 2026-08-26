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
| GitHub watphoutravelsbluehost.git | Reachable, empty |
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
  "php_log_tail": "2026/08/26 20:39:43 [error] 892485#892485: *108 user \"manager\" was not found in \"/etc/nginx/.htpasswd-watphou-demo\", client: 86.104.249.179, server: watphou.smbistro.duckdns.org, request: \"GET / HTTP/1.1\", host: \"watphou.smbistro.duckdns.org\"\n2026/08/26 20:39:56 [error] 892485#892485: *108 user \"manager\" was not found in \"/etc/nginx