"""CLI check: homepage hero crossfade waits for the next photo (no white flash)."""
from __future__ import annotations

import sys
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
JS = ROOT / "wp-content" / "themes" / "watphou-travels" / "assets" / "js" / "theme.js"
CSS = ROOT / "wp-content" / "themes" / "watphou-travels" / "assets" / "css" / "theme.css"
FRONT = ROOT / "wp-content" / "themes" / "watphou-travels" / "front-page.php"
STYLE = ROOT / "wp-content" / "themes" / "watphou-travels" / "style.css"
FUNCS = ROOT / "wp-content" / "themes" / "watphou-travels" / "functions.php"

TRANSPARENT = "R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw=="
DARK = "R0lGODlhAQABAIAAAAUEBAAAACwAAAAAAQABAAACAkQBADs="


def main() -> int:
    js = JS.read_text(encoding="utf-8")
    css = CSS.read_text(encoding="utf-8")
    front = FRONT.read_text(encoding="utf-8")
    style = STYLE.read_text(encoding="utf-8")
    funcs = FUNCS.read_text(encoding="utf-8")

    assert TRANSPARENT not in js, "hero still uses a transparent placeholder"
    assert TRANSPARENT not in front, "front-page still uses a transparent placeholder"
    assert DARK in js and DARK in front
    assert "el.decode()" in js
    assert "transitionend" in js
    assert "parseFloat(window.getComputedStyle(el).opacity)" in js
    assert "fading" in js
    assert "keepAround(idx)" in js
    assert ".wpt-hero--home" in css and "background: #1c1917" in css
    assert "opacity .9s ease-in-out" in css
    assert "2.5.5" in style and "2.5.5" in funcs
    print("ok hero crossfade")
    return 0


if __name__ == "__main__":
    sys.exit(main())
