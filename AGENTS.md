# Rentacar Venezia workspace

## Project objective

Modernize rentacarvenezia.it while preserving the existing WordPress CMS, vehicle records, ACF data, WPML translations, media, important public URLs, and the current manual availability-enquiry workflow. The site sends availability enquiries; it does not guarantee or instantly confirm reservations.

## Repository ownership

- `legacy/active-theme/` is read-only.
- `theme/rentacar-venezia-v2/` contains the new custom theme.
- `plugin/rentacar-core/` contains site-specific business logic.
- `docs/` contains audits, field maps, architecture, and migration plans.
- `tests/` contains automated tests.
- `scripts/` contains development and packaging tools.

## Hard boundaries

Never modify WordPress core, third-party plugins, legacy reference code, or production systems. Never expose or commit secrets or customer information. Never rename or remove existing ACF fields without an approved migration. Never change public URLs without documenting redirects. Do not update WordPress, PHP, WPML, ACF, themes, or plugins as unrelated work. Do not add major dependencies, a page builder, React, Next.js, or real-time availability without approval.

## Architecture

The custom theme owns presentation only. `rentacar-core` owns vehicle normalization, pricing logic, enquiry validation, email generation, and business rules. Theme templates must not contain pricing or enquiry business logic. Initially reuse the existing WordPress and ACF model. Keep PHP integration small, explicit, and readable; use minimal TypeScript only where interaction needs JavaScript; prefer server-rendered semantic HTML.

## Security

Escape dynamic output; sanitize and validate incoming values; verify nonces for mutations and capabilities for administrative actions. Recalculate estimates on the server. Never trust browser-supplied prices or identifiers. Use WordPress APIs instead of direct SQL unless formally justified.

## Working method

1. Inspect relevant code before editing.
2. Explain the planned minimal change.
3. Keep changes bounded and reviewable.
4. Do not combine broad refactors with feature work.
5. Add or update tests where appropriate.
6. Run available checks.
7. Report files changed, commands run, results, assumptions, and risks.
8. Stop and ask before destructive or architectural decisions.
