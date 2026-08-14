<?php
$root = dirname( __DIR__, 2 );
$landing = file_get_contents( $root . '/theme/rentacar-venezia-v2/inc/landing-pages.php' );
$schema = file_get_contents( $root . '/theme/rentacar-venezia-v2/inc/schema.php' );
foreach ( array( 'rentacar_venezia_v2_rental_intents', 'rentacar_venezia_v2_intent_vehicles', 'rentacar_venezia_v2_cli_provision_locations', 'rentacar_venezia_v2_cli_provision_intents', 'Dry run only' ) as $needle ) if ( false === strpos( $landing, $needle ) ) throw new RuntimeException( 'Missing landing page safety feature: ' . $needle );
foreach ( array( "'automatic'", "'seven_seat'", "'nine_seat'", "'manual_review'" ) as $needle ) if ( false === strpos( $landing, $needle ) ) throw new RuntimeException( 'Missing controlled intent rule: ' . $needle );
foreach ( array( 'PickupLocationItemList', 'RentalOptionItemList', 'rentacar_venezia_v2_schema_location_service' ) as $needle ) if ( false === strpos( $schema, $needle ) ) throw new RuntimeException( 'Missing landing schema integration: ' . $needle );
echo "Landing-page registry checks passed.\n";
