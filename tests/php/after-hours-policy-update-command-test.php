<?php
/** Focused checks for the explicit after-hours policy CLI update. */
define( 'ABSPATH', __DIR__ . '/' );
$GLOBALS['after_hours_policy_options'] = array();

function get_option( $key, $default = false ) { return array_key_exists( $key, $GLOBALS['after_hours_policy_options'] ) ? $GLOBALS['after_hours_policy_options'][ $key ] : $default; }
function update_option( $key, $value ) { $GLOBALS['after_hours_policy_options'][ $key ] = $value; return true; }
function apply_filters( $tag, $value ) { return $value; }
function wp_parse_args( $args, $defaults ) { return array_merge( $defaults, is_array( $args ) ? $args : array() ); }
function __( $text ) { return $text; }
function add_settings_error() {}
function absint( $value ) { return abs( (int) $value ); }

require_once dirname( __DIR__, 2 ) . '/plugin/rentacar-core/src/Settings/RentalPolicy.php';
require_once dirname( __DIR__, 2 ) . '/plugin/rentacar-core/src/Cli/Commands.php';

function after_hours_command_assert( $condition, $message ) { if ( ! $condition ) { fwrite( STDERR, "FAIL: {$message}\n" ); exit( 1 ); } }

$GLOBALS['after_hours_policy_options'][ Rentacar_Core_Rental_Policy::OPTION ] = array(
    'insurance' => array( 'base' => array( 'enabled' => false ) ),
    'after_hours' => array( 'early_start' => 330, 'normal_start' => 450, 'evening_start' => 1170, 'night_start' => 1350, 'early_cents' => 2500, 'evening_cents' => 2500, 'night_cents' => 5000 ),
    'mileage' => array( 'daily_km' => 175 ),
);

$dry_run = Rentacar_Core_Cli_Commands::after_hours_update_result();
after_hours_command_assert( $dry_run['changed'] && ! $dry_run['applied'], 'Dry run identifies legacy after-hours boundaries without writing.' );
after_hours_command_assert( '05:30' === $dry_run['fields']['early_start']['before'] && '06:30' === $dry_run['fields']['early_start']['after'], 'Dry run reports the early boundary before and after.' );
after_hours_command_assert( 330 === $GLOBALS['after_hours_policy_options'][ Rentacar_Core_Rental_Policy::OPTION ]['after_hours']['early_start'], 'Dry run preserves the stored policy.' );

$apply = Rentacar_Core_Cli_Commands::after_hours_update_result( true );
$stored = $GLOBALS['after_hours_policy_options'][ Rentacar_Core_Rental_Policy::OPTION ];
after_hours_command_assert( $apply['changed'] && $apply['applied'], 'Apply mode writes the approved after-hours policy.' );
after_hours_command_assert( 390 === $stored['after_hours']['early_start'] && 510 === $stored['after_hours']['normal_start'], 'Apply mode writes the approved 06:30 and 08:30 boundaries.' );
after_hours_command_assert( 175 === $stored['mileage']['daily_km'] && false === $stored['insurance']['base']['enabled'], 'Apply mode preserves unrelated policy sections.' );

$repeat = Rentacar_Core_Cli_Commands::after_hours_update_result( true );
after_hours_command_assert( ! $repeat['changed'] && ! $repeat['applied'], 'A repeated apply is idempotent.' );

$translations = file_get_contents( dirname( __DIR__, 2 ) . '/theme/rentacar-venezia-v2/inc/interface-translations.php' );
after_hours_command_assert( false === strpos( $translations, '22:30–05:30 €50; 05:30–07:30 €25.' ), 'Obsolete visible policy ranges are removed from theme translations.' );
after_hours_command_assert( 6 === substr_count( $translations, '22:30–06:30 €50; 06:30–08:30 €25.' ), 'IT, RO, and RU visible policy translations contain the approved ranges.' );

echo "After-hours policy update command checks passed.\n";
