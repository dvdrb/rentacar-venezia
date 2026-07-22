# Design specification

## Direction

The redesign is clean, modern, local and trustworthy. White and soft-neutral
surfaces carry content; navy anchors navigation and the footer; yellow is the
single primary-call-to-action colour. WhatsApp green is reserved for genuine
WhatsApp actions and positive request preparation states.

The supplied visual reference informs the information hierarchy: a calm,
image-led Venice hero, a focused trip form, compact vehicle cards, a clear
catalogue and a detail page with the request action beside the vehicle. It does
not authorize any claim, policy or pricing shown in the image.

## Tokens

| Token | Value | Use |
| --- | --- | --- |
| `--rc-navy-950` | `#071B3A` | Header, footer, high-emphasis text |
| `--rc-navy-800` | `#123665` | Links and secondary controls |
| `--rc-yellow-500` | `#FFC928` | Primary actions |
| `--rc-green-600` | `#168A4A` | WhatsApp only |
| `--rc-surface` | `#FFFFFF` | Main cards and content |
| `--rc-surface-muted` | `#F4F6F8` | Page background and quiet panels |
| `--rc-ink` | `#14213D` | Body text |
| `--rc-line` | `#D7DEE8` | Borders |

Typography uses the system sans-serif stack initially, with a 16px body size,
1.55 line-height, strong but not oversized headings, and a 70ch reading width.
Spacing follows a 4px base scale. Content is contained at 1200px with 20px
mobile gutters. Card radii are 12px; shadows are restrained and never encode
meaning. Every interactive element has a visible, high-contrast focus outline.

## Interaction and content rules

- Primary buttons are yellow with dark text; secondary buttons use a navy
  outline or neutral surface.
- Use semantic HTML before JavaScript. JavaScript progressively enhances menus,
  accordions and galleries only.
- Respect `prefers-reduced-motion`; no auto-advancing carousels.
- Every price is labelled indicative when it has not been recalculated by the
  future server estimate service.
- The availability notice is always adjacent to vehicle/request calls to action:
  “Availability of a specific model for your dates must be confirmed by our
  team.”
- Do not display payment marks, live inventory, scarcity prompts, testimonials
  or policy claims until the owner configures and approves them.
