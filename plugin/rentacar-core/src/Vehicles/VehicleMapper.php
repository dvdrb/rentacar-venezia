<?php
defined( 'ABSPATH' ) || exit;

final class Rentacar_Core_Vehicle_Mapper {
    private $language_resolver;

    public function __construct( Rentacar_Core_Language_Resolver_Interface $language_resolver = null ) {
        $this->language_resolver = $language_resolver ? $language_resolver : Rentacar_Core_Language_Resolver_Factory::create();
    }

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
            'menu_order'        => (int) $post->menu_order,
            'slug'              => $post->post_name,
            'permalink'         => get_permalink( $post ),
            'language'          => $this->language_resolver->post_language( $post->ID ),
            'translations'      => $this->language_resolver->translations( $post->ID ),
            'featured_image_id' => (int) get_post_thumbnail_id( $post ),
            'gallery'           => $images,
            'vehicle_gallery'   => new Rentacar_Core_Vehicle_Gallery( get_post_thumbnail_id( $post ), $images ),
            'transmission'      => get_post_meta( $post->ID, 'gearbox', true ),
            'passengers'        => $this->integer_meta( $post->ID, 'max_passagers' ),
            'doors'             => $this->integer_meta( $post->ID, 'doors' ),
            'air_conditioning'  => (bool) get_post_meta( $post->ID, 'air_conditioning', true ),
            'powertrain'        => class_exists( 'Rentacar_Core_Vehicle_Maintenance' ) ? Rentacar_Core_Vehicle_Maintenance::normalize_powertrain( get_post_meta( $post->ID, Rentacar_Core_Vehicle_Maintenance::POWERTRAIN_META, true ) ) : 'other',
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

}
