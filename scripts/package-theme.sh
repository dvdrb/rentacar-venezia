#!/usr/bin/env sh
set -eu

ROOT=$(CDPATH= cd -- "$(dirname -- "$0")/.." && pwd)
ARCHIVE="$ROOT/dist/rentacar-venezia-v2.zip"

mkdir -p "$ROOT/dist"
rm -f "$ARCHIVE"
(
    cd "$ROOT/theme"
    zip -rq "$ARCHIVE" rentacar-venezia-v2 -x '*/node_modules/*' '*/tests/*' '*/.DS_Store'
)
unzip -Z1 "$ARCHIVE" | grep -qx 'rentacar-venezia-v2/style.css'
