<?php
/** Ensures private reservation storage retains normalized phone fields. */
define( 'ABSPATH', __DIR__ . '/' );
$GLOBALS['phone_store_meta'] = array();
function __( $text ) { return $text; }
function is_wp_error( $value ) { return false; }
function wp_insert_post() { return 42; }
function update_post_meta( $post_id, $key, $value ) { $GLOBALS['phone_store_meta'][ $key ] = $value; }

require_once dirname( __DIR__, 2 ) . '/plugin/rentacar-core/src/Enquiries/ReservationRequest.php';
require_once dirname( __DIR__, 2 ) . '/plugin/rentacar-core/src/Enquiries/ReservationStore.php';

function phone_storage_assert( $condition, $message ) { if ( ! $condition ) { fwrite( STDERR, "FAIL: {$message}\n" ); exit( 1 ); } }

$request = new Rentacar_Core_Reservation_Request( array(
    'reference' => 'PHONE-TEST', 'phone' => '+37369123456', 'phone_country' => 'MD', 'phone_calling_code' => '+373',
    'phone_national' => '69123456', 'phone_e164' => '+37369123456', 'phone_display' => '+373 691 23 456',
    'after_hours_pickup' => 25, 'after_hours_return' => 50, 'inter_airport_surcharge' => 25,
) );
( new Rentacar_Core_Reservation_Store() )->create( $request );

phone_storage_assert( '+37369123456' === $GLOBALS['phone_store_meta']['_rentacar_phone'], 'The legacy phone property stores the canonical E.164 value.' );
phone_storage_assert( 'MD' === $GLOBALS['phone_store_meta']['_rentacar_phone_country'] && '+373' === $GLOBALS['phone_store_meta']['_rentacar_phone_calling_code'], 'Storage retains country and calling-code structure.' );
phone_storage_assert( '69123456' === $GLOBALS['phone_store_meta']['_rentacar_phone_national'] && '+37369123456' === $GLOBALS['phone_store_meta']['_rentacar_phone_e164'], 'Storage retains normalized national and E.164 values.' );
phone_storage_assert( 25 === $GLOBALS['phone_store_meta']['_rentacar_after_hours_pickup'] && 50 === $GLOBALS['phone_store_meta']['_rentacar_after_hours_return'] && 25 === $GLOBALS['phone_store_meta']['_rentacar_inter_airport_surcharge'], 'Storage retains top-level surcharge fields for notification and retry support.' );

echo "Phone storage checks passed.\n";
