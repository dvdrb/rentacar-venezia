<?php
defined( 'ABSPATH' ) || exit;

/**
 * Returns whether an external SEO plugin owns the SEO output for the request.
 *
 * Yoast is the only planned integration. The filter keeps the theme fallback
 * deliberately small and lets a later integration take ownership cleanly.
 */
function rentacar_venezia_v2_external_seo_plugin_active() {
    $active = defined( 'WPSEO_VERSION' ) || class_exists( 'WPSEO_Frontend' );

    return (bool) apply_filters( 'rentacar_venezia_v2_external_seo_plugin_active', $active );
}

/**
 * Finds the published WordPress page that owns the fleet template.
 *
 * No title, slug or language prefix is assumed. WPML resolves the discovered
 * source page into the active language when it is available.
 */
function rentacar_venezia_v2_fleet_page_id() {
    static $fleet_page_id = null;

    if ( null !== $fleet_page_id ) {
        return $fleet_page_id;
    }

    $fleet_page_id = 0;
    $page_ids = get_posts(
        array(
            'post_type'              => 'page',
            'post_status'            => 'publish',
            'posts_per_page'         => -1,
            'fields'                 => 'ids',
            'meta_key'               => '_wp_page_template',
            'meta_value'             => 'page-templates/template-fleet.php',
            'orderby'                => 'ID',
            'order'                  => 'ASC',
            'suppress_filters'       => true,
            'no_found_rows'          => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
        )
    );

    foreach ( $page_ids as $page_id ) {
        $page_id = absint( $page_id );
        $translated_id = $page_id;

        if ( has_filter( 'wpml_object_id' ) ) {
            $translated_id = (int) apply_filters( 'wpml_object_id', $page_id, 'page', false );
        }

        if ( $translated_id && 'publish' === get_post_status( $translated_id ) ) {
            $fleet_page_id = $translated_id;
            break;
        }
    }

    $fleet_page_id = (int) apply_filters( 'rentacar_venezia_v2_fleet_page_id', $fleet_page_id, $page_ids );

    return $fleet_page_id;
}

/**
 * Returns the current-language fleet URL without assuming a page ID, route or
 * WPML directory structure.
 */
function rentacar_venezia_v2_fleet_url() {
    static $fleet_url = null;

    if ( null !== $fleet_url ) {
        return $fleet_url;
    }

    $fleet_page_id = rentacar_venezia_v2_fleet_page_id();
    if ( $fleet_page_id ) {
        $fleet_url = get_permalink( $fleet_page_id );
    } else {
        $fleet_url = home_url( '/fleet/' );

        if ( has_filter( 'wpml_permalink' ) ) {
            $language_code = apply_filters( 'wpml_current_language', null );
            $fleet_url = apply_filters( 'wpml_permalink', $fleet_url, $language_code );
        }
    }

    $fleet_url = (string) apply_filters( 'rentacar_venezia_v2_fleet_url', $fleet_url, $fleet_page_id );

    return $fleet_url;
}

function rentacar_venezia_v2_is_fleet_request() {
    $fleet_page_id = rentacar_venezia_v2_fleet_page_id();

    return (bool) get_query_var( 'rc_fleet' ) || ( $fleet_page_id && is_page( $fleet_page_id ) );
}

function rentacar_venezia_v2_fleet_filter_keys() {
    return array(
        'transmission',
        'passengers',
        'doors',
        'air_conditioning',
        'sort',
        'pickup_location',
        'dropoff_location',
        'pickup_date',
        'pickup_time',
        'return_date',
        'return_time',
    );
}

function rentacar_venezia_v2_is_filtered_fleet_request() {
    if ( ! rentacar_venezia_v2_is_fleet_request() ) {
        return false;
    }

    foreach ( rentacar_venezia_v2_fleet_filter_keys() as $key ) {
        if ( isset( $_GET[ $key ] ) && is_scalar( $_GET[ $key ] ) && '' !== (string) wp_unslash( $_GET[ $key ] ) ) {
            return true;
        }
    }

    return false;
}

