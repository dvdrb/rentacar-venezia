# Content-quality findings

This is an audit of presentation risks only. No database, media, ACF, WPML or
legacy data was changed.

## Needs owner confirmation

- The WordPress custom-logo setting remains the theme’s authoritative logo
  source. Confirm that the currently configured source asset is the approved
  release identity before production. The theme has a text-wordmark fallback
  but deliberately does not replace the configured asset.
- A business WhatsApp destination is intentionally filter/configuration based.
  The theme hides WhatsApp actions until a value is configured and approved.
- Confirm the privacy-policy page content and any email-recipient configuration
  in the existing plugin before enabling a production request flow.

## Source-data conditions handled by the theme

- Vehicle titles can contain inconsistent repeated whitespace or `|` spacing.
  The theme only normalizes this for display; stored titles, slugs and URLs are
  untouched.
- Visible catalogue examples still needing owner-led CMS cleanup include
  inconsistent title casing (for example “Fiat panda”, “Toyota yaris” and
  all-caps diesel variants) and the typo “Hatcback” in the Fiat Tipo title.
  The theme deliberately does not guess corrected model names.
- A vehicle can lack a featured image, gallery, optional specification or a
  trustworthy complete price band. The UI respectively uses a gallery image,
  a neutral image state, omission, or “Price to be confirmed”; it never
  invents a value.
- Existing vehicle price data is shown as every valid stored day range. The
  theme does not label a single band as “from” when more bands exist.
- Generic page and post content is rendered from the editor. Empty vehicle
  descriptions are omitted rather than replaced with generated copy.

## Vehicle-image presentation hook

- All card images share one fixed, uncropped contain stage. When an individual
  source image has excessive internal whitespace, a child theme may use the
  rentacar_venezia_v2_vehicle_image_presentation_class filter to return one
  safe presentation class, such as vehicle-card__image--compact or
  vehicle-card__image--raised. No post IDs, media files or database values
  are hard-coded by the theme.

## Translation follow-up

All new interface strings use the `rentacar-venezia-v2` text domain. The prior
runtime audit identifies Italian, English, Romanian and Russian as enabled
languages. Actual translated string coverage cannot be verified while the
local WordPress runtime is unavailable; do not silently mix languages. Review
missing theme-string translations in WPML before production.
