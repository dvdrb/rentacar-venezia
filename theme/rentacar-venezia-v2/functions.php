<?php
defined( 'ABSPATH' ) || exit;

require_once get_template_directory() . '/inc/presentation.php';
require_once get_template_directory() . '/inc/multilingual.php';
require_once get_template_directory() . '/inc/interface-translations.php';
require_once get_template_directory() . '/inc/consent.php';
require_once get_template_directory() . '/inc/seo.php';
require_once get_template_directory() . '/inc/breadcrumbs.php';
require_once get_template_directory() . '/inc/locations.php';

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
    add_theme_support( 'editor-styles' );
    add_editor_style( 'assets/editor.css' );

    register_nav_menus(
        array(
            'primary' => __( 'Primary navigation', 'rentacar-venezia-v2' ),
            'footer'  => __( 'Footer navigation', 'rentacar-venezia-v2' ),
        )
    );
}
add_action( 'after_setup_theme', 'rentacar_venezia_v2_setup' );

function rentacar_venezia_v2_core_reservation_locations( $locations ) {
    return wp_list_pluck( rentacar_venezia_v2_pickup_locations(), 'value' );
}
add_filter( 'rentacar_core_reservation_locations', 'rentacar_venezia_v2_core_reservation_locations' );

function rentacar_venezia_v2_core_airport_locations( $locations ) {
    return wp_list_pluck( rentacar_venezia_v2_pickup_locations(), 'value' );
}
add_filter( 'rentacar_core_airport_locations', 'rentacar_venezia_v2_core_airport_locations' );

/** The public contact form uses the approved business inbox unless an owner override is configured. */
function rentacar_venezia_v2_contact_recipient( $recipient ) {
    return is_email( $recipient ) ? $recipient : 'info@rentacarvenezia.it';
}
add_filter( 'rentacar_core_contact_recipient', 'rentacar_venezia_v2_contact_recipient' );

function rentacar_venezia_v2_register_home_patterns() {
    if ( ! function_exists( 'register_block_pattern' ) ) {
        return;
    }

    register_block_pattern_category( 'rentacar-venezia', array( 'label' => __( 'Rent a Car Venezia', 'rentacar-venezia-v2' ) ) );
    $patterns = array(
        'airport-seo' => array( 'title' => __( 'Airport SEO section', 'rentacar-venezia-v2' ), 'content' => '<!-- wp:group {"className":"homepage-content__airport"} --><div class="wp-block-group homepage-content__airport"><!-- wp:paragraph {"className":"eyebrow"} --><p class="eyebrow">' . esc_html__( 'Local information', 'rentacar-venezia-v2' ) . '</p><!-- /wp:paragraph --><!-- wp:heading {"level":2} --><h2>' . esc_html__( 'Add verified airport information', 'rentacar-venezia-v2' ) . '</h2><!-- /wp:heading --><!-- wp:paragraph --><p>' . esc_html__( 'Add verified pickup information, local context and helpful internal links here.', 'rentacar-venezia-v2' ) . '</p><!-- /wp:paragraph --><!-- wp:paragraph --><p>' . esc_html__( 'Use this area only for business-approved details.', 'rentacar-venezia-v2' ) . '</p><!-- /wp:paragraph --></div><!-- /wp:group -->' ),
        'customer-reviews' => array( 'title' => __( 'Customer reviews section', 'rentacar-venezia-v2' ), 'content' => '<!-- wp:group {"className":"home-review-grid"} --><div class="wp-block-group home-review-grid"><!-- wp:heading {"level":2} --><h2>' . esc_html__( 'Verified customer reviews', 'rentacar-venezia-v2' ) . '</h2><!-- /wp:heading --><!-- wp:paragraph --><p>' . esc_html__( 'Add only verified reviews with source, date and public reviewer name or initials.', 'rentacar-venezia-v2' ) . '</p><!-- /wp:paragraph --></div><!-- /wp:group -->' ),
        'faq' => array( 'title' => __( 'Homepage FAQ section', 'rentacar-venezia-v2' ), 'content' => '<!-- wp:group {"className":"home-faq"} --><div class="wp-block-group home-faq"><!-- wp:heading {"level":2} --><h2>' . esc_html__( 'Frequently asked questions', 'rentacar-venezia-v2' ) . '</h2><!-- /wp:heading --><!-- wp:details --><details class="wp-block-details"><summary>' . esc_html__( 'Can I send a request without paying?', 'rentacar-venezia-v2' ) . '</summary><!-- wp:paragraph --><p>' . esc_html__( 'Add verified business information here.', 'rentacar-venezia-v2' ) . '</p><!-- /wp:paragraph --></details><!-- /wp:details --></div><!-- /wp:group -->' ),
        'local-callout' => array( 'title' => __( 'Local information callout', 'rentacar-venezia-v2' ), 'content' => '<!-- wp:group {"className":"local-information-callout"} --><div class="wp-block-group local-information-callout"><!-- wp:heading {"level":2} --><h2>' . esc_html__( 'Local information', 'rentacar-venezia-v2' ) . '</h2><!-- /wp:heading --><!-- wp:paragraph --><p>' . esc_html__( 'Add verified local service information and a useful internal link.', 'rentacar-venezia-v2' ) . '</p><!-- /wp:paragraph --></div><!-- /wp:group -->' ),
    );
    foreach ( $patterns as $slug => $pattern ) {
        register_block_pattern( 'rentacar-venezia/' . $slug, array_merge( $pattern, array( 'categories' => array( 'rentacar-venezia' ) ) ) );
    }
}
add_action( 'init', 'rentacar_venezia_v2_register_home_patterns' );

