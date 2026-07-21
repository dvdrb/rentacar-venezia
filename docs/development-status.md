# Development status

## Current phase

Phase 2 — vehicle-domain migration, staged but not activated.

## Confirmed

- Working branch: `feat/rentacar-venezia-v2`.
- LocalWP theme and plugin symlinks target this workspace.
- Legacy reference is redacted and may now be safely tracked.
- Local email is intercepted through a local-only MU-plugin before any redesign work begins.
- `rentacar-core` now contains PHP 7.4-compatible, read-only vehicle value objects,
  a repository, a mapper for the established `cars`/ACF data model, and a WPML
  translation resolver.
- The plugin remains inactive. Its `cars` registration has a legacy-theme guard,
  so it cannot duplicate the existing post type during the eventual local-only
  activation check.
- Dependency-free vehicle-domain checks pass using the LocalWP PHP 7.4 binary.

## Not started

No visible redesign, pricing service, availability-request flow, or database
migration has been implemented. Activation of `rentacar-core` remains a later
local-only verification step after its runtime integration coverage is added.
