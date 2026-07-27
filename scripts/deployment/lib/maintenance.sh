#!/usr/bin/env bash
set -Eeuo pipefail
enable_maintenance() { local state; [ "$DRY_RUN" -eq 1 ] && { dry 'would enable maintenance mode'; return; }; state=$(wp_remote maintenance-mode status 2>&1 || true); case "$state" in *'is active'*) warn 'maintenance mode is already active; preserving it until verification succeeds'; return;; esac; wp_remote maintenance-mode activate || die 6 'could not enable maintenance mode'; }
disable_maintenance() { [ "$DRY_RUN" -eq 1 ] && return; wp_remote maintenance-mode deactivate || die 7 'could not disable maintenance mode'; }
flush_caches() { [ "$DRY_RUN" -eq 1 ] && { dry 'would flush rewrites and caches'; return; }; wp_remote rewrite flush; wp_remote cache flush || warn 'generic cache flush unavailable'; wp_remote litespeed-purge all || warn 'LiteSpeed cache purge unavailable'; }
