#!/usr/bin/env sh
set -eu
PHP_BIN="${PHP_BIN:-php}"
find theme plugin -name '*.php' -type f -print0 | xargs -0 -n1 "$PHP_BIN" -l