/** Renders the approved brand asset for each header or footer surface without duplicating markup. */
function rentacar_venezia_v2_brand_mark( $context = 'header' ) {
    $context = in_array( $context, array( 'header', 'header-light', 'footer' ), true ) ? $context : 'header';
    $assets = array(
        'header'       => 'assets/images/brand/gd-rent-a-car-logo-light-background.png',
        'header-light' => 'assets/images/brand/logo-reversed-cropped.png',
        'footer'       => 'assets/images/brand/logo-reversed-cropped.png',
    );
    $relative_asset = $assets[ $context ];
    $asset = get_template_directory() . '/' . $relative_asset;
    $classes = 'site-brand site-brand--' . $context;

    if ( is_readable( $asset ) ) {
        $dimensions = function_exists( 'wp_getimagesize' ) ? wp_getimagesize( $asset ) : getimagesize( $asset );
        $width = is_array( $dimensions ) && ! empty( $dimensions[0] ) ? absint( $dimensions[0] ) : 0;
        $height = is_array( $dimensions ) && ! empty( $dimensions[1] ) ? absint( $dimensions[1] ) : 0;
        printf(
            '<a class="%1$s" href="%2$s" rel="home" aria-label="%3$s"><img src="%4$s"%5$s%6$s alt="%7$s"></a>',
            esc_attr( $classes ),
            esc_url( rentacar_venezia_v2_home_url() ),
            esc_attr__( 'G&D Rent A Car', 'rentacar-venezia-v2' ),
            esc_url( get_template_directory_uri() . '/' . $relative_asset ),
            $width ? ' width="' . esc_attr( $width ) . '"' : '',
            $height ? ' height="' . esc_attr( $height ) . '"' : '',
            esc_attr__( 'G&D Rent A Car', 'rentacar-venezia-v2' )
        );
        return;
    }

    printf(
        '<a class="%1$s" href="%2$s" rel="home" aria-label="%3$s"><span class="site-brand__main">%4$s</span><span class="site-brand__sub">%5$s</span></a>',
        esc_attr( $classes ),
        esc_url( rentacar_venezia_v2_home_url() ),
        esc_attr__( 'Rent A Car Venezia', 'rentacar-venezia-v2' ),
        esc_html__( 'RENT A CAR', 'rentacar-venezia-v2' ),
        esc_html__( 'VENEZIA', 'rentacar-venezia-v2' )
    );
}

/** The supplied favicon remains theme-owned and does not require an Admin write. */
function rentacar_venezia_v2_favicon() {
    $asset = get_template_directory() . '/assets/images/brand/favicon.ico';
    if ( ! file_exists( $asset ) ) return;
    echo '<link rel="icon" href="' . esc_url( get_template_directory_uri() . '/assets/images/brand/favicon.ico' ) . '" sizes="any" type="image/x-icon">' . "\n";
}
add_action( 'wp_head', 'rentacar_venezia_v2_favicon', 99 );

