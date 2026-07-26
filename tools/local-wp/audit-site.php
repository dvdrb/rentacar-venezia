<?php
/** Read-only LocalWP audit. Run with: wp eval-file tools/local-wp/audit-site.php */
defined( 'ABSPATH' ) || exit( 1 );

$pages = get_posts( array( 'post_type' => 'page', 'post_status' => 'any', 'posts_per_page' => -1, 'orderby' => 'ID', 'order' => 'ASC' ) );
$report = array(
    'generated_at' => gmdate( 'c' ),
    'site_url'     => home_url( '/' ),
    'theme'        => wp_get_theme()->get( 'Name' ),
    'languages'    => function_exists( 'pll_languages_list' ) ? pll_languages_list( array( 'fields' => 'slug' ) ) : array(),
    'pages'        => array(),
);

foreach ( $pages as $page ) {
    $report['pages'][] = array(
        'id'               => $page->ID,
        'status'           => $page->post_status,
        'title'            => $page->post_title,
        'slug'             => $page->post_name,
        'language'         => function_exists( 'pll_get_post_language' ) ? pll_get_post_language( $page->ID, 'slug' ) : '',
        'template'         => get_page_template_slug( $page->ID ),
        'provisioning_key' => get_post_meta( $page->ID, '_rc_provisioning_key', true ),
        'url'              => get_permalink( $page->ID ),
    );
}

WP_CLI::line( wp_json_encode( $report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
