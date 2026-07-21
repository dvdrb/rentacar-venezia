# Rentacar Venezia redesign workspace

This repository is the isolated custom-code workspace for the LocalWP clone at `/Users/dvdrb/Local Sites/rentacar-venezia-local/app/public`. Production deployment is explicitly out of scope for local development tasks.

## Structure

- `legacy/active-theme/`: read-only legacy reference.
- `theme/rentacar-venezia-v2/`: new theme source.
- `plugin/rentacar-core/`: new site-specific plugin source.
- `docs/`, `tests/`, `scripts/`: audit, verification, and tooling materials.

LocalWP loads the new code directly through these links:

- `wp-content/themes/rentacar-venezia-v2` → `theme/rentacar-venezia-v2`
- `wp-content/plugins/rentacar-core` → `plugin/rentacar-core`

Verify with `ls -l` at the LocalWP destinations. Only the new theme, new plugin, docs, tests, and scripts are owned here. Never commit secrets, `wp-config.php`, uploads, database dumps, backups, archives, customer data, mailbox data, generated caches, dependencies, or environment files.

Future package ZIPs must be generated into an ignored directory from the owned `theme/` or `plugin/` source; never package or deploy the whole LocalWP installation.

Required sequence: approve audit; confirm business rules; scaffold theme/plugin properly; implement vehicle repository; global shell; fleet; vehicle pages; enquiry workflow; multilingual QA; SEO; performance and accessibility; testing. Production deployment is not part of this workspace.
