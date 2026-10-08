<?php
/** Exercises the provisioner's real mutation and rollback paths with in-memory WordPress APIs. */
$root = sys_get_temp_dir() . '/rentacar-provision-test-' . uniqid();
mkdir( $root . '/wp-admin/includes', 0777, true );
foreach ( array( 'file.php', 'media.php', 'image.php' ) as $include ) file_put_contents( $root . '/wp-admin/includes/' . $include, '<?php' );
define( 'ABSPATH', $root . '/' );
class WP_Error {
    private $message;
    public function __construct( $code, $message ) { $this->message = $message; }
    public function get_error_message() { return $this->message; }
}
function is_wp_error( $value ) { return $value instanceof WP_Error; }
function check( $condition, $message ) { if ( ! $condition ) { fwrite( STDERR, "FAIL: $message\n" ); exit( 1 ); } }
function reset_site() {
    $GLOBALS['site'] = array( 'posts' => array(), 'meta' => array(), 'language' => array(), 'families' => array(), 'thumbnails' => array(), 'next_id' => 100, 'insert_attempts' => 0, 'fail_insert_at' => 0, 'publish_attempts' => 0, 'fail_publish_at' => 0, 'deleted_posts' => array(), 'deleted_attachments' => array(), 'fail_metadata' => false );
    $GLOBALS['site']['posts'][99] = (object) array( 'ID' => 99, 'post_type' => 'cars', 'post_status' => 'publish', 'post_name' => 'unrelated', 'post_title' => 'Unrelated', 'post_content' => 'Keep this' );
}
function get_post( $id ) { return $GLOBALS['site']['posts'][ $id ] ?? null; }
function get_post_type( $id ) { return get_post( $id )->post_type ?? false; }
function get_post_status( $id ) { return get_post( $id )->post_status ?? false; }
function get_post_field( $field, $id ) { return get_post( $id )->$field ?? ''; }
function get_post_meta( $id, $key, $single = true ) { return $GLOBALS['site']['meta'][ $id ][ $key ] ?? ''; }
function update_post_meta( $id, $key, $value ) { $GLOBALS['site']['meta'][ $id ][ $key ] = $value; return true; }
function get_posts( $args ) {
    $found = array();
    foreach ( $GLOBALS['site']['posts'] as $id => $post ) {
        if ( $post->post_type !== $args['post_type'] ) continue;
        if ( isset( $args['meta_key'] ) && get_post_meta( $id, $args['meta_key'], true ) !== $args['meta_value'] ) continue;
        $found[] = $id;
    }
    return $found;
}
function wp_insert_post( $data, $error = false ) {
    $site =& $GLOBALS['site'];
    $site['insert_attempts']++;
    if ( $site['fail_insert_at'] === $site['insert_attempts'] ) return new WP_Error( 'injected', 'Injected insert failure' );
    $id = ++$site['next_id'];
    $site['posts'][ $id ] = (object) array_merge( array( 'ID' => $id ), $data );
    return $id;
}
function wp_delete_post( $id, $force = false ) {
    if ( ! $force || ! get_post( $id ) ) return false;
    unset( $GLOBALS['site']['posts'][ $id ], $GLOBALS['site']['meta'][ $id ], $GLOBALS['site']['language'][ $id ], $GLOBALS['site']['thumbnails'][ $id ], $GLOBALS['site']['families'][ $id ] );
    $GLOBALS['site']['deleted_posts'][] = $id;
    return (object) array( 'ID' => $id );
}
function wp_delete_attachment( $id, $force = false ) {
    if ( ! $force || ! get_post( $id ) ) return false;
    unset( $GLOBALS['site']['posts'][ $id ], $GLOBALS['site']['meta'][ $id ] );
    $GLOBALS['site']['deleted_attachments'][] = $id;
    return (object) array( 'ID' => $id );
}
function pll_set_post_language( $id, $language ) { $GLOBALS['site']['language'][ $id ] = $language; }
function pll_get_post_language( $id, $format = 'slug' ) { return $GLOBALS['site']['language'][ $id ] ?? false; }
function pll_save_post_translations( $ids ) { foreach ( $ids as $id ) $GLOBALS['site']['families'][ $id ] = $ids; }
function pll_get_post_translations( $id ) { return $GLOBALS['site']['families'][ $id ] ?? array(); }
function set_post_thumbnail( $id, $attachment ) { $GLOBALS['site']['thumbnails'][ $id ] = $attachment; return true; }
function get_post_thumbnail_id( $id ) { return $GLOBALS['site']['thumbnails'][ $id ] ?? 0; }
function wp_attachment_is_image( $id ) { return 'attachment' === get_post_type( $id ); }
function wp_check_filetype_and_ext( $path, $filename ) { return array( 'type' => 'image/webp' ); }
function wp_tempnam( $filename ) { return tempnam( sys_get_temp_dir(), 'rentacar-media-' ); }
function media_handle_sideload( $file, $parent, $title ) {
    $id = ++$GLOBALS['site']['next_id'];
    $GLOBALS['site']['posts'][ $id ] = (object) array( 'ID' => $id, 'post_type' => 'attachment', 'post_status' => 'inherit', 'post_title' => $title );
    unlink( $file['tmp_name'] );
    return $id;
}
function wp_get_attachment_metadata( $id ) { return $GLOBALS['site']['fail_metadata'] ? false : array( 'width' => 1, 'height' => 1 ); }
function wp_json_encode( $value ) { return json_encode( $value ); }
class Rentacar_Core_Rental_Policy { public static function minimum_rental_days() { return 3; } }
class Rentacar_Core_Vehicle_Maintenance {
    const STARTING_PRICE_META = '_rentacar_starting_price';
    const POWERTRAIN_META = '_rentacar_powertrain';
    const POWERTRAINS = array( 'petrol', 'diesel', 'hybrid', 'plug_in_hybrid', 'electric', 'other' );
    public static function starting_price_result( $id ) {
        $prices = array_map( static function( $key ) use ( $id ) { return (float) get_post_meta( $id, $key, true ); }, array( 'price', 'price2', 'price3', 'price4' ) );
        if ( min( $prices ) <= 0 ) return array( 'status' => 'invalid', 'price' => null );
        return array( 'status' => 'valid', 'price' => min( $prices ) );
    }
    public static function update_starting_price( $id ) {
        $result = self::starting_price_result( $id );
        if ( 'valid' === $result['status'] ) update_post_meta( $id, self::STARTING_PRICE_META, (string) $result['price'] );
        return $result;
    }
}
class Rentacar_Core_Fleet_Migration {
    const IMAGE_HASH_META = '_rentacar_fleet_migration_source_hash';
    public static function pricing_meta_from_row( $row, $minimum ) {
        $ranges = array( '3-5', '6-14', '15-29', '30+' );
        foreach ( $ranges as $index => $range ) if ( $row['price_tier_' . ( $index + 1 ) . '_range'] !== $range || ! is_numeric( $row['price_tier_' . ( $index + 1 ) . '_price'] ) || $row['price_tier_' . ( $index + 1 ) . '_price'] <= 0 ) return new WP_Error( 'pricing', 'Invalid pricing' );
        return array( 'price_1_days_1' => '3', 'price_1_days_2' => '5', 'price' => (string) $row['price_tier_1_price'], 'price_2_days_1' => '6', 'price_2_days_2' => '14', 'price2' => (string) $row['price_tier_2_price'], 'price_3_days_1' => '15', 'price_3_days_2' => '29', 'price3' => (string) $row['price_tier_3_price'], 'price4' => (string) $row['price_tier_4_price'] );
    }
    public static function slug_is_available_in_vehicle_language( $id, $slug ) { return true; }
    public static function update_vehicle_post_without_translation_sync( $id, $change ) {
        $site =& $GLOBALS['site'];
        foreach ( $change as $key => $value ) if ( 'ID' !== $key ) $site['posts'][ $id ]->$key = $value;
        if ( isset( $change['post_status'] ) && 'publish' === $change['post_status'] ) {
            $site['publish_attempts']++;
            if ( $site['fail_publish_at'] === $site['publish_attempts'] ) throw new RuntimeException( 'Injected publish failure' );
        }
    }
}
class Rentacar_Core_Fleet_Translation_Pricing_Sync {
    public static function validated_source_pricing( $id ) {
        $price = Rentacar_Core_Vehicle_Maintenance::starting_price_result( $id );
        if ( 'valid' !== $price['status'] ) return new WP_Error( 'pricing', 'Invalid current pricing' );
        return array( Rentacar_Core_Vehicle_Maintenance::STARTING_PRICE_META => (string) $price['price'] );
    }
}
require_once dirname( __DIR__, 2 ) . '/plugin/rentacar-core/src/Cli/FleetProvision.php';
function fixture_plan( $key ) {
    $content = array();
    foreach ( Rentacar_Core_Fleet_Provision::LANGUAGES as $language ) $content[ $language ] = array( 'content' => $language . ' copy', 'seo_title' => $language . ' SEO title', 'seo_description' => $language . ' SEO description' );
    $vehicle = array( 'key' => $key, 'slug' => $key, 'title' => strtoupper( $key ), 'image' => $key . '.webp', 'gearbox' => 'Automatic', 'passengers' => 5, 'doors' => 5, 'powertrain' => 'diesel', 'engine' => 'TDI', 'pricing' => array( 'price_tier_1_range' => '3-5', 'price_tier_1_price' => 85, 'price_tier_2_range' => '6-14', 'price_tier_2_price' => 80, 'price_tier_3_range' => '15-29', 'price_tier_3_price' => 75, 'price_tier_4_range' => '30+', 'price_tier_4_price' => 70 ), 'locales' => $content );
    $path = ABSPATH . $key . '.webp'; file_put_contents( $path, 'test image bytes for ' . $key );
    return array( 'state' => 'CREATE', 'vehicle' => $vehicle, 'posts' => array_fill_keys( Rentacar_Core_Fleet_Provision::LANGUAGES, 0 ), 'image' => array( 'path' => $path, 'filename' => $vehicle['image'], 'hash' => hash_file( 'sha256', $path ) ) );
}
function family_ids( $result, $key ) { return array_values( $result[ $key ]['posts'] ); }
function assert_only_unrelated_remains( $label ) {
    check( array_keys( $GLOBALS['site']['posts'] ) === array( 99 ), $label . ': created posts or media remained.' );
    check( get_post( 99 )->post_content === 'Keep this', $label . ': unrelated post changed.' );
}
foreach ( array( 2, 3, 4 ) as $failure_attempt ) {
    reset_site(); $plan = fixture_plan( 'first-family' ); $GLOBALS['site']['fail_insert_at'] = $failure_attempt;
    $result = Rentacar_Core_Fleet_Provision::apply_plans( array( $plan ) );
    check( is_wp_error( $result ) && false !== strpos( $result->get_error_message(), 'Injected insert failure' ), 'Original creation failure is reported.' );
    assert_only_unrelated_remains( 'locale failure ' . $failure_attempt );
    check( count( $GLOBALS['site']['deleted_posts'] ) === $failure_attempt - 1 && count( $GLOBALS['site']['deleted_attachments'] ) === 1, 'Created posts and attachment were removed.' );
}
reset_site(); $plan = fixture_plan( 'first-family' ); $GLOBALS['site']['fail_metadata'] = true;
$result = Rentacar_Core_Fleet_Provision::apply_plans( array( $plan ) );
check( is_wp_error( $result ), 'Attachment metadata failure aborts creation.' );
assert_only_unrelated_remains( 'attachment metadata failure' );
check( count( $GLOBALS['site']['deleted_attachments'] ) === 1, 'Attachment created before metadata failure was removed.' );
reset_site(); $plan = fixture_plan( 'first-family' );
$existing_attachment = ++$GLOBALS['site']['next_id'];
$GLOBALS['site']['posts'][ $existing_attachment ] = (object) array( 'ID' => $existing_attachment, 'post_type' => 'attachment', 'post_status' => 'inherit' );
update_post_meta( $existing_attachment, Rentacar_Core_Fleet_Migration::IMAGE_HASH_META, $plan['image']['hash'] );
update_post_meta( $existing_attachment, Rentacar_Core_Fleet_Provision::IMAGE_KEY_META, 'first-family' );
$GLOBALS['site']['fail_insert_at'] = 2;
$result = Rentacar_Core_Fleet_Provision::apply_plans( array( $plan ) );
check( is_wp_error( $result ) && get_post( $existing_attachment ) && ! $GLOBALS['site']['deleted_attachments'], 'Pre-existing matching attachment survives rollback.' );
reset_site(); $first = fixture_plan( 'first-family' ); $second = fixture_plan( 'second-family' ); $GLOBALS['site']['fail_insert_at'] = 6;
$result = Rentacar_Core_Fleet_Provision::apply_plans( array( $first, $second ) );
check( is_wp_error( $result ), 'Later family failure aborts batch.' );
assert_only_unrelated_remains( 'batch failure' );
check( count( $GLOBALS['site']['deleted_posts'] ) === 5 && count( $GLOBALS['site']['deleted_attachments'] ) === 2, 'All new batch posts and attachments were removed: ' . count( $GLOBALS['site']['deleted_posts'] ) . '/' . count( $GLOBALS['site']['deleted_attachments'] ) );
reset_site(); $first = fixture_plan( 'first-family' ); $second = fixture_plan( 'second-family' );
$created = Rentacar_Core_Fleet_Provision::apply_plans( array( $first, $second ) );
check( ! is_wp_error( $created ) && count( $created ) === 2, 'Two families were created.' );
$first['state'] = $second['state'] = 'EXISTING';
$first['posts'] = $created['first-family']['posts']; $second['posts'] = $created['second-family']['posts'];
check( true === Rentacar_Core_Fleet_Provision::publication_check( $first ), 'Complete shared-image family passes publication check.' );
$id = $first['posts']['en'];
foreach ( array( 'post_title' => 'title', 'gearbox' => 'gearbox', 'max_passagers' => 'passengers', 'doors' => 'doors', Rentacar_Core_Vehicle_Maintenance::POWERTRAIN_META => 'powertrain', '_rentacar_engine' => 'engine' ) as $field => $label ) {
    if ( 'post_title' === $field ) { $old = get_post( $id )->post_title; get_post( $id )->post_title = 'Wrong'; }
    else { $old = get_post_meta( $id, $field, true ); update_post_meta( $id, $field, 'Wrong' ); }
    check( is_wp_error( Rentacar_Core_Fleet_Provision::publication_check( $first ) ), 'Wrong ' . $label . ' blocks publication.' );
    if ( 'post_title' === $field ) get_post( $id )->post_title = $old; else update_post_meta( $id, $field, $old );
}
$old_price = get_post_meta( $id, 'price', true );
update_post_meta( $id, 'price', '91' ); Rentacar_Core_Vehicle_Maintenance::update_starting_price( $id );
check( true === Rentacar_Core_Fleet_Provision::publication_check( $first ), 'Valid editor-modified price remains publishable.' );
update_post_meta( $id, Rentacar_Core_Vehicle_Maintenance::STARTING_PRICE_META, '69' );
check( is_wp_error( Rentacar_Core_Fleet_Provision::publication_check( $first ) ), 'Stale derived price blocks publication.' );
Rentacar_Core_Vehicle_Maintenance::update_starting_price( $id );
get_post( $id )->post_content = 'Editor copy'; update_post_meta( $id, 'rank_math_description', 'Editor SEO' );
$existing_result = Rentacar_Core_Fleet_Provision::apply_plans( array( $first ) );
check( ! is_wp_error( $existing_result ) && ! $existing_result && get_post_meta( $id, 'price', true ) === '91' && get_post( $id )->post_content === 'Editor copy' && get_post_meta( $id, 'rank_math_description', true ) === 'Editor SEO', 'Existing family rerun preserves editor changes.' );
$incomplete = $first; $incomplete['posts']['ru'] = 0;
check( is_wp_error( Rentacar_Core_Fleet_Provision::publication_check( $incomplete ) ), 'Incomplete family blocks publication.' );
$wrong_relation = $GLOBALS['site']['families'][ $first['posts']['it'] ]; $GLOBALS['site']['families'][ $first['posts']['it'] ]['ru'] = 99;
check( is_wp_error( Rentacar_Core_Fleet_Provision::publication_check( $first ) ), 'Wrong Polylang relation blocks publication.' );
$GLOBALS['site']['families'][ $first['posts']['it'] ] = $wrong_relation;
$old_image = get_post_thumbnail_id( $id ); set_post_thumbnail( $id, $created['second-family']['attachment_id'] );
check( is_wp_error( Rentacar_Core_Fleet_Provision::publication_check( $first ) ), 'Inconsistent attachment blocks publication.' );
set_post_thumbnail( $id, $old_image );
check( true === Rentacar_Core_Fleet_Provision::publication_check( $first ), 'Restored family passes publication check.' );
$all = array_merge( family_ids( $created, 'first-family' ), family_ids( $created, 'second-family' ) );
$GLOBALS['site']['posts'][ $all[0] ]->post_status = 'publish';
$GLOBALS['site']['fail_publish_at'] = 5;
$result = Rentacar_Core_Fleet_Provision::publish_plans( array( $first, $second ) );
check( is_wp_error( $result ) && false !== strpos( $result->get_error_message(), 'Injected publish failure' ), 'Original publish failure is reported.' );
foreach ( $all as $index => $post_id ) check( get_post_status( $post_id ) === ( 0 === $index ? 'publish' : 'draft' ), 'Every targeted status returned to its original value.' );
check( get_post_status( 99 ) === 'publish', 'Unrelated status is untouched.' );
$GLOBALS['site']['fail_publish_at'] = 0;
check( true === Rentacar_Core_Fleet_Provision::publish_plans( array( $first, $second ) ), 'Complete batch publishes after failure is removed.' );
foreach ( $all as $post_id ) check( get_post_status( $post_id ) === 'publish', 'Every exact target is published.' );
foreach ( glob( $root . '/*' ) as $file ) if ( is_file( $file ) ) unlink( $file );
foreach ( array( 'file.php', 'media.php', 'image.php' ) as $include ) unlink( $root . '/wp-admin/includes/' . $include );
rmdir( $root . '/wp-admin/includes' ); rmdir( $root . '/wp-admin' ); rmdir( $root );
echo "Fleet provision mutation checks passed.\n";
