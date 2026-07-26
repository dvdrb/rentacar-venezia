<?php
/** Minimal provider-neutral resolver checks without a WordPress runtime. */
define( 'ABSPATH', __DIR__ . '/' );

$rentacar_test_language = 'it';
$rentacar_test_translations = array( 10 => array( 'it' => 10, 'en' => 11, 'ro' => 12, 'ru' => 13 ) );
$rentacar_test_post_languages = array( 10 => 'it', 11 => 'en', 12 => 'ro', 13 => 'ru' );

function pll_current_language( $field = 'slug' ) { global $rentacar_test_language; return $rentacar_test_language; }
function pll_default_language( $field = 'slug' ) { return 'it'; }
function pll_get_post_language( $post_id, $field = 'slug' ) { global $rentacar_test_post_languages; return $rentacar_test_post_languages[ $post_id ] ?? false; }
function pll_get_post( $post_id, $language ) { global $rentacar_test_translations; return $rentacar_test_translations[ $post_id ][ $language ] ?? false; }
function pll_get_post_translations( $post_id ) { global $rentacar_test_translations; return $rentacar_test_translations[ $post_id ] ?? array(); }
function pll_home_url( $language ) { return 'https://example.test/' . $language . '/'; }
function get_permalink( $post_id ) { return 'https://example.test/cars/' . (int) $post_id . '/'; }
function home_url( $path = '/' ) { return 'https://example.test' . $path; }
function trailingslashit( $value ) { return rtrim( $value, '/' ) . '/'; }
function determine_locale() { return 'en_US'; }
function has_filter( $filter ) { return false; }

require_once dirname( __DIR__, 2 ) . '/plugin/rentacar-core/src/Multilingual/LanguageResolverInterface.php';
require_once dirname( __DIR__, 2 ) . '/plugin/rentacar-core/src/Multilingual/PolylangLanguageResolver.php';
require_once dirname( __DIR__, 2 ) . '/plugin/rentacar-core/src/Multilingual/WpmlLanguageResolver.php';
require_once dirname( __DIR__, 2 ) . '/plugin/rentacar-core/src/Multilingual/NullLanguageResolver.php';
require_once dirname( __DIR__, 2 ) . '/plugin/rentacar-core/src/Multilingual/LanguageResolverFactory.php';
require_once dirname( __DIR__, 2 ) . '/theme/rentacar-venezia-v2/inc/multilingual.php';

function rentacar_language_assert( $condition, $message ) {
    if ( ! $condition ) {
        fwrite( STDERR, "FAIL: {$message}\n" );
        exit( 1 );
    }
}

$resolver = Rentacar_Core_Language_Resolver_Factory::create();
rentacar_language_assert( $resolver instanceof Rentacar_Core_Polylang_Language_Resolver, 'Polylang is selected when its public API is available.' );
rentacar_language_assert( 'it' === $resolver->current_language(), 'Current language comes from Polylang.' );
rentacar_language_assert( 'it' === $resolver->default_language(), 'Default language comes from Polylang.' );
rentacar_language_assert( 'it' === $resolver->post_language( 10 ), 'Vehicle language is resolved through Polylang.' );
rentacar_language_assert( 11 === $resolver->translate_post_id( 10, 'en' ), 'Translated vehicle ID is resolved through Polylang.' );
rentacar_language_assert( array( 'it' => 10, 'en' => 11, 'ro' => 12, 'ru' => 13 ) === $resolver->translations( 10 ), 'All vehicle translations are returned.' );
rentacar_language_assert( 'https://example.test/cars/12/' === $resolver->translated_permalink( 10, 'ro' ), 'Translated vehicle permalink is derived from the resolved post.' );
rentacar_language_assert( 'https://example.test/it/' === rentacar_venezia_v2_home_url(), 'Home links use Polylang’s current-language URL.' );

$rentacar_test_language = 'en';
rentacar_language_assert( 'https://example.test/en/' === rentacar_venezia_v2_home_url(), 'Home links follow a changed current language without hard-coded prefixes.' );

echo "Multilingual resolver checks passed.\n";
