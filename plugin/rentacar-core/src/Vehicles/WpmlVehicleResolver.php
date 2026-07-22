<?php
defined( 'ABSPATH' ) || exit;

/**
 * Resolves existing WPML car translations without changing language settings.
 */
final class Rentacar_Core_Wpml_Vehicle_Resolver {
    public function translate_id( $vehicle_id, $language = null ) {
        $vehicle_id = (int) $vehicle_id;

        if ( $vehicle_id <= 0 || ! has_filter( 'wpml_object_id' ) ) {
            return $vehicle_id;
        }

        $resolved = apply_filters( 'wpml_object_id', $vehicle_id, 'cars', false, $language );

        return $resolved ? (int) $resolved : null;
    }

    public function language( $vehicle_id ) {
        if ( ! has_filter( 'wpml_element_language_code' ) ) {
            return null;
        }

        $language = apply_filters(
            'wpml_element_language_code',
            null,
            array(
                'element_id'   => (int) $vehicle_id,
                'element_type' => 'post_cars',
            )
        );

        return is_string( $language ) ? $language : null;
    }

    public function translations( $vehicle_id ) {
        if ( ! has_filter( 'wpml_element_trid' ) || ! has_filter( 'wpml_get_element_translations' ) ) {
            return array();
        }

        $trid = apply_filters( 'wpml_element_trid', null, (int) $vehicle_id, 'post_cars' );
        $translations = $trid ? apply_filters( 'wpml_get_element_translations', null, $trid, 'post_cars' ) : array();
        $resolved = array();

        foreach ( is_array( $translations ) ? $translations : array() as $language => $translation ) {
            if ( isset( $translation->element_id ) ) {
                $resolved[ $language ] = (int) $translation->element_id;
            }
        }

        return $resolved;
    }
}
