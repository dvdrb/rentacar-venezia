# Theme dependencies

- **ACF / ACF Repeater**: required for visible `gallery`, `image`, `text_header`, `text_seo`, and coordinates fields.
- **WPML core**: required for the `wpml_current_language` filter in `single.php:209` and `functions.php:1055`; its language widget may occupy sidebar `sidebar-2`.
- **WPML String Translation / Translation Management / ACFML**: active. Runtime confirms four languages and translated `cars`; sanitized settings returned no custom-field translation preferences.
- **WordPress jQuery UI Datepicker**: enqueued in `functions.php:395` and used in booking/search scripts.
- **Bundled Fancybox/Flexslider/Font Awesome/Nivo assets**: legacy front-end presentation dependencies.
- **Contact Form 7 / WPForms Lite**: active. Runtime found CF7 forms 66 and 75, no WPForms forms, and no legacy theme shortcode/API usage.
- **Classic Editor / Classic Widgets**: likely preserve administrative workflows and widget areas.
- **Click to Chat, GTM4WP, LiteSpeed Cache**: potentially current frontend/operational integrations; no direct legacy theme API call found.

## Runtime menus and widgets

- `primary` → menu ID 2 with Italian top-level items HOME PAGE, Domande frequenti, Posti da vedere, Termini e Condizioni, and Contatti.
- `sidebar-1`: five custom-HTML and five media-image widgets; `sidebar-2`: WPML language selector `icl_lang_sel_widget-3`; `sidebar-3`: seven WPML text widgets and one custom-HTML widget.
