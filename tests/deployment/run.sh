#!/usr/bin/env bash
set -Eeuo pipefail
ROOT=$(CDPATH= cd -- "$(dirname -- "$0")/../.." && pwd)
CLI="$ROOT/scripts/deploy-production"
pass=0; fail=0
expect() { local name=$1; shift; if "$@" >/dev/null 2>&1; then pass=$((pass+1)); printf 'ok - %s\n' "$name"; else fail=$((fail+1)); printf 'not ok - %s\n' "$name"; fi; }
expect 'help works' "$CLI" help
if "$CLI" unknown >/dev/null 2>&1; then fail=$((fail+1)); printf 'not ok - unknown command rejected\n'; else pass=$((pass+1)); printf 'ok - unknown command rejected\n'; fi
if "$CLI" --config /definitely/missing status >/dev/null 2>&1; then fail=$((fail+1)); printf 'not ok - missing config rejected\n'; else pass=$((pass+1)); printf 'ok - missing config rejected\n'; fi
setup_output=$(printf '\n' | "$CLI" setup 2>&1 || true)
if printf '%s' "$setup_output" | grep -q 'Hostinger SSH command'; then pass=$((pass+1)); printf 'ok - setup does not require existing config\n'; else fail=$((fail+1)); printf 'not ok - setup does not require existing config\n'; fi
for file in "$ROOT/scripts/deployment/lib/"*.sh; do bash -n "$file" || exit 1; done
grep -q 'REPLACE-PRODUCTION' "$ROOT/scripts/deployment/lib/safety.sh" && pass=$((pass+1)) || fail=$((fail+1))
grep -q -- '--delete' "$ROOT/scripts/deployment/lib/code.sh" && ! grep -q -- 'args+=(--delete)' "$ROOT/scripts/deployment/lib/media.sh" && pass=$((pass+1)) || fail=$((fail+1))
grep -q 'sitepress-multilingual-cms' "$ROOT/scripts/deployment/lib/plugins.sh" && pass=$((pass+1)) || fail=$((fail+1))
grep -q 'discover_remote_roots' "$ROOT/scripts/deployment/lib/config.sh" && pass=$((pass+1)) || fail=$((fail+1))
grep -q 'proc_open/proc_close' "$ROOT/scripts/deployment/lib/remote.sh" && pass=$((pass+1)) || fail=$((fail+1))
grep -q 'reusing saved setup values' "$ROOT/scripts/deployment/lib/config.sh" && pass=$((pass+1)) || fail=$((fail+1))
grep -q 'get_var' "$ROOT/scripts/deployment/lib/remote.sh" && pass=$((pass+1)) || fail=$((fail+1))
grep -q 'LocalWP WP-CLI executable' "$ROOT/scripts/deployment/lib/config.sh" && pass=$((pass+1)) || fail=$((fail+1))
grep -q 'ControlMaster=auto' "$ROOT/scripts/deployment/lib/remote.sh" && pass=$((pass+1)) || fail=$((fail+1))
grep -q "'/tmp/rentacar-venezia-ssh'" "$ROOT/scripts/deployment/lib/remote.sh" && pass=$((pass+1)) || fail=$((fail+1))
grep -q -- '--skip-plugins --skip-themes plugin list' "$ROOT/scripts/deployment/lib/plugins.sh" && pass=$((pass+1)) || fail=$((fail+1))
grep -q 'HTTP_HOST=' "$ROOT/scripts/deployment/lib/local.sh" && pass=$((pass+1)) || fail=$((fail+1))
grep -q 'SELECT @@socket' "$ROOT/scripts/deployment/lib/local.sh" && pass=$((pass+1)) || fail=$((fail+1))
grep -q 'resolve_remote_backup_root' "$ROOT/scripts/deployment/lib/remote.sh" && pass=$((pass+1)) || fail=$((fail+1))
grep -q '__RCV_REMOTE_HOME__' "$ROOT/scripts/deployment/lib/remote.sh" && pass=$((pass+1)) || fail=$((fail+1))
grep -q -- '--files-from=/dev/null' "$ROOT/scripts/deployment/lib/backup.sh" && pass=$((pass+1)) || fail=$((fail+1))
grep -q 'sha256sum' "$ROOT/scripts/deployment/lib/backup.sh" && pass=$((pass+1)) || fail=$((fail+1))
grep -q 'already active' "$ROOT/scripts/deployment/lib/maintenance.sh" && pass=$((pass+1)) || fail=$((fail+1))
grep -q 'theme rsync failed' "$ROOT/scripts/deployment/lib/code.sh" && pass=$((pass+1)) || fail=$((fail+1))
grep -q 'fi; return 0' "$ROOT/scripts/deployment/lib/common.sh" && pass=$((pass+1)) || fail=$((fail+1))
grep -q 'SCP_CONNECTION_ARGS=(-P' "$ROOT/scripts/deployment/lib/remote.sh" && pass=$((pass+1)) || fail=$((fail+1))
grep -q 'database archive upload failed' "$ROOT/scripts/deployment/lib/database.sh" && pass=$((pass+1)) || fail=$((fail+1))
grep -q 'remote_file_sha256' "$ROOT/scripts/deployment/lib/database.sh" && pass=$((pass+1)) || fail=$((fail+1))
grep -q -- '--format=table' "$ROOT/scripts/deployment/lib/database.sh" && pass=$((pass+1)) || fail=$((fail+1))
grep -q 'pll_languages_list' "$ROOT/scripts/deployment/lib/verify.sh" && pass=$((pass+1)) || fail=$((fail+1))
grep -q 'verify_public_http' "$ROOT/scripts/deploy-production" && pass=$((pass+1)) || fail=$((fail+1))
grep -q 'test:browser:production' "$ROOT/scripts/deployment/lib/verify.sh" && pass=$((pass+1)) || fail=$((fail+1))
grep -q 'RankMath\\Sitemap\\Cache::invalidate_storage' "$ROOT/scripts/deployment/lib/maintenance.sh" && pass=$((pass+1)) || fail=$((fail+1))
grep -q 'minify' "$ROOT/vite.config.ts" && grep -q 'analytics:' "$ROOT/vite.config.ts" && grep -q 'style:' "$ROOT/vite.config.ts" && pass=$((pass+1)) || fail=$((fail+1))
grep -q 'PRODUCTION_URL/fleet/' "$ROOT/scripts/deployment/lib/verify.sh" && pass=$((pass+1)) || fail=$((fail+1))
grep -q 'check:assets' "$ROOT/scripts/deployment/lib/local.sh" && pass=$((pass+1)) || fail=$((fail+1))
grep -q 'deploying complete theme asset build before its manifest' "$ROOT/scripts/deployment/lib/code.sh" && pass=$((pass+1)) || fail=$((fail+1))
grep -q 'SCP_CONNECTION_ARGS.*return 0' "$ROOT/scripts/deployment/lib/remote.sh" && pass=$((pass+1)) || fail=$((fail+1))
printf '%s deployment checks passed; %s failed\n' "$pass" "$fail"; [ "$fail" -eq 0 ]
