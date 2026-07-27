# LocalWP to Hostinger production deployment

This tool migrates the verified LocalWP site to the verified Hostinger WordPress site. No production deployment was performed while creating it.

## Important safety rules

`cutover` replaces the verified production database. Put the site in a content freeze first: reservations, users, editor changes and uploads made on production after the local export can be lost. Backups are outside `public_html`. `code` never changes the database. `media` preserves production-only files.

Prerequisites: macOS Bash 3.2+, Git, Node/npm, WP-CLI able to access LocalWP, SSH access to Hostinger, and `rsync`, `tar`, `gzip`, and WP-CLI on Hostinger. The current repository uses `pnpm-lock.yaml`, so install pnpm for the frozen dependency preflight (the tool uses `npm ci` automatically if a `package-lock.json` is later adopted). Configure an SSH key or ssh-agent where possible. Hostinger SSH passwords are never stored.

## One-time setup

From the repository:

```sh
cd "/Users/dvdrb/Projects/rentacar-venezia"
./scripts/deploy-production setup
```

Paste only `ssh [-p PORT] USER@HOST`; arbitrary SSH options are rejected. Supply the absolute Hostinger WordPress root and expected database name. Setup verifies that the remote `home`, `siteurl`, root, and database exactly match `https://rentacarvenezia.it`, then writes `.env.production` mode 600. Use `scripts/deployment/production.example.env` as the key reference; it contains placeholders only.

LocalWP WP-CLI must satisfy `wp --path=LOCAL_WP_ROOT core is-installed`. In Local, use the site’s **Open Site Shell** command to discover the full executable paths with `command -v wp` and `command -v php`; enter those paths as `LOCAL_WP_COMMAND` and `LOCAL_PHP_BIN` in setup. A wrapper executable is also supported. Do not copy `wp-config.php`, database credentials, WordPress core, Local caches, or Local mail guards.

## Cutover

```sh
./scripts/deploy-production cutover --dry-run
./scripts/deploy-production cutover
./scripts/deploy-production verify
```

Dry-run connects and performs all target checks but does not write remotely, lock, import, enter maintenance, or alter caches. A real cutover requires typing the hostname, `REPLACE-PRODUCTION`, and the configured database name. It backs up the database, wp-config, wp-content, owned theme/plugin and inventories; validates archives; enables maintenance; deploys owned code; optionally syncs active allowed plugins; incrementally syncs uploads; imports the LocalWP database; performs serialized-safe URL replacement excluding GUIDs; restores required production settings; flushes caches; verifies; then reopens the site.

If table prefixes differ, wp-config is backed up and only its `table_prefix` is changed through WP-CLI before import. If that cannot be done safely, cutover stops. WPML and WPML bridge plugins are rejected; Polylang, Yoast and authorized ACF/ACF Pro are allowed. Do not copy a licensed plugin unless its licence permits the production use.

## Regular releases and media

```sh
./scripts/deploy-production code
./scripts/deploy-production media
```

Code deploys only `rentacar-venezia-v2` and `rentacar-core`, scoped `rsync --delete` only within those directories. Failed code verification can restore those backups automatically unless configured otherwise. Media uses resumable incremental rsync and retains overwritten remote files under the deployment backup; it never deletes remote-only uploads.

## Verification, backups, and recovery

```sh
./scripts/deploy-production status
./scripts/deploy-production backups
./scripts/deploy-production rollback DEPLOYMENT_ID
```

Verification is read-only (apart from local reports), checks target identity, theme, required plugins, no WPML, vehicles, languages, and HTTP/browser smoke checks. Production browser validation uses a dedicated read-only homepage/Fleet smoke suite; it does not execute the LocalWP acceptance suite or submit forms. Automated verification does not send reservation mail. Configure a controlled recipient separately before any real email delivery test; otherwise review mail manually. Rollback creates a fresh pre-rollback backup, requires the hostname, `ROLLBACK-PRODUCTION`, and database name, restores the matching archive, and keeps maintenance enabled if verification fails.

LiteSpeed purge is attempted as an optional cache operation. Preserve Hostinger-only plugins through `REMOTE_PRESERVE_PLUGINS`; production mail/SMTP settings require manual review because they are environment-specific. Troubleshoot target mismatch by correcting configuration—not by bypassing a safety check. For failed backup, checksum, prefix, or verification steps, do not reopen the site; use the printed rollback command or Hostinger’s independent backups.

Exit codes: 0 success, 2 usage/configuration, 3 local preflight, 4 remote safety, 5 backup, 6 deployment, 7 verification, 8 rollback.
