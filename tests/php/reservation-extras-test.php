<?php
/** Focused PHP 7.4 checks for authoritative reservation-extra behaviour. */
define( 'ABSPATH', __DIR__ . '/' );
$GLOBALS['reservation_extra_options'] = array();
$GLOBALS['reservation_extra_mail'] = array();

function get_option( $key, $default = false ) { return array_key_exists( $key, $GLOBALS['reservation_extra_options'] ) ? $GLOBALS['reservation_extra_options'][ $key ] : $default; }
function apply_filters( $tag, $value ) { return $value; }
function __( $text ) { return $text; }
function absint( $value ) { return abs( (int) $value ); }
function number_format_i18n( $number, $decimals = 0 ) { return number_format( $number, $decimals, '.', '' ); }
function get_bloginfo() { return 'Rentacar test'; }
function wp_mail( $recipient, $subject, $message, $headers = array() ) { $GLOBALS['reservation_extra_mail'] = compact( 'recipient', 'subject', 'message', 'headers' ); return true; }

require_once dirname( __DIR__, 2 ) . '/plugin/rentacar-core/src/Settings/ReservationExtras.php';
require_once dirname( __DIR__, 2 ) . '/plugin/rentacar-core/src/Pricing/RentalDurationCalculator.php';
require_once dirname( __DIR__, 2 ) . '/plugin/rentacar-core/src/Enquiries/ReservationRequest.php';
require_once dirname( __DIR__, 2 ) . '/plugin/rentacar-core/src/Enquiries/ReservationEmailTemplate.php';
require_once dirname( __DIR__, 2 ) . '/plugin/rentacar-core/src/Enquiries/BusinessNotification.php';

function reservation_extra_assert( $condition, $message ) { if ( ! $condition ) { fwrite( STDERR, "FAIL: {$message}\n" ); exit( 1 ); } }

$GLOBALS['reservation_extra_options'][ Rentacar_Core_Reservation_Extras::OPTION ] = array(
    'child_seat' => array( 'enabled' => false, 'pricing_type' => 'per_day', 'price' => 7.5, 'max_quantity' => 1 ),
    'additional_driver' => array( 'enabled' => true, 'pricing_type' => 'fixed', 'price' => 25, 'max_quantity' => 1 ),
);

reservation_extra_assert( ! isset( Rentacar_Core_Reservation_Extras::enabled()['child_seat'] ), 'Disabled extras are not available to customer-facing callers.' );
reservation_extra_assert( ! empty( Rentacar_Core_Reservation_Extras::validate_selection( array( 'child_seat' ) ) ), 'A disabled extra is rejected when manually submitted.' );
reservation_extra_assert( ! empty( Rentacar_Core_Reservation_Extras::validate_selection( array( 'gps' ) ) ), 'Unknown extras are rejected.' );

$GLOBALS['reservation_extra_options'][ Rentacar_Core_Reservation_Extras::OPTION ]['child_seat'] = array( 'enabled' => true, 'pricing_type' => 'per_day', 'price' => 7.5, 'max_quantity' => 1 );
$days = ( new Rentacar_Core_Rental_Duration_Calculator() )->calculate( '2027-04-10', '10:00', '2027-04-12', '11:00' );
$per_day = Rentacar_Core_Reservation_Extras::calculate( array( 'child_seat' ), $days );
reservation_extra_assert( 3 === $days && 22.5 === $per_day['items'][0]['subtotal'], 'Per-day extras use the server-calculated rental duration.' );

$fixed = Rentacar_Core_Reservation_Extras::calculate( array( 'additional_driver' ), 9 );
reservation_extra_assert( 25.0 === $fixed['items'][0]['subtotal'], 'Fixed extras are added once per request.' );

$GLOBALS['reservation_extra_options'][ Rentacar_Core_Reservation_Extras::OPTION ]['additional_driver']['pricing_type'] = 'request_only';
$request_only = Rentacar_Core_Reservation_Extras::calculate( array( 'additional_driver' ), 9 );
reservation_extra_assert( null === $request_only['items'][0]['subtotal'] && 0.0 === $request_only['total'], 'Request-only extras add no amount to an estimate.' );

$GLOBALS['reservation_extra_options'][ Rentacar_Core_Reservation_Extras::OPTION ]['child_seat']['price'] = 5;
$authoritative = Rentacar_Core_Reservation_Extras::calculate( array( 'child_seat' ), 2 );
reservation_extra_assert( 10.0 === $authoritative['total'], 'A browser-submitted extra price cannot replace the configured price.' );
$GLOBALS['reservation_extra_options'][ Rentacar_Core_Reservation_Extras::OPTION ]['child_seat']['price'] = 8;
$changed_setting = Rentacar_Core_Reservation_Extras::calculate( array( 'child_seat' ), 2 );
reservation_extra_assert( 16.0 === $changed_setting['total'], 'Changing the WordPress setting changes subsequent estimates.' );

$request = new Rentacar_Core_Reservation_Request( array(
    'reference' => 'RAV-TEST', 'vehicle_title' => 'Fiat 500', 'pickup_location' => 'Venice', 'pickup_date' => '2027-04-10', 'pickup_time' => '10:00',
    'return_location' => 'Venice', 'return_date' => '2027-04-12', 'return_time' => '11:00', 'full_name' => 'Test Customer', 'phone' => '+39000000000',
    'email' => 'test@example.test', 'similar_vehicle' => false, 'estimate_summary' => '€16.00', 'language' => 'en', 'submitted_at' => '2027-04-01T10:00:00+00:00', 'message' => '',
    'extras' => $changed_setting['items'],
) );
( new Rentacar_Core_Business_Notification() )->send( $request, 'team@example.test' );
reservation_extra_assert( false !== strpos( $GLOBALS['reservation_extra_mail']['message'], 'Child seat — per_day; €8.00; Subtotal: €16.00' ), 'Business notifications use authoritative extra prices and subtotals.' );
reservation_extra_assert( false !== strpos( $GLOBALS['reservation_extra_mail']['message'], '<!doctype html>' ), 'Business notifications use the HTML email template.' );
reservation_extra_assert( in_array( 'Content-Type: text/html; charset=UTF-8', $GLOBALS['reservation_extra_mail']['headers'], true ), 'Business notifications declare an HTML content type.' );

echo "Reservation extra checks passed.\n";
