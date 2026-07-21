# Technical audit

## Scope and status

This began as a static source audit and was completed with a localhost-only, read-only WordPress runtime audit. No form was submitted, no email was sent, no database value was changed, and no external service was called.

## Core implementation

- Vehicle type: `cars`, registered by `cars_init()` on `init` in `legacy/active-theme/functions.php:529–583`. **Verified by both source and runtime.** It is public/admin-visible, non-REST, has rewrite slug `cars`, no archive, supports title/author/thumbnail, and has 156 published posts.
- Vehicle taxonomies: none. **Verified by runtime and source.** `get_object_taxonomies( 'cars' )` returned none and no theme `register_taxonomy()` call exists; there are no vehicle taxonomy terms.
- Fleet rendering: `template-homepage.php:17–58` and `template-results.php:48–101` query `cars`. `query_posts()` is used on the homepage and should be replaced only during a deliberate migration.
- Presentation dependencies: navigation/menu registration is `functions.php:197–245`; widget areas are `functions.php:251–331`; scripts/styles are enqueued at `functions.php:391–441`.
- Legacy behavior coupled to the theme: `cars` registration, pricing, enquiry handling, custom general settings, widget areas, asset enqueueing, and theme settings all disappear if this theme is deactivated.

## Security and maintainability risks

1. An SMTP credential is embedded in legacy theme source (`functions.php:151–181`). It is not reproduced here. Treat it as compromised and rotate it through an approved operational process before any migration.
2. The enquiry handler runs as top-level theme code (`functions.php:981–1163`), has no nonce/capability boundary, uses unvalidated request values, and sends mail directly.
3. Browser-supplied price/day/extra values are used by the server (`functions.php:1101–1109`); server-side price recalculation does not exist.
4. Dynamic output and redirects use unescaped request data, including the success redirect (`functions.php:1159`).
5. IDs `20`, `23`, `122`, `135` are hard-coded in templates/flow. Their content and language relationships need admin verification.
6. The theme disables automatic core/theme/plugin updates (`functions.php:65–69`), uses cache-busting `time()` on a script (`functions.php:431`), and loads third-party resources from front-end code.
7. Local terminal PHP must load LocalWP’s site-specific `php.ini`/bootstrap to use the configured MySQL socket; generic CLI access fails despite the local site being healthy.
