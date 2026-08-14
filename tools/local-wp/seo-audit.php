<?php
/** Local, read-only SEO quality gate. Run with: npm run seo:audit */
defined( 'ABSPATH' ) || exit( 1 );

$issues = array();
$add = function( $severity, $code, $post, $detail ) use ( &$issues ) { $issues[] = array( 'severity' => $severity, 'code' => $code, 'id' => (int) $post->ID, 'language' => function_exists( 'pll_get_post_language' ) ? pll_get_post_language( $post->ID, 'slug' ) : '', 'url' => get_permalink( $post ), 'detail' => $detail ); };
$pages = get_posts( array( 'post_type' => array( 'page', 'post', 'cars' ), 'post_status' => 'publish', 'posts_per_page' => -1, 'suppress_filters' => true ) );
$seen_titles = array(); $seen_descriptions = array(); $indexable = array();
foreach ( $pages as $post ) {
    $is_indexable = 'cars' === $post->post_type || ( 'page' === $post->post_type && '1' === get_post_meta( $post->ID, '_rc_seo_indexable', true ) ) || ( 'post' === $post->post_type && '1' === get_post_meta( $post->ID, '_rc_seo_indexable', true ) );
    if ( ! $is_indexable ) continue;
    $indexable[] = $post;
    $title = trim( (string) get_post_meta( $post->ID, 'rank_math_title', true ) ); $description = trim( (string) get_post_meta( $post->ID, 'rank_math_description', true ) );
    if ( 'cars' !== $post->post_type && '' === $title ) $add( 'WARNING', 'missing_title', $post, 'Rank Math title is not stored; a render-time template may supply it.' );
    if ( 'cars' !== $post->post_type && '' === $description ) $add( 'WARNING', 'missing_description', $post, 'Rank Math description is not stored.' );
    if ( $title ) { if ( isset( $seen_titles[ $title ] ) ) $add( 'WARNING', 'duplicate_title', $post, 'Duplicates page #' . $seen_titles[ $title ] ); else $seen_titles[ $title ] = $post->ID; }
    if ( $description ) { if ( isset( $seen_descriptions[ $description ] ) ) $add( 'WARNING', 'duplicate_description', $post, 'Duplicates page #' . $seen_descriptions[ $description ] ); else $seen_descriptions[ $description ] = $post->ID; }
    if ( 'page' === $post->post_type && substr_count( strtolower( (string) $post->post_content ), '<h1' ) > 1 ) $add( 'ERROR', 'multiple_h1', $post, 'Editor content contains more than one H1.' );
    if ( false !== stripos( $post->post_content . $title . $description, 'localhost' ) ) $add( 'ERROR', 'localhost_public_copy', $post, 'Localhost appears in indexable content or metadata.' );
    if ( false !== stripos( $post->post_content . $title . $description, 'rent a car venezia' ) && false === stripos( $post->post_content . $title . $description, 'G&D Rent A Car' ) ) $add( 'INFO', 'legacy_phrase_review', $post, 'Review whether Rent a Car Venezia is being used as identity instead of a search phrase.' );
    if ( function_exists( 'pll_get_post_translations' ) && count( pll_languages_list( array( 'fields' => 'slug' ) ) ) > 1 && count( pll_get_post_translations( $post->ID ) ) < 4 ) $add( 'WARNING', 'missing_translation', $post, 'Missing one or more configured language counterparts.' );
}
$report = array( 'generated_at' => gmdate( 'c' ), 'site_url' => home_url( '/' ), 'indexable_pages' => count( $indexable ), 'issues' => $issues, 'summary' => array_count_values( array_column( $issues, 'severity' ) ) );
$output = getenv( 'RENTACAR_REPORT_DIR' );
if ( ! $output ) {
    $output = dirname( __DIR__, 2 ) . '/docs/generated';
}
$output = wp_normalize_path( $output ); if ( ! is_dir( $output ) ) wp_mkdir_p( $output );
file_put_contents( $output . '/seo-audit.json', wp_json_encode( $report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) );
$csv = fopen( $output . '/seo-audit.csv', 'w' ); fputcsv( $csv, array( 'severity', 'code', 'id', 'language', 'url', 'detail' ) ); foreach ( $issues as $issue ) fputcsv( $csv, $issue ); fclose( $csv );
$markdown = "# Local SEO audit\n\nGenerated: " . $report['generated_at'] . "\n\nIndexable resources: " . count( $indexable ) . "\n\n| Severity | Code | URL | Detail |\n| --- | --- | --- | --- |\n"; foreach ( $issues as $issue ) $markdown .= '| ' . $issue['severity'] . ' | ' . $issue['code'] . ' | ' . $issue['url'] . ' | ' . $issue['detail'] . " |\n"; file_put_contents( $output . '/seo-audit.md', $markdown );
WP_CLI::success( sprintf( 'SEO audit complete: %d indexable resources, %d errors, %d warnings. Reports: %s', count( $indexable ), (int) ( $report['summary']['ERROR'] ?? 0 ), (int) ( $report['summary']['WARNING'] ?? 0 ), $output ) );
