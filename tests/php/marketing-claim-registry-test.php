<?php
/** Focused policy-registry checks with minimal WordPress stubs. */
define( 'ABSPATH', __DIR__ . '/' );
function register_setting() {}
function get_option( $key, $default = array() ) { return $default; }
function sanitize_text_field( $value ) { return trim( (string) $value ); }
function sanitize_key( $value ) { return preg_replace( '/[^a-z0-9_]/', '', strtolower( (string) $value ) ); }
function esc_url_raw( $value ) { return (string) $value; }

require_once dirname( __DIR__, 2 ) . '/plugin/rentacar-core/src/Settings/MarketingClaimRegistry.php';

function marketing_claim_assert( $condition, $message ) { if ( ! $condition ) { fwrite( STDERR, "FAIL: {$message}\n" ); exit( 1 ); } }

marketing_claim_assert( false !== Rentacar_Core_Marketing_Claim_Registry::enabled( 'no_credit_card_to_reserve' ), 'Credit-card reservation policy is enabled.' );
marketing_claim_assert( false !== Rentacar_Core_Marketing_Claim_Registry::enabled( 'no_advance_reservation_deposit' ), 'Advance-deposit reservation policy is enabled.' );
marketing_claim_assert( false !== Rentacar_Core_Marketing_Claim_Registry::enabled( 'security_deposit_at_pickup' ), 'Pickup security-deposit condition is enabled.' );
marketing_claim_assert( false === Rentacar_Core_Marketing_Claim_Registry::enabled( 'no_deposit' ), 'Generic no-deposit claim stays disabled.' );
marketing_claim_assert( Rentacar_Core_Marketing_Claim_Registry::enabled( 'no_credit_card' ) === Rentacar_Core_Marketing_Claim_Registry::enabled( 'no_credit_card_to_reserve' ), 'Legacy credit-card key resolves safely.' );
foreach ( array( 'it', 'en', 'ro', 'ru' ) as $language ) marketing_claim_assert( '' !== Rentacar_Core_Marketing_Claim_Registry::policy_summary( $language ), 'Policy summary is present for ' . $language . '.' );
marketing_claim_assert( false === strpos( strtolower( Rentacar_Core_Marketing_Claim_Registry::policy_summary( 'en' ) ), 'no deposit' ), 'Summary cannot imply a deposit-free rental.' );

echo "Marketing-claim registry checks passed.\n";
