# Responsive behavior

## Breakpoints

- Small: 0–639px — single-column layouts, 20px gutters, compact header.
- Medium: 640–959px — two-column card grids where space permits.
- Large: 960px and above — full navigation, up to four vehicle cards, detail
  content with a stable request aside.

## Required behaviour

- Header navigation is visible without JavaScript; JavaScript adds an accessible
  menu toggle below the large breakpoint. Escape closes the toggle and focus is
  returned to its trigger.
- Vehicle images reserve their aspect ratio to avoid layout shift. Non-primary
  images use native lazy loading.
- Trip fields stack on small screens, form a compact grid at medium widths, and
  never hide the availability clarification.
- Catalogue filters remain normal GET controls. On small screens they collapse
  visually without relying on JavaScript to submit.
- Vehicle detail galleries use a simple responsive grid; keyboard controls and
  visible focus are required before an enhanced viewer is added.
- The WhatsApp action stays reachable but does not become a disruptive floating
  overlay that conceals content or controls.
- All controls meet a 44px minimum target where practical and remain operable
  with keyboard, touch, zoom and screen readers.
