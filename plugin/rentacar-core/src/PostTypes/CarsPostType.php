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

    /**
     * Polylang's language taxonomy is registered for the legacy cars post
     * type. WordPress does not apply taxonomy queries to singular requests,
     * so constrain same-slug translated car URLs at SQL-clause time.
     */
    public static function constrain_main_query_to_language( $clauses, $query ) {
        if ( is_admin() || ! $query->is_main_query() || ! function_exists( 'pll_get_post_language' ) ) {
            return $clauses;
        }

        $is_car_request = 'cars' === $query->get( 'post_type' ) || '' !== (string) $query->get( 'cars' );
        $language = $query->get( 'lang' );

        if ( ! $is_car_request || ! is_string( $language ) || '' === $language || ! is_object_in_taxonomy( 'cars', 'language' ) ) {
            return $clauses;
        }

        $language_term = get_term_by( 'slug', $language, 'language' );
        if ( ! $language_term || empty( $language_term->term_taxonomy_id ) ) {
            return $clauses;
        }

        global $wpdb;
        $relationship_table = $wpdb->term_relationships;
        $language_taxonomy_id = (int) $language_term->term_taxonomy_id;

        if ( false === strpos( $clauses['join'], $relationship_table ) ) {
            $clauses['join'] .= " INNER JOIN {$relationship_table} AS rentacar_language_relationships ON ({$wpdb->posts}.ID = rentacar_language_relationships.object_id)";
        }
        $clauses['where'] .= $wpdb->prepare( ' AND rentacar_language_relationships.term_taxonomy_id = %d', $language_taxonomy_id );

        return $clauses;
    }
}
