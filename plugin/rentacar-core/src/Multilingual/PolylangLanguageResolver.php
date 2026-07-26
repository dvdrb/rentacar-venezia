<?php
defined( 'ABSPATH' ) || exit;

final class Rentacar_Core_Polylang_Language_Resolver implements Rentacar_Core_Language_Resolver_Interface {
    public function current_language() {
        return function_exists( 'pll_current_language' ) ? (string) pll_current_language( 'slug' ) : '';
    }

    public function default_language() {
        return function_exists( 'pll_default_language' ) ? (string) pll_default_language( 'slug' ) : '';
    }

    public function post_language( $post_id ) {
        $language = function_exists( 'pll_get_post_language' ) ? pll_get_post_language( (int) $post_id, 'slug' ) : false;

        return is_string( $language ) ? $language : null;
    }

    public function translate_post_id( $post_id, $language = null ) {
        $post_id = (int) $post_id;
        if ( $post_id <= 0 || ! function_exists( 'pll_get_post' ) ) {
            return $post_id;
        }

        $language = $language ?: $this->current_language();
        $translated_id = pll_get_post( $post_id, $language );

        return $translated_id ? (int) $translated_id : null;
    }

    public function translations( $post_id ) {
        return function_exists( 'pll_get_post_translations' ) ? array_map( 'intval', (array) pll_get_post_translations( (int) $post_id ) ) : array();
    }

    public function translated_permalink( $post_id, $language = null ) {
        $translated_id = $this->translate_post_id( $post_id, $language );

        return $translated_id ? get_permalink( $translated_id ) : '';
    }
}
