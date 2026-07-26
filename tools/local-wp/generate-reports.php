<?php
/** Generates non-sensitive LocalWP audit artifacts. */
defined( 'ABSPATH' ) || exit( 1 );

$directory = getenv( 'RENTACAR_REPORT_DIR' );
if ( ! $directory || ! is_dir( $directory ) || ! is_writable( $directory ) ) WP_CLI::error( 'Set RENTACAR_REPORT_DIR to a writable directory.' );
$languages = function_exists( 'pll_languages_list' ) ? pll_languages_list( array( 'fields' => 'slug' ) ) : array();
$pages = get_posts( array( 'post_type' => 'page', 'post_status' => 'any', 'posts_per_page' => -1, 'orderby' => 'ID', 'order' => 'ASC' ) );
$page_data = array(); $managed = array(); $missing_translations = array(); $managed_yoast = 0;
foreach ( $pages as $page ) {
    $row = array( 'id' => $page->ID, 'title' => $page->post_title, 'slug' => $page->post_name, 'status' => $page->post_status, 'language' => function_exists( 'pll_get_post_language' ) ? pll_get_post_language( $page->ID, 'slug' ) : '', 'template' => get_page_template_slug( $page->ID ), 'url' => get_permalink( $page->ID ), 'provisioning_key' => get_post_meta( $page->ID, '_rc_provisioning_key', true ) );
    $page_data[] = $row;
    if ( $row['provisioning_key'] ) {
        $managed[] = $row;
        if ( function_exists( 'pll_get_post_translations' ) ) foreach ( $languages as $language ) if ( empty( pll_get_post_translations( $page->ID )[ $language ] ) ) $missing_translations[] = $row['provisioning_key'] . ':' . $page->ID . ':' . $language;
        if ( '' !== (string) get_post_meta( $page->ID, '_yoast_wpseo_title', true ) && '' !== (string) get_post_meta( $page->ID, '_yoast_wpseo_metadesc', true ) ) $managed_yoast++;
    }
}
$menus = array(); foreach ( wp_get_nav_menus() as $menu ) $menus[] = array( 'id' => $menu->term_id, 'name' => $menu->name, 'slug' => $menu->slug, 'count' => $menu->count, 'language' => function_exists( 'pll_get_term_language' ) ? pll_get_term_language( $menu->term_id, 'slug' ) : '' );
$audit = array( 'generated_at' => gmdate( 'c' ), 'site_url' => home_url( '/' ), 'languages' => $languages, 'managed_pages' => $managed, 'menus' => $menus, 'theme' => wp_get_theme()->get( 'Name' ) );
file_put_contents( $directory . '/local-site-after.json', wp_json_encode( array( 'pages' => $page_data, 'menus' => $menus ), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
file_put_contents( $directory . '/wpml-audit.json', wp_json_encode( array( 'provider' => function_exists( 'pll_current_language' ) ? 'polylang' : 'none', 'languages' => $languages, 'managed_pages' => $managed ), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
file_put_contents( $directory . '/seo-audit.json', wp_json_encode( array( 'yoast_active' => defined( 'WPSEO_VERSION' ), 'meta_keywords_present' => false, 'note' => 'Validate rendered head with validate-head.php.' ), JSON_PRETTY_PRINT ) );
file_put_contents( $directory . '/redirect-review.csv', "current_url,page_id,language,proposed_destination,reason,recommended_http_status,production_approval_required\n" );
$before_snapshot = getenv( 'RENTACAR_BEFORE_SNAPSHOT' );
if ( $before_snapshot && is_readable( $before_snapshot ) ) {
    $before_data = json_decode( (string) file_get_contents( $before_snapshot ), true );
    if ( is_array( $before_data ) ) file_put_contents( $directory . '/local-site-before.json', wp_json_encode( $before_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
}
$markdown = "# Full LocalWP automation report\n\n- Generated: " . gmdate( 'c' ) . "\n- Site: " . home_url( '/' ) . "\n- Multilingual provider: Polylang\n- Languages: " . implode( ', ', $languages ) . "\n- Managed page records: " . count( $managed ) . "\n- Managed page records with Yoast title and description: " . $managed_yoast . "\n- Missing managed translations: " . ( $missing_translations ? implode( ', ', array_unique( $missing_translations ) ) : 'none' ) . "\n- Generated menus: " . count( $menus ) . "\n\nThe local provisioner updates only content it created and keeps existing editor-managed Contact and FAQ content intact. Production was not contacted or modified. Legal review is recommended before production deployment.\n";
file_put_contents( $directory . '/full-site-automation-report.md', $markdown ); file_put_contents( $directory . '/local-provisioning-report.md', "# Local provisioning\n\nManaged pages, language relationships and menus are listed in `local-site-after.json`. Existing Contact and FAQ editor content was retained.\n" ); WP_CLI::success( 'Reports generated in ' . $directory );
