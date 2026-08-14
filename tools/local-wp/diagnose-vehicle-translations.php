<?php
/** Read-only vehicle/Polylang diagnostic. Run with wp eval-file ... */
defined( 'ABSPATH' ) || exit( 1 );

$targets = array( 'Fiat Tipo', 'Dacia Duster', 'Dacia Logan', 'Suzuki Baleno' );
$rows = array();
foreach ( get_posts( array( 'post_type' => 'cars', 'post_status' => 'publish', 'posts_per_page' => -1, 'suppress_filters' => true ) ) as $post ) {
    $translations = function_exists( 'pll_get_post_translations' ) ? (array) pll_get_post_translations( $post->ID ) : array();
    $group_titles = array_filter( array_map( 'get_the_title', $translations ) );
    if ( ! array_intersect( $targets, $group_titles ) && ! in_array( $post->post_title, $targets, true ) ) continue;
    $language = function_exists( 'pll_get_post_language' ) ? (string) pll_get_post_language( $post->ID, 'slug' ) : '';
    $source_id = $translations['it'] ?? reset( $translations );
    $rows[] = array(
        'id' => $post->ID, 'language' => $language, 'translation_group' => wp_json_encode( $translations ), 'source_identity' => $source_id ? get_the_title( $source_id ) . ' #' . $source_id : '',
        'post_title' => $post->post_title, 'post_name' => $post->post_name, 'url' => get_permalink( $post ),
        'rank_math_title' => get_post_meta( $post->ID, 'rank_math_title', true ), 'rank_math_description' => get_post_meta( $post->ID, 'rank_math_description', true ),
        'canonical' => get_permalink( $post ), 'original_language_counterpart' => $source_id ? get_permalink( $source_id ) : '',
    );
}
WP_CLI\Utils\format_items( 'table', $rows, array( 'id', 'language', 'translation_group', 'source_identity', 'post_title', 'post_name', 'url', 'rank_math_title', 'rank_math_description', 'canonical', 'original_language_counterpart' ) );
