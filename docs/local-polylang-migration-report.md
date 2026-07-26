# Local Polylang migration record

## Completed locally

- The local WordPress site now uses Polylang as the active multilingual
  provider with Italian as the default language and English, Romanian and
  Russian enabled.
- Existing page, post, vehicle and category translation relationships were
  repaired from the preserved WPML translation records through Polylang's
  public APIs.
- Vehicle technical fields and featured images were synchronized only where a
  translation differed from its recorded source. Editorial content, titles,
  prices, media records and public slugs remain WordPress-managed.
- The owned theme and `rentacar-core` resolve language, translated post IDs,
  current-language URLs and flags through a provider-neutral layer that uses
  Polylang first. The WPML compatibility branch exists only to avoid breaking
  local links during a rollback or interrupted switch.
- The `cars` post type is registered with Polylang and its front-end singular
  queries are constrained to the requested language, so same-slug vehicle
  translations do not redirect to Italian.

## Local verification

- Active provider: Polylang; languages: `it`, `en`, `ro`, `ru`; default:
  `it`.
- All 39 published vehicles have a Polylang language assignment.
- The post-synchronization vehicle audit found zero remaining differences in
  the approved technical field set across 117 translated vehicle targets.
- Homepage, all four language home routes, fleet, translated vehicle routes,
  standard pages and 404 responses return without PHP warnings on LocalWP
  PHP 8.3.
- The browser suite validates the language disclosure, translated routes,
  current-language links, fleet canonicals, reservation modal and 320px
  layout against `http://localhost:10003/`.

## Rollback and production status

- A pre-migration LocalWP database backup is retained at
  `/Users/dvdrb/Local Sites/rentacar-venezia-local/backups/pre-polylang-migration-20260725-183353/`.
- WPML plugin files and their historical translation table remain untouched;
  the WPML runtime plugins are inactive locally.
- No production database, code, plugin configuration or URLs were changed.
- Do not remove retained WPML files or historical data until the local
  acceptance test is complete and a separate production migration window is
  approved.
