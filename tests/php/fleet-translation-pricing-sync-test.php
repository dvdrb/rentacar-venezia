<?php
/** Focused checks for pricing-only Polylang translation synchronization. */
define( 'ABSPATH', __DIR__ . '/' );

class WP_Error {
    private $code;
    private $message;
    public function __construct( $code = '', $message = '' ) { $this->code = $code; $this->message = $message; }
    public function get_error_message() { return $this->message; }
}
function is_wp_error( $value ) { return $value instanceof WP_Error; }
function absint( $value ) { return abs( (int) $value ); }
function get_post( $post_id ) { return $GLOBALS['translation_pricing_posts'][ $post_id ] ?? null; }
function get_post_type( $post_id ) { return isset( $GLOBALS['translation_pricing_posts'][ $post_id ] ) ? $GLOBALS['translation_pricing_posts'][ $post_id ]->post_type : ''; }
function get_post_meta( $post_id, $key, $single = true ) { return $GLOBALS['translation_pricing_meta'][ $post_id ][ $key ] ?? ''; }
function update_post_meta( $post_id, $key, $value ) { $GLOBALS['translation_pricing_meta'][ $post_id ][ $key ] = (string) $value; $GLOBALS['translation_pricing_writes'][] = array( 'post_id' => $post_id, 'key' => $key, 'value' => (string) $value ); return true; }
function pll_get_post_language( $post_id, $field = 'slug' ) { return $GLOBALS['translation_pricing_languages'][ $post_id ] ?? false; }
function pll_get_post_translations( $post_id ) { return $GLOBALS['translation_pricing_relations'][ $post_id ] ?? array(); }

require_once dirname( __DIR__, 2 ) . '/plugin/rentacar-core/src/Cli/FleetMigration.php';
require_once dirname( __DIR__, 2 ) . '/plugin/rentacar-core/src/Cli/FleetTranslationPricingSync.php';

function translation_pricing_assert( $condition, $message ) { if ( ! $condition ) { fwrite( STDERR, "FAIL: {$message}\n" ); exit( 1 ); } }
function translation_pricing_post( $id, $title, $slug ) { return (object) array( 'ID' => $id, 'post_type' => 'cars', 'post_title' => $title, 'post_name' => $slug ); }
function translation_pricing_schedule( $first_start = '3', $first_end = '5', $second_start = '6', $second_end = '14', $third_start = '15', $third_end = '29' ) {
    return array(
        'price_1_days_1' => $first_start, 'price_1_days_2' => $first_end, 'price' => '95',
        'price_2_days_1' => $second_start, 'price_2_days_2' => $second_end, 'price2' => '90',
        'price_3_days_1' => $third_start, 'price_3_days_2' => $third_end, 'price3' => '85',
        'price4' => '70', '_rentacar_starting_price' => '70',
    );
}

$GLOBALS['translation_pricing_posts'] = array(
    10 => translation_pricing_post( 10, 'Peugeot 3008', 'peugeot-3008' ),
    11 => translation_pricing_post( 11, 'Peugeot 3008 EN', 'peugeot-3008-en' ),
    12 => translation_pricing_post( 12, 'Peugeot 3008 RO', 'peugeot-3008-ro' ),
);
$GLOBALS['translation_pricing_languages'] = array( 10 => 'it', 11 => 'en', 12 => 'ro' );
$GLOBALS['translation_pricing_relations'] = array(
    10 => array( 'it' => 10, 'en' => 11, 'ro' => 12 ),
    11 => array( 'it' => 10, 'en' => 11, 'ro' => 12 ),
    12 => array( 'it' => 10, 'en' => 11, 'ro' => 12 ),
);
$GLOBALS['translation_pricing_meta'] = array(
    10 => translation_pricing_schedule(),
    11 => array_merge( translation_pricing_schedule( '2', '5', '5', '14' ), array( 'gearbox' => 'Manual', 'rank_math_title' => 'English SEO', '_rentacar_engine' => '1.5' ) ),
    12 => array_merge( translation_pricing_schedule(), array( 'gearbox' => 'Automatic', 'rank_math_title' => 'Romanian SEO', '_rentacar_engine' => '1.6' ) ),
);
unset( $GLOBALS['translation_pricing_meta'][12]['_rentacar_starting_price'] );
$GLOBALS['translation_pricing_writes'] = array();
$relations_before = $GLOBALS['translation_pricing_relations'];
$target_before = $GLOBALS['translation_pricing_meta'][11];

