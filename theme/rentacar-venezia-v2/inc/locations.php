<?php
defined( 'ABSPATH' ) || exit;

function rentacar_venezia_v2_pickup_locations() {
    return apply_filters(
        'rentacar_venezia_v2_pickup_locations',
        array(
            'venice_marco_polo'     => array( 'value' => 'Airport Venice Marco Polo', 'label' => rentacar_venezia_v2_location_label( 'venice_marco_polo' ), 'map_url' => 'https://www.google.com/maps/search/?api=1&query=Venice+Marco+Polo+Airport', 'type' => 'airport', 'is_airport' => true ),
            'treviso_airport'       => array( 'value' => 'Treviso Airport Arrivals', 'label' => rentacar_venezia_v2_location_label( 'treviso_airport' ), 'map_url' => 'https://www.google.com/maps/search/?api=1&query=Treviso+Airport', 'type' => 'airport', 'is_airport' => true ),
            'treviso_station'       => array( 'value' => 'treviso_station', 'label' => rentacar_venezia_v2_location_label( 'treviso_station' ), 'type' => 'station' ),
            'venezia_mestre_station'=> array( 'value' => 'venezia_mestre_station', 'label' => rentacar_venezia_v2_location_label( 'venezia_mestre_station' ), 'type' => 'station' ),
            'venezia_piazzale_roma' => array( 'value' => 'venezia_piazzale_roma', 'label' => rentacar_venezia_v2_location_label( 'venezia_piazzale_roma' ), 'type' => 'city_access' ),
            'treviso_hotel'         => array( 'value' => 'treviso_hotel', 'label' => rentacar_venezia_v2_location_label( 'treviso_hotel' ), 'type' => 'hotel' ),
            'venice_hotel'          => array( 'value' => 'venice_hotel', 'label' => rentacar_venezia_v2_location_label( 'venice_hotel' ), 'type' => 'hotel' ),
        )
    );
}

/** Centralized labels keep Polylang-aware templates free of language branches. */
function rentacar_venezia_v2_location_label( $location_key, $language = null ) {
    $labels = array(
        'venice_marco_polo'      => array( 'it' => 'Aeroporto di Venezia Marco Polo', 'en' => 'Venice Marco Polo Airport', 'ro' => 'Aeroportul Veneția Marco Polo', 'ru' => 'Аэропорт Венеция-Марко-Поло' ),
        'treviso_airport'        => array( 'it' => 'Aeroporto di Treviso', 'en' => 'Treviso Airport', 'ro' => 'Aeroportul Treviso', 'ru' => 'Аэропорт Тревизо' ),
        'treviso_station'        => array( 'it' => 'Stazione Treviso', 'en' => 'Treviso Train Station', 'ro' => 'Gara Treviso', 'ru' => 'Ж/д вокзал Тревизо' ),
        'venezia_mestre_station' => array( 'it' => 'Stazione Venezia Mestre', 'en' => 'Venice Mestre Train Station', 'ro' => 'Gara Venezia Mestre', 'ru' => 'Ж/д вокзал Венеция-Местре' ),
        'venezia_piazzale_roma'  => array( 'it' => 'Piazzale Roma', 'en' => 'Piazzale Roma', 'ro' => 'Piazzale Roma', 'ru' => 'Пьяццале Рома' ),
        'treviso_hotel'          => array( 'it' => 'Hotel a Treviso', 'en' => 'Hotel in Treviso', 'ro' => 'Hotel în Treviso', 'ru' => 'Отель в Тревизо' ),
        'venice_hotel'           => array( 'it' => 'Hotel a Venezia', 'en' => 'Hotel in Venice', 'ro' => 'Hotel în Veneția', 'ru' => 'Отель в Венеции' ),
    );
    $language = $language ?: rentacar_venezia_v2_current_language();

    return $labels[ $location_key ][ $language ] ?? ( $labels[ $location_key ]['en'] ?? '' );
}

function rentacar_venezia_v2_reservation_location_label( $value, $language = null ) {
    foreach ( rentacar_venezia_v2_pickup_locations() as $key => $location ) {
        if ( (string) $location['value'] === (string) $value ) {
            return rentacar_venezia_v2_location_label( $key, $language );
        }
    }

    return (string) $value;
}

function rentacar_venezia_v2_hotel_location_values() {
    return array( 'treviso_hotel', 'venice_hotel' );
}

function rentacar_venezia_v2_hotel_details_instruction( $language = null ) {
    $instructions = array(
        'it' => 'Indica il nome e l’indirizzo dell’hotel.',
        'en' => 'Please provide the hotel name and address.',
        'ro' => 'Indică numele și adresa hotelului.',
        'ru' => 'Укажите название и адрес отеля.',
    );
    $language = $language ?: rentacar_venezia_v2_current_language();

    return $instructions[ $language ] ?? $instructions['en'];
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

function rentacar_venezia_v2_location_hub_page_id( $language = null ) {
    $pages = get_posts( array( 'post_type' => 'page', 'post_status' => 'publish', 'posts_per_page' => 1, 'fields' => 'ids', 'meta_key' => '_rc_provisioning_key', 'meta_value' => 'pickup_locations', 'suppress_filters' => true, 'no_found_rows' => true ) );
    $page_id = $pages ? (int) $pages[0] : 0;
    return $page_id && function_exists( 'rentacar_venezia_v2_translated_post_id' ) ? rentacar_venezia_v2_translated_post_id( $page_id, $language ) : $page_id;
}

function rentacar_venezia_v2_location_hub_url( $language = null ) {
    $page_id = rentacar_venezia_v2_location_hub_page_id( $language );
    return $page_id ? get_permalink( $page_id ) : '';
}
