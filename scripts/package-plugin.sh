#!/usr/bin/env sh
set -eu
mkdir -p dist
rm -f dist/rentacar-core.zip
zip -rq dist/rentacar-core.zip plugin/rentacar-core -x '*/node_modules/*' '*/tests/*' '*/.DS_Store'
