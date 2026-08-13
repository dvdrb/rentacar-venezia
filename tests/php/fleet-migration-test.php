<?php
/** Dependency-free checks for the pure CSV and pricing guards. */
define( 'ABSPATH', __DIR__ . '/' );

class WP_Error {
    private $code;
    private $message;
    public function __construct( $code = '', $message = '' ) { $this->code = $code; $this->message = $message; }
    public function get_error_message() { return $this->message; }
}
function is_wp_error( $value ) { return $value instanceof WP_Error; }
function absint( $value ) { return abs( (int) $value ); }
function sanitize_title( $value ) { return trim( preg_replace( '/-+/', '-', preg_replace( '/[^a-z0-9]+/', '-', strtolower( (string) $value ) ) ), '-' ); }
function sanitize_text_field( $value ) { return trim( strip_tags( (string) $value ) ); }
function wp_parse_url( $url, $component = -1 ) { return parse_url( $url, $component ); }
function home_url( $path = '/' ) { return 'https://local.test' . $path; }
function untrailingslashit( $value ) { return rtrim( $value, '/' ); }
function wp_basename( $value ) { return basename( $value ); }
function trailingslashit( $value ) { return rtrim( $value, '/\\' ) . '/'; }
function get_post_thumbnail_id( $post_id ) { return $GLOBALS['fleet_migration_test_thumbnails'][ $post_id ] ?? 0; }
function set_post_thumbnail( $post_id, $attachment_id ) { $GLOBALS['fleet_migration_test_thumbnails'][ $post_id ] = (int) $attachment_id; return true; }
function get_attached_file( $attachment_id ) { return $GLOBALS['fleet_migration_test_attachment_files'][ $attachment_id ] ?? ''; }
function get_post_meta( $post_id, $key, $single = true ) { return $GLOBALS['fleet_migration_test_meta'][ $post_id ][ $key ] ?? ''; }
function add_filter( $hook, $callback, $priority = 10, $accepted_args = 1 ) { $GLOBALS['fleet_migration_test_filters'][ $hook ][ $priority ][] = array( 'callback' => $callback, 'accepted_args' => $accepted_args ); }
function remove_filter( $hook, $callback, $priority = 10 ) {
    foreach ( $GLOBALS['fleet_migration_test_filters'][ $hook ][ $priority ] ?? array() as $index => $filter ) {
        if ( $filter['callback'] === $callback ) { unset( $GLOBALS['fleet_migration_test_filters'][ $hook ][ $priority ][ $index ] ); }
    }
}
function apply_filters( $hook, $value ) {
    $args = func_get_args();
    array_shift( $args );
    foreach ( $GLOBALS['fleet_migration_test_filters'][ $hook ] ?? array() as $filters ) {
        foreach ( $filters as $filter ) {
            $value = call_user_func_array( $filter['callback'], array_slice( array_merge( array( $value ), array_slice( $args, 1 ) ), 0, $filter['accepted_args'] ) );
        }
    }
    return $value;
}
function get_post_type( $post_id ) { return $GLOBALS['fleet_migration_test_post_types'][ $post_id ] ?? ''; }
function get_post_field( $field, $post_id ) { return $GLOBALS['fleet_migration_test_post_fields'][ $post_id ][ $field ] ?? ''; }
function wp_update_post( $post, $wp_error = false ) {
    $post_id = (int) $post['ID'];
    $GLOBALS['fleet_migration_test_filter_count_during_update'] = count( $GLOBALS['fleet_migration_test_filters']['pre_wp_unique_post_slug'][10] ?? array() );
    $GLOBALS['fleet_migration_test_filter_observations'] = array(
        'expected' => apply_filters( 'pre_wp_unique_post_slug', null, $post['post_name'] ?? '', $post_id, 'publish', 'cars', 0 ),
        'wrong_post' => apply_filters( 'pre_wp_unique_post_slug', null, $post['post_name'] ?? '', $post_id + 1, 'publish', 'cars', 0 ),
        'wrong_slug' => apply_filters( 'pre_wp_unique_post_slug', null, 'other-slug', $post_id, 'publish', 'cars', 0 ),
    );
    if ( ! empty( $GLOBALS['fleet_migration_test_update_error'] ) ) { return new WP_Error( 'update_failed', 'simulated update failure' ); }
    $slug = $GLOBALS['fleet_migration_test_filter_observations']['expected'];
    $GLOBALS['fleet_migration_test_post_fields'][ $post_id ]['post_name'] = ! empty( $GLOBALS['fleet_migration_test_force_suffixed_slug'] )
        ? $post['post_name'] . '-2'
        : ( null === $slug ? ( $post['post_name'] . '-2' ) : $slug );
    return $post_id;
}
function get_posts( $args ) {
    $matches = array();
    if ( isset( $args['meta_key'], $args['meta_value'] ) ) {
        foreach ( $GLOBALS['fleet_migration_test_attachments_by_hash'] ?? array() as $hash => $attachment_id ) {
            if ( $args['meta_value'] === $hash ) { return array( $attachment_id ); }
        }
        return array();
    }
    foreach ( $GLOBALS['fleet_migration_test_posts'] ?? array() as $post ) {
        if ( isset( $args['name'] ) && $post->post_name === $args['name'] ) {
            $matches[] = $post;
        }
    }
    return $matches;
}
function pll_get_post_language( $post_id ) { return $GLOBALS['fleet_migration_test_languages'][ $post_id ] ?? false; }
function pll_get_post_translations( $post_id ) { return $GLOBALS['fleet_migration_test_translations'][ $post_id ] ?? array(); }

