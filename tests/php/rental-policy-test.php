<?php
/** Focused PHP 7.4 checks for authoritative reservation policy settings. */
define( 'ABSPATH', __DIR__ . '/' );
$GLOBALS['rental_policy_options'] = array();

function get_option( $key, $default = false ) { return array_key_exists( $key, $GLOBALS['rental_policy_options'] ) ? $GLOBALS['rental_policy_options'][ $key ] : $default; }
function update_option( $key, $value ) { $GLOBALS['rental_policy_options'][ $key ] = $value; return true; }
function apply_filters( $tag, $value ) { return $value; }
function wp_parse_args( $args, $defaults ) { return array_merge( $defaults, is_array( $args ) ? $args : array() ); }
function __( $text ) { return $text; }
function add_settings_error() {}
function absint( $value ) { return abs( (int) $value ); }

require_once dirname( __DIR__, 2 ) . '/plugin/rentacar-core/src/Settings/RentalPolicy.php';

function rental_policy_assert( $condition, $message ) { if ( ! $condition ) { fwrite( STDERR, "FAIL: {$message}\n" ); exit( 1 ); } }

rental_policy_assert( 2500 === Rentacar_Core_Rental_Policy::after_hours_cents( '07:00' ), 'Default early pickup surcharge is authoritative.' );
rental_policy_assert( 0 === Rentacar_Core_Rental_Policy::after_hours_cents( '12:00' ), 'Normal-hours pickup has no surcharge.' );
rental_policy_assert( 5000 === Rentacar_Core_Rental_Policy::after_hours_cents( '23:00' ), 'Default night pickup surcharge is authoritative.' );

$policy = Rentacar_Core_Rental_Policy::sanitize( array(
    'insurance' => array( 'base' => array( 'enabled' => 1, 'daily_price' => 0 ) ),
    'after_hours' => array(
        'early_start' => '05:00', 'normal_start' => '07:00', 'evening_start' => '19:00', 'night_start' => '22:00',
        'early_price' => '30', 'evening_price' => '35', 'night_price' => '55',
    ),
    'deposits' => array( 'up_to_five' => '360', 'seven_to_nine' => '520' ),
    'mileage' => array( 'daily_km' => '175', 'excess_price' => '0.15' ),
    'inter_airport_surcharge' => '25',
) );
$GLOBALS['rental_policy_options'][ Rentacar_Core_Rental_Policy::OPTION ] = $policy;

rental_policy_assert( 3000 === Rentacar_Core_Rental_Policy::after_hours_cents( '05:30' ), 'Updated early rate applies after the configured boundary.' );
rental_policy_assert( 3500 === Rentacar_Core_Rental_Policy::after_hours_cents( '20:00' ), 'Updated evening rate applies after the configured boundary.' );
rental_policy_assert( 5500 === Rentacar_Core_Rental_Policy::after_hours_cents( '23:00' ), 'Updated night rate applies after the configured boundary.' );
rental_policy_assert( 36000 === Rentacar_Core_Rental_Policy::deposit_cents( 5 ), 'Configured deposit is used for smaller vehicles.' );
rental_policy_assert( 52000 === Rentacar_Core_Rental_Policy::deposit_cents( 7 ), 'Configured deposit is used for larger vehicles.' );
rental_policy_assert( 2500 === Rentacar_Core_Rental_Policy::inter_airport_surcharge_cents( 'Airport Venice Marco Polo', 'Treviso Airport Arrivals' ), 'Configured inter-airport charge is used server-side.' );

echo "Rental policy checks passed.\n";