function rentacar_venezia_v2_fleet_current_page() {
    return max( 1, absint( get_query_var( 'paged' ) ), absint( get_query_var( 'page' ) ) );
}

/**
 * Provides a canonical for the fleet catalogue only. Filtered requests point
 * at the clean current-language catalogue; paginated catalogue pages retain
 * their own URL.
 */
function rentacar_venezia_v2_fleet_canonical_url() {
    $canonical = rentacar_venezia_v2_fleet_url();

    if ( rentacar_venezia_v2_is_filtered_fleet_request() ) {
        return (string) apply_filters( 'rentacar_venezia_v2_fleet_canonical_url', $canonical, true );
    }

    $paged = rentacar_venezia_v2_fleet_current_page();
    if ( $paged > 1 ) {
        $canonical = trailingslashit( $canonical ) . 'page/' . $paged . '/';
    }

    return (string) apply_filters( 'rentacar_venezia_v2_fleet_canonical_url', $canonical, false );
}

function rentacar_venezia_v2_fleet_robots( $robots ) {
    if ( rentacar_venezia_v2_is_filtered_fleet_request() ) {
        $robots['noindex'] = true;
        $robots['follow'] = true;
    }

    return $robots;
}
add_filter( 'wp_robots', 'rentacar_venezia_v2_fleet_robots' );

function rentacar_venezia_v2_fleet_canonical() {
    if ( ! get_query_var( 'rc_fleet' ) || rentacar_venezia_v2_external_seo_plugin_active() || ! apply_filters( 'rentacar_venezia_v2_enable_fleet_canonical_fallback', true ) ) {
        return;
    }

    printf( "<link rel=\"canonical\" href=\"%s\">\n", esc_url( rentacar_venezia_v2_fleet_canonical_url() ) );
}
add_action( 'wp_head', 'rentacar_venezia_v2_fleet_canonical', 5 );

/**
 * Let WordPress retain canonical ownership for a real fleet page while making
 * its filtered and paginated catalogue states explicit. The custom fallback
 * route above still needs its own small canonical output because it is not a
 * normal singular page.
 */
function rentacar_venezia_v2_fleet_page_canonical( $canonical, $post ) {
    $fleet_page_id = rentacar_venezia_v2_fleet_page_id();

    if ( rentacar_venezia_v2_external_seo_plugin_active() || ! $fleet_page_id || ! $post instanceof WP_Post || $fleet_page_id !== (int) $post->ID || ! rentacar_venezia_v2_is_fleet_request() ) {
        return $canonical;
    }

    return rentacar_venezia_v2_fleet_canonical_url();
}
add_filter( 'get_canonical_url', 'rentacar_venezia_v2_fleet_page_canonical', 10, 2 );

function rentacar_venezia_v2_document_title( $parts ) {
    if ( get_query_var( 'rc_fleet' ) && ! rentacar_venezia_v2_external_seo_plugin_active() ) {
        $parts['title'] = __( 'Rental cars in Venice and Treviso | Rent a Car Venezia', 'rentacar-venezia-v2' );
    }

    return $parts;
}
add_filter( 'document_title_parts', 'rentacar_venezia_v2_document_title' );

/**
 * Uses supplied attachment alt text first. A generated fallback is limited to
 * the primary vehicle image and only names the visible vehicle title.
 */
function rentacar_venezia_v2_vehicle_image_alt( Rentacar_Core_Vehicle $vehicle, $image_id, $is_primary ) {
    $image_id = absint( $image_id );
    $attachment_alt = $image_id ? trim( (string) get_post_meta( $image_id, '_wp_attachment_image_alt', true ) ) : '';

    if ( '' !== $attachment_alt ) {
        return $attachment_alt;
    }

    if ( ! $is_primary ) {
        return '';
    }

    return sprintf(
        __( '%s rental vehicle', 'rentacar-venezia-v2' ),
        rentacar_venezia_v2_vehicle_title( $vehicle )
    );
}

