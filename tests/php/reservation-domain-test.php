<?php
/** Minimal PHP 7.4 checks for non-WordPress reservation domain values. */
define( 'ABSPATH', __DIR__ . '/' );
function wp_date( $format ) { return '20260723'; }
function wp_generate_password() { return 'a1B2c3'; }
require_once dirname( __DIR__, 2 ) . '/plugin/rentacar-core/src/Enquiries/ReservationReference.php';
require_once dirname( __DIR__, 2 ) . '/plugin/rentacar-core/src/Enquiries/ReservationRequest.php';
function reservation_assert( $condition, $message ) { if ( ! $condition ) { fwrite( STDERR, "FAIL: {$message}\n" ); exit( 1 ); } }
$reference = Rentacar_Core_Reservation_Reference::generate();
reservation_assert( 0 === strpos( $reference, 'RAV-20260723-' ), 'Reference has a stable non-personal prefix.' );
$request = new Rentacar_Core_Reservation_Request( array( 'vehicle_id' => 123, 'email' => 'customer@example.test' ) );
reservation_assert( 123 === $request->get( 'vehicle_id' ), 'Request exposes supplied vehicle id.' );
reservation_assert( null === $request->get( 'missing' ), 'Request has safe missing-value behavior.' );
echo "Reservation domain checks passed.\n";
