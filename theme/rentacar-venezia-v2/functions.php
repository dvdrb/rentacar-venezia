<?php
defined( 'ABSPATH' ) || exit;

function rentacar_venezia_v2_setup() {
    load_theme_textdomain( 'rentacar-venezia-v2', get_template_directory() . '/languages' );
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    register_nav_menus( array( 'primary' => __( 'Primary navigation', 'rentacar-venezia-v2' ) ) );
}
add_action( 'after_setup_theme', 'rentacar_venezia_v2_setup' );

function rentacar_venezia_v2_assets() {
    wp_enqueue_style( 'rentacar-venezia-v2', get_stylesheet_uri(), array(), '0.1.0' );
}
add_action( 'wp_enqueue_scripts', 'rentacar_venezia_v2_assets' );
