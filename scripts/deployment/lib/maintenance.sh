#!/usr/bin/env bash
set -Eeuo pipefail
enable_maintenance() { [ "$DRY_RUN" -eq 1 ] && { dry 'would enable maintenance mode'; return; }; wp_remote maintenance-mode activate || die 6 'could not enable maintenance mode'; }
disable_maintenance() { [ "$DRY_RUN" -eq 1 ] && return; wp_remote maintenance-mode deactivate || die 7 'could not disable maintenance mode'; }
flush_caches() { [ "$DRY_RUN" -eq 1 ] && { dry 'would flush rewrites and caches'; return; }; wp_remote rewrite flush; wp_remote cache flush || warn 'generic cache flush unavailable'; wp_remote litespeed-purge all || warn 'LiteSpeed cache purge unavailable'; }
