#!/usr/bin/env bash
set -Eeuo pipefail
log() { printf '[%s] %s\n' "$1" "$2" >&2; }
info() { log INFO "$*"; }; warn() { log WARN "$*"; }; ok() { log OK "$*"; }
die() { local code=$1; shift; log ERROR "$*"; exit "$code"; }
dry() { [ "${DRY_RUN:-0}" -eq 1 ] && log DRY-RUN "$*"; }
command_exists() { command -v "$1" >/dev/null 2>&1; }
timestamp() { date '+%Y%m%d-%H%M%S'; }
absolute_path() { (cd "$(dirname -- "$1")" && printf '%s/%s\n' "$(pwd -P)" "$(basename -- "$1")"); }
sha256() { if command_exists shasum; then shasum -a 256 "$1" | awk '{print $1}'; else sha256sum "$1" | awk '{print $1}'; fi; }
safe_tmpdir() { mktemp -d "${TMPDIR:-/tmp}/rentacar-deploy.XXXXXX"; }
normalize_url() { local u=${1%/}; case "$u" in http://www.*) printf 'https://%s\n' "${u#http://www.}";; https://www.*) printf 'https://%s\n' "${u#https://www.}";; http://*) printf 'https://%s\n' "${u#http://}";; https://*) printf '%s\n' "$u";; *) return 1;; esac; }
shell_quote() { printf "'%s'" "$(printf '%s' "$1" | sed "s/'/'\\\\''/g")"; }
json_escape() { printf '%s' "$1" | sed 's/\\/\\\\/g; s/"/\\"/g'; }
on_error() { local ec=$1 cmd=$2; [ "$ec" -eq 0 ] && return; log ERROR "phase=${PHASE:-unknown}; command failed (exit $ec)"; [ -n "${DEPLOYMENT_ID:-}" ] && log ERROR "deployment=$DEPLOYMENT_ID; rollback: ./scripts/deploy-production rollback $DEPLOYMENT_ID"; }
