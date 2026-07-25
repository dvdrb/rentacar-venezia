<?php
/** Isolated fallback-route check because the theme caches fleet resolution per request. */
define( 'ABSPATH', __DIR__ . '/' );
$GLOBALS['theme_seo_filters'] = array();
function add_filter( $tag, $callback, $priority = 10 ) { $GLOBALS['theme_seo_filters'][ $tag ][] = $callback; }
function add_action( $tag, $callback, $priority = 10 ) { add_filter( $tag, $callback, $priority ); }
function has_filter() { return false; }
function apply_filters( $tag, $value ) { return $value; }
function get_posts() { return array(); }
function home_url( $path = '/' ) { return 'https://example.test' . $path; }
function get_query_var() { return 0; }
function is_page() { return false; }
function is_singular() { return false; }
function absint( $value ) { return abs( (int) $value ); }
function wp_unslash( $value ) { return $value; }
function trailingslashit( $value ) { return rtrim( $value, '/' ) . '/'; }
function esc_url( $value ) { return $value; }
function __( $text ) { return $text; }
require_once dirname( __DIR__, 2 ) . '/plugin/rentacar-core/src/Vehicles/Vehicle.php';
require_once dirname( __DIR__, 2 ) . '/theme/rentacar-venezia-v2/inc/presentation.php';
require_once dirname( __DIR__, 2 ) . '/theme/rentacar-venezia-v2/inc/seo.php';
if ( 'https://example.test/fleet/' !== rentacar_venezia_v2_fleet_url() ) { fwrite( STDERR, "FAIL: Custom fleet fallback URL was not retained.\n" ); exit( 1 ); }
echo "Theme SEO fallback checks passed.\n";
