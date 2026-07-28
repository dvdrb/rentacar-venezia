# Stabilization plan

## Scope and ownership

This local-only stabilization keeps business rules in `rentacar-core` and presentation in `rentacar-venezia-v2`. No production system, production database, production request form, or real email transport is used.

## Implemented work

1. A single `Rentacar_Core_Rental_Policy::minimum_rental_days()` policy now supplies the three-billable-day rule to estimates, server validation, and frontend date constraints.
2. Estimates carry stable line-item keys, totals, deposit, mileage and surcharge data; the modal debounces and cancels superseded estimate requests.
3. Requests are persisted privately before email delivery; delivery is recorded as sent, partially failed, or failed.
4. Price-band audits, starting-price metadata, fleet price sorting, controlled powertrains and safe WP-CLI maintenance commands are provided by the plugin.
5. The custom cookie banner, dialog, footer settings control, JavaScript and CSS have been removed. The theme continues to suppress optional GTM/analytics output.

## Owner/runtime dependencies

- The supplied PNG logo and ICO favicon are deployed under `assets/images/brand/`. `logo-reversed-cropped.png` is a transparent-padding crop of the approved PNG only; it is not redrawn or recoloured.
- LocalWP must be started before visual and browser-route acceptance can run.
- Owned theme strings, customer acknowledgements and public reservation/estimate errors are translated in Italian, English, Romanian and Russian and covered by a focused test. Editor-owned text remains owner-managed.
- The local operational reservation recipient is configured as `robudavid21@gmail.com`; mail delivery is not invoked during code/data verification.

## Local migration sequence

1. Back up the local database.
2. Deploy owned theme/plugin code.
3. Activate the updated custom plugin and visit an admin page once to register `rentacar_request`.
4. Run `wp rentacar pricing audit` and resolve reported source-data gaps/overlaps before enabling public requests. Completed locally with zero remaining findings after boundary-only repairs.
5. Run `wp rentacar vehicles backfill-starting-price --dry-run`, then `--apply` after review. Completed locally for 156 records.
6. Run `wp rentacar vehicles audit-powertrain`; use `infer-powertrain --dry-run`, review, then only use `--apply` if approved. Completed for 12 unambiguous hybrid titles; ambiguous records remain untouched.
7. Start LocalWP, run browser/visual QA, and use mocked mail for a stored-request and partial-failure check.
