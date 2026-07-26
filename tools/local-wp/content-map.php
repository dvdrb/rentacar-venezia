<?php
/** Read-only map of managed pages and their Polylang relationships. */
defined( 'ABSPATH' ) || exit( 1 );

$pages = get_posts( array( 'post_type' => 'page', 'post_status' => 'any', 'posts_per_page' => -1, 'meta_key' => '_rc_provisioning_key', 'orderby' => 'meta_value', 'order' => 'ASC' ) );
$map = array();
foreach ( $pages as $page ) {
    $key = (string) get_post_meta( $page->ID, '_rc_provisioning_key', true );
    if ( '' === $key ) {
        continue;
    }
    $map[ $key ][] = array(
        'id'           => $page->ID,
        'language'     => function_exists( 'pll_get_post_language' ) ? pll_get_post_language( $page->ID, 'slug' ) : '',
        'title'        => $page->post_title,
        'url'          => get_permalink( $page->ID ),
        'template'     => get_page_template_slug( $page->ID ),
        'content_hash' => hash( 'sha256', (string) $page->post_content ),
    );
}
WP_CLI::line( wp_json_encode( $map, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
