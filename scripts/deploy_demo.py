#!/usr/bin/env python3
"""Deploy wp-content and content/ to demo VPS via tar over SSH."""

from __future__ import annotations

import os
import subprocess
import sys
import tarfile
import tempfile
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
SSH_HOST = os.environ.get("DEMO_SSH_HOST", "sm")
WEB_ROOT = os.environ.get("DEMO_WEB_ROOT", "/var/www/watphou-demo")
REMOTE_CONTENT = "/var/www/watphou-content"

SYNC_PATHS = [
    "wp-content/themes/watphou-travels",
    "wp-content/plugins/watphou-core",
    "wp-content/plugins/watphou-bookings",
    "wp-content/mu-plugins",
    "content",
]


def build_tar(tar_path: Path) -> None:
    with tarfile.open(tar_path, "w:gz") as tar:
        for rel in SYNC_PATHS:
            src = ROOT / rel
            if src.exists():
                tar.add(src, arcname=rel)
            else:
                print(f"WARN: missing {rel}")


def main() -> int:
    with tempfile.NamedTemporaryFile(suffix=".tar.gz", delete=False) as tmp:
        tar_path = Path(tmp.name)
    try:
        print("Building deployment archive...")
        build_tar(tar_path)
        remote_tar = f"/tmp/watphou-deploy-{os.getpid()}.tar.gz"
        print(f"Uploading to {SSH_HOST}...")
        subprocess.run(["scp", str(tar_path), f"{SSH_HOST}:{remote_tar}"], check=True)

        remote_script = f"""set -e
tar -xzf {remote_tar} -C {WEB_ROOT} wp-content
mkdir -p {REMOTE_CONTENT}
tar -xzf {remote_tar} -C /tmp watphou-content-extract 2>/dev/null || true
if tar -tzf {remote_tar} content 2>/dev/null | head -1 | grep -q content; then
  rm -rf /tmp/watphou-content-stage
  mkdir -p /tmp/watphou-content-stage
  tar -xzf {remote_tar} -C /tmp/watphou-content-stage content
  cp -a /tmp/watphou-content-stage/content/. {REMOTE_CONTENT}/
fi
chown -R watphou:watphou {WEB_ROOT}/wp-content
cd {WEB_ROOT}
sudo -u watphou wp cache flush 2>/dev/null || true
sudo -u watphou wp rewrite flush 2>/dev/null || true
rm -f {remote_tar}
echo DEPLOY_OK
"""
        result = subprocess.run(["ssh", SSH_HOST, remote_script], capture_output=True, text=True)
        print(result.stdout)
        if result.stderr:
            print(result.stderr, file=sys.stderr)
        if result.returncode != 0 or "DEPLOY_OK" not in result.stdout:
            return 1
        print("Deploy complete.")
        return 0
    finally:
        tar_path.unlink(missing_ok=True)


if __name__ == "__main__":
    raise SystemExit(main())
