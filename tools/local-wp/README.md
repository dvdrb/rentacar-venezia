# LocalWP tooling

All commands in this folder target LocalWP only. They never use production URLs or credentials.

Run a read-only audit:

```sh
wp eval-file tools/local-wp/audit-site.php
wp eval-file tools/local-wp/content-map.php
wp eval-file tools/local-wp/validate-head.php
wp eval-file tools/local-wp/validate-translations.php
wp eval-file tools/local-wp/optimize-seo.php
```

Export a restorable content snapshot outside the repository:

```sh
wp eval-file tools/local-wp/export-content-snapshots.php -- --output=/absolute/path/content.json
```

`provision-site.php` is intentionally an explicit local operation. Run it only after a database backup and review its dry-run output. Apply it with `RENTACAR_LOCAL_APPLY=1 wp eval-file tools/local-wp/provision-site.php`. The LocalWP theme and plugin targets are repository symlinks; `sync-to-local.sh` verifies those links and runs the production asset build without copying files.

`optimize-seo.php` writes tailored Yoast titles and descriptions only for the approved current pages and homepage translations. It records the generated values, so later owner edits are preserved. Review its dry run, then apply it with `RENTACAR_LOCAL_APPLY=1 wp eval-file tools/local-wp/optimize-seo.php`.
