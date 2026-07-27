#!/usr/bin/env bash
set -Eeuo pipefail
is_wpml_plugin() { case "$1" in sitepress-multilingual-cms|wpml-string-translation|wpml-translation-management|acfml|*wpml*) return 0;; *) return 1;; esac; }
sync_active_plugins() { [ "$SYNC_ACTIVE_THIRD_PARTY_PLUGINS" -eq 1 ] || return; local p inventory; inventory=$(wp_local --skip-plugins --skip-themes plugin list --status=active --field=name) || die 6 'cannot list active LocalWP plugins'; while IFS= read -r p; do [ -z "$p" ] && continue; case "$p" in *[!A-Za-z0-9._-]*) die 6 "unexpected output while listing active LocalWP plugins: $p";; esac; [ "$p" = rentacar-core ] && continue; is_wpml_plugin "$p" && die 6 "retired WPML plugin is active locally: $p"; [ -d "$LOCAL_WP_ROOT/wp-content/plugins/$p" ] || die 6 "active local plugin code missing: $p"; dry "would synchronize active plugin $p"; if [ "$DRY_RUN" -eq 0 ]; then rsync_owned "$LOCAL_WP_ROOT/wp-content/plugins/$p" "$PRODUCTION_ROOT/wp-content/plugins/$p"; fi; done <<EOF
$inventory
EOF
}
