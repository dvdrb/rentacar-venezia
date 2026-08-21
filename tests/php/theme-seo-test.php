<?php
/** Focused PHP 7.4 checks for theme-level SEO helpers. */
define( 'ABSPATH', __DIR__ . '/' );
define( 'RANK_MATH_VERSION', 'test' );

$GLOBALS['theme_seo_filters'] = array();
$GLOBALS['theme_seo_query_vars'] = array( 'rc_fleet' => 1, 'paged' => 0, 'page' => 0 );
$GLOBALS['theme_seo_page_ids'] = array( 10 );
$GLOBALS['theme_seo_post_status'] = array( 10 => 'publish', 110 => 'publish' );
$GLOBALS['theme_seo_attachment_alts'] = array();
$GLOBALS['theme_seo_post_meta'] = array();
$GLOBALS['theme_seo_is_page'] = false;
$GLOBALS['theme_seo_is_singular'] = false;
$GLOBALS['theme_seo_current_post_type'] = 'page';
$GLOBALS['theme_seo_templates'] = array();

function add_filter( $tag, $callback, $priority = 10, $accepted_args = 1 ) { $GLOBALS['theme_seo_filters'][ $tag ][ $priority ][] = $callback; }
function add_action( $tag, $callback, $priority = 10, $accepted_args = 1 ) { add_filter( $tag, $callback, $priority, $accepted_args ); }
function has_filter( $tag ) { return ! empty( $GLOBALS['theme_seo_filters'][ $tag ] ); }
function apply_filters( $tag, $value ) { $args = func_get_args(); if ( empty( $GLOBALS['theme_seo_filters'][ $tag ] ) ) { return $value; } ksort( $GLOBALS['theme_seo_filters'][ $tag ] ); foreach ( $GLOBALS['theme_seo_filters'][ $tag ] as $callbacks ) { foreach ( $callbacks as $callback ) { $args[1] = call_user_func_array( $callback, array_slice( $args, 1 ) ); } } return $args[1]; }
function __( $text ) { return $text; }
function get_posts() { return $GLOBALS['theme_seo_page_ids']; }
function get_post_status( $id ) { return isset( $GLOBALS['theme_seo_post_status'][ $id ] ) ? $GLOBALS['theme_seo_post_status'][ $id ] : false; }
function get_option( $key ) { return 'page_on_front' === $key ? 0 : null; }
function get_permalink( $id ) { return 'https://example.test/fleet-' . $id . '/'; }
function home_url( $path = '/' ) { return 'https://example.test' . $path; }
function wp_parse_url( $url, $component = -1 ) { return parse_url( $url, $component ); }
function get_query_var( $key ) { return isset( $GLOBALS['theme_seo_query_vars'][ $key ] ) ? $GLOBALS['theme_seo_query_vars'][ $key ] : ''; }
function is_page( $id = 0 ) { return $GLOBALS['theme_seo_is_page'] && ( ! $id || 110 === (int) $id ); }
function is_front_page() { return false; }
function is_singular( $post_type = '' ) { return $GLOBALS['theme_seo_is_singular'] && ( '' === $post_type || $post_type === $GLOBALS['theme_seo_current_post_type'] ); }
function is_archive() { return false; }
function is_home() { return false; }
function get_queried_object_id() { return 42; }
function get_page_template_slug( $id ) { return isset( $GLOBALS['theme_seo_templates'][ $id ] ) ? $GLOBALS['theme_seo_templates'][ $id ] : ''; }
function get_post_meta( $id, $key = '', $single = true ) {
    if ( isset( $GLOBALS['theme_seo_post_meta'][ $id ][ $key ] ) ) {
        return $GLOBALS['theme_seo_post_meta'][ $id ][ $key ];
    }
    return '_wp_attachment_image_alt' === $key && isset( $GLOBALS['theme_seo_attachment_alts'][ $id ] ) ? $GLOBALS['theme_seo_attachment_alts'][ $id ] : '';
}
function wp_get_attachment_image_url( $id ) { return $id ? 'https://example.test/media/' . $id . '.webp' : false; }
function get_post_field( $field, $id ) { return 'post_content' === $field ? 'A visible vehicle description.' : ''; }
function wp_strip_all_tags( $value ) { return strip_tags( $value ); }
function wp_trim_words( $text ) { return $text; }
function trailingslashit( $value ) { return rtrim( $value, '/' ) . '/'; }
function wp_unslash( $value ) { return $value; }
function absint( $value ) { return abs( (int) $value ); }
function sanitize_key( $value ) { return strtolower( preg_replace( '/[^a-z0-9_\-]/', '', (string) $value ) ); }
function esc_url( $value ) { return $value; }
function wp_json_encode( $value, $flags = 0 ) { return json_encode( $value, $flags ); }
function get_the_title( $id = 0 ) { return 'Example title ' . $id; }
function get_the_date() { return '2026-01-02T03:04:05+00:00'; }
function get_the_modified_date() { return '2026-02-03T04:05:06+00:00'; }
function get_the_excerpt() { return 'A reviewed guide excerpt.'; }
function get_post_thumbnail_id() { return 99; }
function get_post_ancestors() { return array(); }
function wp_get_document_title() { return 'Archive'; }
function determine_locale() { return 'en_US'; }
function get_post_type( $post_id ) { return $GLOBALS['theme_seo_current_post_type']; }

