<?php
/** Read-only Polylang validation for managed pages. */
defined( 'ABSPATH' ) || exit( 1 );

if ( ! function_exists( 'pll_languages_list' ) ) {
    WP_CLI::error( 'Polylang is not active.' );
}

$languages = pll_languages_list( array( 'fields' => 'slug' ) );
$pages = get_posts( array( 'post_type' => 'page', 'post_status' => 'publish', 'posts_per_page' => -1, 'meta_key' => '_rc_provisioning_key' ) );
$missing = array();
foreach ( $pages as $page ) {
    $key = (string) get_post_meta( $page->ID, '_rc_provisioning_key', true );
    $translations = pll_get_post_translations( $page->ID );
    foreach ( $languages as $language ) {
        if ( empty( $translations[ $language ] ) ) {
            $missing[] = $key . ' (' . $page->ID . '): missing ' . $language;
        }
    }
}
if ( $missing ) {
    WP_CLI::warning( implode( "\n", array_unique( $missing ) ) );
    exit( 1 );
}
WP_CLI::success( 'All managed pages have translations for: ' . implode( ', ', $languages ) );
