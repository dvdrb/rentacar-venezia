<?php
defined( 'ABSPATH' ) || exit;

final class Rentacar_Core_Wpml_Language_Resolver implements Rentacar_Core_Language_Resolver_Interface {
    public function current_language() {
        return has_filter( 'wpml_current_language' ) ? (string) apply_filters( 'wpml_current_language', '' ) : '';
    }

    public function default_language() {
        return has_filter( 'wpml_default_language' ) ? (string) apply_filters( 'wpml_default_language', '' ) : '';
    }

    public function post_language( $post_id ) {
        if ( ! has_filter( 'wpml_element_language_code' ) ) {
            return null;
        }

        $language = apply_filters(
            'wpml_element_language_code',
            null,
            array(
                'element_id'   => (int) $post_id,
                'element_type' => 'post_' . get_post_type( $post_id ),
            )
        );

        return is_string( $language ) ? $language : null;
    }

    public function translate_post_id( $post_id, $language = null ) {
        $post_id = (int) $post_id;
        if ( $post_id <= 0 || ! has_filter( 'wpml_object_id' ) ) {
            return $post_id;
        }

        $language = $language ?: $this->current_language();
        $translated_id = apply_filters( 'wpml_object_id', $post_id, get_post_type( $post_id ), false, $language );

        return $translated_id ? (int) $translated_id : null;
    }

    public function translations( $post_id ) {
        if ( ! has_filter( 'wpml_element_trid' ) || ! has_filter( 'wpml_get_element_translations' ) ) {
            return array();
        }

        $element_type = 'post_' . get_post_type( $post_id );
        $trid = apply_filters( 'wpml_element_trid', null, (int) $post_id, $element_type );
        $translations = $trid ? apply_filters( 'wpml_get_element_translations', null, $trid, $element_type ) : array();
        $resolved = array();

        foreach ( is_array( $translations ) ? $translations : array() as $language => $translation ) {
            if ( isset( $translation->element_id ) ) {
                $resolved[ $language ] = (int) $translation->element_id;
            }
        }

        return $resolved;
    }

    public function translated_permalink( $post_id, $language = null ) {
        $translated_id = $this->translate_post_id( $post_id, $language );

        return $translated_id ? get_permalink( $translated_id ) : '';
    }
}
