# Stabilization release candidate

## Completed code changes

- Three-billable-day policy, client date constraints, server validation and estimate rejection outside the policy.
- Stable estimate line-item keys, complete modal estimates, debounce/cancellation/retry presentation.
- Private `rentacar_request` persistence before mail, structured estimates in notifications, and distinct delivery states.
- Pricing integrity audit, starting-price derived metadata and price sorting.
- Controlled powertrain field, audit/inference commands and customer-facing eco badges; 12 explicit hybrid records were normalized locally.
- Removed cookie banner/preferences/settings UI and theme optional-analytics injection.
- Complete owned-theme translation coverage, localized customer acknowledgement emails, and localized public reservation/estimate errors.
- Local recipient configured, starting-price metadata backfilled for 156 vehicles, and all pricing-band audit findings corrected without changing rates.
- Optional extras are priced server-side and locally configured as €5/day child seat, €5/day additional driver, and an €80 fixed authorization for abroad.

## Required WordPress Admin actions

1. Review editor-owned page text and any future vehicle-title changes in Italian, English, Romanian and Russian.
2. Run one controlled mocked-mail delivery test before a production release; this local task intentionally sent no mail.
3. Review remaining ambiguous powertrains manually.
4. Deactivate any temporary WPCode snippets that inject a cookie banner, cookie-settings control, GTM, Google Analytics or analytics consent script. The current local audit does not identify snippet IDs; do not remove unrelated snippets.

## Deployment sequence

1. Create a normal owned-code release package; do not perform a database cutover.
2. Back up production through the regular workflow and deploy only the custom theme/plugin.
3. Run the pricing audit and starting-price backfill in dry-run mode, review results, then explicitly apply derived metadata.
4. Verify request storage, controlled mail delivery, all four languages and desktop/mobile routes in a staging/local clone.
5. Activate the code release; monitor `rentacar_request` delivery-status metadata and rollback owned code if required.

## Release blockers

- A controlled mail-transport test and manual review of ambiguous powertrains remain release checks; neither blocks the completed local code and data work.

## Local verification evidence

- `PLAYWRIGHT_BASE_URL=http://rentacar-venezia-local.local pnpm exec playwright test` passed all 45 tests.
- `tests/php/theme-translation-coverage-test.php` and the focused acknowledgement-email test passed.
- Local pricing audit completed with zero reported data issues after the five range-boundary repairs.
- Local DOM review confirmed no custom cookie UI, no horizontal overflow at 390×844 and 1440×900, and the native fleet sort control at mobile width.
- Captures: `docs/generated/screenshots/stabilization-homepage-1440.png`, `docs/generated/screenshots/stabilization-fleet-390.png`, and `docs/generated/screenshots/stabilization-logo-390.png`.
