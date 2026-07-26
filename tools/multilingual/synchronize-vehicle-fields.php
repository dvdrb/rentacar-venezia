<?php
/**
 * WP-CLI migration utility. Synchronizes only the approved technical vehicle
 * fields from each preserved WPML source post to its Polylang translations.
 *
 * Usage: wp eval-file tools/multilingual/synchronize-vehicle-fields.php -- --apply
 * Omit --apply to report differences without writing anything.
 */
defined( 'ABSPATH' ) || exit( 1 );

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
    exit( 1 );
}

global $wpdb;
$apply = in_array( '--apply', WP_CLI::get_runner()->arguments, true );
$rows = $wpdb->get_results(
    "SELECT trid, element_id, language_code, source_language_code
    FROM {$wpdb->prefix}icl_translations
    WHERE element_type = 'post_cars'
    ORDER BY trid, element_id",
    ARRAY_A
);
$groups = array();
foreach ( $rows as $row ) {
    if ( 'publish' === get_post_status( (int) $row['element_id'] ) ) {
        $groups[ $row['trid'] ][] = $row;
    }
}

$audit = new Rentacar_Core_Vehicle_Translation_Audit();
$synchronizer = new Rentacar_Core_Vehicle_Field_Synchronizer();
$summary = array( 'groups' => 0, 'targets' => 0, 'different_targets' => 0, 'different_fields' => 0, 'synchronized' => 0 );

foreach ( $groups as $group ) {
    $sources = array_values( array_filter( $group, function( $row ) { return empty( $row['source_language_code'] ); } ) );
    if ( count( $group ) < 2 || 1 !== count( $sources ) ) {
        continue;
    }

    ++$summary['groups'];
    $source_id = (int) $sources[0]['element_id'];
    foreach ( $group as $row ) {
        $target_id = (int) $row['element_id'];
        if ( $target_id === $source_id ) {
            continue;
        }

        ++$summary['targets'];
        $differences = $audit->compare( $source_id, $target_id );
        if ( ! $differences ) {
            continue;
        }

        ++$summary['different_targets'];
        $summary['different_fields'] += count( $differences );
        if ( $apply ) {
            $result = $synchronizer->synchronize( $source_id, $target_id );
            if ( is_wp_error( $result ) ) {
                WP_CLI::error( $result->get_error_message() );
            }
            ++$summary['synchronized'];
        }
    }
}

WP_CLI::success( wp_json_encode( $summary ) );
