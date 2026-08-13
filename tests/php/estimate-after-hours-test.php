<?php
/** Focused checks for independent pickup and return after-hours surcharges. */
define( 'ABSPATH', __DIR__ . '/' );

function get_option( $key, $default = false ) { return $default; }
function apply_filters( $tag, $value ) { return $value; }
function wp_parse_args( $args, $defaults ) { return array_merge( $defaults, is_array( $args ) ? $args : array() ); }
function __( $text ) { return $text; }
function absint( $value ) { return abs( (int) $value ); }

require_once dirname( __DIR__, 2 ) . '/plugin/rentacar-core/src/Settings/RentalPolicy.php';
require_once dirname( __DIR__, 2 ) . '/plugin/rentacar-core/src/Settings/ReservationExtras.php';
require_once dirname( __DIR__, 2 ) . '/plugin/rentacar-core/src/Pricing/RentalDurationCalculator.php';
require_once dirname( __DIR__, 2 ) . '/plugin/rentacar-core/src/Pricing/Estimate.php';
require_once dirname( __DIR__, 2 ) . '/plugin/rentacar-core/src/Vehicles/PricingBand.php';
require_once dirname( __DIR__, 2 ) . '/plugin/rentacar-core/src/Vehicles/PricingBandCollection.php';
require_once dirname( __DIR__, 2 ) . '/plugin/rentacar-core/src/Vehicles/Vehicle.php';

final class Rentacar_Core_Vehicle_Repository {
    private $vehicle;

    public function __construct( Rentacar_Core_Vehicle $vehicle ) {
        $this->vehicle = $vehicle;
    }

    public function find( $vehicle_id ) {
        return 123 === (int) $vehicle_id ? $this->vehicle : null;
    }
}

require_once dirname( __DIR__, 2 ) . '/plugin/rentacar-core/src/Pricing/EstimateService.php';

function estimate_after_hours_assert( $condition, $message ) { if ( ! $condition ) { fwrite( STDERR, "FAIL: {$message}\n" ); exit( 1 ); } }
function estimate_after_hours_line_item( array $estimate, $key ) {
    foreach ( $estimate['line_items'] as $item ) {
        if ( $key === $item['key'] ) return $item;
    }

    return null;
}

$vehicle = new Rentacar_Core_Vehicle( array(
    'id' => 123,
    'passengers' => 5,
    'pricing_bands' => new Rentacar_Core_Pricing_Band_Collection( array( new Rentacar_Core_Pricing_Band( 3, null, 80 ) ) ),
) );
$service = new Rentacar_Core_Estimate_Service( new Rentacar_Core_Vehicle_Repository( $vehicle ) );

$estimate = function( $pickup_time, $return_time ) use ( $service ) {
    return $service->estimate( 123, '2027-04-10', $pickup_time, '2027-04-13', $return_time )->to_array();
};

$normal = $estimate( '08:30', '08:30' );
estimate_after_hours_assert( 0.0 === (float) $normal['after_hours_pickup'] && 0.0 === (float) $normal['after_hours_return'], 'Normal pickup and return do not add an after-hours surcharge.' );
estimate_after_hours_assert( null === estimate_after_hours_line_item( $normal, 'after_hours_pickup' ) && null === estimate_after_hours_line_item( $normal, 'after_hours_return' ), 'Zero-valued after-hours surcharge line items are omitted.' );

$pickup_after_hours = $estimate( '23:00', '12:00' );
estimate_after_hours_assert( 50.0 === (float) $pickup_after_hours['after_hours_pickup'] && 0.0 === (float) $pickup_after_hours['after_hours_return'], 'An after-hours pickup only charges the pickup surcharge.' );
estimate_after_hours_assert( 50.0 === (float) estimate_after_hours_line_item( $pickup_after_hours, 'after_hours_pickup' )['amount'] && null === estimate_after_hours_line_item( $pickup_after_hours, 'after_hours_return' ), 'Pickup-only after-hours estimates expose only the pickup line item.' );

$return_after_hours = $estimate( '12:00', '05:00' );
estimate_after_hours_assert( 0.0 === (float) $return_after_hours['after_hours_pickup'] && 50.0 === (float) $return_after_hours['after_hours_return'], 'An after-hours return only charges the return surcharge.' );
estimate_after_hours_assert( 290.0 === (float) $return_after_hours['estimate_total'], 'A €240 vehicle subtotal plus a €50 after-hours return totals €290.' );
estimate_after_hours_assert( 50.0 === (float) estimate_after_hours_line_item( $return_after_hours, 'after_hours_return' )['amount'] && null === estimate_after_hours_line_item( $return_after_hours, 'after_hours_pickup' ), 'Return-only after-hours estimates expose only the return line item.' );
estimate_after_hours_assert( array_key_exists( 'after_hours_return', $return_after_hours ), 'The REST estimate payload includes after_hours_return via Estimate::to_array().' );

$both_after_hours = $estimate( '20:00', '23:00' );
estimate_after_hours_assert( 25.0 === (float) $both_after_hours['after_hours_pickup'] && 50.0 === (float) $both_after_hours['after_hours_return'], 'Pickup and return after-hours surcharges are calculated independently.' );
estimate_after_hours_assert( 75.0 === (float) ( $both_after_hours['after_hours_pickup'] + $both_after_hours['after_hours_return'] ) && (float) $both_after_hours['base_total'] + 75.0 === (float) $both_after_hours['estimate_total'], 'Both after-hours surcharges are included in the estimate total.' );
estimate_after_hours_assert( 25.0 === (float) estimate_after_hours_line_item( $both_after_hours, 'after_hours_pickup' )['amount'] && 50.0 === (float) estimate_after_hours_line_item( $both_after_hours, 'after_hours_return' )['amount'], 'Both after-hours line items are exposed when both events are outside normal hours.' );

$boundary_after_hours = $estimate( '06:30', '08:15' );
estimate_after_hours_assert( 25.0 === (float) $boundary_after_hours['after_hours_pickup'] && 25.0 === (float) $boundary_after_hours['after_hours_return'], 'Pickup and return both apply the €25 early band at its exact boundaries.' );

$night_boundary = $estimate( '06:15', '22:30' );
estimate_after_hours_assert( 50.0 === (float) $night_boundary['after_hours_pickup'] && 50.0 === (float) $night_boundary['after_hours_return'], 'Pickup and return both apply the €50 night band at its exact boundaries.' );

echo "Estimate after-hours checks passed.\n";
