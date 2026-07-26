<?php
/** Rebuild Polylang post relationships from preserved WPML translation groups. */
defined( 'ABSPATH' ) || exit( 1 );
if ( ! function_exists( 'pll_set_post_language' ) || ! function_exists( 'pll_save_post_translations' ) ) WP_CLI::error( 'Polylang is not active.' );
global $wpdb;
$rows = $wpdb->get_results( "SELECT element_id, trid, language_code, element_type FROM {$wpdb->prefix}icl_translations WHERE element_type IN ('post_page','post_post','post_cars') ORDER BY element_type,trid", ARRAY_A );
$groups = array();
foreach ( $rows as $row ) { $post = get_post( (int) $row['element_id'] ); if ( ! $post ) continue; $groups[ $row['element_type'] ][ $row['trid'] ][ $row['language_code'] ] = (int) $row['element_id']; }
$updated = 0;
foreach ( $groups as $type_groups ) foreach ( $type_groups as $translations ) {
    foreach ( $translations as $language => $post_id ) pll_set_post_language( $post_id, $language );
    if ( count( $translations ) > 1 ) { pll_save_post_translations( $translations ); ++$updated; }
}
$term_rows = $wpdb->get_results( "SELECT element_id, trid, language_code FROM {$wpdb->prefix}icl_translations WHERE element_type IN ('tax_nav_menu','tax_category') ORDER BY element_type,trid", ARRAY_A );
$term_groups = array();
foreach ( $term_rows as $row ) if ( term_exists( (int) $row['element_id'] ) ) $term_groups[ $row['trid'] ][ $row['language_code'] ] = (int) $row['element_id'];
$term_updated = 0;
foreach ( $term_groups as $translations ) { foreach ( $translations as $language => $term_id ) pll_set_term_language( $term_id, $language ); if ( count( $translations ) > 1 ) { pll_save_term_translations( $translations ); ++$term_updated; } }
WP_CLI::success( sprintf( 'Repaired %d post and %d term translation groups.', $updated, $term_updated ) );
