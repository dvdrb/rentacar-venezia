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

/** Reconciles Yoast's global graph with the approved theme business record. */
function rentacar_venezia_v2_yoast_business_organization( $organization ) {
    if ( ! is_array( $organization ) || ! function_exists( 'rentacar_venezia_v2_business_data' ) ) {
        return $organization;
    }

    $business = rentacar_venezia_v2_business_data();
    $organization['@type'] = array( 'Organization', 'AutoRental' );
    $organization['name'] = $business['public_name'];
    $organization['legalName'] = $business['legal_name'];
    $organization['email'] = $business['email'];
    $organization['telephone'] = $business['phone'];
    $organization['address'] = array(
        '@type'           => 'PostalAddress',
        'streetAddress'   => $business['street_address'],
        'addressLocality' => $business['locality'],
        'addressCountry'  => $business['country'],
    );
    $organization['contactPoint'] = array(
        '@type'       => 'ContactPoint',
        'telephone'   => $business['phone'],
        'email'       => $business['email'],
        'contactType' => 'customer service',
    );
    $organization['openingHoursSpecification'] = array(
        array(
            '@type'     => 'OpeningHoursSpecification',
            'dayOfWeek' => array( 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday' ),
            'opens'     => '00:00',
            'closes'    => '23:59',
        ),
    );

    return $organization;
}
add_filter( 'wpseo_schema_organization', 'rentacar_venezia_v2_yoast_business_organization' );

function rentacar_venezia_v2_yoast_business_website( $website ) {
    if ( ! is_array( $website ) || ! function_exists( 'rentacar_venezia_v2_business_value' ) ) {
        return $website;
    }

    $website['name'] = rentacar_venezia_v2_business_value( 'public_name' );
    unset( $website['description'] );

    return $website;
}
add_filter( 'wpseo_schema_website', 'rentacar_venezia_v2_yoast_business_website' );
add_filter( 'wpseo_schema_company_name', function( $name ) { return function_exists( 'rentacar_venezia_v2_business_value' ) ? rentacar_venezia_v2_business_value( 'public_name' ) : $name; } );

/**
 * Finds the published WordPress page that owns the fleet template.
 *
 * No title, slug or language prefix is assumed. The active multilingual
 * provider resolves the discovered source page into the current language.
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

        $translated_id = function_exists( 'rentacar_venezia_v2_translated_post_id' )
            ? rentacar_venezia_v2_translated_post_id( $page_id )
            : $page_id;

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
 * multilingual directory structure.
 */
function rentacar_venezia_v2_fleet_url() {
    static $fleet_url = null;

    if ( null !== $fleet_url ) {
        return $fleet_url;
    }

    $fleet_page_id = rentacar_venezia_v2_fleet_page_id();
    if ( $fleet_page_id ) {
        $fleet_url = get_permalink( $fleet_page_id );
        // WordPress can suffix a translated page slug even though the
        // established multilingual catalogue route remains /{lang}/fleet/.
        if ( preg_match( '#/fleet-[0-9]+/?$#', (string) wp_parse_url( $fleet_url, PHP_URL_PATH ) ) && function_exists( 'rentacar_venezia_v2_localized_fallback_url' ) ) {
            $fleet_url = rentacar_venezia_v2_localized_fallback_url( home_url( '/fleet/' ) );
        }
    } else {
        $fleet_url = home_url( '/fleet/' );

        if ( function_exists( 'rentacar_venezia_v2_localized_fallback_url' ) ) {
            $fleet_url = rentacar_venezia_v2_localized_fallback_url( $fleet_url );
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

/**
 * Legacy results and success screens are transactional utilities, not landing
 * pages. They remain reachable for existing visitors but are excluded from
 * search results and sitemap discovery.
 */
function rentacar_venezia_v2_is_utility_page() {
    if ( ! is_page() ) {
        return false;
    }

    return in_array(
        get_page_template_slug( get_queried_object_id() ),
        array( 'template-results.php', 'template-success.php', 'page-templates/template-review-request.php' ),
        true
    );
}

/**
 * Only the approved public architecture is indexable. Historic WordPress
 * pages remain available at their old URLs, but cannot compete with the
 * current multilingual pages or leak obsolete business copy into search.
 */
function rentacar_venezia_v2_is_indexable_page() {
    if ( ! is_page() ) {
        return false;
    }

    $page_id = get_queried_object_id();
    if ( '1' === (string) get_post_meta( $page_id, '_rc_seo_indexable', true ) ) {
        return true;
    }

    $front_page_id = (int) get_option( 'page_on_front' );
    if ( $front_page_id && function_exists( 'pll_get_post_translations' ) ) {
        return in_array( $page_id, array_map( 'absint', (array) pll_get_post_translations( $front_page_id ) ), true );
    }

    return $front_page_id === $page_id;
}

/**
 * Legacy posts include unreviewed guide copy and must not enter search results
 * until an editor explicitly approves the individual guide for indexing.
 */
function rentacar_venezia_v2_is_indexable_guide() {
    return is_singular( 'post' ) && '1' === (string) get_post_meta( get_queried_object_id(), '_rc_seo_indexable', true );
}

function rentacar_venezia_v2_legacy_page_robots( $robots ) {
    if ( is_page() && ! rentacar_venezia_v2_is_indexable_page() ) {
        $robots['noindex'] = true;
        $robots['follow']  = true;
    }

    if ( is_singular( 'post' ) && ! rentacar_venezia_v2_is_indexable_guide() ) {
        $robots['noindex'] = true;
        $robots['follow']  = true;
    }

    return $robots;
}
add_filter( 'wp_robots', 'rentacar_venezia_v2_legacy_page_robots' );

function rentacar_venezia_v2_yoast_legacy_page_robots( $robots ) {
    if ( is_page() && ! rentacar_venezia_v2_is_indexable_page() ) {
        return 'noindex, follow';
    }

    return is_singular( 'post' ) && ! rentacar_venezia_v2_is_indexable_guide() ? 'noindex, follow' : $robots;
}
add_filter( 'wpseo_robots', 'rentacar_venezia_v2_yoast_legacy_page_robots' );

function rentacar_venezia_v2_indexable_page_sitemap_query( $args, $post_type ) {
    if ( ! in_array( $post_type, array( 'page', 'post' ), true ) ) {
        return $args;
    }

    if ( empty( $args['meta_query'] ) ) {
        $args['meta_query'] = array();
    }
    $args['meta_query'][] = array(
        'key'   => '_rc_seo_indexable',
        'value' => '1',
    );

    return $args;
}
add_filter( 'wp_sitemaps_posts_query_args', 'rentacar_venezia_v2_indexable_page_sitemap_query', 10, 2 );

/**
 * Core's sitemap provider can lose a Polylang prefix for translated custom
 * posts that share the same slug. Re-resolve every entry through WordPress so
 * sitemap URLs always match the visible page canonical and never list a 301.
 */
function rentacar_venezia_v2_localized_sitemap_entry( $entry, $post ) {
    if ( ! $post instanceof WP_Post ) {
        return $entry;
    }

    $permalink = get_permalink( $post );
    if ( $permalink ) {
        $entry['loc'] = $permalink;
    }

    if ( ! function_exists( 'pll_get_post_language' ) || ! function_exists( 'pll_home_url' ) || ! $permalink ) {
        return $entry;
    }

    $language = (string) pll_get_post_language( $post->ID, 'slug' );
    if ( '' === $language ) {
        return $entry;
    }

    $relative_path = ltrim( (string) wp_parse_url( $permalink, PHP_URL_PATH ), '/' );
    $languages = function_exists( 'pll_languages_list' ) ? (array) pll_languages_list( array( 'fields' => 'slug' ) ) : array();
    foreach ( $languages as $slug ) {
        $prefix = trim( (string) $slug, '/' ) . '/';
        if ( 0 === strpos( $relative_path, $prefix ) ) {
            $relative_path = substr( $relative_path, strlen( $prefix ) );
            break;
        }
    }

    $entry['loc'] = trailingslashit( pll_home_url( $language ) ) . $relative_path;

    return $entry;
}
add_filter( 'wp_sitemaps_posts_entry', 'rentacar_venezia_v2_localized_sitemap_entry', 10, 2 );

/**
 * Resolve bare vehicle slugs to the default-language record before WordPress
 * canonical handling runs. This only applies to the unprefixed rewrite; a
 * localized route already carries `lang` and remains entirely Polylang-owned.
 */
function rentacar_venezia_v2_default_language_vehicle_request( $query_vars ) {
    if ( empty( $query_vars['cars'] ) || ! empty( $query_vars['lang'] ) || ! function_exists( 'pll_default_language' ) || ! function_exists( 'pll_get_post_language' ) ) {
        return $query_vars;
    }

    $slug = sanitize_title_for_query( (string) $query_vars['cars'] );
    if ( '' === $slug ) {
        return $query_vars;
    }

    $vehicles = get_posts(
        array(
            'post_type'              => 'cars',
            'post_status'            => 'publish',
            'name'                   => $slug,
            'posts_per_page'         => -1,
            'fields'                 => 'ids',
            'suppress_filters'       => true,
            'no_found_rows'          => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
        )
    );
    $default_language = (string) pll_default_language( 'slug' );

    foreach ( $vehicles as $vehicle_id ) {
        if ( $default_language === (string) pll_get_post_language( $vehicle_id, 'slug' ) ) {
            $query_vars['p'] = (int) $vehicle_id;
            unset( $query_vars['cars'] );
            break;
        }
    }

    return $query_vars;
}
add_filter( 'request', 'rentacar_venezia_v2_default_language_vehicle_request', 20 );

function rentacar_venezia_v2_utility_page_robots( $robots ) {
    if ( rentacar_venezia_v2_is_utility_page() ) {
        $robots['noindex'] = true;
        $robots['follow']  = true;
    }

    return $robots;
}
add_filter( 'wp_robots', 'rentacar_venezia_v2_utility_page_robots' );

function rentacar_venezia_v2_yoast_utility_page_robots( $robots ) {
    return rentacar_venezia_v2_is_utility_page() ? 'noindex, follow' : $robots;
}
add_filter( 'wpseo_robots', 'rentacar_venezia_v2_yoast_utility_page_robots' );

/**
 * The historic Italian terms page has one exact, maintained replacement.
 * Redirect only this page ID so transactional legacy utilities remain
 * available to visitors who still have their old reservation links.
 */
function rentacar_venezia_v2_redirect_legacy_terms_page() {
    if ( ! is_page( 3216 ) ) {
        return;
    }

    $destination = rentacar_venezia_v2_managed_page_url( 'terms' );
    if ( $destination ) {
        wp_safe_redirect( $destination, 301, 'Rentacar Venezia' );
        exit;
    }
}
add_action( 'template_redirect', 'rentacar_venezia_v2_redirect_legacy_terms_page', 1 );

/** Keep author archives out of the XML sitemap on this single-business site. */
function rentacar_venezia_v2_disable_user_sitemap( $provider, $name ) {
    return 'users' === $name ? false : $provider;
}
add_filter( 'wp_sitemaps_add_provider', 'rentacar_venezia_v2_disable_user_sitemap', 10, 2 );

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

/**
 * Keep the catalogue's indexing rules intact when Yoast owns the document
 * canonical. Yoast remains responsible for all ordinary WordPress pages;
 * this only covers the fleet's filter and pagination states.
 */
function rentacar_venezia_v2_yoast_fleet_canonical( $canonical ) {
    if ( ! rentacar_venezia_v2_external_seo_plugin_active() || ! rentacar_venezia_v2_is_fleet_request() ) {
        return $canonical;
    }

    return rentacar_venezia_v2_fleet_canonical_url();
}
add_filter( 'wpseo_canonical', 'rentacar_venezia_v2_yoast_fleet_canonical' );

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

/**
 * Yoast's legacy vehicle templates are Italian for every Polylang locale.
 * Keep the correction at render time so translated vehicle records remain
 * editorially independent and no site-wide Yoast template is changed.
 */
function rentacar_venezia_v2_vehicle_metadata( $key ) {
    if ( ! is_singular( 'cars' ) ) {
        return '';
    }

    $title = trim( wp_strip_all_tags( get_the_title( get_queried_object_id() ) ) );
    if ( '' === $title ) {
        return '';
    }

    $language = function_exists( 'rentacar_venezia_v2_current_language' ) ? rentacar_venezia_v2_current_language() : 'en';
    $strings = array(
        'title' => array(
            'it' => 'Noleggio %s a Venezia e Treviso',
            'en' => '%s rental in Venice and Treviso',
            'ro' => 'Închiriere %s în Veneția și Treviso',
            'ru' => 'Аренда %s в Венеции и Тревизо',
        ),
        'description' => array(
            'it' => 'Scopri trasmissione, carburante e fasce di prezzo giornaliere indicative per %s. Invia una richiesta per confermare disponibilità e prezzo finale.',
            'en' => 'See the transmission, fuel type and indicative daily price bands for %s. Send a request to confirm availability and final price.',
            'ro' => 'Consultați transmisia, tipul de combustibil și intervalele orientative de preț zilnic pentru %s. Trimiteți o solicitare pentru confirmarea disponibilității și a prețului final.',
            'ru' => 'Узнайте трансмиссию, тип топлива и ориентировочные дневные цены для %s. Отправьте запрос, чтобы подтвердить наличие и окончательную цену.',
        ),
    );

    if ( empty( $strings[ $key ] ) ) {
        return '';
    }

    $template = $strings[ $key ][ $language ] ?? $strings[ $key ]['en'];

    return sprintf( $template, $title );
}

function rentacar_venezia_v2_yoast_vehicle_title( $title ) {
    $localized = rentacar_venezia_v2_vehicle_metadata( 'title' );

    return '' !== $localized ? $localized : $title;
}
add_filter( 'wpseo_title', 'rentacar_venezia_v2_yoast_vehicle_title' );
add_filter( 'wpseo_opengraph_title', 'rentacar_venezia_v2_yoast_vehicle_title' );

function rentacar_venezia_v2_yoast_vehicle_description( $description ) {
    $localized = rentacar_venezia_v2_vehicle_metadata( 'description' );

    return '' !== $localized ? $localized : $description;
}
add_filter( 'wpseo_metadesc', 'rentacar_venezia_v2_yoast_vehicle_description' );
add_filter( 'wpseo_opengraph_desc', 'rentacar_venezia_v2_yoast_vehicle_description' );

/**
 * Builds FAQ schema exclusively from visible <details>/<summary> content on
 * the FAQ template. Editorial FAQ text remains the single source of truth.
 */
function rentacar_venezia_v2_visible_faq_schema_questions() {
    if ( ! is_page() || 'page-templates/template-faq.php' !== get_page_template_slug( get_queried_object_id() ) ) {
        return array();
    }

    $content = (string) get_post_field( 'post_content', get_queried_object_id() );
    if ( ! preg_match_all( '~<details\b[^>]*>\s*<summary\b[^>]*>(.*?)</summary>(.*?)</details>~is', $content, $matches, PREG_SET_ORDER ) ) {
        return array();
    }

    $questions = array();
    foreach ( $matches as $match ) {
        $question = trim( wp_strip_all_tags( $match[1] ) );
        $answer = trim( wp_strip_all_tags( $match[2] ) );
        if ( '' === $question || '' === $answer ) {
            continue;
        }
        $questions[] = array(
            '@type'          => 'Question',
            'name'           => $question,
            'acceptedAnswer' => array(
                '@type' => 'Answer',
                'text'  => $answer,
            ),
        );
    }

    return $questions;
}

function rentacar_venezia_v2_visible_faq_schema() {
    $questions = rentacar_venezia_v2_visible_faq_schema_questions();
    if ( ! $questions ) {
        return;
    }

    $schema = array(
        '@context'   => 'https://schema.org',
        '@type'      => 'FAQPage',
        '@id'        => trailingslashit( get_permalink( get_queried_object_id() ) ) . '#faq',
        'mainEntity' => $questions,
        'inLanguage' => get_bloginfo( 'language' ),
    );

    printf( "<script type=\"application/ld+json\" class=\"rentacar-faq-schema\">%s</script>\n", wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) );
}
add_action( 'wp_head', 'rentacar_venezia_v2_visible_faq_schema', 30 );

function rentacar_venezia_v2_vehicle_schema_data( Rentacar_Core_Vehicle $vehicle ) {
    $vehicle_id = absint( $vehicle->get( 'id' ) );
    $url = (string) $vehicle->get( 'permalink' );
    $description = trim( wp_strip_all_tags( (string) get_post_field( 'post_content', $vehicle_id ) ) );

    if ( '' === $description ) {
        $description = __( 'Vehicle shown in the Rent a Car Venezia fleet. Availability is confirmed personally.', 'rentacar-venezia-v2' );
    }

    $properties = array_filter(
        array(
            $vehicle->get( 'transmission' ) ? array( '@type' => 'PropertyValue', 'name' => __( 'Transmission', 'rentacar-venezia-v2' ), 'value' => rentacar_venezia_v2_vehicle_transmission_label( $vehicle->get( 'transmission' ) ) ) : null,
            $vehicle->get( 'powertrain' ) && rentacar_venezia_v2_vehicle_powertrain_label( $vehicle->get( 'powertrain' ) ) ? array( '@type' => 'PropertyValue', 'name' => rentacar_venezia_v2_vehicle_schema_text( 'fuel_type' ), 'value' => rentacar_venezia_v2_vehicle_powertrain_label( $vehicle->get( 'powertrain' ) ) ) : null,
            $vehicle->get( 'passengers' ) ? array( '@type' => 'PropertyValue', 'name' => rentacar_venezia_v2_vehicle_schema_text( 'passenger_capacity' ), 'value' => (string) absint( $vehicle->get( 'passengers' ) ) ) : null,
            $vehicle->get( 'doors' ) ? array( '@type' => 'PropertyValue', 'name' => rentacar_venezia_v2_vehicle_schema_text( 'doors' ), 'value' => (string) absint( $vehicle->get( 'doors' ) ) ) : null,
            $vehicle->get( 'air_conditioning' ) ? array( '@type' => 'PropertyValue', 'name' => rentacar_venezia_v2_vehicle_schema_text( 'air_conditioning' ), 'value' => rentacar_venezia_v2_vehicle_schema_text( 'yes' ) ) : null,
        )
    );
    $offers = array();
    foreach ( rentacar_venezia_v2_vehicle_bands( $vehicle ) as $band ) {
        $offers[] = array(
            '@type'         => 'Offer',
            'price'         => number_format( (float) $band->daily_price, 2, '.', '' ),
            'priceCurrency' => 'EUR',
            'description'   => sprintf(
                rentacar_venezia_v2_vehicle_schema_text( 'indicative_offer' ),
                rentacar_venezia_v2_price_range_label( $band )
            ),
        );
    }

    return array_filter(
        array(
            '@context'            => 'https://schema.org',
            '@type'               => 'Vehicle',
            '@id'                 => $url ? trailingslashit( $url ) . '#vehicle' : '',
            'name'                => rentacar_venezia_v2_vehicle_title( $vehicle ),
            'url'                 => $url,
            'image'               => rentacar_venezia_v2_primary_image_url( $vehicle ),
            'description'         => $description,
            'vehicleConfiguration'=> rentacar_venezia_v2_vehicle_schema_text( 'rental_vehicle' ),
            'additionalProperty'  => $properties,
            'offers'              => $offers,
        )
    );
}

function rentacar_venezia_v2_vehicle_schema() {
    if ( ! is_singular( 'cars' ) || ! class_exists( 'Rentacar_Core_Vehicle_Repository' ) ) {
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
