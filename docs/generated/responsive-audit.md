# Responsive and popup audit

Audited: 2026-07-28  
Target: running LocalWP site at `http://rentacar-venezia-local.local`

## Result

No responsive blocker was found in the current LocalWP build. The tested
mobile layouts contain no document-level horizontal overflow, and the
interactive overlays remain usable at the required narrow widths.

## Mobile coverage

| Area | Widths checked | Result |
| --- | --- | --- |
| Homepage hero, trip finder, trust strip, featured vehicles, CTAs | 320, 390, 430, 768, 1024, 1440px | No horizontal overflow; mobile order remains copy, trip finder, then imagery. |
| Fleet catalogue | 320px | Single-column layout remains within viewport. |
| Reservation request sheet | 320, 390px | Bottom-sheet panel scrolls internally; the close control, trip fields, optional details and continue action remain reachable. |
| General contact form | 320px | Inputs, consent and submit action remain visible without overflow. |
| Shared country/phone picker | 320, 360, 375, 390, 430px | Inline selector and phone field fit side by side; search dialog stays within viewport. |
| Mobile header, language menu and navigation drawer | 320, 390px | Header does not overflow; drawer is escapable and makes the page inert while open. |
| Cookie Policy and its wide inventory table | 320, 390, 430, 768, 1024, 1440px | Page does not cause document overflow; the table scrolls within its own container. |
| Airport, how-it-works and rental-requirements templates | 390px | Required content and mobile conversion action render correctly. |
| Vehicle-detail conversion action | 390px | Mobile action bar exposes a visible reservation action. |

## Popup and overlay coverage

- Reservation modal: opens from vehicle cards, exposes a labelled 44px close
  control, closes correctly, restores focus, and keeps focus inside while open.
- International phone selector: opens inline with its field, is searchable,
  keyboard-operable, and does not close the reservation modal when a country
  is selected.
- Mobile navigation drawer: opens, makes background content inert, and closes
  with Escape while returning focus to the menu control.
- Language selector: remains inside the 320px header and closes with Escape.
- Cookie popup: none exists by design; optional tracking is disabled and no
  custom consent dialog is rendered.

## Pages and templates covered by the review

The public page inventory contains shared responsive template families for the
home page, fleet, contact, FAQ, airport location pages, how-it-works, rental
requirements, terms, generic content/legal pages, results, success pages,
vehicle detail pages, and the transfer article. Locale variants use the same
template and responsive CSS, so the layout coverage applies to Italian,
English, Romanian and Russian; the shared phone picker was additionally
checked in each language.

## Validation performed

- Ran 23 focused live-browser checks covering responsive layouts and popups:
  all passed.
- Ran the additional airport/information-page mobile check: passed.
- Inspected the live homepage and reservation modal in the local browser:
  six reservation triggers, one phone dialog, and no cookie dialog are present;
  the labelled close control hides the modal correctly.
- Reviewed mobile CSS paths for all template families, including the 767px,
  639px, 479px and 959px breakpoints, safe-area padding, internal modal
  scrolling, and horizontally-scrollable legal tables.

## Remaining device-specific check

Automated browser checks cannot fully reproduce the iOS Safari and Android
Chrome virtual keyboards, browser chrome collapse, text-size overrides or a
real touch screen. Before production release, perform a short manual check on
one iPhone and one Android device: open the reservation sheet, open and search
the country picker, scroll the sheet, then close it and open the navigation
drawer. No code change is currently required from this audit.
