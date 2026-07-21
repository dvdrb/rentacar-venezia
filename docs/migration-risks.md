# Migration risks

1. Moving `cars` registration out of the active theme changes rewrite availability and admin behavior; migrate it first into the site plugin with a tested rewrite plan.
2. Rate bands and extras are stored as raw post meta/general options, not a normalized model. Preserve keys while introducing a server-side adapter.
3. Hard-coded page/post IDs and locale-specific content links may break after content migration or translation changes.
4. The enquiry flow exposes customer values in query strings after submission and has no durable record. Redesign requires data-minimization and retention decisions from the owner.
5. WPML runtime confirms directory URLs and translated `cars`, but sanitized settings return no custom-field translation preferences. Establish approved ACF/WPML field translation rules before migration.
6. PHP 8.2 and modern WordPress compatibility are unverified; short tags, legacy constructors, and direct request handling are high risks.
7. Embedded legacy mail credentials require operational rotation; do not copy mail configuration into new code or Git.
8. `cars` has no taxonomy. Do not add taxonomy-driven fleet filters without an approved data migration.
