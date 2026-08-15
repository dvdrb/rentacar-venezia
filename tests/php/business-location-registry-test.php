<?php
/** Focused PHP 7.4 checks for the physical-business-location registry. */
define( 'ABSPATH', __DIR__ . '/' );

function add_action() {}
function add_filter() {}
function apply_filters( $tag, $value ) { return $value; }
function get_option( $name, $default = array() ) { return 'rentacar_venezia_business' === $name ? array( 'street_address' => 'Via Montello, 7/A' ) : $default; }
function wp_parse_args( $args, $defaults ) { return array_merge( $defaults, $args ); }
function home_url( $path = '/' ) { return 'https://rentacarvenezia.it' . $path; }

require_once dirname( __DIR__, 2 ) . '/theme/rentacar-venezia-v2/inc/business.php';

function business_location_registry_assert( $condition, $message ) { if ( ! $condition ) { fwrite( STDERR, "FAIL: {$message}\n" ); exit( 1 ); } }

$business = rentacar_venezia_v2_business_data();
$locations = rentacar_venezia_v2_business_locations();

business_location_registry_assert( ! array_key_exists( 'legal_name', rentacar_venezia_v2_business_defaults() ), 'Public business defaults must not include legal_name.' );
business_location_registry_assert( 'Via Montello, 7' === $business['street_address'], 'The old saved Treviso address normalizes to the approved public NAP.' );
business_location_registry_assert( array( 'treviso', 'venice_marco_polo' ) === array_keys( $locations ), 'Exactly two physical business locations are registered.' );
business_location_registry_assert( 'G&D Rent A Car' === $locations['treviso']['public_name'] && '31100' === $locations['treviso']['postal_code'] && 'TV' === $locations['treviso']['region'] && '+39 344 506 8823' === $locations['treviso']['phone_display'], 'Treviso registry data matches the approved public NAP.' );
business_location_registry_assert( 'Airport, Viale Galileo Galilei, 30/1' === $locations['venice_marco_polo']['street_address'] && '30173' === $locations['venice_marco_polo']['postal_code'] && 'VE' === $locations['venice_marco_polo']['region'] && '+393445068823' === $locations['venice_marco_polo']['phone'], 'Venice Marco Polo registry data uses the verified GBP NAP.' );

foreach ( $locations as $location ) {
    business_location_registry_assert( false === strpos( $location['google_business_profile_url'], 'writereview' ), 'Business identity URLs must not be review-write URLs.' );
}

echo "Business-location registry checks passed.\n";
