# Theme data-source map

The V2 theme is a presentation layer. It reads the established WordPress data
model through core APIs and the read-only `rentacar-core` vehicle repository;
it does not create or alter vehicle, ACF, WPML, menu, page or contact data.

| UI value | Existing WordPress source | Safe fallback |
| --- | --- | --- |
| Vehicle ID | `cars` post ID via `Rentacar_Core_Vehicle` | none; reservation trigger is not rendered without a vehicle |
| Vehicle title | post title via the repository | none |
| Permalink | `get_permalink()` via the repository | none |
| Featured image | post thumbnail via `Rentacar_Core_Vehicle_Gallery` | first gallery image |
| Gallery | existing `gallery` ACF data via the repository | featured image |
| Description | existing post content | section omitted |
| Transmission | normalized `gearbox` field | omitted |
| Passengers | normalized `max_passagers` field | omitted |
| Doors | normalized `doors` field | omitted |
| Air conditioning | normalized `air_conditioning` field | omitted |
| Pricing bands | normalized existing ACF/meta price-band fields | “Price to be confirmed” |
| Logo | WordPress custom logo | text wordmark |
| Main navigation | registered WordPress primary menu | WordPress page-menu fallback |
| Footer navigation | registered WordPress footer menu, else primary menu | omitted when no menu exists |
| Language links | WPML `icl_get_languages()` | current-language WordPress page only |
| Page title | WordPress post/page title | none |
| Page content | WordPress editor content | omitted |
| Contact data | configured WordPress option/filter or existing page content | hidden when absent |
| WhatsApp URL | `rentacar_venezia_v2_whatsapp_url` filter | action hidden |
| Reservation endpoint | existing `admin-post.php` handler supplied by `rentacar-core` | ordinary form POST when JavaScript is unavailable |

## Presentation-only normalization

`inc/presentation.php` centralizes title whitespace cleanup, price-range labels,
vehicle specifications, image selection and fleet-filter values. It does not
recreate pricing, vehicle mapping, validation, estimation or enquiry handling.
Those remain in the existing plugin.

## Asset source of truth

`theme/rentacar-venezia-v2/style.css` is the sole authored stylesheet and
retains the required WordPress theme header. TypeScript is authored only in
`assets/src/ts/main.ts`; Vite emits its hashed production script and manifest
to `assets/dist/`. WordPress resolves the script from that manifest and simply
renders the semantic form when JavaScript is absent.
