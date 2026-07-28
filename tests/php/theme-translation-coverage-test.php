<?php
/** Ensure every owned, literal theme-interface string has all three translations. */
define( 'ABSPATH', __DIR__ . '/' );
function add_filter() {}

require_once dirname( __DIR__, 2 ) . '/theme/rentacar-venezia-v2/inc/interface-translations.php';

$translations = rentacar_venezia_v2_interface_translation_map();
$missing = array( 'it' => array(), 'ro' => array(), 'ru' => array() );
$root = dirname( __DIR__, 2 ) . '/theme/rentacar-venezia-v2';
$files = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $root, FilesystemIterator::SKIP_DOTS ) );

foreach ( $files as $file ) {
    if ( 'php' !== $file->getExtension() || 'interface-translations.php' === $file->getFilename() ) {
        continue;
    }

    $source = file_get_contents( $file->getPathname() );
    preg_match_all( "/\\b(?:__|_e|esc_html_e|esc_attr_e)\\(\\s*'((?:\\\\\\'|[^'])*)'\\s*,\\s*'rentacar-venezia-v2'/", $source, $matches );

    foreach ( $matches[1] as $raw_text ) {
        $text = stripslashes( $raw_text );
        foreach ( $missing as $language => $strings ) {
            if ( ! isset( $translations[ $language ][ $text ] ) ) {
                $missing[ $language ][ $text ] = $file->getPathname();
            }
        }
    }
}

foreach ( $missing as $language => $strings ) {
    if ( ! $strings ) {
        continue;
    }

    fwrite( STDERR, sprintf( "Missing %s translations:\n%s\n", $language, implode( "\n", array_keys( $strings ) ) ) );
    exit( 1 );
}

echo "Theme translation coverage checks passed.\n";