function rentacar_venezia_v2_assets() {
    $theme = wp_get_theme();
    $style_path = get_stylesheet_directory() . '/style.css';
    $script = rentacar_venezia_v2_compiled_asset( 'main' );

    wp_enqueue_style( 'rentacar-venezia-v2', get_stylesheet_uri(), array(), rentacar_venezia_v2_asset_version( $style_path, $theme->get( 'Version' ) ) );
    if ( $script ) {
        wp_enqueue_script( 'rentacar-venezia-v2', $script['uri'], array(), $script['version'], true );
        wp_localize_script(
            'rentacar-venezia-v2',
            'rentacarVenezia',
            array(
                'reservationUrl' => esc_url_raw( admin_url( 'admin-post.php' ) ),
                'estimateUrl'    => esc_url_raw( rest_url( 'rentacar/v1/estimate' ) ),
                'minimumRentalDays' => class_exists( 'Rentacar_Core_Rental_Policy' ) ? Rentacar_Core_Rental_Policy::minimum_rental_days() : 3,
                'strings'        => array(
                    'menuOpen'       => __( 'Open navigation', 'rentacar-venezia-v2' ),
                    'menuClose'      => __( 'Close navigation', 'rentacar-venezia-v2' ),
                    'sending'        => __( 'Sending…', 'rentacar-venezia-v2' ),
                    'sendRequest'    => __( 'Send request', 'rentacar-venezia-v2' ),
                    'reviewForm'     => __( 'Please review the form and try again.', 'rentacar-venezia-v2' ),
                    'deliveryFailed' => __( 'We could not send the request. Please try again.', 'rentacar-venezia-v2' ),
                    'reference'      => __( 'Reference: %s', 'rentacar-venezia-v2' ),
                    'stepTrip'       => __( '1 of 2 · Trip', 'rentacar-venezia-v2' ),
                    'stepDetails'    => __( '2 of 2 · Contact', 'rentacar-venezia-v2' ),
                    'minimumRental'  => sprintf( __( 'The minimum rental period is %d billable days.', 'rentacar-venezia-v2' ), class_exists( 'Rentacar_Core_Rental_Policy' ) ? Rentacar_Core_Rental_Policy::minimum_rental_days() : 3 ),
                    'invalidPeriod'  => __( 'Please enter a valid pickup and return date and time.', 'rentacar-venezia-v2' ),
                    'loadingEstimate'=> __( 'Loading estimate…', 'rentacar-venezia-v2' ),
                    'estimateUnavailable' => __( 'An indicative estimate is not available for these dates. You can still review your trip.', 'rentacar-venezia-v2' ),
                    'estimateError'  => __( 'We could not load the estimate. Please try again.', 'rentacar-venezia-v2' ),
                    'retryEstimate'  => __( 'Retry estimate', 'rentacar-venezia-v2' ),
                    'rentalDays'     => __( 'rental days', 'rentacar-venezia-v2' ),
                    'dailyRate'      => __( 'Daily rate', 'rentacar-venezia-v2' ),
                    'vehicleSubtotal'=> __( 'Vehicle subtotal', 'rentacar-venezia-v2' ),
                    'insurance'      => __( 'Insurance', 'rentacar-venezia-v2' ),
                    'extras'         => __( 'Extras', 'rentacar-venezia-v2' ),
                    'afterHoursFee'  => __( 'After-hours pickup', 'rentacar-venezia-v2' ),
                    'airportTransfer'=> __( 'Different-airport transfer', 'rentacar-venezia-v2' ),
                    'indicativeTotal'=> __( 'Indicative rental total', 'rentacar-venezia-v2' ),
                    'includedKm'     => __( 'Included kilometres', 'rentacar-venezia-v2' ),
                    'excessKm'       => __( 'Additional kilometres', 'rentacar-venezia-v2' ),
                ),
            )
        );
    }
}
add_action( 'wp_enqueue_scripts', 'rentacar_venezia_v2_assets' );

