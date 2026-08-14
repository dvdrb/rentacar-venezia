<?php
defined( 'ABSPATH' ) || exit;

/**
 * Rank Math schema integration. Rank Math remains the only JSON-LD renderer;
 * this file only enriches its keyed graph before it is rendered.
 */
function rentacar_venezia_v2_schema_organization_id() { return 'https://rentacarvenezia.it/#organization'; }
function rentacar_venezia_v2_schema_website_id() { return 'https://rentacarvenezia.it/#website'; }

/**
 * Keep WordPress/Polylang's resolved path, but make schema portable between
 * LocalWP and production. The public site has one HTTPS canonical origin.
 */
function rentacar_venezia_v2_schema_public_url( $url ) {
    $url = (string) $url;
    $parts = wp_parse_url( $url );
    $home = wp_parse_url( home_url( '/' ) );
    if ( ! is_array( $parts ) || empty( $parts['host'] ) || empty( $home['host'] ) || $parts['host'] !== $home['host'] ) return $url;

    $public = 'https://rentacarvenezia.it' . ( $parts['path'] ?? '/' );
    if ( ! empty( $parts['query'] ) ) $public .= '?' . $parts['query'];
    if ( ! empty( $parts['fragment'] ) ) $public .= '#' . $parts['fragment'];

    return $public;
}

function rentacar_venezia_v2_schema_normalize_urls( $value ) {
    if ( is_string( $value ) ) return rentacar_venezia_v2_schema_public_url( $value );
    if ( ! is_array( $value ) ) return $value;
    foreach ( $value as $key => $child ) $value[ $key ] = rentacar_venezia_v2_schema_normalize_urls( $child );
    return $value;
}

function rentacar_venezia_v2_schema_language() {
    $locale = function_exists( 'determine_locale' ) ? determine_locale() : get_bloginfo( 'language' );
    return str_replace( '_', '-', (string) $locale );
}

function rentacar_venezia_v2_schema_logo_url() {
    $relative_path = '/assets/images/brand/gd-rent-a-car-logo-light-background.png';
    if ( ! function_exists( 'get_theme_file_uri' ) ) return '';

    return rentacar_venezia_v2_schema_public_url( get_theme_file_uri( $relative_path ) );
}

function rentacar_venezia_v2_schema_page_url() {
    if ( is_front_page() && function_exists( 'rentacar_venezia_v2_home_url' ) ) return rentacar_venezia_v2_schema_public_url( trailingslashit( rentacar_venezia_v2_home_url() ) );
    if ( is_singular() ) return rentacar_venezia_v2_schema_public_url( get_permalink( get_queried_object_id() ) );
    return rentacar_venezia_v2_schema_public_url( function_exists( 'rentacar_venezia_v2_fleet_canonical_url' ) && rentacar_venezia_v2_is_fleet_request() ? rentacar_venezia_v2_fleet_canonical_url() : home_url( '/' ) );
}

function rentacar_venezia_v2_schema_is_indexable() {
    if ( function_exists( 'rentacar_venezia_v2_is_utility_page' ) && rentacar_venezia_v2_is_utility_page() ) return false;
    if ( function_exists( 'rentacar_venezia_v2_is_filtered_fleet_request' ) && rentacar_venezia_v2_is_filtered_fleet_request() ) return false;
    if ( is_singular( 'post' ) ) return function_exists( 'rentacar_venezia_v2_is_indexable_guide' ) && rentacar_venezia_v2_is_indexable_guide();
    if ( is_page() ) return ! function_exists( 'rentacar_venezia_v2_is_indexable_page' ) || rentacar_venezia_v2_is_indexable_page();
    return true;
}

