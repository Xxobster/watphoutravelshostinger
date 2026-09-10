#!/usr/bin/env python3
"""Verify Hostinger staging site and that the VPS Watphou demo is gone.

Does not modify smbistro, cirlapp, or other vhosts on VPS sm.
"""

from __future__ import annotations

import json
import os
import subprocess
import sys
import urllib.error
import urllib.request
from datetime import datetime
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
SSH_HOST = os.environ.get("DEMO_SSH_HOST", "sm")
STAGING_URL = os.environ.get(
    "STAGING_URL",
    "https://darkslategray-snake-182151.hostingersite.com",
)
# Legacy alias so older docs still work
DEMO_URL = os.environ.get("DEMO_URL", STAGING_URL)


def fetch_status(url: str) -> int:
    req = urllib.request.Request(url, method="GET")
    req.add_header("User-Agent", "WatphouVerify/1.0")
    try:
        with urllib.request.urlopen(req, timeout=30) as resp:
            return resp.status
    except urllib.error.HTTPError as e:
        return e.code
    except urllib.error.URLError:
        return 0


def ssh_check(cmd: str) -> str:
    r = subprocess.run(["ssh", SSH_HOST, cmd], capture_output=True, text=True, timeout=30)
    return (r.stdout or r.stderr).strip()


def main() -> int:
    results: dict = {"checks": []}
    ok = True

    staging = fetch_status(STAGING_URL.rstrip("/") + "/")
    results["checks"].append(
        {"name": "hostinger_https", "status": staging, "ok": staging in (200, 301, 302)}
    )
    if staging not in (200, 301, 302):
        ok = False
        print(f"FAIL Hostinger staging: HTTP {staging}")
    else:
        print(f"OK Hostinger staging: HTTP {staging}")

    login = fetch_status(STAGING_URL.rstrip("/") + "/wp-login.php")
    results["checks"].append({"name": "wp_login", "status": login, "ok": login in (200, 302)})
    print(f"wp-login.php: HTTP {login}")
    if login not in (200, 302):
        ok = False

    nginx = ssh_check("systemctl is-active nginx")
    results["checks"].append({"name": "vps_nginx", "status": nginx, "ok": nginx == "active"})
    print(f"VPS nginx: {nginx}")
    if nginx != "active":
        ok = False

    sites = ssh_check("ls /etc/nginx/sites-enabled/")
    watphou_gone = "watphou-demo" not in sites.split()
    smbistro_present = "smbistro" in sites.split()
    results["checks"].append({"name": "vps_watphou_vhost_removed", "ok": watphou_gone})
    results["checks"].append({"name": "vps_smbistro_vhost_present", "ok": smbistro_present})
    print(f"VPS sites-enabled: {sites.replace(chr(10), ' ')}")
    if not watphou_gone:
        ok = False
        print("FAIL: watphou-demo nginx site still enabled")
    if not smbistro_present:
        ok = False
        print("FAIL: smbistro nginx site missing")

    webroot = ssh_check("test ! -d /var/www/watphou-demo && echo gone || echo present")
    results["checks"].append({"name": "vps_watphou_webroot_removed", "ok": webroot == "gone"})
    print(f"VPS watphou webroot: {webroot}")
    if webroot != "gone":
        ok = False

    log_path = ROOT / "docs" / "project_memory" / "TEST_LOG.md"
    entry = (
        f"\n## {datetime.now().isoformat(timespec='seconds')} — verify_demo.py "
        f"(Hostinger staging + VPS deprovision)\n\n"
        f"```json\n{json.dumps(results, indent=2)}\n```\n"
    )
    if log_path.exists():
        log_path.write_text(log_path.read_text(encoding="utf-8") + entry, encoding="utf-8")

    return 0 if ok else 1


if __name__ == "__main__":
    raise SystemExit(main())
