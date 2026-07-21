#!/usr/bin/env sh
set -eu
mkdir -p dist
rm -f dist/rentacar-venezia-v2.zip
zip -rq dist/rentacar-venezia-v2.zip theme/rentacar-venezia-v2 -x '*/node_modules/*' '*/tests/*' '*/.DS_Store'
