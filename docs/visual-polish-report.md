# Visual polish report

## WPML language selector

- The header now receives enabled language data through
  `wpml_active_languages`, including WPML-provided country flag URLs, native
  names and translated-equivalent page URLs. The theme does not copy flags,
  build translated URLs or hard-code languages.
- Desktop presents a quiet, 40px flag-and-code trigger after primary
  navigation and before the optional WhatsApp action. The opened menu is a
  right-aligned white disclosure with the active language visibly checked.
- Mobile places the compact selector between the logo and menu button, keeps
  its touch target at least 44px high and caps the opened menu to the viewport.
- The selector uses regular links. Escape closes the menu and restores focus;
  an outside click closes it; opening mobile navigation closes any open
  language menu, and opening a language menu closes mobile navigation.

## Validation status

- PHP lint, TypeScript typecheck, ESLint and Stylelint passed.
- The Vite production build completed and generated the current theme bundle.
- The local WordPress preview at `http://localhost:10003/` was not accepting
  connections during validation. As a result, the browser suite and the
  desktop/mobile selector screenshots need to be run after LocalWP starts the
  site; no screenshots have been fabricated.
