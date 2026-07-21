# Enquiry flow

## Flow

1. Sidebar search posts pickup/drop-off and dates to hard-coded page ID 122 (`legacy/active-theme/sidebar.php:10–71`).
2. Results query `cars` and calculate a displayed base rate from date/rate bands (`template-results.php:48–101`).
3. The single vehicle booking view is rendered only when `type=iframe` (`single.php:2–6`) and contains the modal/form at `single.php:58–267`.
4. Datepickers and inline JavaScript update the displayed estimate and hidden fields (`single.php:403–679`). There is no `admin-ajax.php` or custom `wp_ajax` enquiry action.
5. A normal POST with `action=send_order` reaches top-level `functions.php:981–1163`, builds HTML mail, sends it to business and visitor recipients, and redirects to hard-coded page ID 135.

## Calculation visible in source

Rental days start as the date difference; one day is added when return time is later than pickup time (`single.php:26–40`; JavaScript `single.php:610–621`). The selected daily rate is one of four bands (`single.php:42–50`). Night surcharges are applied for specified early/late time ranges (`single.php:8–19`). Browser JavaScript adds insurance and checked extras to the daily price and multiplies by days (`single.php:476–675`). The server only multiplies submitted daily price, submitted days, and submitted night amount (`functions.php:1101–1109`).

## Records and required migration behavior

No code creates a post, custom table, or other enquiry record. **Verified by source.** Runtime found CF7 forms 66 and 75 and no WPForms posts; neither is referenced by the legacy theme, and neither configuration contained the known business-recipient marker. No submissions were read. The vehicle enquiry remains email-only theme code.
