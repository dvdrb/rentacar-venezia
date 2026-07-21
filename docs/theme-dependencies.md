# Theme dependencies

- **ACF / ACF Repeater**: required for visible `gallery`, `image`, `text_header`, `text_seo`, and coordinates fields.
- **WPML core**: required for the `wpml_current_language` filter in `single.php:209` and `functions.php:1055`; its language widget may occupy sidebar `sidebar-2`.
- **WPML String Translation / Translation Management / ACFML**: likely required to preserve translations and field translation behavior; verify configuration in admin.
- **WordPress jQuery UI Datepicker**: enqueued in `functions.php:395` and used in booking/search scripts.
- **Bundled Fancybox/Flexslider/Font Awesome/Nivo assets**: legacy front-end presentation dependencies.
- **Contact Form 7 / WPForms Lite**: installed but no theme shortcode/API usage was found. Their active forms require manual verification.
- **Classic Editor / Classic Widgets**: likely preserve administrative workflows and widget areas.
- **Click to Chat, GTM4WP, LiteSpeed Cache**: potentially current frontend/operational integrations; no direct legacy theme API call found.
