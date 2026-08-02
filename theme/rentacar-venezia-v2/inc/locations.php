<?php
defined( 'ABSPATH' ) || exit;

function rentacar_venezia_v2_pickup_locations() {
    return apply_filters(
        'rentacar_venezia_v2_pickup_locations',
        array(
            'venice_marco_polo' => array( 'value' => 'Airport Venice Marco Polo', 'label' => __( 'Venice Marco Polo Airport', 'rentacar-venezia-v2' ), 'map_url' => 'https://www.google.com/maps/search/?api=1&query=Venice+Marco+Polo+Airport' ),
            'treviso_airport'   => array( 'value' => 'Treviso Airport Arrivals', 'label' => __( 'Treviso Airport', 'rentacar-venezia-v2' ), 'map_url' => 'https://www.google.com/maps/search/?api=1&query=Treviso+Airport' ),
        )
    );
}

function rentacar_venezia_v2_location_page_id( $location_key ) {
    $locations = rentacar_venezia_v2_pickup_locations();
    if ( empty( $locations[ $location_key ] ) ) {
        return 0;
    }

    $page_id = (int) apply_filters( 'rentacar_venezia_v2_location_page_id', 0, $location_key );
    if ( ! $page_id ) {
        $pages = get_posts(
            array(
                'post_type'              => 'page',
                'post_status'            => 'publish',
                'posts_per_page'         => 1,
                'fields'                 => 'ids',
                'meta_key'               => '_rentacar_location_key',
                'meta_value'             => $location_key,
                'suppress_filters'       => true,
                'no_found_rows'          => true,
                'update_post_meta_cache' => false,
            )
        );
        $page_id = $pages ? (int) $pages[0] : 0;
    }

    if ( $page_id && function_exists( 'rentacar_venezia_v2_translated_post_id' ) ) $page_id = rentacar_venezia_v2_translated_post_id( $page_id );

    return $page_id && 'publish' === get_post_status( $page_id ) ? $page_id : 0;
}

function rentacar_venezia_v2_location_page_url( $location_key ) {
    $locations = rentacar_venezia_v2_pickup_locations();
    if ( empty( $locations[ $location_key ] ) ) {
        return '';
    }

    $page_id = rentacar_venezia_v2_location_page_id( $location_key );

    $url = $page_id && 'publish' === get_post_status( $page_id )
        ? get_permalink( $page_id )
        : add_query_arg( 'pickup_location', $locations[ $location_key ]['value'], rentacar_venezia_v2_fleet_url() );

    return (string) apply_filters( 'rentacar_venezia_v2_location_page_url', $url, $location_key, $page_id );
}

function rentacar_venezia_v2_location_page_image_id( $location_key ) {
    $page_id = rentacar_venezia_v2_location_page_id( $location_key );
    return $page_id ? (int) get_post_thumbnail_id( $page_id ) : 0;
}

/** Theme-owned location imagery is a presentation fallback, not CMS media. */
function rentacar_venezia_v2_location_theme_image( $location_key ) {
    $images = array(
        'venice_marco_polo' => array(
            'path'   => '/assets/images/locations/venice-marco-polo-airport.webp',
            'width'  => 2000,
            'height' => 667,
        ),
        'treviso_airport' => array(
            'path'   => '/assets/images/locations/treviso-airport.webp',
            'width'  => 1600,
            'height' => 900,
        ),
    );

    if ( empty( $images[ $location_key ] ) ) {
        return array();
    }

    $image = $images[ $location_key ];
    $image['url'] = get_theme_file_uri( $image['path'] );

    return (array) apply_filters( 'rentacar_venezia_v2_location_theme_image', $image, $location_key );
}
