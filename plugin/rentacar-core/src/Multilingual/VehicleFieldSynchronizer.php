<?php
defined( 'ABSPATH' ) || exit;

/** Copies only the technical policy fields from one vehicle translation to another. */
final class Rentacar_Core_Vehicle_Field_Synchronizer {
    private $policy;

    public function __construct( Rentacar_Core_Vehicle_Field_Policy $policy = null ) {
        $this->policy = $policy ? $policy : new Rentacar_Core_Vehicle_Field_Policy();
    }

    public function synchronize( $source_id, $target_id ) {
        $source_id = absint( $source_id );
        $target_id = absint( $target_id );

        if ( ! $source_id || ! $target_id || 'cars' !== get_post_type( $source_id ) || 'cars' !== get_post_type( $target_id ) ) {
            return new WP_Error( 'rentacar_invalid_vehicle_sync', __( 'Vehicle field synchronization requires two vehicle posts.', 'rentacar-core' ) );
        }

        foreach ( $this->policy->field_names() as $field_name ) {
            $value = $this->policy->value( $source_id, $field_name );
            $field_key = 'gallery' === $field_name ? $this->policy->gallery_field_key() : $field_name;

            if ( function_exists( 'update_field' ) ) {
                update_field( $field_key, $value, $target_id );
            } else {
                update_post_meta( $target_id, $field_name, $value );
            }
        }

        $featured_image_id = (int) get_post_thumbnail_id( $source_id );
        if ( $featured_image_id ) {
            set_post_thumbnail( $target_id, $featured_image_id );
        } else {
            delete_post_thumbnail( $target_id );
        }

        return true;
    }
}