class WP_Post {
    public $ID;
    public $post_type;

    public function __construct( $id, $post_type = 'page' ) {
        $this->ID = $id;
        $this->post_type = $post_type;
    }
}

require_once dirname( __DIR__, 2 ) . '/plugin/rentacar-core/src/Vehicles/PricingBand.php';
require_once dirname( __DIR__, 2 ) . '/plugin/rentacar-core/src/Vehicles/PricingBandCollection.php';
require_once dirname( __DIR__, 2 ) . '/plugin/rentacar-core/src/Vehicles/Vehicle.php';
require_once dirname( __DIR__, 2 ) . '/plugin/rentacar-core/src/Vehicles/VehicleGallery.php';
require_once dirname( __DIR__, 2 ) . '/theme/rentacar-venezia-v2/inc/presentation.php';
require_once dirname( __DIR__, 2 ) . '/theme/rentacar-venezia-v2/inc/multilingual.php';
require_once dirname( __DIR__, 2 ) . '/theme/rentacar-venezia-v2/inc/seo.php';
require_once dirname( __DIR__, 2 ) . '/theme/rentacar-venezia-v2/inc/breadcrumbs.php';

function rentacar_venezia_v2_business_data() { return array( 'public_name' => 'G&D Rent A Car', 'email' => 'info@example.test', 'phone' => '+3900000000', 'street_address' => 'Via Test 1', 'postal_code' => '31100', 'locality' => 'Treviso', 'region' => 'TV', 'country' => 'IT', 'weekday_hours' => 'Monday–Friday, 24/24', 'weekend_hours' => 'Saturday–Sunday, 07:00–23:00' ); }
function rentacar_venezia_v2_business_value( $key ) { $business = rentacar_venezia_v2_business_data(); return $business[ $key ] ?? ''; }
function rentacar_venezia_v2_business_locations() { return array(
    'treviso' => array( 'key' => 'treviso', 'public_name' => 'G&D Rent A Car', 'street_address' => 'Via Montello, 7', 'postal_code' => '31100', 'locality' => 'Treviso', 'region' => 'TV', 'country' => 'IT', 'phone' => '+393445068823', 'google_business_profile_url' => 'https://www.google.com/maps/search/?api=1&query_place_id=ChIJ_4ELE5U3eUcRjwKQKULkwKA', 'opening_hours_source' => 'business' ),
    'venice_marco_polo' => array( 'key' => 'venice_marco_polo', 'public_name' => 'G&D Rent A Car', 'street_address' => 'Airport, Viale Galileo Galilei, 30/1', 'postal_code' => '30173', 'locality' => 'Venice', 'region' => 'VE', 'country' => 'IT', 'phone' => '+393445068823', 'google_business_profile_url' => 'https://www.google.com/maps/search/?api=1&query_place_id=ChIJX5MLBACzfkcRkpzxcPjF0es' ),
); }
function rentacar_venezia_v2_business_location_url( $key ) { return 'https://example.test/' . $key . '/'; }
function rentacar_venezia_v2_location_label( $key ) { return array( 'venice_marco_polo' => 'Venice Marco Polo Airport', 'treviso_airport' => 'Treviso Airport', 'treviso_hotel' => 'Hotel in Treviso', 'venice_hotel' => 'Hotel in Venice' )[ $key ] ?? $key; }
require_once dirname( __DIR__, 2 ) . '/theme/rentacar-venezia-v2/inc/schema.php';