require_once dirname( __DIR__, 2 ) . '/plugin/rentacar-core/src/Cli/FleetMigration.php';

function fleet_migration_assert( $condition, $message ) {
    if ( ! $condition ) { fwrite( STDERR, "FAIL: {$message}\n" ); exit( 1 ); }
}

$csv = tempnam( sys_get_temp_dir(), 'fleet-migration-' );
file_put_contents( $csv, "\xEF\xBB\xBFpost_id,current_slug,title\n4063,fiat-500,Fiat 500\n" );
$rows = Rentacar_Core_Fleet_Migration::parse_csv_file( $csv );
unlink( $csv );
fleet_migration_assert( 1 === count( $rows ) && '4063' === $rows[0]['post_id'], 'CSV parsing accepts a UTF-8 BOM and maps headers.' );
fleet_migration_assert( array( 3, 5 ) === Rentacar_Core_Fleet_Migration::parse_closed_range( '3-5' ), 'Closed price ranges parse inclusively.' );
fleet_migration_assert( 15 === Rentacar_Core_Fleet_Migration::parse_open_range( '15+' ), 'Open-ended tier four range parses.' );
fleet_migration_assert( null === Rentacar_Core_Fleet_Migration::parse_closed_range( '5-3' ), 'Inverted ranges are rejected.' );
fleet_migration_assert( Rentacar_Core_Fleet_Migration::is_unconfirmed( 'UNCONFIRMED' ), 'Unconfirmed fields are recognized.' );

$warnings = array();
$engine = Rentacar_Core_Fleet_Migration::vehicle_meta_from_row( array( 'engine' => '1.5 TSI', 'transmission' => 'Direct-shift gearbox' ), 'Škoda Octavia', $warnings );
fleet_migration_assert( '1.5 TSI' === $engine['_rentacar_engine'], 'Engine CSV values map to the structured engine meta key.' );
fleet_migration_assert( 'Direct-shift gearbox' === $engine['gearbox'], 'The established Direct-shift gearbox ACF choice is accepted.' );
$same_engine = Rentacar_Core_Fleet_Migration::vehicle_meta_from_row( array( 'engine' => '1.5 TSI' ), 'Škoda Octavia', $warnings );
fleet_migration_assert( $engine['_rentacar_engine'] === $same_engine['_rentacar_engine'], 'Repeated engine input remains idempotent.' );
$blank_engine = Rentacar_Core_Fleet_Migration::vehicle_meta_from_row( array( 'engine' => '' ), 'Škoda Octavia', $warnings );
$unconfirmed_engine = Rentacar_Core_Fleet_Migration::vehicle_meta_from_row( array( 'engine' => 'UNCONFIRMED' ), 'Škoda Octavia', $warnings );
fleet_migration_assert( ! isset( $blank_engine['_rentacar_engine'] ) && ! isset( $unconfirmed_engine['_rentacar_engine'] ), 'Blank or unconfirmed engines do not overwrite existing engine metadata.' );

