#!/usr/bin/env bash
set -euo pipefail

project_root=$(cd "$(dirname "$0")/.." && pwd)
wp_path=${RENTACAR_WP_PATH:-"$HOME/Local Sites/rentacar-venezia-local/app/public"}
wp_command=${RENTACAR_WP_COMMAND:-}

if [ ! -f "$wp_path/wp-load.php" ]; then
  printf 'Local WordPress root is unavailable: %s\nSet RENTACAR_WP_PATH to the LocalWP app/public directory.\n' "$wp_path" >&2
  exit 1
fi

if [ -z "$wp_command" ]; then
  if command -v wp >/dev/null 2>&1; then
    wp_args=(wp)
  else
    php_bin=${RENTACAR_PHP_BIN:-"$HOME/Library/Application Support/Local/lightning-services/php-8.3.30+1/bin/darwin-arm64/bin/php"}
    php_ini=${RENTACAR_PHP_INI:-"$HOME/Library/Application Support/Local/run/PAWVmkfpE/conf/php/php.ini"}
    wp_cli=/Applications/Local.app/Contents/Resources/extraResources/bin/wp-cli/wp-cli.phar
    if [ ! -x "$php_bin" ] || [ ! -f "$php_ini" ] || [ ! -f "$wp_cli" ]; then
      printf 'WP-CLI is unavailable. Set RENTACAR_WP_COMMAND or LocalWP PHP overrides.\n' >&2
      exit 1
    fi
    wp_args=("$php_bin" -c "$php_ini" "$wp_cli")
  fi
else
  # An override is one executable command path. This keeps the runner safe
  # around paths containing spaces; use a wrapper for commands needing flags.
  wp_args=("$wp_command")
fi

export RENTACAR_REPORT_DIR="${RENTACAR_REPORT_DIR:-$project_root/docs/generated}"
export HTTP_HOST=${HTTP_HOST:-rentacar-venezia-local.local}
"${wp_args[@]}" --path="$wp_path" eval-file "$project_root/tools/local-wp/seo-audit.php"