function rentacar_venezia_v2_primary_image_url( Rentacar_Core_Vehicle $vehicle, $size = 'large' ) {
    $image_id = rentacar_venezia_v2_vehicle_image_id( $vehicle );

    return $image_id ? (string) wp_get_attachment_image_url( $image_id, $size ) : '';
}

function rentacar_venezia_v2_vehicle_schema_data( Rentacar_Core_Vehicle $vehicle ) {
    $vehicle_id = absint( $vehicle->get( 'id' ) );
    $url = (string) $vehicle->get( 'permalink' );
    $description = trim( wp_strip_all_tags( (string) get_post_field( 'post_content', $vehicle_id ) ) );

    if ( '' === $description ) {
        $description = __( 'Vehicle shown in the Rent a Car Venezia fleet. Availability is confirmed personally.', 'rentacar-venezia-v2' );
    }

    return array_filter(
        array(
            '@context'    => 'https://schema.org',
            '@type'       => 'Product',
            '@id'         => $url ? trailingslashit( $url ) . '#vehicle' : '',
            'name'        => rentacar_venezia_v2_vehicle_title( $vehicle ),
            'url'         => $url,
            'image'       => rentacar_venezia_v2_primary_image_url( $vehicle ),
            'description' => $description,
            'category'    => __( 'Car rental vehicle', 'rentacar-venezia-v2' ),
        )
    );
}

function rentacar_venezia_v2_vehicle_schema() {
    if ( ! is_singular( 'cars' ) || ! class_exists( 'Rentacar_Core_Vehicle_Repository' ) || rentacar_venezia_v2_external_seo_plugin_active() ) {
        return;
    }

    $vehicle = ( new Rentacar_Core_Vehicle_Repository() )->find( get_queried_object_id() );
    if ( ! $vehicle || ! apply_filters( 'rentacar_venezia_v2_enable_vehicle_schema_fallback', true, $vehicle ) ) {
        return;
    }

    printf( "<script type=\"application/ld+json\">%s</script>\n", wp_json_encode( rentacar_venezia_v2_vehicle_schema_data( $vehicle ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) );
}
add_action( 'wp_head', 'rentacar_venezia_v2_vehicle_schema', 30 );

/**
 * Renders stored page content through normal WordPress content filters.
 */
function rentacar_venezia_v2_render_page_content( $page_id, $content ) {
    $page = get_post( $page_id );
    if ( ! $page instanceof WP_Post || '' === trim( (string) $content ) ) {
        return '';
    }

    global $post;
    $previous_post = $post;
    $post = $page;
    setup_postdata( $post );
    $rendered = apply_filters( 'the_content', $content );
    $post = $previous_post;
    wp_reset_postdata();

    return $rendered;
}

/**
 * Splits a fleet page's content around WordPress's More marker. Without a
 * marker, the full editor content intentionally follows the catalogue.
 */
function rentacar_venezia_v2_fleet_page_content() {
    $page_id = rentacar_venezia_v2_fleet_page_id();
    $content = $page_id ? (string) get_post_field( 'post_content', $page_id ) : '';
    $sections = array( 'before' => '', 'after' => '' );

    if ( '' === trim( $content ) ) {
        return $sections;
    }

    $parts = preg_split( '/<!--\s*wp:more\s*-->\s*<!--more(?:\s.*?)?-->\s*<!--\s*\/wp:more\s*-->|<!--more(?:\s.*?)?-->/i', $content, 2 );
    if ( is_array( $parts ) && 2 === count( $parts ) ) {
        $sections['before'] = rentacar_venezia_v2_render_page_content( $page_id, $parts[0] );
        $sections['after'] = rentacar_venezia_v2_render_page_content( $page_id, $parts[1] );
    } else {
        $sections['after'] = rentacar_venezia_v2_render_page_content( $page_id, $content );
    }

    return $sections;
}