$image_directory = sys_get_temp_dir() . '/fleet-image-test-' . uniqid();
mkdir( $image_directory );
$valid_png = base64_decode( 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVQIHWP4z8DwHwAFgAI/ScL6KQAAAABJRU5ErkJggg==' );
file_put_contents( $image_directory . '/skoda-octavia-1-0-etec.webp', $valid_png );
$image_source = Rentacar_Core_Fleet_Migration::inspect_image_source( $image_directory, 'skoda-octavia-1-0-etec.webp' );
fleet_migration_assert( 'INVALID' === $image_source['status'], 'An image extension that does not match its real MIME type is rejected safely.' );
file_put_contents( $image_directory . '/skoda-octavia-1-0-etec.png', $valid_png );
$image_source = Rentacar_Core_Fleet_Migration::inspect_image_source( $image_directory, 'skoda-octavia-1-0-etec.png' );
fleet_migration_assert( 'VALID' === $image_source['status'] && 'image/png' === $image_source['mime'], 'Readable non-empty image files are validated through real MIME and dimensions.' );
fleet_migration_assert( 'MISSING' === Rentacar_Core_Fleet_Migration::inspect_image_source( $image_directory, 'missing.webp' )['status'], 'A missing source image is identified without a write.' );
file_put_contents( $image_directory . '/invalid.webp', '' );
fleet_migration_assert( 'INVALID' === Rentacar_Core_Fleet_Migration::inspect_image_source( $image_directory, 'invalid.webp' )['status'], 'A zero-byte source image is rejected safely.' );
fleet_migration_assert( 'INVALID' === Rentacar_Core_Fleet_Migration::inspect_image_source( $image_directory, '../skoda-octavia-1-0-etec.png' )['status'], 'Image filenames cannot escape the controlled image directory.' );
fleet_migration_assert( is_wp_error( Rentacar_Core_Fleet_Migration::validate_image_manifest( array( array( 'image_file' => 'skoda-octavia-1-0-etec.png' ), array( 'image_file' => 'skoda-octavia-1-0-etec.png' ) ), $image_directory ) ), 'Duplicate CSV image filenames are rejected before import.' );

$GLOBALS['fleet_migration_test_thumbnails'] = array( 2942 => 3052, 4383 => 3052, 4384 => 3052, 4385 => 3052 );
$GLOBALS['fleet_migration_test_attachment_files'] = array( 3052 => '/uploads/old-ford-focus.jpg' );
$GLOBALS['fleet_migration_test_meta'] = array( 3052 => array( '_wp_attachment_image_alt' => 'Ford Focus' ) );
$GLOBALS['fleet_migration_test_attachments_by_hash'] = array();
$GLOBALS['fleet_migration_test_post_types'] = array( 2942 => 'cars', 4383 => 'cars', 4384 => 'cars', 4385 => 'cars' );
$GLOBALS['fleet_migration_test_languages'] = array( 2942 => 'it', 4383 => 'en', 4384 => 'ro', 4385 => 'ru' );
$GLOBALS['fleet_migration_test_translations'] = array( 2942 => array( 'it' => 2942, 'en' => 4383, 'ro' => 4384, 'ru' => 4385 ) );
$image_warnings = array();
$octavia_plan = Rentacar_Core_Fleet_Migration::image_plan( 2942, array( 'title' => 'Škoda Octavia 1.0 e-TEC', 'image_file' => 'skoda-octavia-1-0-etec.png', 'image_alt' => 'Škoda Octavia 1.0 e-TEC a noleggio a Venezia e Treviso' ), $image_directory, 'Škoda Octavia 1.0 e-TEC', $image_warnings );
fleet_migration_assert( 'REPLACE' === $octavia_plan['status'] && 3052 === $octavia_plan['current_attachment'] && 'old-ford-focus.jpg' === $octavia_plan['current_file'] && 0 === $octavia_plan['attachment_id'], 'The Škoda Octavia plan replaces legacy Ford attachment 3052 with a first imported source image.' );
fleet_migration_assert( 'Škoda Octavia 1.0 e-TEC a noleggio a Venezia e Treviso' === $octavia_plan['alt'], 'CSV image ALT is retained for the imported attachment.' );
fleet_migration_assert( array( 4383, 4384, 4385 ) === $octavia_plan['translation_targets'], 'Existing Polylang translations sharing the source image are deliberately selected for featured-image reuse.' );
$first_hash = $octavia_plan['hash'];
$GLOBALS['fleet_migration_test_attachments_by_hash'][ $first_hash ] = 9001;
$GLOBALS['fleet_migration_test_thumbnails'][2942] = 9001;
$GLOBALS['fleet_migration_test_meta'][9001] = array( '_wp_attachment_image_alt' => $octavia_plan['alt'] );
$same_hash_plan = Rentacar_Core_Fleet_Migration::image_plan( 2942, array( 'title' => 'Škoda Octavia 1.0 e-TEC', 'image_file' => 'skoda-octavia-1-0-etec.png', 'image_alt' => $octavia_plan['alt'] ), $image_directory, 'Škoda Octavia 1.0 e-TEC', $image_warnings );
fleet_migration_assert( 'UNCHANGED' === $same_hash_plan['status'] && 9001 === $same_hash_plan['attachment_id'], 'A matching SHA-256 featured attachment is idempotently unchanged.' );
file_put_contents( $image_directory . '/skoda-octavia-1-0-etec.png', base64_decode( 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVQIHWP4//8/AwAI/AL+KDv1XQAAAABJRU5ErkJggg==' ) );
$changed_content_plan = Rentacar_Core_Fleet_Migration::image_plan( 2942, array( 'title' => 'Škoda Octavia 1.0 e-TEC', 'image_file' => 'skoda-octavia-1-0-etec.png', 'image_alt' => $octavia_plan['alt'] ), $image_directory, 'Škoda Octavia 1.0 e-TEC', $image_warnings );
fleet_migration_assert( 'REPLACE' === $changed_content_plan['status'] && $first_hash !== $changed_content_plan['hash'] && 0 === $changed_content_plan['attachment_id'], 'Changed source content with the same filename is detected and scheduled as a replacement.' );
$synced_images = Rentacar_Core_Fleet_Migration::sync_featured_image_translations( 2942, 9002, array( 4383, 4384, 4385 ) );
fleet_migration_assert( 3 === $synced_images && array( 9002, 9002, 9002 ) === array( $GLOBALS['fleet_migration_test_thumbnails'][4383], $GLOBALS['fleet_migration_test_thumbnails'][4384], $GLOBALS['fleet_migration_test_thumbnails'][4385] ), 'Translated posts reuse one imported attachment without creating media duplicates or changing Polylang data.' );
unlink( $image_directory . '/skoda-octavia-1-0-etec.webp' );
unlink( $image_directory . '/skoda-octavia-1-0-etec.png' );
unlink( $image_directory . '/invalid.webp' );
rmdir( $image_directory );

$GLOBALS['fleet_migration_test_posts'] = array(
    (object) array( 'ID' => 100, 'post_name' => 'fiat-500' ),
    (object) array( 'ID' => 101, 'post_name' => 'fiat-500' ),
    (object) array( 'ID' => 102, 'post_name' => 'fiat-500' ),
);
$GLOBALS['fleet_migration_test_languages'] = array( 100 => 'it', 101 => 'en', 102 => 'it' );
fleet_migration_assert( ! Rentacar_Core_Fleet_Migration::slug_is_available_in_vehicle_language( 100, 'fiat-500' ), 'A different vehicle in the same language blocks the requested slug.' );
$GLOBALS['fleet_migration_test_posts'] = array_slice( $GLOBALS['fleet_migration_test_posts'], 0, 2 );
fleet_migration_assert( Rentacar_Core_Fleet_Migration::slug_is_available_in_vehicle_language( 100, 'fiat-500' ), 'A matching slug in a different Polylang language is allowed.' );
$GLOBALS['fleet_migration_test_posts'] = array( (object) array( 'ID' => 100, 'post_name' => 'fiat-500' ) );
fleet_migration_assert( Rentacar_Core_Fleet_Migration::slug_is_available_in_vehicle_language( 100, 'fiat-500' ), 'The current post is never treated as its own slug collision.' );

function fleet_migration_test_slug_write( $post_id, $slug, $foreign_post_id, $foreign_language ) {
    $GLOBALS['fleet_migration_test_posts'] = array(
        (object) array( 'ID' => $post_id, 'post_name' => $slug ),
        (object) array( 'ID' => $foreign_post_id, 'post_name' => $slug ),
    );
    $GLOBALS['fleet_migration_test_languages'] = array( $post_id => 'it', $foreign_post_id => $foreign_language );
    $GLOBALS['fleet_migration_test_post_types'] = array( $post_id => 'cars' );
    $GLOBALS['fleet_migration_test_post_fields'] = array( $post_id => array( 'post_name' => $slug . '-old' ) );
    $GLOBALS['fleet_migration_test_filters'] = array();
    $GLOBALS['fleet_migration_test_update_error'] = false;
    $GLOBALS['fleet_migration_test_force_suffixed_slug'] = false;
    Rentacar_Core_Fleet_Migration::update_post_with_migration_slug_override( $post_id, array( 'ID' => $post_id, 'post_name' => $slug ) );
    fleet_migration_assert( $slug === get_post_field( 'post_name', $post_id ), 'A validated Italian vehicle keeps the exact requested slug after wp_update_post().' );
    fleet_migration_assert( 1 === $GLOBALS['fleet_migration_test_filter_count_during_update'], 'The temporary uniqueness filter is active only while wp_update_post runs.' );
    fleet_migration_assert( $slug === $GLOBALS['fleet_migration_test_filter_observations']['expected'], 'The temporary filter applies to its exact expected post and slug.' );
    fleet_migration_assert( null === $GLOBALS['fleet_migration_test_filter_observations']['wrong_post'] && null === $GLOBALS['fleet_migration_test_filter_observations']['wrong_slug'], 'The temporary filter does not override another post ID or slug.' );
    fleet_migration_assert( empty( $GLOBALS['fleet_migration_test_filters']['pre_wp_unique_post_slug'][10] ), 'The temporary uniqueness filter is removed after wp_update_post().' );
}

fleet_migration_test_slug_write( 4063, 'fiat-500', 4365, 'en' );
fleet_migration_test_slug_write( 4062, 'dacia-duster', 4374, 'en' );

$GLOBALS['fleet_migration_test_posts'] = array(
    (object) array( 'ID' => 4063, 'post_name' => 'fiat-500' ),
    (object) array( 'ID' => 9999, 'post_name' => 'fiat-500' ),
);
$GLOBALS['fleet_migration_test_languages'] = array( 4063 => 'it', 9999 => 'it' );
fleet_migration_assert( ! Rentacar_Core_Fleet_Migration::slug_is_available_in_vehicle_language( 4063, 'fiat-500' ), 'Different Italian cars cannot share a vehicle slug.' );

$GLOBALS['fleet_migration_test_posts'] = array( (object) array( 'ID' => 4063, 'post_name' => 'fiat-500' ) );
$GLOBALS['fleet_migration_test_languages'] = array( 4063 => 'it' );
$GLOBALS['fleet_migration_test_post_types'] = array( 4063 => 'cars' );
$GLOBALS['fleet_migration_test_post_fields'] = array( 4063 => array( 'post_name' => 'fiat-500-old' ) );
$GLOBALS['fleet_migration_test_filters'] = array();
$GLOBALS['fleet_migration_test_update_error'] = true;
$GLOBALS['fleet_migration_test_force_suffixed_slug'] = false;
$GLOBALS['fleet_migration_test_redirect_writes'] = 0;
try {
    Rentacar_Core_Fleet_Migration::update_post_with_migration_slug_override( 4063, array( 'ID' => 4063, 'post_name' => 'fiat-500' ) );
    fleet_migration_assert( false, 'Failed slug persistence must stop before redirect creation.' );
} catch ( RuntimeException $exception ) {
    fleet_migration_assert( empty( $GLOBALS['fleet_migration_test_filters']['pre_wp_unique_post_slug'][10] ), 'The temporary uniqueness filter is removed when wp_update_post fails, before any redirect can be created.' );
    fleet_migration_assert( 0 === $GLOBALS['fleet_migration_test_redirect_writes'], 'A failed wp_update_post leaves redirect creation unreachable.' );
}

$GLOBALS['fleet_migration_test_update_error'] = false;
$GLOBALS['fleet_migration_test_force_suffixed_slug'] = true;
try {
    Rentacar_Core_Fleet_Migration::update_post_with_migration_slug_override( 4063, array( 'ID' => 4063, 'post_name' => 'fiat-500' ) );
    fleet_migration_assert( false, 'A WordPress-generated suffixed slug must fail exact slug persistence.' );
} catch ( RuntimeException $exception ) {
    fleet_migration_assert( empty( $GLOBALS['fleet_migration_test_filters']['pre_wp_unique_post_slug'][10] ), 'The temporary uniqueness filter is removed when exact slug persistence fails.' );
    fleet_migration_assert( 0 === $GLOBALS['fleet_migration_test_redirect_writes'], 'A redirect cannot be created before exact slug persistence succeeds.' );
}
$GLOBALS['fleet_migration_test_force_suffixed_slug'] = false;

$GLOBALS['fleet_migration_test_redirect_writes'] = 0;
$redirect = Rentacar_Core_Fleet_Migration::redirect_preview( 'https://example.test/cars/rent-a-car-ford-focus/', 'https://example.test/cars/skoda-octavia-1-0-etec/' );
fleet_migration_assert( '/cars/rent-a-car-ford-focus/' === $redirect['from'] && '/cars/skoda-octavia-1-0-etec/' === $redirect['to'], 'Dry-run redirect previews show the exact old and target paths.' );
fleet_migration_assert( 0 === $GLOBALS['fleet_migration_test_redirect_writes'], 'Dry-run redirect preview does not write a Rank Math redirect.' );
unset( $_SERVER['HTTP_HOST'] );
$fleet_redirect_plan = Rentacar_Core_Fleet_Migration::fleet_redirect_plan( 'rent-a-car-ford-focus', 'skoda-octavia-1-0-etec' );
fleet_migration_assert( 'cars/rent-a-car-ford-focus' === $fleet_redirect_plan['source'] && 'https://local.test/cars/skoda-octavia-1-0-etec/' === $fleet_redirect_plan['destination'], 'Redirect plans use a valid WordPress-configured destination when WP-CLI has no HTTP_HOST.' );
fleet_migration_assert( false === strpos( $fleet_redirect_plan['destination'], 'http:/cars/' ), 'Redirect destinations never use the malformed http:/cars form.' );
fleet_migration_assert( null === Rentacar_Core_Fleet_Migration::fleet_redirect_plan( 'fiat-500', 'fiat-500' ), 'Matching old and new slugs never create a redirect plan.' );
fleet_migration_assert( Rentacar_Core_Fleet_Migration::rank_math_redirect_matches_plan( array( 'header_code' => '301', 'status' => 'active', 'url_to' => $fleet_redirect_plan['destination'] ), $fleet_redirect_plan ), 'An existing matching Rank Math redirect is recognized as idempotent.' );
fleet_migration_assert( ! Rentacar_Core_Fleet_Migration::rank_math_redirect_matches_plan( array( 'header_code' => '301', 'status' => 'active', 'url_to' => 'http:/cars/skoda-octavia-1-0-etec/' ), $fleet_redirect_plan ), 'Malformed existing Rank Math destinations are never treated as idempotent matches.' );

$valid = Rentacar_Core_Fleet_Migration::pricing_meta_from_row( array(
    'price_tier_1_range' => '3-5', 'price_tier_1_price' => '50',
    'price_tier_2_range' => '6-14', 'price_tier_2_price' => '45',
    'price_tier_3_range' => '15-29', 'price_tier_3_price' => '40',
    'price_tier_4_range' => '30+', 'price_tier_4_price' => '35',
) );
fleet_migration_assert( is_array( $valid ) && '35' === $valid['price4'], 'Valid continuous pricing produces legacy meta values.' );
$overlap = Rentacar_Core_Fleet_Migration::pricing_meta_from_row( array(
    'price_tier_1_range' => '3-5', 'price_tier_1_price' => '50',
    'price_tier_2_range' => '5-14', 'price_tier_2_price' => '45',
    'price_tier_3_range' => '15-29', 'price_tier_3_price' => '40',
    'price_tier_4_price' => '35',
) );
fleet_migration_assert( is_wp_error( $overlap ), 'Overlapping pricing is rejected without normalization.' );
$unconfirmed = Rentacar_Core_Fleet_Migration::pricing_meta_from_row( array(
    'price_tier_1_range' => '3-5', 'price_tier_1_price' => 'UNCONFIRMED',
    'price_tier_2_range' => '6-14', 'price_tier_2_price' => '45',
    'price_tier_3_range' => '15-29', 'price_tier_3_price' => '40',
    'price_tier_4_price' => '35',
) );
fleet_migration_assert( is_wp_error( $unconfirmed ), 'Unconfirmed prices cannot replace valid pricing.' );

echo "Fleet migration checks passed.\n";
