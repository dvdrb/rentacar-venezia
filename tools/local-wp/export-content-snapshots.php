<?php
/** Read-only content snapshot. Use --output=/absolute/path.json to persist it. */
defined( 'ABSPATH' ) || exit( 1 );

$arguments = isset( $assoc_args ) && is_array( $assoc_args ) ? $assoc_args : array();
$output = isset( $arguments['output'] ) ? (string) $arguments['output'] : '';
$pages = get_posts( array( 'post_type' => 'page', 'post_status' => 'any', 'posts_per_page' => -1, 'orderby' => 'ID', 'order' => 'ASC' ) );
$snapshot = array( 'generated_at' => gmdate( 'c' ), 'pages' => array() );
foreach ( $pages as $page ) {
    $snapshot['pages'][] = array(
        'id'       => $page->ID,
        'language' => function_exists( 'pll_get_post_language' ) ? pll_get_post_language( $page->ID, 'slug' ) : '',
        'title'    => $page->post_title,
        'slug'     => $page->post_name,
        'content'  => $page->post_content,
        'template' => get_page_template_slug( $page->ID ),
    );
}
$json = wp_json_encode( $snapshot, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
if ( $output ) {
    if ( false === file_put_contents( $output, $json ) ) {
        WP_CLI::error( 'Could not write the requested snapshot output.' );
    }
    WP_CLI::success( 'Snapshot written to ' . $output );
} else {
    WP_CLI::line( $json );
}
