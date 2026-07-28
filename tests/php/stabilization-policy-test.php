<?php
/** Focused, dependency-free checks for the shared rental policy and pricing audit. */
define( 'ABSPATH', __DIR__ . '/' );
function get_option( $key, $default = false ) { return $default; }
function apply_filters( $tag, $value ) { return $value; }
function wp_parse_args( $args, $defaults ) { return array_merge( $defaults, is_array( $args ) ? $args : array() ); }

require_once dirname( __DIR__, 2 ) . '/plugin/rentacar-core/src/Settings/RentalPolicy.php';
require_once dirname( __DIR__, 2 ) . '/plugin/rentacar-core/src/Pricing/RentalDurationCalculator.php';
require_once dirname( __DIR__, 2 ) . '/plugin/rentacar-core/src/Vehicles/PricingBand.php';
require_once dirname( __DIR__, 2 ) . '/plugin/rentacar-core/src/Vehicles/PricingBandCollection.php';

function stabilization_assert( $condition, $message ) { if ( ! $condition ) { fwrite( STDERR, "FAIL: {$message}\n" ); exit( 1 ); } }

$duration = new Rentacar_Core_Rental_Duration_Calculator();
stabilization_assert( 3 === Rentacar_Core_Rental_Policy::minimum_rental_days(), 'Three billable days is centralized in the rental policy.' );
stabilization_assert( Rentacar_Core_Rental_Policy::supports_reservation_time( '10:15' ) && Rentacar_Core_Rental_Policy::supports_reservation_time( '23:45' ), 'Quarter-hour reservation times are allowed.' );
stabilization_assert( ! Rentacar_Core_Rental_Policy::supports_reservation_time( '10:10' ) && ! Rentacar_Core_Rental_Policy::supports_reservation_time( '24:00' ), 'Off-increment or invalid reservation times are rejected.' );
stabilization_assert( 1 === $duration->calculate( '2027-04-10', '10:00', '2027-04-11', '10:00' ), 'One billable day is calculated consistently.' );
stabilization_assert( 2 === $duration->calculate( '2027-04-10', '10:00', '2027-04-12', '10:00' ), 'Two billable days is calculated consistently.' );
stabilization_assert( 3 === $duration->calculate( '2027-04-10', '10:00', '2027-04-12', '11:00' ), 'Later return times receive the established rounding behaviour.' );
stabilization_assert( null === $duration->calculate( '2027-04-12', '10:00', '2027-04-10', '10:00' ), 'Returns before pickup are invalid.' );
stabilization_assert( null === $duration->calculate( 'not-a-date', '10:00', '2027-04-12', '10:00' ), 'Malformed dates are invalid.' );

$valid = new Rentacar_Core_Pricing_Band_Collection( array( new Rentacar_Core_Pricing_Band( 3, 6, 60 ), new Rentacar_Core_Pricing_Band( 7, null, 50 ) ) );
stabilization_assert( array() === $valid->audit( 3, 60 ), 'Continuous positive bands audit cleanly.' );
$invalid = new Rentacar_Core_Pricing_Band_Collection( array( new Rentacar_Core_Pricing_Band( 3, 5, 60 ), new Rentacar_Core_Pricing_Band( 5, null, 50 ) ) );
$codes = array_unique( array_column( $invalid->audit( 3, 60 ), 'code' ) );
stabilization_assert( in_array( 'overlap', $codes, true ), 'Overlapping price bands are detected.' );

echo "Stabilization policy checks passed.\n";