if ( ! class_exists( 'Rentacar_Core_Vehicle_Repository' ) ) {
    class Rentacar_Core_Vehicle_Repository {
        public function find() { return $GLOBALS['theme_seo_vehicle']; }
    }
}

function theme_seo_assert( $condition, $message ) { if ( ! $condition ) { fwrite( STDERR, "FAIL: {$message}\n" ); exit( 1 ); } }

add_filter( 'wpml_object_id', function( $id ) { return 110; } );
theme_seo_assert( 110 === rentacar_venezia_v2_fleet_page_id(), 'A template-assigned fleet page resolves through WPML.' );
theme_seo_assert( 'https://example.test/fleet/' === rentacar_venezia_v2_fleet_url(), 'A suffixed translation permalink retains the established fleet route.' );
theme_seo_assert( 'https://example.test/en/fleet/' === rentacar_venezia_v2_fleet_url( 'en' ), 'The English catalogue uses its stable public route rather than the translated page slug.' );
theme_seo_assert( 'https://example.test/ro/flota/' === rentacar_venezia_v2_fleet_url( 'ro' ), 'The Romanian catalogue keeps its established public route.' );
theme_seo_assert( 'https://example.test/ru/avtopark/' === rentacar_venezia_v2_fleet_url( 'ru' ), 'The Russian catalogue keeps its established public route.' );
$fleet_menu = array( (object) array( 'type' => 'post_type', 'object' => 'page', 'object_id' => 110, 'url' => 'https://example.test/en/fleet-2/' ) );
theme_seo_assert( 'https://example.test/en/fleet/' === rentacar_venezia_v2_canonicalize_fleet_menu_links( $fleet_menu )[0]->url, 'Fleet page-backed menu items use the canonical public route.' );
$fleet_hreflangs = rentacar_venezia_v2_canonicalize_fleet_hreflangs( array( 'it' => 'https://example.test/fleet/', 'en' => 'https://example.test/en/fleet-2/', 'ro' => 'https://example.test/ro/flota/', 'ru' => 'https://example.test/ru/avtopark/' ) );
theme_seo_assert( 'https://example.test/en/fleet/' === $fleet_hreflangs['en'], 'Fleet hreflang uses the English canonical route.' );
theme_seo_assert( 'https://example.test/ro/flota/' === $fleet_hreflangs['ro'] && 'https://example.test/ru/avtopark/' === $fleet_hreflangs['ru'], 'Fleet hreflang retains canonical Romanian and Russian routes.' );
$localized_content = rentacar_venezia_v2_localize_fleet_content_links( '<p><a href="/fleet/?pickup_location=venice">Fleet</a></p>' );
theme_seo_assert( false !== strpos( $localized_content, rentacar_venezia_v2_fleet_url() . '?pickup_location=venice' ), 'Editor fleet links resolve through the canonical fleet helper and retain query context.' );
theme_seo_assert( 'Rental cars in Venice and Treviso | G&D Rent A Car' === rentacar_venezia_v2_fleet_public_title( 'en' ), 'English fleet public metadata uses the approved G&D brand.' );

$_GET = array();
theme_seo_assert( ! rentacar_venezia_v2_is_filtered_fleet_request(), 'A clean fleet request is not treated as filtered.' );
theme_seo_assert( 'https://example.test/fleet/' === rentacar_venezia_v2_fleet_canonical_url(), 'A clean fleet canonical is self-referencing.' );

$GLOBALS['theme_seo_query_vars']['paged'] = 2;
theme_seo_assert( 'https://example.test/fleet/page/2/' === rentacar_venezia_v2_fleet_canonical_url(), 'A paginated fleet canonical retains its page number.' );

