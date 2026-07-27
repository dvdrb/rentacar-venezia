# Production deployment implementation report

## Scope

Added `./scripts/deploy-production` on branch `feat/production-deployment-tool`. It provides setup, cutover, code, media, verify, status, backups, rollback, help, global dry-run and safety options. No production deployment was performed.

## Safety model

The tool requires an exact canonical production URL, reachable configured SSH host, non-broad WordPress root containing `wp-config.php`, matching remote `home`/`siteurl`, expected database name, table prefix, and writable owned paths before remote mutation. Cutover/rollback require typed confirmations. Local and remote atomic directory locks prevent concurrent mutations. Secrets remain in ignored `.env.production`, written mode 600.

## Execution model

Cutover backs up and validates production database/wp-config/wp-content/owned code, enables maintenance, deploys repository-owned code, rejects legacy WPML, safely syncs allowed active plugins and media, exports/imports LocalWP through WP-CLI, reconciles table prefixes through WP-CLI, uses serialized-safe URL replacement excluding GUIDs, restores key production settings, flushes caches, verifies, and only then disables maintenance. Code deployments do not import data; media deployments never use `--delete`. Backup reports are ignored under `runtime/` and backup retention honours `.protected`.

## Validation planned/run

Completed: Bash syntax checks, `npm run check`, `npm run build`, `npm run test:deployment`, `./scripts/deploy-production help`, and `git diff --check`. `npm ci` could not run because the repository intentionally has no `package-lock.json`; the existing `pnpm-lock.yaml` is preserved and the deployment preflight uses frozen pnpm when available. PHP lint could not run because `php` is unavailable in this local environment. Fixture tests cover command parsing and static safety invariants; they do not contact Hostinger.

## Remaining manual requirements

Hostinger SSH user/host/port, production root, expected database name, authorized third-party plugin licences, remote-only plugin preservation list, LocalWP WP-CLI access, production-safe browser suite configuration, controlled email test recipient, and a maintenance content freeze are still required.
