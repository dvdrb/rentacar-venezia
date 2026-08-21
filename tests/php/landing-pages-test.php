<?php
$root = dirname( __DIR__, 2 );
$landing = file_get_contents( $root . '/theme/rentacar-venezia-v2/inc/landing-pages.php' );
$schema = file_get_contents( $root . '/theme/rentacar-venezia-v2/inc/schema.php' );
foreach ( array( 'rentacar_venezia_v2_rental_intents', 'rentacar_venezia_v2_intent_vehicles', 'rentacar_venezia_v2_cli_provision_locations', 'rentacar_venezia_v2_cli_provision_intents', 'rentacar_venezia_v2_cli_noindex_legacy_pages', 'Dry run only' ) as $needle ) if ( false === strpos( $landing, $needle ) ) throw new RuntimeException( 'Missing landing page safety feature: ' . $needle );
foreach ( array( "'automatic'", "'seven_seat'", "'nine_seat'", "'no_credit_card'", "'policy_keys'" ) as $needle ) if ( false === strpos( $landing, $needle ) ) throw new RuntimeException( 'Missing controlled intent rule: ' . $needle );
if ( false === strpos( $landing, "if ( ! get_post_meta( \$id, 'rank_math_title', true ) ) update_post_meta" ) ) throw new RuntimeException( 'Existing landing metadata must remain editor-owned.' );
foreach ( array( 'PickupLocationItemList', 'RentalOptionItemList', 'rentacar_venezia_v2_schema_location_service' ) as $needle ) if ( false === strpos( $schema, $needle ) ) throw new RuntimeException( 'Missing landing schema integration: ' . $needle );
echo "Landing-page registry checks passed.\n";
