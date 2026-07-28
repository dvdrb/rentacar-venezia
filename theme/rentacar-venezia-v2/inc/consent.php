<?php
defined( 'ABSPATH' ) || exit;

/** Optional analytics is deliberately disabled; no consent UI or choice cookie is used. */
function rentacar_venezia_v2_gate_optional_tracking() {
    foreach ( array( 'wp_head', 'wp_footer', 'wp_body_open', 'body_open' ) as $hook ) {
        remove_action( $hook, 'gtm4wp_wp_header_begin' );
        remove_action( $hook, 'gtm4wp_wp_header_top' );
        remove_action( $hook, 'gtm4wp_wp_footer' );
        remove_action( $hook, 'gtm4wp_wp_body_open' );
    }
    remove_action( 'wp_enqueue_scripts', 'gtm4wp_enqueue_scripts' );
}
add_action( 'wp', 'rentacar_venezia_v2_gate_optional_tracking', 1 );
