# Cookie audit

Generated 2026-07-27T07:54:14.916Z. Audit method: Clean anonymous Playwright contexts; no reused cookies, localStorage, sessionStorage or IndexedDB.

## Public visitor technologies

| Name | Provider | Category | Duration | Consent | Evidence |
| --- | --- | --- | --- | --- | --- |
| pll_language | Polylang | Necessary | Approximately 1 year | No | Observed in clean browser context: Homepage, Fleet, Vehicle page, Venice Marco Polo Airport, Treviso Airport, Contact, Reservation modal and estimate, Language switching, Privacy Policy, Cookie Policy, Optional analytics after consent |
| rentacar_cookie_consent | Rent a Car Venezia | Necessary | Approximately 180 days | No | Observed in clean browser context: Optional analytics after consent |
| _ga | Google Analytics | Analytics | Approximately 1 year | Yes | Observed in clean browser context: Optional analytics after consent |
| _gid | Google Analytics | Analytics | Approximately 1 day | Yes | Observed in clean browser context: Optional analytics after consent |
| _gat_UA-117885941-1 | Google Analytics | Analytics | Less than one day | Yes | Observed in clean browser context: Optional analytics after consent |
| _ga_ZHEY6F2CG0 | Google Analytics | Analytics | Approximately 1 year | Yes | Observed in clean browser context: Optional analytics after consent |
| wpEmojiSettingsSupports | WordPress core | Necessary | Session | Requires confirmation | Observed sessionStorage key in clean browser context: Homepage, Fleet, Vehicle page, Venice Marco Polo Airport, Treviso Airport, Contact, Reservation modal and estimate, Language switching, Privacy Policy, Cookie Policy, Optional analytics after consent |

## Third-party network technologies

| Name | Provider | Category | Consent | Review |
| --- | --- | --- | --- | --- |
| s.w.org | WordPress.org | Requires confirmation | Requires confirmation | Requires confirmation |
| www.googletagmanager.com | Google Tag Manager | Analytics | Yes | Verified in audit |
| www.google-analytics.com | Google Analytics | Analytics | Yes | Verified in audit |
| region1.analytics.google.com | Google Analytics | Analytics | Yes | Verified in audit |
| stats.g.doubleclick.net | Google Analytics | Marketing or profiling | Yes | Verified in audit |
| www.google.it | Google advertising | Marketing or profiling | Yes | Verified in audit |

## Administrator/login technologies

| Name | Provider | Category | Duration | Evidence |
| --- | --- | --- | --- | --- |
| wordpress_test_cookie | WordPress | Necessary | Session | Observed in clean browser context: Administrator login surface |

## Network and embeds

No CAPTCHA, map, video or chat embed loaded in the audited interactions. The raw response headers, storage state and request evidence are in `cookie-audit.json`.

## Review status

Google analytics and advertising-audience traffic was observed only after the explicit optional choice. The consent layer must remain in place and the owner/legal review must confirm the Google Tag Manager configuration before production.
