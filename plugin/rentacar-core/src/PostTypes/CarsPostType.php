<?php
defined( 'ABSPATH' ) || exit;

final class Rentacar_Core_Cars_Post_Type {
    public static function register_when_legacy_absent() {
        if ( post_type_exists( 'cars' ) || function_exists( 'cars_init' ) ) {
            return;
        }

        register_post_type( 'cars', array(
            'labels' => array( 'name' => __( 'Cars', 'rentacar-core' ), 'singular_name' => __( 'Car', 'rentacar-core' ) ),
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'query_var' => true,
            'rewrite' => array( 'slug' => 'cars' ),
            'has_archive' => false,
            'show_in_rest' => false,
            'supports' => array( 'title', 'author', 'thumbnail' ),
            'menu_icon' => 'dashicons-sos',
            'menu_position' => 10,
        ) );
    }
}