$dry_run = Rentacar_Core_Fleet_Translation_Pricing_Sync::synchronize_source( 10, 'it', false );
translation_pricing_assert( 2 === $dry_run['counts']['translations'] && 1 === $dry_run['counts']['updated'] && 1 === $dry_run['counts']['unchanged'] && 1 === $dry_run['counts']['missing'], 'Dry-run identifies one changed existing translation, one unchanged translation, and missing RU safely.' );
translation_pricing_assert( empty( $GLOBALS['translation_pricing_writes'] ), 'Dry-run performs no metadata writes.' );
translation_pricing_assert( $target_before === $GLOBALS['translation_pricing_meta'][11], 'Dry-run leaves target pricing and non-pricing metadata intact.' );
translation_pricing_assert( $relations_before === $GLOBALS['translation_pricing_relations'], 'Dry-run preserves the Polylang relation map.' );

$apply = Rentacar_Core_Fleet_Translation_Pricing_Sync::synchronize_source( 10, 'it', true );
translation_pricing_assert( 1 === $apply['counts']['updated'] && 0 === $apply['counts']['errors'], 'Apply synchronizes the changed existing translation without an error.' );
$source_pricing = Rentacar_Core_Fleet_Translation_Pricing_Sync::validated_source_pricing( 10 );
translation_pricing_assert( ! is_wp_error( $source_pricing ) && ! Rentacar_Core_Fleet_Translation_Pricing_Sync::pricing_changes( 11, $source_pricing ) && '70' === $GLOBALS['translation_pricing_meta'][11]['_rentacar_starting_price'], 'Applied EN pricing exactly matches the validated Italian source including derived starting price.' );
translation_pricing_assert( 'Manual' === $GLOBALS['translation_pricing_meta'][11]['gearbox'] && 'English SEO' === $GLOBALS['translation_pricing_meta'][11]['rank_math_title'] && '1.5' === $GLOBALS['translation_pricing_meta'][11]['_rentacar_engine'], 'Apply changes only pricing metadata and leaves translated technical and SEO fields untouched.' );
translation_pricing_assert( 'peugeot-3008-en' === $GLOBALS['translation_pricing_posts'][11]->post_name && 'Peugeot 3008 EN' === $GLOBALS['translation_pricing_posts'][11]->post_title, 'Apply leaves translated title and slug untouched.' );
translation_pricing_assert( $relations_before === $GLOBALS['translation_pricing_relations'], 'Apply preserves Polylang language relationships.' );
$written_keys = array_unique( array_column( $GLOBALS['translation_pricing_writes'], 'key' ) );
translation_pricing_assert( ! array_diff( $written_keys, array_merge( Rentacar_Core_Fleet_Translation_Pricing_Sync::pricing_meta_keys(), array( '_rentacar_starting_price' ) ) ), 'Apply writes only the pricing keys and derived starting-price metadata.' );

$writes_after_apply = count( $GLOBALS['translation_pricing_writes'] );
$second_apply = Rentacar_Core_Fleet_Translation_Pricing_Sync::synchronize_source( 10, 'it', true );
translation_pricing_assert( 0 === $second_apply['counts']['updated'] && 2 === $second_apply['counts']['unchanged'], 'A second apply is idempotent once translated price tiers match the Italian pricing.' );
translation_pricing_assert( $writes_after_apply === count( $GLOBALS['translation_pricing_writes'] ), 'Idempotent apply makes no additional writes.' );

$GLOBALS['translation_pricing_meta'][10]['price_2_days_1'] = '5';
$writes_before_invalid = count( $GLOBALS['translation_pricing_writes'] );
$invalid = Rentacar_Core_Fleet_Translation_Pricing_Sync::synchronize_source( 10, 'it', true );
translation_pricing_assert( 1 === $invalid['counts']['errors'] && 0 === $invalid['counts']['translations'], 'Invalid Italian source pricing is rejected before any translation is scanned or written.' );
translation_pricing_assert( $writes_before_invalid === count( $GLOBALS['translation_pricing_writes'] ), 'Invalid Italian source pricing is never propagated.' );

echo "Fleet translation pricing sync checks passed.\n";
