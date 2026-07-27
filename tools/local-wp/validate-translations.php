<?php
/** Read-only Polylang validation for every public translated content group. */
defined( 'ABSPATH' ) || exit( 1 );

if ( ! function_exists( 'pll_languages_list' ) ) {
    WP_CLI::error( 'Polylang is not active.' );
}

$languages = pll_languages_list( array( 'fields' => 'slug' ) );
$post_types = array_filter(
    array( 'page', 'cars', 'post' ),
    'post_type_exists'
);
$missing = array();
foreach ( $post_types as $post_type ) {
    $posts = get_posts( array( 'post_type' => $post_type, 'post_status' => 'publish', 'posts_per_page' => -1 ) );
    $checked_groups = array();

    foreach ( $posts as $post ) {
        $translations = (array) pll_get_post_translations( $post->ID );
        $group_key = implode( ',', array_map( 'intval', $translations ) );
        if ( isset( $checked_groups[ $group_key ] ) ) {
            continue;
        }
        $checked_groups[ $group_key ] = true;

        foreach ( $languages as $language ) {
            if ( empty( $translations[ $language ] ) ) {
                $label = (string) get_post_meta( $post->ID, '_rc_provisioning_key', true );
                $missing[] = ( $label ? $label . ' ' : '' ) . $post_type . ' "' . $post->post_title . '" (' . $post->ID . '): missing ' . $language;
            }
        }
    }
}
if ( $missing ) {
    WP_CLI::warning( implode( "\n", array_unique( $missing ) ) );
    exit( 1 );
}
WP_CLI::success( 'All published pages, vehicles and posts have translations for: ' . implode( ', ', $languages ) );
