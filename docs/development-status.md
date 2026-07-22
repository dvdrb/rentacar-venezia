# Development status

## Current phase

Phase 6 — homepage, staged but not activated.

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
- The inactive theme now has a semantic header/footer, WordPress menu locations,
  guarded WPML language links, an accessible progressive-enhancement mobile menu,
  skip link, reduced-motion handling, and an availability notice component.
- The inactive homepage is server-rendered and shows a limited six-vehicle
  catalogue through the core vehicle repository. Its trip form intentionally
  collects preferences only and never asserts live availability.

## Not started

The theme has not been activated, so the baseline LocalWP site has no visible
change. The dedicated catalogue, detail experience, pricing service and
availability-request flow remain unimplemented. Activation of `rentacar-core`
remains a later local-only verification step after its runtime integration
coverage is added.
