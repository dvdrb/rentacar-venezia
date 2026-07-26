<?php
/** Read-only SEO-head validation for the LocalWP homepage and fleet URL. */
defined( 'ABSPATH' ) || exit( 1 );

$urls = array( home_url( '/' ), function_exists( 'rentacar_venezia_v2_fleet_url' ) ? rentacar_venezia_v2_fleet_url() : home_url( '/fleet/' ) );
$failures = array();
foreach ( array_unique( $urls ) as $url ) {
    $response = wp_remote_get( $url, array( 'timeout' => 15 ) );
    if ( is_wp_error( $response ) ) {
        $failures[] = $url . ': ' . $response->get_error_message();
        continue;
    }
    $html = (string) wp_remote_retrieve_body( $response );
    if ( 1 !== preg_match_all( '/<link[^>]+rel=["\']canonical["\']/i', $html ) ) {
        $failures[] = $url . ': expected exactly one canonical link';
    }
    if ( preg_match( '/<meta[^>]+name=["\']keywords["\']/i', $html ) ) {
        $failures[] = $url . ': meta keywords must not be present';
    }
}
if ( $failures ) {
    WP_CLI::error( implode( "\n", $failures ) );
}
WP_CLI::success( 'Head validation passed.' );
