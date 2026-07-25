<?php
/**
 * Minimal dependency-free compatibility checks for PHP 7.4.
 *
 * Run with the LocalWP PHP binary. This intentionally tests pure domain values
 * only; WordPress runtime integration remains inactive until a later phase.
 */

define( 'ABSPATH', __DIR__ . '/' );

function get_option( $key ) {
    return '';
}

require_once dirname( __DIR__, 2 ) . '/plugin/rentacar-core/src/Vehicles/PricingBand.php';
require_once dirname( __DIR__, 2 ) . '/plugin/rentacar-core/src/Vehicles/PricingBandCollection.php';
require_once dirname( __DIR__, 2 ) . '/plugin/rentacar-core/src/Vehicles/Vehicle.php';
require_once dirname( __DIR__, 2 ) . '/plugin/rentacar-core/src/Vehicles/VehicleGallery.php';
require_once dirname( __DIR__, 2 ) . '/plugin/rentacar-core/src/Pricing/RentalDurationCalculator.php';

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

$gallery = new Rentacar_Core_Vehicle_Gallery( 99, array( 0, 99, 100, 100 ) );
rentacar_test_assert( array( 99, 100 ) === $gallery->all_image_ids(), 'Gallery handles a featured image and duplicate/empty images.' );
rentacar_test_assert( array() === ( new Rentacar_Core_Vehicle_Gallery( 0 ) )->all_image_ids(), 'Gallery handles missing images.' );

$duration = new Rentacar_Core_Rental_Duration_Calculator();
rentacar_test_assert( 2 === $duration->calculate( '2026-08-10', '10:00', '2026-08-10', '14:00' ), 'Later same-day returns follow the established chargeable-day rule.' );
rentacar_test_assert( 2 === $duration->calculate( '2026-08-10', '10:00', '2026-08-12', '10:00' ), 'Calendar duration preserves the established day calculation.' );
rentacar_test_assert( 3 === $duration->calculate( '2026-08-10', '10:00', '2026-08-12', '11:00' ), 'Later return times add a chargeable day.' );
rentacar_test_assert( null === $duration->calculate( '2026-08-12', '10:00', '2026-08-10', '10:00' ), 'Returns before pickup are rejected.' );
rentacar_test_assert( null === $duration->calculate( 'bad-date', '10:00', '2026-08-12', '10:00' ), 'Malformed dates are rejected.' );

echo "Vehicle domain checks passed.\n";