function rentacar_venezia_v2_schema_hours( array $business ) {
    $hours = array();
    if ( false !== strpos( (string) ( $business['weekday_hours'] ?? '' ), '24/24' ) ) $hours[] = array( '@type' => 'OpeningHoursSpecification', 'dayOfWeek' => array( 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday' ), 'opens' => '00:00', 'closes' => '23:59' );
    if ( preg_match( '/(\d{2}:\d{2})\D+(\d{2}:\d{2})/', (string) ( $business['weekend_hours'] ?? '' ), $matches ) ) $hours[] = array( '@type' => 'OpeningHoursSpecification', 'dayOfWeek' => array( 'Saturday', 'Sunday' ), 'opens' => $matches[1], 'closes' => $matches[2] );
    return $hours;
}

function rentacar_venezia_v2_schema_vehicle( $vehicle, $url ) {
    if ( ! $vehicle instanceof Rentacar_Core_Vehicle ) return array();
    $car = array( '@type' => 'Car', '@id' => trailingslashit( $url ) . '#vehicle', 'url' => $url, 'name' => rentacar_venezia_v2_vehicle_title( $vehicle ) );
    $description = trim( wp_strip_all_tags( (string) get_post_field( 'post_content', $vehicle->get( 'id' ) ) ) );
    $image = function_exists( 'rentacar_venezia_v2_primary_image_url' ) ? rentacar_venezia_v2_primary_image_url( $vehicle, 'full' ) : '';
    if ( $description ) $car['description'] = $description;
    if ( $image ) $car['image'] = rentacar_venezia_v2_schema_public_url( $image );
    $fuel = function_exists( 'rentacar_venezia_v2_vehicle_powertrain_label' ) ? rentacar_venezia_v2_vehicle_powertrain_label( $vehicle->get( 'powertrain' ) ) : '';
    $transmission = function_exists( 'rentacar_venezia_v2_vehicle_transmission_label' ) ? rentacar_venezia_v2_vehicle_transmission_label( $vehicle->get( 'transmission' ) ) : '';
    if ( $fuel ) $car['fuelType'] = $fuel;
    if ( $transmission ) $car['vehicleTransmission'] = $transmission;
    if ( $vehicle->get( 'passengers' ) ) $car['seatingCapacity'] = (int) $vehicle->get( 'passengers' );
    if ( $vehicle->get( 'doors' ) ) $car['numberOfDoors'] = (int) $vehicle->get( 'doors' );
    $price = function_exists( 'rentacar_venezia_v2_vehicle_starting_price' ) ? rentacar_venezia_v2_vehicle_starting_price( $vehicle ) : null;
    if ( null !== $price && $price > 0 ) $car['offers'] = array( '@type' => 'Offer', 'offeredBy' => array( '@id' => rentacar_venezia_v2_schema_organization_id() ), 'priceSpecification' => array( '@type' => 'UnitPriceSpecification', 'price' => number_format( (float) $price, 2, '.', '' ), 'priceCurrency' => 'EUR', 'referenceQuantity' => array( '@type' => 'QuantitativeValue', 'value' => 1, 'unitCode' => 'DAY' ) ) );
    return $car;
}

function rentacar_venezia_v2_schema_location_service( $key, $url ) {
    $areas = array(
        'venice_marco_polo'      => array( 'Airport', 'VCE' ),
        'treviso_airport'        => array( 'Airport', 'TSF' ),
        'treviso_station'        => array( 'TrainStation', '' ),
        'venezia_mestre_station' => array( 'TrainStation', '' ),
        'venezia_piazzale_roma'  => array( 'Place', '' ),
        'treviso_hotel'          => array( 'City', '' ),
        'venice_hotel'           => array( 'City', '' ),
    );
    if ( empty( $areas[ $key ] ) ) return array();

    $area = array(
        '@type' => $areas[ $key ][0],
        'name'  => rentacar_venezia_v2_schema_location_area_name( $key ),
    );
    if ( $areas[ $key ][1] ) $area['iataCode'] = $areas[ $key ][1];

    return array(
        '@type'       => 'Service',
        '@id'         => trailingslashit( $url ) . '#service',
        'name'        => rentacar_venezia_v2_location_label( $key ),
        'serviceType' => rentacar_venezia_v2_schema_service_type(),
        'provider'    => array( '@id' => rentacar_venezia_v2_schema_organization_id() ),
        'areaServed'  => $area,
    );
}

function rentacar_venezia_v2_schema_location_area_name( $key ) {
    $cities = array(
        'treviso_hotel' => array( 'it' => 'Treviso', 'en' => 'Treviso', 'ro' => 'Treviso', 'ru' => 'Тревизо' ),
        'venice_hotel'  => array( 'it' => 'Venezia', 'en' => 'Venice', 'ro' => 'Veneția', 'ru' => 'Венеция' ),
    );
    $language = function_exists( 'rentacar_venezia_v2_current_language' ) ? rentacar_venezia_v2_current_language() : 'en';

    return $cities[ $key ][ $language ] ?? ( $cities[ $key ]['en'] ?? rentacar_venezia_v2_location_label( $key ) );
}

function rentacar_venezia_v2_schema_service_type() {
    $types = array(
        'it' => 'Noleggio auto con ritiro',
        'en' => 'Car rental pickup',
        'ro' => 'Preluare auto închiriată',
        'ru' => 'Получение арендованного автомобиля',
    );
    $language = function_exists( 'rentacar_venezia_v2_current_language' ) ? rentacar_venezia_v2_current_language() : 'en';

    return $types[ $language ] ?? $types['en'];
}

/** Development/test helper: graph nodes must not define the same @id twice. */
function rentacar_venezia_v2_schema_has_unique_ids( array $graph ) {
    $ids = array();
    foreach ( $graph as $node ) {
        if ( ! is_array( $node ) || empty( $node['@id'] ) ) continue;
        if ( isset( $ids[ $node['@id'] ] ) ) return false;
        $ids[ $node['@id'] ] = true;
    }
    return true;
}

/**
 * Supplies Rank Math with the exact breadcrumb hierarchy rendered by the
 * theme when Rank Math has not already supplied a BreadcrumbList node.
 */
function rentacar_venezia_v2_schema_breadcrumb( array &$data, $page_url ) {
    if ( ! empty( $data['BreadcrumbList']['@id'] ) ) {
        return $data['BreadcrumbList']['@id'];
    }

    $items = function_exists( 'rentacar_venezia_v2_breadcrumb_items' ) ? rentacar_venezia_v2_breadcrumb_items() : array();
    if ( ! $items ) {
        return '';
    }

    $list_id = trailingslashit( $page_url ) . '#breadcrumb';
    $elements = array();
    foreach ( $items as $position => $item ) {
        $element = array(
            '@type'    => 'ListItem',
            'position' => $position + 1,
            'name'     => $item['label'],
        );
        if ( ! empty( $item['url'] ) ) {
            $element['item'] = rentacar_venezia_v2_schema_public_url( $item['url'] );
        }
        $elements[] = $element;
    }

    $data['BreadcrumbList'] = array(
        '@type'           => 'BreadcrumbList',
        '@id'             => $list_id,
        'itemListElement' => $elements,
    );

    return $list_id;
}

function rentacar_venezia_v2_schema_fleet_items() {
    if ( ! class_exists( 'Rentacar_Core_Vehicle_Repository' ) ) return array();
    $vehicles = ( new Rentacar_Core_Vehicle_Repository() )->query();
    $vehicles = function_exists( 'rentacar_venezia_v2_sort_fleet_vehicles' ) ? rentacar_venezia_v2_sort_fleet_vehicles( $vehicles ) : $vehicles;
    $page = function_exists( 'rentacar_venezia_v2_fleet_current_page' ) ? rentacar_venezia_v2_fleet_current_page() : 1;
    $vehicles = array_slice( $vehicles, ( $page - 1 ) * 12, 12 );
    $items = array(); foreach ( $vehicles as $i => $vehicle ) $items[] = array( '@type' => 'ListItem', 'position' => $i + 1, 'name' => rentacar_venezia_v2_vehicle_title( $vehicle ), 'url' => rentacar_venezia_v2_schema_public_url( $vehicle->get( 'permalink' ) ) );
    return $items;
}

function rentacar_venezia_v2_schema_vehicle_items( array $vehicles ) {
    $items = array();
    foreach ( $vehicles as $i => $vehicle ) $items[] = array( '@type' => 'ListItem', 'position' => $i + 1, 'name' => rentacar_venezia_v2_vehicle_title( $vehicle ), 'url' => rentacar_venezia_v2_schema_public_url( $vehicle->get( 'permalink' ) ) );
    return $items;
}

function rentacar_venezia_v2_schema_location_items() {
    $items = array();
    foreach ( rentacar_venezia_v2_pickup_locations() as $key => $location ) { $url = rentacar_venezia_v2_location_page_url( $key ); if ( $url ) $items[] = array( '@type' => 'ListItem', 'position' => count( $items ) + 1, 'name' => $location['label'], 'url' => rentacar_venezia_v2_schema_public_url( $url ) ); }
    return $items;
}

function rentacar_venezia_v2_schema_blog_posting( $page_url, $webpage_id ) {
    $article = array(
        '@type'            => 'BlogPosting',
        '@id'              => trailingslashit( $page_url ) . '#article',
        'headline'         => get_the_title(),
        'mainEntityOfPage' => array( '@id' => $webpage_id ),
        'datePublished'    => get_the_date( DATE_W3C ),
        'dateModified'     => get_the_modified_date( DATE_W3C ),
        'inLanguage'       => rentacar_venezia_v2_schema_language(),
        'publisher'        => array( '@id' => rentacar_venezia_v2_schema_organization_id() ),
        // There is no verified public editorial person in the source data.
        'author'           => array( '@id' => rentacar_venezia_v2_schema_organization_id() ),
    );
    $description = trim( wp_strip_all_tags( (string) get_the_excerpt() ) );
    $image_id = (int) get_post_thumbnail_id( get_queried_object_id() );
    $image = $image_id ? wp_get_attachment_image_url( $image_id, 'full' ) : '';

    if ( $description ) $article['description'] = $description;
    if ( $image ) $article['image'] = rentacar_venezia_v2_schema_public_url( $image );

    return $article;
}

function rentacar_venezia_v2_schema_graph( $data ) {
    // Noindex legacy guides and transactional/filter states must not inherit
    // a rich snippet configured in Rank Math for otherwise similar content.
    if ( ! rentacar_venezia_v2_schema_is_indexable() ) {
        return array();
    }

    $business = rentacar_venezia_v2_business_data();
    $page_url = rentacar_venezia_v2_schema_page_url();
    unset( $data['place'] );

    $data['publisher'] = array(
        '@type'       => array( 'Organization', 'AutoRental' ),
        '@id'         => rentacar_venezia_v2_schema_organization_id(),
        'name'        => $business['public_name'],
        'legalName'   => $business['legal_name'],
        'url'         => 'https://rentacarvenezia.it/',
        'email'       => $business['email'],
        'telephone'   => $business['phone'],
        'address'     => array(
            '@type'           => 'PostalAddress',
            'streetAddress'   => $business['street_address'],
            'addressLocality' => $business['locality'],
            'addressCountry'  => $business['country'],
        ),
        'contactPoint' => array(
            '@type'       => 'ContactPoint',
            'contactType' => 'customer service',
            'telephone'   => $business['phone'],
            'email'       => $business['email'],
        ),
    );

    $hours = rentacar_venezia_v2_schema_hours( $business );
    if ( $hours ) {
        $data['publisher']['openingHoursSpecification'] = $hours;
    }

    $logo = rentacar_venezia_v2_schema_logo_url();
    if ( $logo ) {
        $data['publisher']['logo'] = $logo;
        $data['publisher']['image'] = $logo;
    }

    $data['WebSite'] = array(
        '@type'         => 'WebSite',
        '@id'           => rentacar_venezia_v2_schema_website_id(),
        'url'           => 'https://rentacarvenezia.it/',
        'name'          => $business['public_name'],
        'alternateName' => 'RentacarVenezia.it',
        'publisher'     => array( '@id' => rentacar_venezia_v2_schema_organization_id() ),
    );

    $page = isset( $data['WebPage'] ) && is_array( $data['WebPage'] ) ? $data['WebPage'] : array();
    $page['@type'] = 'WebPage';
    $page['@id'] = trailingslashit( $page_url ) . '#webpage';
    $page['url'] = $page_url;
    $page['isPartOf'] = array( '@id' => rentacar_venezia_v2_schema_website_id() );
    $page['about'] = array( '@id' => rentacar_venezia_v2_schema_organization_id() );
    $page['inLanguage'] = rentacar_venezia_v2_schema_language();

    $breadcrumb_id = rentacar_venezia_v2_schema_breadcrumb( $data, $page_url );
    if ( $breadcrumb_id ) {
        $page['breadcrumb'] = array( '@id' => $breadcrumb_id );
    }

    if ( is_page() && 'page-templates/template-contact.php' === get_page_template_slug( get_queried_object_id() ) ) {
        $page['@type'] = 'ContactPage';
        $page['mainEntity'] = array( '@id' => rentacar_venezia_v2_schema_organization_id() );
    }

    if ( function_exists( 'rentacar_venezia_v2_is_fleet_request' ) && rentacar_venezia_v2_is_fleet_request() && ! rentacar_venezia_v2_is_filtered_fleet_request() ) {
        $list_id = trailingslashit( $page_url ) . '#itemlist';
        $items = rentacar_venezia_v2_schema_fleet_items();
        $page['@type'] = 'CollectionPage';
        $page['mainEntity'] = array( '@id' => $list_id );
        $data['ItemList'] = array( '@type' => 'ItemList', '@id' => $list_id, 'numberOfItems' => count( $items ), 'itemListElement' => $items );
    }

    if ( is_singular( 'cars' ) && class_exists( 'Rentacar_Core_Vehicle_Repository' ) ) {
        $car = rentacar_venezia_v2_schema_vehicle( ( new Rentacar_Core_Vehicle_Repository() )->find( get_queried_object_id() ), $page_url );
        if ( $car ) {
            $page['mainEntity'] = array( '@id' => $car['@id'] );
            $data['Car'] = $car;
        }
    }

    if ( is_page() ) {
        $key = (string) get_post_meta( get_queried_object_id(), '_rentacar_location_key', true );
        $service = rentacar_venezia_v2_schema_location_service( $key, $page_url );
        if ( $service ) {
            $page['mainEntity'] = array( '@id' => $service['@id'] );
            $data['Service'] = $service;
        }
    }
    if ( is_page() && 'pickup_locations' === (string) get_post_meta( get_queried_object_id(), '_rc_provisioning_key', true ) ) {
        $items = rentacar_venezia_v2_schema_location_items(); $list_id = trailingslashit( $page_url ) . '#itemlist'; $page['@type'] = 'CollectionPage'; $page['mainEntity'] = array( '@id' => $list_id ); $data['PickupLocationItemList'] = array( '@type' => 'ItemList', '@id' => $list_id, 'numberOfItems' => count( $items ), 'itemListElement' => $items );
    }
    if ( is_page() && function_exists( 'rentacar_venezia_v2_intent_vehicles' ) ) {
        $intent = (string) get_post_meta( get_queried_object_id(), '_rentacar_intent_key', true );
        if ( $intent ) { $items = rentacar_venezia_v2_schema_vehicle_items( rentacar_venezia_v2_intent_vehicles( $intent ) ); $list_id = trailingslashit( $page_url ) . '#itemlist'; $page['@type'] = 'CollectionPage'; $page['mainEntity'] = array( '@id' => $list_id ); $data['RentalOptionItemList'] = array( '@type' => 'ItemList', '@id' => $list_id, 'numberOfItems' => count( $items ), 'itemListElement' => $items ); }
    }
    if ( is_page() && 'guides' === (string) get_post_meta( get_queried_object_id(), '_rc_provisioning_key', true ) && class_exists( 'WP_Query' ) ) {
        $guides = new WP_Query( array( 'post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => 12, 'ignore_sticky_posts' => true, 'meta_key' => '_rc_seo_indexable', 'meta_value' => '1' ) );
        $items = array(); foreach ( $guides->posts as $i => $guide ) $items[] = array( '@type' => 'ListItem', 'position' => $i + 1, 'name' => get_the_title( $guide ), 'url' => rentacar_venezia_v2_schema_public_url( get_permalink( $guide ) ) );
        $list_id = trailingslashit( $page_url ) . '#itemlist'; $page['@type'] = 'CollectionPage'; $page['mainEntity'] = array( '@id' => $list_id ); $data['GuideItemList'] = array( '@type' => 'ItemList', '@id' => $list_id, 'numberOfItems' => count( $items ), 'itemListElement' => $items );
    }
    if ( is_singular( 'post' ) ) {
        $article = rentacar_venezia_v2_schema_blog_posting( $page_url, $page['@id'] );
        $page['mainEntity'] = array( '@id' => $article['@id'] );
        unset( $data['Article'] );
        $data['BlogPosting'] = $article;
    }
    $data['WebPage'] = $page;
    return rentacar_venezia_v2_schema_normalize_urls( $data );
}
add_filter( 'rank_math/json_ld', 'rentacar_venezia_v2_schema_graph', 90 );
