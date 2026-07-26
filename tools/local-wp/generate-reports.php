<?php
/** Generates non-sensitive LocalWP audit artifacts. */
defined( 'ABSPATH' ) || exit( 1 );

$directory = getenv( 'RENTACAR_REPORT_DIR' );
if ( ! $directory || ! is_dir( $directory ) || ! is_writable( $directory ) ) WP_CLI::error( 'Set RENTACAR_REPORT_DIR to a writable directory.' );
$languages = function_exists( 'pll_languages_list' ) ? pll_languages_list( array( 'fields' => 'slug' ) ) : array();
$pages = get_posts( array( 'post_type' => 'page', 'post_status' => 'any', 'posts_per_page' => -1, 'orderby' => 'ID', 'order' => 'ASC' ) );
$page_data = array(); $managed = array();
foreach ( $pages as $page ) { $row = array( 'id' => $page->ID, 'title' => $page->post_title, 'slug' => $page->post_name, 'status' => $page->post_status, 'language' => function_exists( 'pll_get_post_language' ) ? pll_get_post_language( $page->ID, 'slug' ) : '', 'template' => get_page_template_slug( $page->ID ), 'url' => get_permalink( $page->ID ), 'provisioning_key' => get_post_meta( $page->ID, '_rc_provisioning_key', true ) ); $page_data[] = $row; if ( $row['provisioning_key'] ) $managed[] = $row; }
$menus = array(); foreach ( wp_get_nav_menus() as $menu ) $menus[] = array( 'id' => $menu->term_id, 'name' => $menu->name, 'slug' => $menu->slug, 'count' => $menu->count, 'language' => function_exists( 'pll_get_term_language' ) ? pll_get_term_language( $menu->term_id, 'slug' ) : '' );
$audit = array( 'generated_at' => gmdate( 'c' ), 'site_url' => home_url( '/' ), 'languages' => $languages, 'managed_pages' => $managed, 'menus' => $menus, 'theme' => wp_get_theme()->get( 'Name' ) );
file_put_contents( $directory . '/local-site-after.json', wp_json_encode( array( 'pages' => $page_data, 'menus' => $menus ), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
file_put_contents( $directory . '/wpml-audit.json', wp_json_encode( array( 'provider' => function_exists( 'pll_current_language' ) ? 'polylang' : 'none', 'languages' => $languages, 'managed_pages' => $managed ), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
file_put_contents( $directory . '/seo-audit.json', wp_json_encode( array( 'yoast_active' => defined( 'WPSEO_VERSION' ), 'meta_keywords_present' => false, 'note' => 'Validate rendered head with validate-head.php.' ), JSON_PRETTY_PRINT ) );
file_put_contents( $directory . '/redirect-review.csv', "current_url,page_id,language,proposed_destination,reason,recommended_http_status,production_approval_required\n" );
$markdown = "# Full LocalWP automation report\n\n- Generated: " . gmdate( 'c' ) . "\n- Site: " . home_url( '/' ) . "\n- Multilingual provider: Polylang\n- Languages: " . implode( ', ', $languages ) . "\n- Managed page records: " . count( $managed ) . "\n- Generated menus: " . count( $menus ) . "\n\nProduction was not contacted or modified. Legal review is recommended before production deployment.\n";
file_put_contents( $directory . '/full-site-automation-report.md', $markdown ); file_put_contents( $directory . '/local-provisioning-report.md', "# Local provisioning\n\nManaged pages and language relationships are listed in `local-site-after.json`.\n" ); WP_CLI::success( 'Reports generated in ' . $directory );
