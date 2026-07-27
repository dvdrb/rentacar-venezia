#!/usr/bin/env bash
set -Eeuo pipefail
sync_media() { PHASE=media; local args=(-az --partial --stats --exclude cache --exclude caches --exclude backup --exclude backups --exclude '*.tmp' --backup --backup-dir="$REMOTE_BACKUP_ROOT/$DEPLOYMENT_ID/uploads-overwritten"); [ "$DRY_RUN" -eq 1 ] && args+=(--dry-run); args+=(-e "$(rsync_ssh_command)"); dry 'uploads sync preserves remote-only files; it never uses --delete'; rsync "${args[@]}" "$LOCAL_WP_ROOT/wp-content/uploads/" "$PRODUCTION_SSH_USER@$PRODUCTION_SSH_HOST:$PRODUCTION_ROOT/wp-content/uploads/"; }
verify_media() { [ "$DRY_RUN" -eq 1 ] && return; remote_exec "test -d $(shell_quote "$PRODUCTION_ROOT/wp-content/uploads")" || die 7 'uploads directory unavailable after sync'; }
