#!/usr/bin/env python3
"""Verify demo and existing smbistro site health."""

from __future__ import annotations

import json
import os
import subprocess
import sys
import urllib.error
import urllib.request
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
SSH_HOST = os.environ.get("DEMO_SSH_HOST", "sm")
DEMO_URL = os.environ.get("DEMO_URL", "https://watphou.smbistro.duckdns.org")
SMBISTRO_URL = "https://smbistro.duckdns.org/"
BASIC_USER = os.environ.get("DEMO_BASIC_AUTH_USER", "")
BASIC_PASS = os.environ.get("DEMO_BASIC_AUTH_PASSWORD", "")


def fetch_status(url: str, user: str = "", password: str = "") -> int:
    req = urllib.request.Request(url, method="GET")
    req.add_header("User-Agent", "WatphouVerify/1.0")
    if user and password:
        import base64

        creds = base64.b64encode(f"{user}:{password}".encode()).decode()
        req.add_header("Authorization", f"Basic {creds}")
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

    smb = fetch_status(SMBISTRO_URL)
    results["checks"].append({"name": "smbistro_https", "status": smb, "ok": smb == 200})
    if smb != 200:
        ok = False
        print(f"FAIL smbistro: HTTP {smb}")

    demo = fetch_status(DEMO_URL, BASIC_USER, BASIC_PASS)
    results["checks"].append({"name": "demo_https", "status": demo, "ok": demo in (200, 301, 302)})
    if demo not in (200, 301, 302):
        ok = False
        print(f"FAIL demo: HTTP {demo} (set DEMO_BASIC_AUTH_USER/PASS if gated)")
    else:
        print(f"OK demo: HTTP {demo}")

    nginx = ssh_check("systemctl is-active nginx")
    results["checks"].append({"name": "nginx", "status": nginx, "ok": nginx == "active"})
    print(f"nginx: {nginx}")

    pm2 = ssh_check("ss -tlnp | grep 5000 || true")
    results["checks"].append({"name": "smbistro_node_5000", "ok": "5000" in pm2})
    print(f"node:5000 present: {'5000' in pm2}")

    php_log = ssh_check("tail -3 /var/log/nginx/watphou-demo-error.log 2>/dev/null || echo none")
    results["php_log_tail"] = php_log
    print(f"demo error log: {php_log[:200]}")

    log_path = ROOT / "docs" / "project_memory" / "TEST_LOG.md"
    entry = f"\n## {__import__('datetime').datetime.now().isoformat(timespec='seconds')} — verify_demo.py\n\n```json\n{json.dumps(results, indent=2)}\n```\n"
    if log_path.exists():
        log_path.write_text(log_path.read_text(encoding="utf-8") + entry, encoding="utf-8")

    return 0 if ok else 1


if __name__ == "__main__":
    raise SystemExit(main())