/**
 * LocalWP's port-forwarded HTTP preview can coexist with a database whose
 * attachment URLs still use HTTPS. The port exposes no TLS endpoint, so
 * browser image requests fail before WordPress can serve them. Normalize only
 * same-host media for an incoming local HTTP request; production HTTPS and
 * external media URLs are intentionally untouched.
 */
function rentacar_venezia_v2_local_http_media_url( $url ) {
    if ( ! is_string( $url ) || '' === $url || is_ssl() || empty( $_SERVER['HTTP_HOST'] ) ) {
        return $url;
    }

    $request_host = wp_parse_url( 'http://' . sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ), PHP_URL_HOST );
    $request_port = wp_parse_url( 'http://' . sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ), PHP_URL_PORT );
    $url_host = wp_parse_url( $url, PHP_URL_HOST );
    $url_port = wp_parse_url( $url, PHP_URL_PORT );

    if ( 'localhost' !== $request_host || 'localhost' !== $url_host || (int) $request_port !== (int) $url_port || 'https' !== wp_parse_url( $url, PHP_URL_SCHEME ) ) {
        return $url;
    }

    return set_url_scheme( $url, 'http' );
}
add_filter( 'wp_get_attachment_url', 'rentacar_venezia_v2_local_http_media_url', 99 );

function rentacar_venezia_v2_local_http_image_srcset( $sources ) {
    if ( ! is_array( $sources ) ) {
        return $sources;
    }

    foreach ( $sources as $width => $source ) {
        if ( isset( $source['url'] ) ) {
            $sources[ $width ]['url'] = rentacar_venezia_v2_local_http_media_url( $source['url'] );
        }
    }

    return $sources;
}
add_filter( 'wp_calculate_image_srcset', 'rentacar_venezia_v2_local_http_image_srcset', 99 );

/**
 * Keep enqueued WordPress assets on the LocalWP port-forward host. A few
 * plugins retain the clone's .local base URL when they enqueue assets; that
 * host has no listener on port 80 during this localhost preview.
 */
function rentacar_venezia_v2_local_http_preview_host() {
    if ( is_ssl() || empty( $_SERVER['HTTP_HOST'] ) ) {
        return '';
    }

    $host = sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) );

    return 'localhost' === wp_parse_url( 'http://' . $host, PHP_URL_HOST ) && wp_parse_url( 'http://' . $host, PHP_URL_PORT ) ? $host : '';
}

/**
 * Translate only same-site clone URLs to the LocalWP port-forward address.
 * This is evaluated per request, so no URL is stored or changed outside the
 * HTTP localhost preview.
 */
function rentacar_venezia_v2_local_http_preview_url( $url ) {
    $preview_host = rentacar_venezia_v2_local_http_preview_host();
    $url_host = wp_parse_url( $url, PHP_URL_HOST );
    $path = wp_parse_url( $url, PHP_URL_PATH );
    $path = null === $path ? '/' : $path;

    if ( ! $preview_host || ! is_string( $url_host ) || ! preg_match( '/(^localhost$|\.local$)/', $url_host ) || ! is_string( $path ) ) {
        return $url;
    }

    $query = wp_parse_url( $url, PHP_URL_QUERY );
    $fragment = wp_parse_url( $url, PHP_URL_FRAGMENT );
    $local_url = 'http://' . $preview_host . $path;

    if ( $query ) {
        $local_url .= '?' . $query;
    }
    if ( $fragment ) {
        $local_url .= '#' . $fragment;
    }

    return esc_url_raw( $local_url );
}

function rentacar_venezia_v2_local_http_content_url( $url ) {
    return rentacar_venezia_v2_local_http_preview_url( $url );
}
add_filter( 'home_url', 'rentacar_venezia_v2_local_http_content_url', 999 );
add_filter( 'page_link', 'rentacar_venezia_v2_local_http_content_url', 999 );
add_filter( 'post_link', 'rentacar_venezia_v2_local_http_content_url', 999 );
add_filter( 'post_type_link', 'rentacar_venezia_v2_local_http_content_url', 999 );
add_filter( 'term_link', 'rentacar_venezia_v2_local_http_content_url', 999 );

