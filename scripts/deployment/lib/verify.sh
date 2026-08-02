#!/usr/bin/env bash
set -Eeuo pipefail

verify_production() {
  PHASE=verify
  [ "$DRY_RUN" -eq 1 ] && { dry 'would verify production WordPress, pages, plugins, languages, REST endpoint, and HTTP smoke checks'; return 0; }
  [ "$(remote_value option get blog_public)" = 1 ] || die 7 'production remains noindex'
  [ "$(remote_value option get stylesheet)" = rentacar-venezia-v2 ] || die 7 'owned theme is not active'
  wp_remote plugin is-active rentacar-core || die 7 'rentacar-core is inactive'
  wp_remote plugin is-active polylang || die 7 'Polylang is inactive'
  local p polylang_count
  for p in sitepress-multilingual-cms wpml-string-translation wpml-translation-management acfml; do
    wp_remote plugin is-active "$p" >/dev/null 2>&1 && die 7 "WPML plugin active: $p" || true
  done
  wp_remote post list --post_type=cars --format=count | tail -n 1 | grep -Eq '^[1-9][0-9]*$' || die 7 'vehicle posts missing'
  polylang_count=$(wp_remote eval 'echo function_exists("pll_languages_list") ? count(pll_languages_list()) : 0;' | tail -n 1)
  case "$polylang_count" in [4-9]|[1-9][0-9]*) ;; *) die 7 'expected Polylang languages missing';; esac
  local maintenance_state
  maintenance_state=$(wp_remote maintenance-mode status 2>&1 || true)
  case "$maintenance_state" in *'is active'*) warn 'maintenance mode is active; public HTTP and browser smoke checks will run after reopening';; *) verify_public_http;; esac
  ok 'production internal verification passed'
}

verify_public_http() {
  [ "$DRY_RUN" -eq 1 ] && { dry 'would run public HTTP and browser smoke checks'; return 0; }
  curl -fsSI "$PRODUCTION_URL/" | head -1 | grep -q ' 200 ' || die 7 'homepage HTTP smoke test failed'
  curl -fsSI "$PRODUCTION_URL/flotta/" | head -1 | grep -q ' 200 ' || die 7 'fleet HTTP smoke test failed'
  if [ "$RUN_BROWSER_TESTS" -eq 1 ] && [ "$SKIP_BROWSER_TESTS" -ne 1 ]; then
    (cd "$PROJECT_ROOT" && PLAYWRIGHT_BASE_URL="$PRODUCTION_URL" npm run test:browser:production) || die 7 'production browser smoke tests failed'
  fi
  ok 'production public smoke checks passed'
}

show_status() {
  local php_version
  php_version=$(remote_exec 'php -r "echo PHP_VERSION;"')
  printf 'Target: %s\nSSH: %s@%s:%s\nRoot: %s\nHome: %s\nSite URL: %s\nDatabase: %s\nPHP: %s\nWordPress: %s\nTheme: %s\nMaintenance: %s\nblog_public: %s\n' "$PRODUCTION_URL" "$PRODUCTION_SSH_USER" "$PRODUCTION_SSH_HOST" "$PRODUCTION_SSH_PORT" "$PRODUCTION_ROOT" "$REMOTE_HOME" "$REMOTE_SITEURL" "$REMOTE_DB" "$php_version" "$(wp_remote core version)" "$(remote_value option get stylesheet)" "$(wp_remote maintenance-mode status | tail -1)" "$(remote_value option get blog_public)"
}
