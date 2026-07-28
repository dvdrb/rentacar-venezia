<?php
defined( 'ABSPATH' ) || exit;

/**
 * Technical vehicle fields shared across translations. Editorial content and
 * SEO metadata are deliberately excluded from this policy.
 */
final class Rentacar_Core_Vehicle_Field_Policy {
    public function field_names() {
        return array(
            'gallery',
            'gearbox',
            'max_passagers',
            'doors',
            'air_conditioning',
            '_rentacar_powertrain',
            'price_1_days_1',
            'price_1_days_2',
            'price',
            'price_2_days_1',
            'price_2_days_2',
            'price2',
            'price_3_days_1',
            'price_3_days_2',
            'price3',
            'price4',
        );
    }

    public function gallery_field_key() {
        return 'field_5ab2613571fba';
    }

    public function value( $post_id, $field_name ) {
        if ( function_exists( 'get_field' ) ) {
            return get_field( $field_name, $post_id, false );
        }

        return get_post_meta( $post_id, $field_name, true );
    }

    public function snapshot( $post_id ) {
        $values = array(
            'featured_image_id' => (int) get_post_thumbnail_id( $post_id ),
        );

        foreach ( $this->field_names() as $field_name ) {
            $values[ $field_name ] = $this->value( $post_id, $field_name );
        }

        return $values;
    }
}