$GLOBALS['theme_seo_query_vars']['paged'] = 0;
$_GET = array( 'transmission' => 'manual' );
theme_seo_assert( rentacar_venezia_v2_is_filtered_fleet_request(), 'Recognized fleet filters are detected.' );
theme_seo_assert( 'https://example.test/fleet/' === rentacar_venezia_v2_fleet_canonical_url(), 'A filtered fleet canonical points to the clean catalogue.' );
$robots = rentacar_venezia_v2_fleet_robots( array() );
theme_seo_assert( ! empty( $robots['noindex'] ) && ! empty( $robots['follow'] ), 'Filtered fleet requests are noindex,follow.' );
$rank_math_robots = rentacar_venezia_v2_rank_math_noindex_robots( array( 'index' => 'index' ) );
theme_seo_assert( isset( $rank_math_robots['noindex'], $rank_math_robots['follow'] ) && ! isset( $rank_math_robots['index'] ), 'Rank Math keeps filtered fleet requests noindex,follow.' );

add_filter( 'rentacar_venezia_v2_external_seo_plugin_active', function() { return true; } );
theme_seo_assert( rentacar_venezia_v2_external_seo_plugin_active(), 'External SEO ownership can be enabled through the integration filter.' );
$GLOBALS['theme_seo_filters']['rentacar_venezia_v2_external_seo_plugin_active'] = array();
theme_seo_assert( isset( $GLOBALS['theme_seo_filters']['rank_math/opengraph/facebook/og_site_name'] ), 'Rank Math receives the Open Graph site-name filter on its og_site_name field.' );
theme_seo_assert( 'G&D Rent A Car' === apply_filters( 'rank_math/opengraph/facebook/og_site_name', 'Rent a Car Venezia' ), 'Rank Math Open Graph site name uses the authoritative public business name.' );

$vehicle = new Rentacar_Core_Vehicle( array(
    'id'              => 42,
    'title'           => 'Fiat 500',
    'permalink'       => 'https://example.test/vehicles/fiat-500/',
    'vehicle_gallery' => new Rentacar_Core_Vehicle_Gallery( 99 ),
    'pricing_bands'   => new Rentacar_Core_Pricing_Band_Collection( array( new Rentacar_Core_Pricing_Band( 1, 3, 50 ) ) ),
) );
$GLOBALS['theme_seo_vehicle'] = $vehicle;
theme_seo_assert( 'Fiat 500 rental vehicle' === rentacar_venezia_v2_vehicle_image_alt( $vehicle, 99, true ), 'Primary images receive a restrained title-based fallback alt.' );
theme_seo_assert( '' === rentacar_venezia_v2_vehicle_image_alt( $vehicle, 100, false ), 'Repeated gallery images remain decorative without supplied alt text.' );
$GLOBALS['theme_seo_attachment_alts'][99] = 'White Fiat 500 parked in Venice';
theme_seo_assert( 'White Fiat 500 parked in Venice' === rentacar_venezia_v2_vehicle_image_alt( $vehicle, 99, true ), 'Attachment alt text takes precedence.' );
theme_seo_assert( 'https://example.test/media/99.webp' === rentacar_venezia_v2_primary_image_url( $vehicle ), 'Primary image URLs use WordPress media.' );

