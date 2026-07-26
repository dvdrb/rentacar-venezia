<?php
defined( 'ABSPATH' ) || exit;

/**
 * Small provider-neutral multilingual layer for theme code. Polylang is the
 * active provider; the WPML branch exists solely so an interrupted migration
 * cannot break public links before Polylang is enabled.
 */
function rentacar_venezia_v2_multilingual_provider() {
    if ( function_exists( 'pll_current_language' ) ) {
        return 'polylang';
    }

    return has_filter( 'wpml_current_language' ) ? 'wpml' : 'none';
}

function rentacar_venezia_v2_current_language() {
    $provider = rentacar_venezia_v2_multilingual_provider();

    if ( 'polylang' === $provider ) {
        return (string) pll_current_language( 'slug' );
    }

    if ( 'wpml' === $provider ) {
        return (string) apply_filters( 'wpml_current_language', '' );
    }

    return substr( determine_locale(), 0, 2 );
}

function rentacar_venezia_v2_default_language() {
    $provider = rentacar_venezia_v2_multilingual_provider();

    if ( 'polylang' === $provider ) {
        return (string) pll_default_language( 'slug' );
    }

    if ( 'wpml' === $provider ) {
        return (string) apply_filters( 'wpml_default_language', '' );
    }

    return rentacar_venezia_v2_current_language();
}

function rentacar_venezia_v2_post_language( $post_id ) {
    if ( 'polylang' === rentacar_venezia_v2_multilingual_provider() ) {
        return (string) pll_get_post_language( $post_id, 'slug' );
    }

    if ( has_filter( 'wpml_element_language_code' ) ) {
        return (string) apply_filters(
            'wpml_element_language_code',
            null,
            array(
                'element_id'   => (int) $post_id,
                'element_type' => 'post_' . get_post_type( $post_id ),
            )
        );
    }

    return '';
}

function rentacar_venezia_v2_translated_post_id( $post_id, $language = null ) {
    $post_id = absint( $post_id );
    $language = $language ?: rentacar_venezia_v2_current_language();

    if ( ! $post_id ) {
        return 0;
    }

    if ( 'polylang' === rentacar_venezia_v2_multilingual_provider() ) {
        return (int) pll_get_post( $post_id, $language );
    }

    if ( has_filter( 'wpml_object_id' ) ) {
        return (int) apply_filters( 'wpml_object_id', $post_id, get_post_type( $post_id ), false, $language );
    }

    return $post_id;
}

function rentacar_venezia_v2_translations( $post_id ) {
    if ( 'polylang' === rentacar_venezia_v2_multilingual_provider() ) {
        return (array) pll_get_post_translations( $post_id );
    }

    return array();
}

function rentacar_venezia_v2_translated_permalink( $post_id, $language = null ) {
    $translated_id = rentacar_venezia_v2_translated_post_id( $post_id, $language );

    return $translated_id ? get_permalink( $translated_id ) : '';
}

function rentacar_venezia_v2_languages() {
    if ( 'polylang' === rentacar_venezia_v2_multilingual_provider() ) {
        return (array) pll_the_languages(
            array(
                'raw'                         => 1,
                'hide_if_no_translation'      => 0,
                'hide_current'                => 0,
            )
        );
    }

    return (array) apply_filters(
        'wpml_active_languages',
        array(),
        array(
            'skip_missing' => 0,
            'orderby'      => 'code',
        )
    );
}

/**
 * Localizes a provider-agnostic fallback URL such as the legacy fleet route.
 */
function rentacar_venezia_v2_localized_fallback_url( $url ) {
    if ( 'polylang' === rentacar_venezia_v2_multilingual_provider() ) {
        $path = trim( (string) wp_parse_url( $url, PHP_URL_PATH ), '/' );
        $home = trailingslashit( pll_home_url( rentacar_venezia_v2_current_language() ) );

        return $path ? $home . trailingslashit( $path ) : $home;
    }

    if ( has_filter( 'wpml_permalink' ) ) {
        return (string) apply_filters( 'wpml_permalink', $url, rentacar_venezia_v2_current_language() );
    }

    return $url;
}