function rentacar_venezia_v2_local_http_menu_urls( $items ) {
    foreach ( $items as $index => $item ) {
        if ( isset( $item->url ) ) {
            $items[ $index ]->url = rentacar_venezia_v2_local_http_preview_url( $item->url );
        }
    }

    return $items;
}
add_filter( 'wp_nav_menu_objects', 'rentacar_venezia_v2_local_http_menu_urls', 20 );

function rentacar_venezia_v2_local_http_asset_url( $src ) {
    $path = wp_parse_url( $src, PHP_URL_PATH );

    if ( ! is_string( $path ) || 0 !== strpos( $path, '/wp-' ) ) {
        return $src;
    }

    return rentacar_venezia_v2_local_http_preview_url( $src );
}
add_filter( 'style_loader_src', 'rentacar_venezia_v2_local_http_asset_url', 999 );
add_filter( 'script_loader_src', 'rentacar_venezia_v2_local_http_asset_url', 999 );

function rentacar_venezia_v2_manifest_warning() {
    if ( ! current_user_can( 'manage_options' ) || ! defined( 'WP_DEBUG' ) || ! WP_DEBUG || rentacar_venezia_v2_compiled_asset( 'main' ) ) {
        return;
    }
    echo '<div class="notice notice-warning"><p>' . esc_html__( 'Rentacar Venezia V2: the compiled asset manifest or main bundle is missing. Run the production build before release.', 'rentacar-venezia-v2' ) . '</p></div>';
}
add_action( 'admin_notices', 'rentacar_venezia_v2_manifest_warning' );

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
        if ( isset( $entry['name'], $entry['file'] ) && 'main' === $entry['name'] && is_string( $entry['file'] ) ) {
            $assets['main'] = $entry['file'];
        }
    }

    return $assets;
}

/** Uses a deployed file modification time so caches cannot reuse a previous asset URL. */
function rentacar_venezia_v2_asset_version( $path, $fallback ) {
    return is_file( $path ) ? (string) filemtime( $path ) : (string) $fallback;
}

/** Resolves a manifest asset only when it remains safely inside the theme build directory. */
function rentacar_venezia_v2_compiled_asset( $name ) {
    $manifest = rentacar_venezia_v2_asset_manifest();
    $relative_path = isset( $manifest[ $name ] ) ? ltrim( (string) $manifest[ $name ], '/' ) : '';
    $dist_directory = realpath( get_template_directory() . '/assets/dist' );

    if ( ! $dist_directory || '' === $relative_path || ! preg_match( '/\.js$/', $relative_path ) ) {
        return null;
    }

    $path = realpath( $dist_directory . '/' . $relative_path );
    $prefix = trailingslashit( $dist_directory );
    if ( ! $path || 0 !== strpos( $path, $prefix ) || ! is_file( $path ) ) {
        return null;
    }

    return array(
        'path'    => $path,
        'uri'     => get_template_directory_uri() . '/assets/dist/' . str_replace( '%2F', '/', rawurlencode( $relative_path ) ),
        'version' => rentacar_venezia_v2_asset_version( $path, wp_get_theme()->get( 'Version' ) ),
    );
}

