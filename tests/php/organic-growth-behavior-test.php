<?php
/** Static guardrails for policy, lifecycle and local acquisition behavior. */
$root = dirname( __DIR__, 2 );
$landing = file_get_contents( $root . '/theme/rentacar-venezia-v2/inc/landing-pages.php' );
$analytics = file_get_contents( $root . '/theme/rentacar-venezia-v2/assets/js/analytics-events.js' );
$store = file_get_contents( $root . '/plugin/rentacar-core/src/Enquiries/ReservationStore.php' );
$modal = file_get_contents( $root . '/theme/rentacar-venezia-v2/template-parts/enquiry/reservation-modal.php' );
$theme = file_get_contents( $root . '/theme/rentacar-venezia-v2/functions.php' );
$fleet_template = file_get_contents( $root . '/theme/rentacar-venezia-v2/page-templates/template-fleet.php' );
$airport_template = file_get_contents( $root . '/theme/rentacar-venezia-v2/page-templates/template-airport-location.php' );

function organic_growth_assert( $condition, $message ) { if ( ! $condition ) { fwrite( STDERR, "FAIL: {$message}\n" ); exit( 1 ); } }

organic_growth_assert( false !== strpos( $landing, "'no_credit_card' => array( 'minimum' => 1" ), 'No-credit-card intent has a real eligibility threshold.' );
organic_growth_assert( false !== strpos( $landing, "'no_advance_reservation_deposit'" ) && false !== strpos( $landing, "'security_deposit_at_pickup'" ), 'No-credit-card intent requires the complete verified policy.' );
organic_growth_assert( false === strpos( $analytics, "'reservation_confirmed'" ), 'Frontend submission cannot emit reservation_confirmed.' );
organic_growth_assert( false !== strpos( $analytics, "'reservation_submitted'" ), 'Frontend emits reservation_submitted.' );
organic_growth_assert( false !== strpos( $analytics, 'Array.isArray(window.dataLayer) ? window.dataLayer : null' ), 'Acquisition context remains available when analytics is disabled.' );
organic_growth_assert( false !== strpos( $store, "'reservation_confirmed'" ) && false !== strpos( $store, "'rental_completed'" ), 'Admin lifecycle has confirmed and completed states.' );
organic_growth_assert( false !== strpos( $store, "'_rentacar_review_request_status'" ), 'Completed rentals receive review workflow support.' );
organic_growth_assert( false !== strpos( $store, "'_rentacar_review_requested_at'" ), 'A manually sent review request retains its timestamp.' );
organic_growth_assert( false !== strpos( $modal, 'acquisition_first_landing_page' ) && false !== strpos( $modal, 'acquisition_utm_campaign' ), 'Request form captures minimal acquisition context.' );
organic_growth_assert( false !== strpos( $modal, 'template-parts/global/reservation-policy' ) && false !== strpos( $fleet_template, 'template-parts/global/reservation-policy' ) && false !== strpos( $airport_template, 'template-parts/global/reservation-policy' ), 'The verified reservation policy is available through fleet, airport and request journeys.' );
organic_growth_assert( false !== strpos( $theme, 'rentacar_venezia_v2_fleet_url_with_trip' ) && false !== strpos( $theme, "'pickup_date', 'pickup_time', 'return_date', 'return_time'" ), 'Commercial context forwards only approved reservation fields.' );

echo "Organic-growth behavior checks passed.\n";
