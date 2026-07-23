<?php
defined( 'ABSPATH' ) || exit;

function rentacar_venezia_v2_setup() {
    load_theme_textdomain( 'rentacar-venezia-v2', get_template_directory() . '/languages' );
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support(
        'custom-logo',
        array(
            'height'               => 120,
            'width'                => 360,
            'flex-height'          => true,
            'flex-width'           => true,
            'unlink-homepage-logo' => true,
        )
    );
    add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );

    register_nav_menus(
        array(
            'primary' => __( 'Primary navigation', 'rentacar-venezia-v2' ),
            'footer'  => __( 'Footer navigation', 'rentacar-venezia-v2' ),
        )
    );
}
add_action( 'after_setup_theme', 'rentacar_venezia_v2_setup' );

function rentacar_venezia_v2_assets() {
    $theme = wp_get_theme();
    $manifest = rentacar_venezia_v2_asset_manifest();

    wp_enqueue_style( 'rentacar-venezia-v2', get_stylesheet_uri(), array(), $theme->get( 'Version' ) );
    if ( isset( $manifest['main'] ) ) {
        wp_enqueue_script( 'rentacar-venezia-v2', get_template_directory_uri() . '/assets/dist/' . ltrim( $manifest['main'], '/' ), array(), $theme->get( 'Version' ), true );
    }
    wp_localize_script(
        'rentacar-venezia-v2',
        'rentacarVenezia',
        array(
            'estimateUrl'          => esc_url_raw( rest_url( 'rentacar/v1/estimate' ) ),
            'reservationUrl'       => esc_url_raw( admin_url( 'admin-post.php' ) ),
            'whatsappDestination'  => esc_url_raw( apply_filters( 'rentacar_core_whatsapp_destination', '' ) ),
            'estimateUnavailable'  => __( 'An indicative estimate is not available for these details. Our team will confirm the final price.', 'rentacar-venezia-v2' ),
        )
    );
}
add_action( 'wp_enqueue_scripts', 'rentacar_venezia_v2_assets' );

function rentacar_venezia_v2_asset_manifest() {
    static $assets = null;

    if ( null !== $assets ) {
        return $assets;
    }

    $assets = array();
    $path = get_template_directory() . '/assets/dist/manifest.json';

    if ( ! is_readable( $path ) ) {
        return $assets;
    }

    $manifest = json_decode( file_get_contents( $path ), true );

    if ( ! is_array( $manifest ) ) {
        return $assets;
    }

    foreach ( $manifest as $entry ) {
        if ( isset( $entry['name'], $entry['file'] ) && 'main' === $entry['name'] ) {
            $assets['main'] = $entry['file'];
        }
    }

    return $assets;
}

function rentacar_venezia_v2_register_routes() {
    add_rewrite_rule( '^fleet/?$', 'index.php?rc_fleet=1', 'top' );
}
add_action( 'init', 'rentacar_venezia_v2_register_routes' );

function rentacar_venezia_v2_ensure_routes() {
    /* Kept as a no-op for backward compatibility: never flush or update options on requests. */
}
add_action( 'init', 'rentacar_venezia_v2_ensure_routes', 20 );

function rentacar_venezia_v2_query_vars( $variables ) {
    $variables[] = 'rc_fleet';

    return $variables;
}
add_filter( 'query_vars', 'rentacar_venezia_v2_query_vars' );

function rentacar_venezia_v2_template_router( $template ) {
    if ( ! get_query_var( 'rc_fleet' ) ) {
        return $template;
    }

    global $wp_query;
    $wp_query->is_404 = false;
    status_header( 200 );

    return get_template_directory() . '/page-templates/template-fleet.php';
}
add_filter( 'template_include', 'rentacar_venezia_v2_template_router' );

function rentacar_venezia_v2_document_title( $parts ) {
    if ( get_query_var( 'rc_fleet' ) ) {
        $parts['title'] = __( 'Our fleet', 'rentacar-venezia-v2' );
    }

    return $parts;
}
add_filter( 'document_title_parts', 'rentacar_venezia_v2_document_title' );

function rentacar_venezia_v2_fleet_canonical() {
    if ( get_query_var( 'rc_fleet' ) ) {
        printf( "<link rel=\"canonical\" href=\"%s\">\n", esc_url( rentacar_venezia_v2_fleet_url() ) );
    }
}
add_action( 'wp_head', 'rentacar_venezia_v2_fleet_canonical', 5 );

function rentacar_venezia_v2_flush_routes() {
    rentacar_venezia_v2_register_routes();
    flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'rentacar_venezia_v2_flush_routes' );

function rentacar_venezia_v2_language_links() {
    if ( ! function_exists( 'icl_get_languages' ) ) {
        return array();
    }

    $languages = icl_get_languages( 'skip_missing=0&orderby=code' );

    return is_array( $languages ) ? $languages : array();
}

function rentacar_venezia_v2_whatsapp_url() {
    /**
     * The number is intentionally not supplied until it has owner approval.
     * A theme/plugin setting can provide it in a later request-flow phase.
     */
    return apply_filters( 'rentacar_venezia_v2_whatsapp_url', '' );
}

function rentacar_venezia_v2_fleet_url() {
    return home_url( '/fleet/' );
}

function rentacar_venezia_v2_trip_query() {
    $keys = array( 'pickup_location', 'dropoff_location', 'pickup_date', 'pickup_time', 'return_date', 'return_time' );
    $trip = array();

    foreach ( $keys as $key ) {
        if ( isset( $_GET[ $key ] ) && is_scalar( $_GET[ $key ] ) && '' !== $_GET[ $key ] ) {
            $trip[ $key ] = sanitize_text_field( wp_unslash( $_GET[ $key ] ) );
        }
    }

    return $trip;
}

function rentacar_venezia_v2_vehicle_schema() {
    if ( ! is_singular( 'cars' ) || ! class_exists( 'Rentacar_Core_Vehicle_Repository' ) ) {
        return;
    }

    $vehicle = ( new Rentacar_Core_Vehicle_Repository() )->find( get_queried_object_id() );

    if ( ! $vehicle ) {
        return;
    }

    $image = $vehicle->get( 'featured_image_id' ) ? wp_get_attachment_image_url( $vehicle->get( 'featured_image_id' ), 'large' ) : '';
    $schema = array_filter(
        array(
            '@context'    => 'https://schema.org',
            '@type'       => 'Product',
            'name'        => $vehicle->get( 'title' ),
            'url'         => $vehicle->get( 'permalink' ),
            'image'       => $image,
            'category'    => 'Car rental vehicle',
            'description' => __( 'Vehicle shown in the Rent a Car Venezia fleet. Availability is confirmed personally.', 'rentacar-venezia-v2' ),
        )
    );

    printf( "<script type=\"application/ld+json\">%s</script>\n", wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) );
}
add_action( 'wp_head', 'rentacar_venezia_v2_vehicle_schema', 30 );
