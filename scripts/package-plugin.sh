#!/usr/bin/env sh
set -eu

ROOT=$(CDPATH= cd -- "$(dirname -- "$0")/.." && pwd)
ARCHIVE="$ROOT/dist/rentacar-core.zip"

mkdir -p "$ROOT/dist"
rm -f "$ARCHIVE"
(
    cd "$ROOT/plugin"
    zip -rq "$ARCHIVE" rentacar-core -x '*/node_modules/*' '*/tests/*' '*/.DS_Store'
)
unzip -Z1 "$ARCHIVE" | grep -qx 'rentacar-core/rentacar-core.php'
