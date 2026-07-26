<?php
/** Focused PHP 7.4 checks for the authoritative airport-transfer surcharge. */
define( 'ABSPATH', __DIR__ . '/' );

function get_option( $key, $default = false ) { return $default; }
function apply_filters( $tag, $value ) { return $value; }
function wp_parse_args( $args, $defaults ) { return array_merge( $defaults, is_array( $args ) ? $args : array() ); }

require_once dirname( __DIR__, 2 ) . '/plugin/rentacar-core/src/Settings/RentalPolicy.php';

function reservation_location_assert( $condition, $message ) { if ( ! $condition ) { fwrite( STDERR, "FAIL: {$message}\n" ); exit( 1 ); } }

$venice = 'Airport Venice Marco Polo';
$treviso = 'Treviso Airport Arrivals';

reservation_location_assert( 2500 === Rentacar_Core_Rental_Policy::inter_airport_surcharge_cents( $venice, $treviso ), 'Different configured airports add the authoritative €25 surcharge.' );
reservation_location_assert( 2500 === Rentacar_Core_Rental_Policy::inter_airport_surcharge_cents( $treviso, $venice ), 'The surcharge applies in either airport direction.' );
reservation_location_assert( 0 === Rentacar_Core_Rental_Policy::inter_airport_surcharge_cents( $venice, $venice ), 'The same airport does not add a transfer surcharge.' );
reservation_location_assert( 0 === Rentacar_Core_Rental_Policy::inter_airport_surcharge_cents( 'Untrusted location', $treviso ), 'Unconfigured locations cannot trigger a surcharge.' );

echo "Reservation location surcharge checks passed.\n";
