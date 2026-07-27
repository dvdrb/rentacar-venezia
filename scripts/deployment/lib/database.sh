#!/usr/bin/env bash
set -Eeuo pipefail

remote_file_sha256() {
  local file=$1
  remote_exec "if command -v shasum >/dev/null 2>&1; then shasum -a 256 $(shell_quote "$file"); elif command -v sha256sum >/dev/null 2>&1; then sha256sum $(shell_quote "$file"); else exit 127; fi" | tail -n 1 | awk '{print $1}'
}

export_and_import_database() {
  PHASE=database
  record_local_metadata
  local sql="$DEPLOY_RUNTIME/local.sql" gz="$DEPLOY_RUNTIME/local.sql.gz" checksum remote_checksum
  info 'exporting LocalWP database'
  export_local_database "$sql" || die 6 'LocalWP database export failed'
  info 'compressing local database archive'
  gzip -c "$sql" > "$gz" || die 6 'local database archive compression failed'
  info 'validating local database archive'
  gzip -t "$gz" || die 6 'local database archive is invalid'
  checksum=$(sha256 "$gz") || die 6 'cannot calculate local database checksum'
  if [ "$DRY_RUN" -eq 1 ]; then
    dry "would upload $gz to $(remote_backup_dir), verify checksum, reset only $PRODUCTION_EXPECTED_DB_NAME, import, and execute serialized-safe search-replace dry-runs"
    wp_remote search-replace "$LOCAL_HOME" "$PRODUCTION_URL" --all-tables-with-prefix --precise --skip-columns=guid --dry-run
    return
  fi
  info 'uploading compressed database archive'
  scp_args
  scp "${SCP_CONNECTION_ARGS[@]}" "$gz" "$PRODUCTION_SSH_USER@$PRODUCTION_SSH_HOST:$(remote_backup_dir)/database-local.sql.gz" || die 6 'database archive upload failed'
  info 'verifying uploaded database checksum'
  remote_checksum=$(remote_file_sha256 "$(remote_backup_dir)/database-local.sql.gz") || die 6 'cannot calculate remote database checksum'
  [ "$checksum" = "$remote_checksum" ] || die 6 'database upload checksum mismatch'
  if [ "$LOCAL_PREFIX" != "$REMOTE_PREFIX" ]; then
    info 'reconciling table prefix'
    remote_exec "cd $(shell_quote "$PRODUCTION_ROOT") && $REMOTE_WP_COMMAND config set table_prefix $(shell_quote "$LOCAL_PREFIX") --type=variable" || die 6 'TABLE_PREFIX_RECONCILIATION_REQUIRED'
  fi
  info 'resetting and importing verified production database'
  remote_exec "cd $(shell_quote "$PRODUCTION_ROOT") && test \"\$($REMOTE_WP_COMMAND db query 'SELECT DATABASE()' --skip-column-names)\" = $(shell_quote "$PRODUCTION_EXPECTED_DB_NAME") && $REMOTE_WP_COMMAND db reset --yes && gzip -dc $(shell_quote "$(remote_backup_dir)/database-local.sql.gz") | $REMOTE_WP_COMMAND db import -" || die 6 'database import failed'
  wp_remote option update home "$PRODUCTION_URL" || die 6 'cannot set production home URL'
  wp_remote option update siteurl "$PRODUCTION_URL" || die 6 'cannot set production site URL'
  wp_remote core is-installed || die 6 'imported database is not a WordPress installation'
  local url
  for url in "$LOCAL_HOME" "$LOCAL_WP_URL"; do
    [ -n "$url" ] || continue
    wp_remote search-replace "$url" "$PRODUCTION_URL" --all-tables-with-prefix --precise --skip-columns=guid --dry-run || die 6 'URL replacement dry-run failed'
    wp_remote search-replace "$url" "$PRODUCTION_URL" --all-tables-with-prefix --precise --skip-columns=guid || die 6 'URL replacement failed'
  done
  local remaining_urls
  remaining_urls=$(remote_exec "cd $(shell_quote "$PRODUCTION_ROOT") && $REMOTE_WP_COMMAND db search '.local' --all-tables-with-prefix --format=table") || die 7 'cannot scan for remaining LocalWP URLs'
  if printf '%s\n' "$remaining_urls" | grep -Fq '.local'; then
    die 7 'remaining LocalWP URLs detected'
  fi
}

restore_production_settings() {
  [ "$DRY_RUN" -eq 1 ] && return
  wp_remote option update home "$PRODUCTION_URL"
  wp_remote option update siteurl "$PRODUCTION_URL"
  wp_remote option update blog_public 1
  wp_remote theme activate rentacar-venezia-v2
  wp_remote plugin activate rentacar-core polylang || die 6 'required plugin activation failed'
  local p
  for p in sitepress-multilingual-cms wpml-string-translation wpml-translation-management acfml; do
    wp_remote plugin deactivate "$p" >/dev/null 2>&1 || true
  done
}
