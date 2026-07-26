# Multilingual migration utilities

Run these tools only against the LocalWP clone after a database backup.

- `repair-polylang-relations.php` restores deterministic post and term translation groups from preserved WPML records.
- `synchronize-vehicle-fields.php` reports technical vehicle-field differences by default. Add `--apply` only after reviewing its summary; it copies the approved technical policy fields and featured image, never editorial content or SEO metadata.

Example:

```sh
wp eval-file tools/multilingual/synchronize-vehicle-fields.php -- --apply
```