function rentacar_venezia_v2_register_routes() {
    if ( ! rentacar_venezia_v2_fleet_page_id() ) {
        add_rewrite_rule( '^fleet/?$', 'index.php?rc_fleet=1', 'top' );
        add_rewrite_rule( '^fleet/page/([0-9]+)/?$', 'index.php?rc_fleet=1&paged=$matches[1]', 'top' );
    }

    /*
     * A fleet page can exist only in the default language while migration is
     * in progress. Let Polylang receive its own language query var for the
     * custom fallback instead of redirecting a translated fleet link to that
     * default-language page. A real translated page still wins below.
     */
    if ( 'polylang' === rentacar_venezia_v2_multilingual_provider() ) {
    }
    add_rewrite_rule( '^([^/]+)/fleet/?$', 'index.php?rc_fleet=1&lang=$matches[1]', 'top' );
    add_rewrite_rule( '^([^/]+)/fleet/page/([0-9]+)/?$', 'index.php?rc_fleet=1&lang=$matches[1]&paged=$matches[2]', 'top' );
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

/**
 * A legacy custom fleet rewrite can remain in the stored rewrite rules after
 * a fleet page is added. Prefer the real page without flushing rules during a
 * visitor request, so WordPress and the active multilingual provider own it
 * as a normal page.
 */
function rentacar_venezia_v2_prefer_fleet_page( $wp ) {
    $fleet_page_id = rentacar_venezia_v2_fleet_page_id();

    if ( ! $fleet_page_id && empty( $wp->query_vars['rc_fleet'] ) ) {
        $fleet_path = trim( (string) wp_parse_url( rentacar_venezia_v2_fleet_url(), PHP_URL_PATH ), '/' );
        $request_path = trim( (string) $wp->request, '/' );

        if ( $fleet_path && preg_match( '#^' . preg_quote( $fleet_path, '#' ) . '/page/([0-9]+)/?$#', $request_path, $matches ) ) {
            $wp->query_vars['rc_fleet'] = 1;
            $wp->query_vars['paged'] = absint( $matches[1] );
        }
    }

    if ( empty( $wp->query_vars['rc_fleet'] ) || ! $fleet_page_id ) {
        return;
    }

    $paged = isset( $wp->query_vars['paged'] ) ? absint( $wp->query_vars['paged'] ) : 0;
    $wp->query_vars = array( 'page_id' => $fleet_page_id );

    if ( $paged > 1 ) {
        $wp->query_vars['paged'] = $paged;
    }
}
add_action( 'parse_request', 'rentacar_venezia_v2_prefer_fleet_page', 1 );

function rentacar_venezia_v2_template_router( $template ) {
    if ( ! get_query_var( 'rc_fleet' ) || rentacar_venezia_v2_fleet_page_id() ) {
        return $template;
    }

    global $wp_query;
    $wp_query->is_404 = false;
    status_header( 200 );

    return get_template_directory() . '/page-templates/template-fleet.php';
}
add_filter( 'template_include', 'rentacar_venezia_v2_template_router' );

/**
 * The logo already leads home, so suppress an otherwise redundant Home item
 * only while the visitor is on the front page. Menu ownership remains with
 * WordPress and no menu, page or language IDs are assumed.
 */
function rentacar_venezia_v2_hide_current_home_menu_item( $items, $args ) {
    if ( ! is_front_page() || empty( $args->theme_location ) || 'primary' !== $args->theme_location ) {
        return $items;
    }

    $home_path = untrailingslashit( (string) wp_parse_url( rentacar_venezia_v2_home_url(), PHP_URL_PATH ) );

    foreach ( $items as $index => $item ) {
        $item_path = untrailingslashit( (string) wp_parse_url( $item->url, PHP_URL_PATH ) );
        if ( $item_path === $home_path ) {
            unset( $items[ $index ] );
        }
    }

    return $items;
}
add_filter( 'wp_nav_menu_objects', 'rentacar_venezia_v2_hide_current_home_menu_item', 10, 2 );

function rentacar_venezia_v2_flush_routes() {
    rentacar_venezia_v2_register_routes();
    flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'rentacar_venezia_v2_flush_routes' );

function rentacar_venezia_v2_language_links() {
    $languages = rentacar_venezia_v2_languages();
    $items = array();

    foreach ( $languages as $key => $language ) {
        $code = ! empty( $language['slug'] ) ? $language['slug'] : ( $language['language_code'] ?? $key );
        if ( ! is_string( $code ) || empty( $language['url'] ) ) {
            continue;
        }

        $name = $language['name'] ?? ( $language['native_name'] ?? strtoupper( $code ) );
        $items[ $code ] = array(
            'language_code'   => $code,
            'native_name'     => $name,
            'translated_name' => $language['translated_name'] ?? $name,
            'country_flag_url'=> $language['flag'] ?? ( $language['flag_url'] ?? ( $language['country_flag_url'] ?? '' ) ),
            'url'             => $language['url'],
            'active'          => ! empty( $language['current_lang'] ) || ! empty( $language['active'] ),
        );
    }

    return count( $items ) > 1 ? $items : array();
}

function rentacar_venezia_v2_whatsapp_url() {
    return (string) apply_filters( 'rentacar_venezia_v2_whatsapp_url', 'https://wa.me/393445068823' );
}

function rentacar_venezia_v2_telegram_url() {
    return (string) apply_filters( 'rentacar_venezia_v2_telegram_url', 'https://t.me/+393445068823' );
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
