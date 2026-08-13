<?php
namespace RankMath {
    class Helper { public static function is_module_active( $module ) { return 'redirections' === $module; } }
}
namespace RankMath\Redirections {
    class DB { public static function match_redirections( $source ) { return $GLOBALS['content_redirects'][ $source ] ?? false; } }
    class Redirection {
        private $data;
        public static function from( $data ) { $instance = new self(); $instance->data = $data; return $instance; }
        public function is_infinite_loop() { return false; }
        public function save() { $source = $this->data['sources'][0]['pattern']; $GLOBALS['content_redirects'][ $source ] = array( 'header_code' => '301', 'status' => 'active', 'url_to' => $this->data['url_to'] ); return true; }
    }
}
namespace {
/** Focused checks for the fixed Škoda Octavia EN/RO/RU editorial translation repair. */
define( 'ABSPATH', __DIR__ . '/' );

class WP_Error {
    private $message;
    public function __construct( $code = '', $message = '' ) { $this->message = $message; }
    public function get_error_message() { return $this->message; }
}
function is_wp_error( $value ) { return $value instanceof WP_Error; }
function absint( $value ) { return abs( (int) $value ); }
function sanitize_title( $value ) { $value = strtolower( (string) $value ); $value = str_replace( array( 'š', ' ' ), array( 's', '-' ), $value ); return trim( preg_replace( '/[^a-z0-9-]+/', '', $value ), '-' ); }
function get_post( $id ) { return $GLOBALS['content_posts'][ $id ] ?? null; }
function get_post_type( $id ) { return isset( $GLOBALS['content_posts'][ $id ] ) ? $GLOBALS['content_posts'][ $id ]->post_type : ''; }
function get_post_meta( $id, $key, $single = true ) { return $GLOBALS['content_meta'][ $id ][ $key ] ?? ''; }
function update_post_meta( $id, $key, $value ) { $GLOBALS['content_meta'][ $id ][ $key ] = (string) $value; $GLOBALS['content_writes'][] = array( 'meta', $id, $key ); return true; }
function pll_get_post_language( $id, $field = 'slug' ) { return $GLOBALS['content_languages'][ $id ] ?? false; }
function pll_get_post_translations( $id ) { return $GLOBALS['content_relations'][ $id ] ?? array(); }
function get_permalink( $id ) { $post = get_post( $id ); $language = pll_get_post_language( $id, 'slug' ); return 'https://example.test/' . ( 'it' === $language ? '' : $language . '/' ) . 'cars/' . $post->post_name . '/'; }
function home_url( $path = '' ) { return 'https://example.test' . $path; }
function wp_parse_url( $url, $component = -1 ) { return -1 === $component ? parse_url( $url ) : parse_url( $url, $component ); }
function untrailingslashit( $value ) { return rtrim( $value, '/' ); }
function get_posts( $args = array() ) {
    $posts = array_values( $GLOBALS['content_posts'] );
    if ( isset( $args['name'] ) ) $posts = array_values( array_filter( $posts, static function( $post ) use ( $args ) { return $post->post_name === $args['name']; } ) );
    return $posts;
}
function wp_update_post( $update, $error = false ) { $id = $update['ID']; foreach ( array( 'post_title', 'post_name', 'post_content' ) as $field ) if ( array_key_exists( $field, $update ) ) $GLOBALS['content_posts'][ $id ]->$field = $update[ $field ]; $GLOBALS['content_writes'][] = array( 'post', $id ); return $id; }
function get_post_field( $field, $id ) { return get_post( $id )->$field; }
function add_filter() { $GLOBALS['content_filter_added'] = true; }
function remove_filter() { $GLOBALS['content_filter_removed'] = true; }
function remove_action() {}
function add_action() {}
function get_post_thumbnail_id( $id ) { return (int) ( $GLOBALS['content_meta'][ $id ]['_thumbnail_id'] ?? 0 ); }
function set_post_thumbnail() { return true; }
function wp_is_post_revision() { return false; }
function wp_is_post_autosave() { return false; }
function url_to_postid( $url ) { foreach ( $GLOBALS['content_posts'] as $post ) if ( get_permalink( $post->ID ) === $url ) return $post->ID; return 0; }

require_once dirname( __DIR__, 2 ) . '/plugin/rentacar-core/src/Cli/FleetMigration.php';
require_once dirname( __DIR__, 2 ) . '/plugin/rentacar-core/src/Cli/FleetTranslationPricingSync.php';

function content_assert( $condition, $message ) { if ( ! $condition ) { fwrite( STDERR, "FAIL: {$message}\n" ); exit( 1 ); } }
function content_post( $id, $language, $title, $slug ) { return (object) array( 'ID' => $id, 'post_type' => 'cars', 'post_title' => $title, 'post_name' => $slug, 'post_content' => '' ); }

$GLOBALS['content_posts'] = array(
    2942 => content_post( 2942, 'it', 'Škoda Octavia 1.0 e-TEC', 'skoda-octavia-1-0-etec' ),
    4383 => content_post( 4383, 'en', 'Ford Focus | 1.5 Diesel', 'rent-a-car-ford-focus' ),
    4384 => content_post( 4384, 'ro', 'Ford Focus | 1.5 Diesel', 'rent-a-car-ford-focus' ),
    4385 => content_post( 4385, 'ru', 'Ford Focus | 1.5 Diesel', 'rent-a-car-ford-focus' ),
    5000 => content_post( 5000, 'en', 'Unrelated car', 'unrelated-car' ),
    5001 => content_post( 5001, 'en', 'Collision car', 'skoda-octavia-1-0-etec' ),
);
$GLOBALS['content_languages'] = array( 2942 => 'it', 4383 => 'en', 4384 => 'ro', 4385 => 'ru', 5000 => 'en', 5001 => 'en' );
$GLOBALS['content_relations'] = array(
    2942 => array( 'it' => 2942, 'en' => 4383, 'ro' => 4384, 'ru' => 4385 ),
    4383 => array( 'it' => 2942, 'en' => 4383, 'ro' => 4384, 'ru' => 4385 ),
    4384 => array( 'it' => 2942, 'en' => 4383, 'ro' => 4384, 'ru' => 4385 ),
    4385 => array( 'it' => 2942, 'en' => 4383, 'ro' => 4384, 'ru' => 4385 ),
);
$GLOBALS['content_meta'] = array();
foreach ( array( 2942, 4383, 4384, 4385, 5000 ) as $id ) $GLOBALS['content_meta'][ $id ] = array( '_thumbnail_id' => '5261', 'price' => '95', 'price2' => '90', 'price3' => '85', 'price4' => '80', '_rentacar_starting_price' => '80' );
$GLOBALS['content_writes'] = array();
$GLOBALS['content_redirects'] = array();
$relations_before = $GLOBALS['content_relations'];
$technical_before = $GLOBALS['content_meta'][4383];

// The production group permits the same logical slug across languages; remove the test-only EN collision first.
unset( $GLOBALS['content_posts'][5001], $GLOBALS['content_languages'][5001] );
$dry = Rentacar_Core_Fleet_Translation_Pricing_Sync::synchronize_octavia_vehicle_content( false );
content_assert( 3 === $dry['counts']['translations'] && 3 === $dry['counts']['updated'], 'Dry-run resolves exactly the approved EN/RO/RU Octavia family.' );
content_assert( empty( $GLOBALS['content_writes'] ), 'Dry-run makes no post or metadata writes.' );
content_assert( $relations_before === $GLOBALS['content_relations'], 'Dry-run preserves the Polylang relation map.' );
content_assert( 'Ford Focus | 1.5 Diesel' === $GLOBALS['content_posts'][4383]->post_title, 'Dry-run leaves translated title untouched.' );

$apply = Rentacar_Core_Fleet_Translation_Pricing_Sync::synchronize_octavia_vehicle_content( true );
content_assert( 3 === $apply['counts']['updated'] && 0 === $apply['counts']['errors'], 'Apply updates only all three approved translations.' );
content_assert( 3 === $apply['counts']['redirects'] && 3 === count( $GLOBALS['content_redirects'] ), 'Apply creates one localized 301 redirect per changed translated slug.' );
foreach ( array( 'en' => 4383, 'ro' => 4384, 'ru' => 4385 ) as $language => $id ) {
    $profile = Rentacar_Core_Fleet_Translation_Pricing_Sync::octavia_vehicle_content_profiles()[ $language ];
    content_assert( $profile['title'] === $GLOBALS['content_posts'][ $id ]->post_title && $profile['content'] === $GLOBALS['content_posts'][ $id ]->post_content, strtoupper( $language ) . ' gets localized Octavia title and content.' );
    content_assert( $profile['seo_title'] === $GLOBALS['content_meta'][ $id ]['rank_math_title'] && $profile['seo_description'] === $GLOBALS['content_meta'][ $id ]['rank_math_description'], strtoupper( $language ) . ' gets localized Rank Math metadata.' );
    content_assert( '5261' === $GLOBALS['content_meta'][ $id ]['_thumbnail_id'] && '80' === $GLOBALS['content_meta'][ $id ]['_rentacar_starting_price'], strtoupper( $language ) . ' retains image and pricing.' );
}
content_assert( 'Unrelated car' === $GLOBALS['content_posts'][5000]->post_title, 'Unrelated translations are untouched.' );
content_assert( $relations_before === $GLOBALS['content_relations'], 'Apply preserves the Polylang relation map.' );
content_assert( $technical_before['price'] === $GLOBALS['content_meta'][4383]['price'] && $technical_before['_thumbnail_id'] === $GLOBALS['content_meta'][4383]['_thumbnail_id'], 'Apply does not change pricing or featured image.' );

$writes_after_apply = count( $GLOBALS['content_writes'] );
$again = Rentacar_Core_Fleet_Translation_Pricing_Sync::synchronize_octavia_vehicle_content( true );
content_assert( 3 === $again['counts']['unchanged'] && 0 === $again['counts']['updated'] && $writes_after_apply === count( $GLOBALS['content_writes'] ), 'A second apply is idempotent.' );

$GLOBALS['content_posts'][4383]->post_name = 'rent-a-car-ford-focus';
$GLOBALS['content_posts'][5001] = content_post( 5001, 'en', 'Collision car', 'skoda-octavia-1-0-etec' );
$GLOBALS['content_languages'][5001] = 'en';
$collision = Rentacar_Core_Fleet_Translation_Pricing_Sync::synchronize_octavia_vehicle_content( false );
content_assert( 1 === $collision['counts']['errors'], 'Same-language translated slug collisions fail safely.' );

echo "Fleet translation vehicle-content sync checks passed.\n";
}
