<?php
/** Focused checks for shared, metadata-backed international phone handling. */
define( 'ABSPATH', __DIR__ . '/' );
function __( $text ) { return $text; }

require_once dirname( __DIR__, 2 ) . '/plugin/rentacar-core/src/Enquiries/PhoneNumberService.php';
require_once dirname( __DIR__, 2 ) . '/plugin/rentacar-core/src/Enquiries/ContactController.php';
require_once dirname( __DIR__, 2 ) . '/plugin/rentacar-core/src/Multilingual/ReservationTranslations.php';

function phone_assert( $condition, $message ) { if ( ! $condition ) { fwrite( STDERR, "FAIL: {$message}\n" ); exit( 1 ); } }
function normalized_phone( $country, $number, $calling_code = '' ) { return ( new Rentacar_Core_Phone_Number_Service() )->normalize( $country, $number, $calling_code ); }

$countries = Rentacar_Core_Phone_Number_Service::country_options( 'en' );
phone_assert( count( $countries ) >= 245, 'The bundled metadata covers all supported countries and territories, not a hand-maintained subset.' );

$italy = normalized_phone( 'IT', '312 345 6789' );
phone_assert( $italy['valid'] && '+393123456789' === $italy['phone_e164'], 'Italy normalizes a national number to E.164.' );
$romania = normalized_phone( 'RO', '0712-345-678' );
phone_assert( $romania['valid'] && '+40712345678' === $romania['phone_e164'], 'Romania removes its national trunk prefix.' );
$moldova = normalized_phone( 'MD', '+373 (69) 123 456', '+373' );
phone_assert( $moldova['valid'] && '+37369123456' === $moldova['phone_e164'], 'Moldova accepts a pasted international number with separators.' );
$united_kingdom = normalized_phone( 'GB', '07123 456789' );
phone_assert( $united_kingdom['valid'] && '+447123456789' === $united_kingdom['phone_e164'], 'United Kingdom normalizes the selected territory.' );
$united_states = normalized_phone( 'US', '(202) 555-0123', '+1' );
$canada = normalized_phone( 'CA', '416 555 0123', '+1' );
phone_assert( $united_states['valid'] && $canada['valid'] && '+12025550123' === $united_states['phone_e164'] && '+14165550123' === $canada['phone_e164'], 'United States and Canada remain independently selectable despite sharing +1.' );
$guernsey = normalized_phone( 'GG', '+44 1481 234567', '+44' );
phone_assert( $guernsey['valid'] && 'GG' === $guernsey['phone_country'], 'Territories sharing another calling code remain independently selectable.' );

phone_assert( ! normalized_phone( 'IT', '123' )['valid'], 'Numbers that are too short are rejected.' );
phone_assert( ! normalized_phone( 'IT', str_repeat( '3', 16 ) )['valid'], 'Numbers that are too long are rejected.' );
phone_assert( 'phone_country_required' === normalized_phone( '', '3123456789' )['code'], 'A missing country is rejected.' );
phone_assert( 'phone_number_required' === normalized_phone( 'IT', '' )['code'], 'A missing national number is rejected.' );
phone_assert( 'phone_calling_code_mismatch' === normalized_phone( 'IT', '3123456789', '+40' )['code'], 'A forged calling-code field cannot disagree with the selected country.' );
phone_assert( 'phone_calling_code_mismatch' === normalized_phone( 'IT', '+40712345678', '+39' )['code'], 'A pasted international number cannot disagree with the selected country.' );
phone_assert( ! normalized_phone( 'ZZ', '3123456789' )['valid'], 'Unknown country selections are rejected.' );
phone_assert( ! normalized_phone( 'IT', '3123<script>' )['valid'], 'Malformed or malicious input is rejected.' );

$phone_translation_method = new ReflectionMethod( 'Rentacar_Core_Reservation_Translations', 'phone_public_strings' );
$phone_translation_method->setAccessible( true );
$phone_translations = $phone_translation_method->invoke( null );
phone_assert( 'Please select a country.' === Rentacar_Core_Phone_Number_Service::error_message( 'phone_country_required' ) && 'Seleziona un Paese.' === $phone_translations['it']['Please select a country.'] && 'Selectați o țară.' === $phone_translations['ro']['Please select a country.'] && 'Выберите страну.' === $phone_translations['ru']['Please select a country.'], 'Phone validation messages are available in English, Italian, Romanian, and Russian.' );

$contact_message = Rentacar_Core_Contact_Controller::email_message( array_merge( array( 'name' => 'Test Customer', 'email' => 'test@example.test', 'topic' => 'general', 'message' => 'Test message.' ), $moldova ) );
phone_assert( false !== strpos( $contact_message, '+373 691 23 456 (+37369123456)' ), 'General contact emails receive the normalized international number.' );

echo "Phone-number service checks passed.\n";
