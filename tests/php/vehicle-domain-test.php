<?php
/**
 * Minimal dependency-free compatibility checks for PHP 7.4.
 *
 * Run with the LocalWP PHP binary. This intentionally tests pure domain values
 * only; WordPress runtime integration remains inactive until a later phase.
 */

define( 'ABSPATH', __DIR__ . '/' );

require_once dirname( __DIR__, 2 ) . '/plugin/rentacar-core/src/Vehicles/PricingBand.php';
require_once dirname( __DIR__, 2 ) . '/plugin/rentacar-core/src/Vehicles/PricingBandCollection.php';
require_once dirname( __DIR__, 2 ) . '/plugin/rentacar-core/src/Vehicles/Vehicle.php';

function rentacar_test_assert( $condition, $message ) {
    if ( ! $condition ) {
        fwrite( STDERR, "FAIL: {$message}\n" );
        exit( 1 );
    }
}

$short_stay = new Rentacar_Core_Pricing_Band( 1, 3, '55.00' );
$medium_stay = new Rentacar_Core_Pricing_Band( 4, 7, 48 );
$long_stay = new Rentacar_Core_Pricing_Band( 8, null, 42 );
$bands = new Rentacar_Core_Pricing_Band_Collection( array( $short_stay, $medium_stay, $long_stay ) );

rentacar_test_assert( $short_stay->applies_to( 1 ), 'First pricing band starts inclusively.' );
rentacar_test_assert( ! $short_stay->applies_to( 4 ), 'First pricing band ends inclusively.' );
rentacar_test_assert( 48.0 === $bands->for_days( 5 )->daily_price, 'Middle pricing band is selected.' );
rentacar_test_assert( 42.0 === $bands->for_days( 30 )->daily_price, 'Open-ended pricing band is selected.' );
rentacar_test_assert( null === ( new Rentacar_Core_Pricing_Band( 1, 2, 'not-a-price' ) )->daily_price, 'Invalid prices are not treated as estimates.' );

$vehicle = new Rentacar_Core_Vehicle( array( 'id' => 123, 'transmission' => 'Automatic' ) );
rentacar_test_assert( 123 === $vehicle->get( 'id' ), 'Vehicle exposes known data.' );
rentacar_test_assert( null === $vehicle->get( 'unknown' ), 'Vehicle has safe missing-value behavior.' );

echo "Vehicle domain checks passed.\n";
