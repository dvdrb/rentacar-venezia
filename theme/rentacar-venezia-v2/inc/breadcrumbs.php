<?php
defined( 'ABSPATH' ) || exit;

/**
 * Builds visible breadcrumb items from WordPress and the multilingual fleet
 * helper. The final item intentionally has no URL.
 */
function rentacar_venezia_v2_breadcrumb_items( $post_id = 0 ) {
    if ( is_front_page() ) {
        return array();
    }

    $items = array(
        array(
            'label' => __( 'Home', 'rentacar-venezia-v2' ),
            'url'   => rentacar_venezia_v2_home_url(),
        ),
    );

    if ( rentacar_venezia_v2_is_fleet_request() ) {
        $items[] = array(
            'label' => __( 'Fleet', 'rentacar-venezia-v2' ),
            'url'   => '',
        );

        return $items;
    }

    if ( is_singular( 'cars' ) ) {
        $items[] = array(
            'label' => __( 'Fleet', 'rentacar-venezia-v2' ),
            'url'   => rentacar_venezia_v2_fleet_url(),
        );
        $items[] = array(
            'label' => rentacar_venezia_v2_vehicle_title( ( new Rentacar_Core_Vehicle_Repository() )->find( get_queried_object_id() ) ),
            'url'   => '',
        );

        return $items;
    }

    $post_id = $post_id ? absint( $post_id ) : get_queried_object_id();
    if ( $post_id && is_page( $post_id ) ) {
        foreach ( array_reverse( get_post_ancestors( $post_id ) ) as $ancestor_id ) {
            $items[] = array(
                'label' => get_the_title( $ancestor_id ),
                'url'   => get_permalink( $ancestor_id ),
            );
        }
        $items[] = array(
            'label' => get_the_title( $post_id ),
            'url'   => '',
        );

        return $items;
    }

    if ( is_archive() || is_home() ) {
        $items[] = array(
            'label' => wp_get_document_title(),
            'url'   => '',
        );

        return $items;
    }

    if ( $post_id ) {
        $items[] = array(
            'label' => get_the_title( $post_id ),
            'url'   => '',
        );
    }

    return $items;
}
