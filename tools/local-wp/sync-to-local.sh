#!/usr/bin/env bash
set -euo pipefail

repo_root="$(cd "$(dirname "$0")/../.." && pwd)"
local_root="${LOCALWP_PUBLIC_ROOT:-/Users/dvdrb/Local Sites/rentacar-venezia-local/app/public}"

for source in theme/rentacar-venezia-v2 plugin/rentacar-core; do
  if [[ "$source" == theme/* ]]; then
    target="$local_root/wp-content/themes/rentacar-venezia-v2"
  else
    target="$local_root/wp-content/plugins/rentacar-core"
  fi
  if [[ ! -e "$target" ]]; then
    printf 'Missing LocalWP target: %s\n' "$target" >&2
    exit 1
  fi
  if [[ "$(realpath "$target")" != "$repo_root/$source" ]]; then
    printf 'Refusing to sync: %s is not the expected repository symlink.\n' "$target" >&2
    exit 1
  fi
done

npm --prefix "$repo_root" run build
printf 'LocalWP uses the verified repository symlinks; no copy was needed.\n'
