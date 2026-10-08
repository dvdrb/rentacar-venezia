<?php
/** Dependency-free manifest and planning guards for new fleet families. */
define( 'ABSPATH', __DIR__ . '/' );
class WP_Error {
    private $message;
    public function __construct( $code, $message ) { $this->message = $message; }
    public function get_error_message() { return $this->message; }
}
function is_wp_error( $value ) { return $value instanceof WP_Error; }
function wp_strip_all_tags( $value ) { return strip_tags( $value ); }
function wp_basename( $value ) { return basename( $value ); }
function trailingslashit( $value ) { return rtrim( $value, '/' ) . '/'; }
function get_posts( $args ) { return array(); }
function get_post_meta( $id, $key, $single = true ) { return $GLOBALS['provision_meta'][ $id ][ $key ] ?? ''; }
function absint( $value ) { return abs( (int) $value ); }
function sanitize_title( $value ) { return (string) $value; }
function pll_get_post_language( $id, $format = 'slug' ) { return 100 === (int) $id ? 'en' : false; }
class Rentacar_Core_Rental_Policy { public static function minimum_rental_days() { return 3; } }
class Rentacar_Core_Vehicle_Maintenance { const POWERTRAINS = array( 'petrol', 'diesel', 'hybrid', 'plug_in_hybrid', 'electric', 'other' ); }
require_once dirname( __DIR__, 2 ) . '/plugin/rentacar-core/src/Cli/FleetMigration.php';
require_once dirname( __DIR__, 2 ) . '/plugin/rentacar-core/src/Cli/FleetProvision.php';
function check( $condition, $message ) { if ( ! $condition ) { fwrite( STDERR, "FAIL: $message\n" ); exit( 1 ); } }
$path = dirname( __DIR__, 2 ) . '/runtime/fleet-additions-2026-10/manifest.json';
$images = dirname( __DIR__, 2 ) . '/runtime/fleet-additions-2026-10/images';
$local_images = is_file( $path );
if ( $local_images ) {
    $raw = file_get_contents( $path );
} else {
    $vehicles = array();
    foreach ( array( 'volkswagen-t-roc', 'volkswagen-tiguan-4motion', 'kia-sportage' ) as $key ) {
        $copy = array();
        foreach ( array( 'it', 'en', 'ro', 'ru' ) as $language ) $copy[ $language ] = array( 'content' => $language . ' vehicle description', 'seo_title' => $language . ' rental title', 'seo_description' => $language . ' rental description' );
        $vehicles[] = array( 'key' => $key, 'title' => $key, 'slug' => $key, 'image' => $key . '.webp', 'engine' => 'TDI', 'powertrain' => 'diesel', 'gearbox' => 'Automatic', 'passengers' => 5, 'doors' => 5, 'pricing' => array( 'price_tier_1_range' => '3-5', 'price_tier_1_price' => 105, 'price_tier_2_range' => '6-14', 'price_tier_2_price' => 95, 'price_tier_3_range' => '15-29', 'price_tier_3_price' => 85, 'price_tier_4_range' => '30+', 'price_tier_4_price' => 75 ), 'locales' => $copy );
    }
    $raw = json_encode( array( 'vehicles' => $vehicles ) );
}
$manifest = Rentacar_Core_Fleet_Provision::parse_manifest( $raw );
check( ! is_wp_error( $manifest ) && 3 === count( $manifest['vehicles'] ), 'Three valid families parse.' );
$expected_keys = array( 'volkswagen-t-roc', 'volkswagen-tiguan-4motion', 'kia-sportage' );
foreach ( $manifest['vehicles'] as $index => $vehicle ) {
    check( $expected_keys[ $index ] === $vehicle['key'], 'Stable provisioning key matches.' );
    check( array( 'it', 'en', 'ro', 'ru' ) === array_keys( $vehicle['locales'] ), 'Four locale assignments are present.' );
    if ( $local_images ) {
        $plan = Rentacar_Core_Fleet_Provision::plan_family( $vehicle, $images );
        check( ! is_wp_error( $plan ) && 'CREATE' === $plan['state'] && 4 === count( $plan['posts'] ), 'Dry creation plan has four draft slots.' );
        check( 'image/webp' === $plan['image']['mime'] && strlen( $plan['image']['hash'] ) === 64, 'Image has valid WebP MIME and SHA-256.' );
    }
}
check( is_wp_error( Rentacar_Core_Fleet_Provision::parse_manifest( '{' ) ), 'Malformed JSON is rejected.' );
$invalid = $manifest;
$invalid['vehicles'][0]['year'] = 2024;
check( is_wp_error( Rentacar_Core_Fleet_Provision::parse_manifest( json_encode( $invalid ) ) ), 'Unknown fields are rejected.' );
$invalid = $manifest;
$invalid['vehicles'][0]['pricing']['price_tier_2_range'] = '5-14';
check( is_wp_error( Rentacar_Core_Fleet_Provision::parse_manifest( json_encode( $invalid ) ) ), 'Overlapping pricing is rejected.' );
$invalid = $manifest;
$invalid['vehicles'][0]['locales']['en']['content'] = '';
check( is_wp_error( Rentacar_Core_Fleet_Provision::parse_manifest( json_encode( $invalid ) ) ), 'Missing localized copy is rejected.' );
$invalid = $manifest;
$invalid['vehicles'][1]['image'] = $invalid['vehicles'][0]['image'];
check( is_wp_error( Rentacar_Core_Fleet_Provision::parse_manifest( json_encode( $invalid ) ) ), 'Duplicate filenames are rejected.' );
if ( $local_images ) {
    $invalid = $manifest['vehicles'][0];
    $invalid['image'] = 'missing.webp';
    check( is_wp_error( Rentacar_Core_Fleet_Provision::plan_family( $invalid, $images ) ), 'Missing image blocks creation.' );
    $invalid['image'] = 'kia-sportage.webp';
    check( ! is_wp_error( Rentacar_Core_Fleet_Provision::plan_family( $invalid, $images ) ), 'Distinct valid image is accepted.' );
}
$GLOBALS['provision_meta'][100][Rentacar_Core_Fleet_Provision::KEY_META] = 'volkswagen-t-roc';
check( 'volkswagen-t-roc' === Rentacar_Core_Fleet_Provision::preserve_provisioned_slug( null, 'volkswagen-t-roc', 100, 'publish', 'cars', 0 ), 'Ordinary editor saves preserve the exact translated slug.' );
check( null === Rentacar_Core_Fleet_Provision::preserve_provisioned_slug( null, 'other-slug', 100, 'publish', 'cars', 0 ), 'Different slug is not overridden.' );
echo "Fleet provision checks passed.\n";
