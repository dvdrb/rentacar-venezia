<?php
/**
 * Plugin Name: Rentacar Local Mail Safety
 * Description: LocalWP-only mail interceptor. Never deploy this file.
 */

add_filter(
    'pre_wp_mail',
    static function ( $pre, array $atts ) {
        $host = (string) wp_parse_url( home_url( '/' ), PHP_URL_HOST );

        if ( false === strpos( $host, '.local' ) && false === strpos( $host, 'rentacar-venezia-local' ) ) {
            return $pre;
        }

        error_log( 'Rentacar local mail safety intercepted wp_mail().' );
        return true;
    },
    10,
    2
);
