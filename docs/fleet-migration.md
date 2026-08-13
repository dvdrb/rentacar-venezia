# Fleet migration

This one-time importer updates existing `cars` posts. It never creates or
deletes vehicles, translations, attachments, or price rules. Take a database
and uploads backup before an apply run.

## Current implementation findings

- Vehicles use the `cars` custom post type. The active fleet is rendered by
  `theme/rentacar-venezia-v2/page-templates/template-fleet.php`, vehicle cards
  by `template-parts/vehicle/card.php`, and single vehicles by `single-cars.php`.
- The established ACF/custom-meta fields are `gallery`, `gearbox`,
  `max_passagers`, `doors`, `air_conditioning`, `price_1_days_1`,
  `price_1_days_2`, `price`, `price_2_days_1`, `price_2_days_2`, `price2`,
  `price_3_days_1`, `price_3_days_2`, `price3`, and `price4`.
- `engine` is the simple `_rentacar_engine` post-meta value. There is no
  vehicle taxonomy. Fuel is the controlled `_rentacar_powertrain` value (`petrol`, `diesel`, `hybrid`,
  `plug_in_hybrid`, `electric`, or `other`). Gallery data is an ACF repeater;
  this migration leaves it untouched and only uses the native featured image.
- The fourth price tier is open-ended. `_rentacar_starting_price` is derived
  from valid pricing bands by Rentacar Core; `starting_price` is only checked,
  never directly written. The catalogue sorts by its displayed lowest valid
  price, then menu order, title, and ID.
- Polylang owns language and translation relationships. The importer changes
  only the matched record and temporarily suppresses the existing technical
  field synchronizer during `wp_update_post()`; engine values are likewise
  intentionally record-specific unless an explicit translation mapping is run.
- Rank Math SEO fields are `rank_math_title` and `rank_math_description`.
  When Rank Math’s Redirections module is active, changed vehicle slugs get an
  exact 301 through its existing API. Without that module, a slug is skipped.

## CSV columns

Use [fleet-import-template.csv](fleet-import-template.csv) as the starting
point. `post_id` is the preferred identifier; `current_slug` is the fallback.
When matching by slug in a multilingual fleet, include `language` to avoid an
ambiguous match. Empty fields leave existing values intact. `UNCONFIRMED`,
`UNKNOWN`, `TBD`, and `N/A` also leave the individual field unchanged.

The complete pricing set must be supplied together: the first three ranges are
closed ranges such as `3-5`; tier four is open-ended such as `15+`. Prices must
be positive numbers. The first range must start at the current minimum rental
duration (currently 3), and all tiers must be continuous with no gap or
overlap. `starting_price` is optional verification data and must equal the
lowest supplied tier price.

`engine` is optional structured vehicle metadata. It is displayed on the
single-vehicle specification list and never appended to the customer-facing
post title.

## Commands

Run a dry run first; it is the default even when `--dry-run` is omitted:

```bash
wp rentacar fleet migrate --csv=/absolute/path/to/fleet.csv --dry-run
```

Include a directory of optional replacement featured images:

```bash
wp rentacar fleet migrate --csv=/absolute/path/to/fleet.csv --images=/absolute/path/to/fleet-images --dry-run
```

Apply only after reviewing the full dry-run report and confirming backups:

```bash
wp rentacar fleet migrate --csv=/absolute/path/to/fleet.csv --images=/absolute/path/to/fleet-images --apply
```

Image files must be plain filenames in the CSV (for example `fiat-500.webp`),
not paths. The importer records a SHA-256 checksum on each newly imported
attachment and reuses that attachment on later runs. It does not delete or
alter previous media; a missing file preserves the existing featured image.

### Fleet image source standard

Use one approved primary image for every Italian CSV row. `image_file` is the
authoritative mapping; filenames must be unique, plain filenames, and resolve
inside the supplied `--images` directory. WebP is preferred (JPEG and PNG are
accepted only when their extension matches their real image MIME type). Every
file must be readable, non-empty, have valid dimensions, and have unique
SHA-256 content within the manifest.

Prepare the set as a consistent 16:9 canvas (recommended: 1600×900 pixels),
with the vehicle centered at a consistent visual scale. Prefer transparency
when it works in the existing UI; otherwise use the same neutral background
throughout. Do not use text overlays, watermarks, embedded dealer logos,
unrelated scenery, or a different vehicle model.

Dry-run prints `[IMPORT]`, `[REPLACE]`, `[UNCHANGED]`, `[MISSING]`, or
`[INVALID]` for each source image, including current attachment/file, source
dimensions, MIME, size, and checksum. A changed file with the same filename is
detected by its new SHA-256 checksum. Replacing a featured image never deletes
the old attachment.

When an existing EN, RO, or RU vehicle already shares its Italian source's
featured attachment, a successful image apply updates only that translated
featured-image reference to reuse the same new attachment. It reads the
existing Polylang relation map but never changes language assignments,
translation relationships, or translated content, SEO, pricing, titles, or
slugs. A translation with a distinct current thumbnail is left untouched and
reported as a warning.

The required production image manifest is maintained in
[`fleet-images-manifest.csv`](fleet-images-manifest.csv). Do not run `--apply`
until every listed approved asset is present and the dry run reports no missing
or invalid files.

## Translation pricing sync

To copy only validated Italian price tiers to existing EN, RO, and RU vehicle
translations, first run:

```bash
wp rentacar fleet sync-translations --source-language=it --fields=pricing --dry-run
```

Apply only after reviewing its per-translation pricing diff and taking a
database backup:

```bash
wp rentacar fleet sync-translations --source-language=it --fields=pricing --apply
```

The command reads each source from Polylang, validates its four-tier schedule
with the fleet migration validator, and writes only the ten legacy pricing
keys. It recalculates `_rentacar_starting_price` only for translations whose
price tiers changed. It never creates, deletes, or relinks translations, and
does not copy content, slugs, SEO, media, or other vehicle fields.

## Škoda Octavia translation-content repair

The former Ford Focus translation family for Italian post `2942` is repaired by
an intentionally narrow, reproducible content migration. It updates only the
approved EN, RO, and RU titles, localized editorial content, Rank Math title and
description, and the semantically incorrect translated slugs. It does not update
pricing, technical fields, featured images, language assignments, or Polylang
relationships.

Review the exact localized diff and any planned 301 redirects first:

```bash
wp rentacar fleet sync-translations --source-language=it --fields=vehicle-content --post-id=2942 --dry-run
```

After a database backup, apply the approved repair:

```bash
wp rentacar fleet sync-translations --source-language=it --fields=vehicle-content --post-id=2942 --apply
```

The command refuses any source language or post ID other than `it` / `2942`.
Slug updates use the same same-language collision validation, scoped temporary
WordPress uniqueness override, exact-persistence verification, and Rank Math 301
redirect validation as the fleet migration. Run the dry-run again after applying;
it must report all three translations unchanged.

## Verification

1. Review the dry-run diffs, warnings, skipped rows, and price validation.
2. Confirm that every planned slug change shows an exact 301 and that no slug
   is being skipped for an unavailable redirect module.
3. Run the apply command once, then run the same command again as a dry run.
   The second run should be predominantly `UNCHANGED`.
4. Check the fleet, a changed vehicle page in each intended language, one
   changed old URL, and the WordPress Media Library.
5. Run `wp rentacar pricing audit` and the existing PHP checks before release.
