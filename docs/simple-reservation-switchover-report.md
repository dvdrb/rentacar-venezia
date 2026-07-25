# Simple reservation flow — local switchover report

## Scope

The `feat/simple-reservation-flow` branch introduces a single-modal reservation-request path for the local clone only. It does not connect to production, activate a theme or plugin, change WordPress content, or create a persistent enquiry record.

## Implemented path

Vehicle cards, the fleet catalogue, and `single-cars.php` use the same progressive-enhancement form. The request handler reloads the published `cars` post, validates trip and contact inputs, recalculates the base vehicle estimate on the server, sends business and customer notifications through `wp_mail()`, and returns the same-modal success state. The request is explicitly not a confirmation.

## Local safeguards

- The local-only MU mail interceptor remains linked from `wp-content/mu-plugins/` and short-circuits `wp_mail()` on the `.local` hostname.
- Its local-only recipient filter uses the non-routable `local-reservation@example.invalid` solely to exercise the local handler; the production plugin has no recipient until a trusted configuration filter supplies one.
- No submitted daily rate, total, vehicle title, or vehicle image is trusted by the controller.
- Customer data is used only during the request and is not written to a post, option, or custom table. The short rate-limit transient stores only a one-way email hash.

## Activation test status

The LocalWP HTTP service was not running during this task (`curl http://rentacar-venezia-local.local/` returned connection failure), so activation, browser modal, and intercepted-mail delivery tests could not be run. No attempt was made to start LocalWP, activate or deactivate anything, or change WordPress settings. Start the existing LocalWP site before the following local-only checks:

1. Confirm `rentacar-core` is active with `autocar`, then inspect several Italian, English, Romanian, and Russian `cars` URLs.
2. Activate `rentacar-venezia-v2` locally, verify homepage, `/fleet/`, and vehicle-page Reservation buttons.
3. Submit a valid request after three seconds and verify both mails are intercepted by the local MU plugin (no external delivery).
4. Verify the modal success copy, Escape/focus return, invalid field errors, and a rollback to `autocar`.

## Static checks completed

- LocalWP PHP 7.4 lint: passed for all theme and plugin PHP files.
- Existing vehicle-domain compatibility tests: passed.
- Diff whitespace check: passed.
- npm, Node/Vite, Playwright, ESLint, and Stylelint were unavailable in this shell (`npm: command not found`); generated asset output was refreshed from the TypeScript source as a constrained local fallback and still needs a normal Vite rebuild when Node is available.
