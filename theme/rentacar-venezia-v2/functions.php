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

    wp_enqueue_style( 'rentacar-venezia-v2', get_stylesheet_uri(), array(), $theme->get( 'Version' ) );
    wp_enqueue_script(
        'rentacar-venezia-v2',
        get_template_directory_uri() . '/assets/dist/main.js',
        array(),
        $theme->get( 'Version' ),
        true
    );
}
add_action( 'wp_enqueue_scripts', 'rentacar_venezia_v2_assets' );

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