theme_seo_assert( function_exists( 'rentacar_venezia_v2_schema_graph' ), 'Vehicle schema is centrally integrated through Rank Math rather than emitted by a template.' );
$airport_service = rentacar_venezia_v2_schema_location_service( 'venice_marco_polo', 'https://example.test/venice-airport/' );
theme_seo_assert( 'Service' === $airport_service['@type'] && 'VCE' === $airport_service['areaServed']['iataCode'] && 'Airport' === $airport_service['areaServed']['@type'] && rentacar_venezia_v2_schema_business_location_id( 'venice_marco_polo' ) === $airport_service['provider']['@id'], 'Venice airport remains a Service and references its physical business provider.' );
$hotel_service = rentacar_venezia_v2_schema_location_service( 'venice_hotel', 'https://example.test/hotel-pickup/' );
theme_seo_assert( 'City' === $hotel_service['areaServed']['@type'] && 'Venice' === $hotel_service['areaServed']['name'], 'Hotel pickup uses its city service area and never fabricates a Hotel entity.' );
$GLOBALS['theme_seo_is_singular'] = true;
$GLOBALS['theme_seo_current_post_type'] = 'cars';
$GLOBALS['theme_seo_post_meta'][42] = array(
    'rank_math_title'       => 'Noleggio Fiat 500 a Venezia | G&D Rent',
    'rank_math_description' => 'Noleggia Fiat 500 a Venezia e Treviso.',
);
theme_seo_assert( isset( $GLOBALS['theme_seo_filters']['rank_math/frontend/title'] ), 'Rank Math receives the vehicle title localization filter.' );
theme_seo_assert( isset( $GLOBALS['theme_seo_filters']['rank_math/frontend/description'] ), 'Rank Math receives the vehicle description localization filter.' );
theme_seo_assert( 'Noleggio Fiat 500 a Venezia | G&D Rent' === rentacar_venezia_v2_rank_math_vehicle_title( 'legacy title' ), 'Stored Rank Math vehicle titles take precedence over the generic fallback.' );
theme_seo_assert( 'Noleggia Fiat 500 a Venezia e Treviso.' === rentacar_venezia_v2_rank_math_vehicle_description( 'legacy description' ), 'Stored Rank Math vehicle descriptions take precedence over the generic fallback.' );
theme_seo_assert( 'Example title 42 rental in Venice and Treviso' === rentacar_venezia_v2_vehicle_metadata_for_post( 'title', 42, 'en' ), 'Vehicle metadata can be generated deterministically for a specific translated record.' );
unset( $GLOBALS['theme_seo_post_meta'][42] );
theme_seo_assert( 'Example title 42 rental in Venice and Treviso' === rentacar_venezia_v2_rank_math_vehicle_title( 'legacy title' ), 'Rank Math vehicle titles retain the generic fallback when stored metadata is empty.' );
theme_seo_assert( false !== strpos( rentacar_venezia_v2_rank_math_vehicle_description( 'legacy description' ), 'Example title 42' ), 'Rank Math vehicle descriptions retain the generic fallback when stored metadata is empty.' );
$GLOBALS['theme_seo_current_post_type'] = 'page';
$GLOBALS['theme_seo_post_meta'][42] = array( 'rank_math_title' => 'Non-car title' );
theme_seo_assert( 'legacy title' === rentacar_venezia_v2_rank_math_vehicle_title( 'legacy title' ), 'Rank Math vehicle metadata does not override non-car pages.' );
$GLOBALS['theme_seo_is_page'] = true;
$GLOBALS['theme_seo_post_meta'][42]['_rc_provisioning_key'] = 'cookie_policy';
theme_seo_assert( 'Cookie Policy for G&D Rent A Car' === apply_filters( 'rank_math/frontend/title', 'legacy title' ), 'The English Cookie Policy title is distinct from its Italian equivalent.' );
$GLOBALS['theme_seo_post_meta'][42]['_rc_provisioning_key'] = '';
$GLOBALS['theme_seo_post_meta'][42]['_rentacar_location_key'] = 'venice_marco_polo';
theme_seo_assert( 'Venice Airport Car Rental | No Credit Card to Reserve | G&D' === apply_filters( 'rank_math/frontend/title', 'legacy title' ), 'The English Venice Airport page has targeted commercial Rank Math title metadata.' );
theme_seo_assert( false !== strpos( apply_filters( 'rank_math/frontend/description', 'legacy description' ), 'security deposit is required at pickup' ), 'The Venice Airport description keeps the security-deposit condition explicit.' );
$GLOBALS['theme_seo_is_page'] = false;

