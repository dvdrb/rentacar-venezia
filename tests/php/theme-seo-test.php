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
function esc_url( $value ) { return $value; }
function wp_json_encode( $value, $flags = 0 ) { return json_encode( $value, $flags ); }
function get_the_title( $id = 0 ) { return 'Example title ' . $id; }
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

if ( ! class_exists( 'Rentacar_Core_Vehicle_Repository' ) ) {
    class Rentacar_Core_Vehicle_Repository {
        public function find() { return $GLOBALS['theme_seo_vehicle']; }
    }
}

function theme_seo_assert( $condition, $message ) { if ( ! $condition ) { fwrite( STDERR, "FAIL: {$message}\n" ); exit( 1 ); } }

add_filter( 'wpml_object_id', function( $id ) { return 110; } );
theme_seo_assert( 110 === rentacar_venezia_v2_fleet_page_id(), 'A template-assigned fleet page resolves through WPML.' );
theme_seo_assert( 'https://example.test/fleet/' === rentacar_venezia_v2_fleet_url(), 'A suffixed translation permalink retains the established fleet route.' );

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

add_filter( 'rentacar_venezia_v2_external_seo_plugin_active', function() { return true; } );
theme_seo_assert( rentacar_venezia_v2_external_seo_plugin_active(), 'External SEO ownership can be enabled through the integration filter.' );
$GLOBALS['theme_seo_filters']['rentacar_venezia_v2_external_seo_plugin_active'] = array();

$vehicle = new Rentacar_Core_Vehicle( array(
    'id'              => 42,
    'title'           => 'Fiat 500',
    'permalink'       => 'https://example.test/vehicles/fiat-500/',
    'vehicle_gallery' => new Rentacar_Core_Vehicle_Gallery( 99 ),
    'pricing_bands'   => new Rentacar_Core_Pricing_Band_Collection( array() ),
) );
$GLOBALS['theme_seo_vehicle'] = $vehicle;
theme_seo_assert( 'Fiat 500 rental vehicle' === rentacar_venezia_v2_vehicle_image_alt( $vehicle, 99, true ), 'Primary images receive a restrained title-based fallback alt.' );
theme_seo_assert( '' === rentacar_venezia_v2_vehicle_image_alt( $vehicle, 100, false ), 'Repeated gallery images remain decorative without supplied alt text.' );
$GLOBALS['theme_seo_attachment_alts'][99] = 'White Fiat 500 parked in Venice';
theme_seo_assert( 'White Fiat 500 parked in Venice' === rentacar_venezia_v2_vehicle_image_alt( $vehicle, 99, true ), 'Attachment alt text takes precedence.' );
theme_seo_assert( 'https://example.test/media/99.webp' === rentacar_venezia_v2_primary_image_url( $vehicle ), 'Primary image URLs use WordPress media.' );

theme_seo_assert( ! function_exists( 'rentacar_venezia_v2_vehicle_schema' ), 'Vehicle JSON-LD is not emitted because availability and final pricing require a manual confirmation.' );
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
unset( $GLOBALS['theme_seo_post_meta'][42] );
theme_seo_assert( 'Example title 42 rental in Venice and Treviso' === rentacar_venezia_v2_rank_math_vehicle_title( 'legacy title' ), 'Rank Math vehicle titles retain the generic fallback when stored metadata is empty.' );
theme_seo_assert( false !== strpos( rentacar_venezia_v2_rank_math_vehicle_description( 'legacy description' ), 'Example title 42' ), 'Rank Math vehicle descriptions retain the generic fallback when stored metadata is empty.' );
$GLOBALS['theme_seo_current_post_type'] = 'page';
$GLOBALS['theme_seo_post_meta'][42] = array( 'rank_math_title' => 'Non-car title' );
theme_seo_assert( 'legacy title' === rentacar_venezia_v2_rank_math_vehicle_title( 'legacy title' ), 'Rank Math vehicle metadata does not override non-car pages.' );

$_GET = array();
$breadcrumb_items = rentacar_venezia_v2_breadcrumb_items();
theme_seo_assert( 2 === count( $breadcrumb_items ) && 'Fleet' === $breadcrumb_items[1]['label'], 'Fleet breadcrumbs include crawlable Home and current Fleet items.' );

$GLOBALS['theme_seo_is_page'] = true;
$GLOBALS['theme_seo_templates'][42] = 'template-results.php';
$rank_math_robots = rentacar_venezia_v2_rank_math_noindex_robots( array( 'index' => 'index', 'nofollow' => 'nofollow' ) );
theme_seo_assert( 'noindex' === $rank_math_robots['noindex'] && 'follow' === $rank_math_robots['follow'], 'Rank Math noindexes transactional pages while retaining link discovery.' );
theme_seo_assert( false === rentacar_venezia_v2_rank_math_sitemap_entry( array( 'loc' => 'https://example.test/total/' ), 'post', new WP_Post( 42 ) ), 'Rank Math excludes transactional pages from the sitemap.' );

echo "Theme SEO checks passed.\n";
