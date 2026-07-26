# Development status

## Current local phase

The owned `rentacar-venezia-v2` theme and `rentacar-core` plugin are active in
the LocalWP clone. The local site now uses Polylang for Italian, English,
Romanian and Russian; the WPML runtime plugins are inactive and retained only
as rollback evidence while local acceptance testing continues.

## Completed locally

- Server-rendered homepage, trip filter, vehicle catalogue, vehicle details,
  pricing bands, request modal, manual-confirmation journey, FAQ/editorial
  templates, archives and 404 experience are active.
- Native fleet filters, pagination, responsive image stages, language selector,
  visible breadcrumbs, fleet indexing rules and vehicle schema compatibility
  are implemented in the owned theme.
- The reservation backend validates requests and calculates vehicle, insurance
  and optional-extra estimates from authoritative WordPress settings.
- The Polylang migration has repaired translation groups, synchronized only
  approved shared vehicle fields, and verified the default and translated
  vehicle routes under LocalWP PHP 8.3.
- PHP domain checks, SEO checks, type checking, ESLint, Stylelint, the Vite
  production build, PHP lint and the browser suite are run against the local
  application before each hand-off.

## Before production is considered

- Complete owner-led visual and content acceptance on the LocalWP site,
  including the configured logo, contact/WhatsApp details, privacy policy and
  business notification recipient.
- Review translated interface strings and any editor-owned content in all four
  languages.
- Keep the WPML plugins and pre-migration backup until a separate, approved
  production migration plan and rollback window exist.
- Production deployment, production database migration and removal of legacy
  WPML data are deliberately not part of this workspace.