$GLOBALS['theme_seo_current_post_type'] = 'cars';
theme_seo_assert( array() === rentacar_venezia_v2_schema_graph( array( 'WebPage' => array( '@id' => 'https://example.test/fleet/#webpage' ) ) ), 'Filtered fleet requests do not create an alternative schema graph.' );
$_GET = array();
$GLOBALS['theme_seo_query_vars']['rc_fleet'] = 0;
$graph = rentacar_venezia_v2_schema_graph( array() );
theme_seo_assert( rentacar_venezia_v2_schema_organization_id() === $graph['publisher']['@id'] && rentacar_venezia_v2_schema_website_id() === $graph['WebSite']['@id'], 'The graph has one canonical business and WebSite identity.' );
theme_seo_assert( rentacar_venezia_v2_schema_has_unique_ids( $graph ), 'Schema graph nodes do not duplicate @id values.' );
theme_seo_assert( 'Organization' === $graph['publisher']['@type'] && ! isset( $graph['publisher']['legalName'] ), 'The parent graph is public Organization identity without legalName.' );
theme_seo_assert( 'https://rentacarvenezia.it/#location-treviso' === $graph['BusinessLocation_treviso']['@id'] && 'Via Montello, 7' === $graph['BusinessLocation_treviso']['address']['streetAddress'] && '31100' === $graph['BusinessLocation_treviso']['address']['postalCode'] && 'TV' === $graph['BusinessLocation_treviso']['address']['addressRegion'], 'Treviso has one verified physical AutoRental entity with its approved NAP.' );
theme_seo_assert( 'https://rentacarvenezia.it/#location-venice-marco-polo' === $graph['BusinessLocation_venice_marco_polo']['@id'] && false === strpos( implode( ' ', $graph['BusinessLocation_venice_marco_polo']['sameAs'] ), 'writereview' ), 'Venice has one verified physical AutoRental entity whose sameAs is not a review-write URL.' );
theme_seo_assert( 'BreadcrumbList' === $graph['BreadcrumbList']['@type'] && $graph['WebPage']['breadcrumb']['@id'] === $graph['BreadcrumbList']['@id'], 'Rank Math renders the visible breadcrumb hierarchy as one connected BreadcrumbList.' );
theme_seo_assert( 'Car' === $graph['Car']['@type'] && $graph['WebPage']['mainEntity']['@id'] === $graph['Car']['@id'], 'Vehicle pages connect their WebPage and Car entities.' );
theme_seo_assert( 'EUR' === $graph['Car']['offers']['priceSpecification']['priceCurrency'] && 'DAY' === $graph['Car']['offers']['priceSpecification']['referenceQuantity']['unitCode'] && ! isset( $graph['Car']['offers']['availability'] ), 'Vehicle offers describe daily EUR rental pricing without fabricated availability.' );
$GLOBALS['theme_seo_current_post_type'] = 'post';
$GLOBALS['theme_seo_post_meta'][42] = array( '_rc_seo_indexable' => '1' );
$guide_graph = rentacar_venezia_v2_schema_graph( array( 'Article' => array( '@id' => 'https://example.test/legacy/#article' ) ) );
theme_seo_assert( 'BlogPosting' === $guide_graph['BlogPosting']['@type'] && rentacar_venezia_v2_schema_organization_id() === $guide_graph['BlogPosting']['author']['@id'] && ! isset( $guide_graph['Article'] ), 'Approved guides receive one BlogPosting with the verified organization author fallback.' );
unset( $GLOBALS['theme_seo_post_meta'][42]['_rc_seo_indexable'] );
theme_seo_assert( array() === rentacar_venezia_v2_schema_graph( array( 'Article' => array( '@id' => 'https://example.test/legacy/#article' ) ) ), 'Unapproved guides do not inherit a rich BlogPosting graph.' );
$GLOBALS['theme_seo_current_post_type'] = 'page';

$GLOBALS['theme_seo_is_page'] = true;
$GLOBALS['theme_seo_templates'][42] = 'template-results.php';
theme_seo_assert( array() === rentacar_venezia_v2_schema_graph( array( 'WebPage' => array( '@id' => 'https://example.test/total/#webpage' ) ) ), 'Transactional noindex pages do not inherit Rank Math rich schema.' );
$GLOBALS['theme_seo_is_page'] = false;

$_GET = array();
$GLOBALS['theme_seo_query_vars']['rc_fleet'] = 1;
$breadcrumb_items = rentacar_venezia_v2_breadcrumb_items();
theme_seo_assert( 2 === count( $breadcrumb_items ) && 'Fleet' === $breadcrumb_items[1]['label'], 'Fleet breadcrumbs include crawlable Home and current Fleet items.' );

$GLOBALS['theme_seo_is_page'] = true;
$GLOBALS['theme_seo_templates'][42] = 'template-results.php';
$rank_math_robots = rentacar_venezia_v2_rank_math_noindex_robots( array( 'index' => 'index', 'nofollow' => 'nofollow' ) );
theme_seo_assert( 'noindex' === $rank_math_robots['noindex'] && 'follow' === $rank_math_robots['follow'], 'Rank Math noindexes transactional pages while retaining link discovery.' );
theme_seo_assert( false === rentacar_venezia_v2_rank_math_sitemap_entry( array( 'loc' => 'https://example.test/total/' ), 'post', new WP_Post( 42 ) ), 'Rank Math excludes transactional pages from the sitemap.' );

echo "Theme SEO checks passed.\n";
