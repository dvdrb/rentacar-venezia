<?php
defined( 'ABSPATH' ) || exit;

final class Rentacar_Core_Vehicle_Mapper {
    public function map( WP_Post $post ) {
        $gallery = function_exists( 'get_field' ) ? get_field( 'gallery', $post->ID ) : array();
        $images = array();

        foreach ( is_array( $gallery ) ? $gallery : array() as $row ) {
            if ( ! empty( $row['image'] ) ) {
                $images[] = (int) $row['image'];
            }
        }

        $last_banded_day = $this->integer_meta( $post->ID, 'price_3_days_2' );

        return new Rentacar_Core_Vehicle( array(
            'id'                => (int) $post->ID,
            'title'             => get_the_title( $post ),
            'slug'              => $post->post_name,
            'permalink'         => get_permalink( $post ),
            'language'          => $this->language( $post->ID ),
            'featured_image_id' => (int) get_post_thumbnail_id( $post ),
            'gallery'           => $images,
            'transmission'      => get_post_meta( $post->ID, 'gearbox', true ),
            'passengers'        => $this->integer_meta( $post->ID, 'max_passagers' ),
            'doors'             => $this->integer_meta( $post->ID, 'doors' ),
            'air_conditioning'  => (bool) get_post_meta( $post->ID, 'air_conditioning', true ),
            'pricing_bands' => new Rentacar_Core_Pricing_Band_Collection( array(
                new Rentacar_Core_Pricing_Band( $this->integer_meta( $post->ID, 'price_1_days_1' ), $this->integer_meta( $post->ID, 'price_1_days_2' ), get_post_meta( $post->ID, 'price', true ) ),
                new Rentacar_Core_Pricing_Band( $this->integer_meta( $post->ID, 'price_2_days_1' ), $this->integer_meta( $post->ID, 'price_2_days_2' ), get_post_meta( $post->ID, 'price2', true ) ),
                new Rentacar_Core_Pricing_Band( $this->integer_meta( $post->ID, 'price_3_days_1' ), $last_banded_day, get_post_meta( $post->ID, 'price3', true ) ),
                new Rentacar_Core_Pricing_Band( $last_banded_day ? $last_banded_day + 1 : 0, null, get_post_meta( $post->ID, 'price4', true ) ),
            ) ),
        ) );
    }

    private function integer_meta( $post_id, $key ) {
        return max( 0, (int) get_post_meta( $post_id, $key, true ) );
    }

    private function language( $post_id ) {
        if ( ! has_filter( 'wpml_element_language_code' ) ) {
            return null;
        }

        $language = apply_filters(
            'wpml_element_language_code',
            null,
            array(
                'element_id'   => (int) $post_id,
                'element_type' => 'post_cars',
            )
        );

        return is_string( $language ) ? $language : null;
    }
}
