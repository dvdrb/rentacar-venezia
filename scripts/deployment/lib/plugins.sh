#!/usr/bin/env bash
set -Eeuo pipefail
is_wpml_plugin() { case "$1" in sitepress-multilingual-cms|wpml-string-translation|wpml-translation-management|acfml|*wpml*) return 0;; *) return 1;; esac; }
sync_active_plugins() { [ "$SYNC_ACTIVE_THIRD_PARTY_PLUGINS" -eq 1 ] || return; local p; for p in $(wp_local plugin list --status=active --field=name); do [ "$p" = rentacar-core ] && continue; is_wpml_plugin "$p" && die 6 "retired WPML plugin is active locally: $p"; [ -d "$LOCAL_WP_ROOT/wp-content/plugins/$p" ] || die 6 "active local plugin code missing: $p"; dry "would synchronize active plugin $p"; if [ "$DRY_RUN" -eq 0 ]; then rsync_owned "$LOCAL_WP_ROOT/wp-content/plugins/$p" "$PRODUCTION_ROOT/wp-content/plugins/$p"; fi; done; }
