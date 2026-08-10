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
            if ( $value === $this->policy->value( $target_id, $field_name ) ) {
                continue;
            }
            $field_key = 'gallery' === $field_name ? $this->policy->gallery_field_key() : $field_name;

            if ( function_exists( 'update_field' ) ) {
                update_field( $field_key, $value, $target_id );
            } else {
                update_post_meta( $target_id, $field_name, $value );
            }
        }

        $featured_image_id = (int) get_post_thumbnail_id( $source_id );
        if ( $featured_image_id === (int) get_post_thumbnail_id( $target_id ) ) {
            return true;
        }

        if ( $featured_image_id ) {
            set_post_thumbnail( $target_id, $featured_image_id );
        } else {
            delete_post_thumbnail( $target_id );
        }

        return true;
    }

    /** Synchronizes controlled fields from the Polylang default-language vehicle. */
    public static function synchronize_from_default_translation( $post_id ) {
        $post_id = absint( $post_id );
        if ( ! $post_id || wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) || ! function_exists( 'pll_get_post_translations' ) ) {
            return;
        }

        self::synchronize_translation_group( (array) pll_get_post_translations( $post_id ) );
    }

    /** Synchronizes every published translation group for controlled vehicle fields. */
    public static function synchronize_all_from_default_language() {
        if ( ! function_exists( 'pll_get_post_translations' ) ) {
            return array( 'groups' => 0, 'translations' => 0 );
        }

        $groups = 0;
        $translations = 0;
        $sources = array();

        foreach ( get_posts( array( 'post_type' => 'cars', 'post_status' => 'publish', 'posts_per_page' => -1, 'fields' => 'ids', 'suppress_filters' => true ) ) as $post_id ) {
            $group = (array) pll_get_post_translations( $post_id );
            $source_id = self::default_language_vehicle_id( $group );
            if ( ! $source_id || isset( $sources[ $source_id ] ) ) {
                continue;
            }

            $sources[ $source_id ] = true;
            $groups++;
            $translations += self::synchronize_translation_group( $group );
        }

        return array( 'groups' => $groups, 'translations' => $translations );
    }

    private static function synchronize_translation_group( array $translations ) {
        $source_id = self::default_language_vehicle_id( $translations );
        if ( ! $source_id ) {
            return 0;
        }

        $synchronizer = new self();
        $count = 0;
        foreach ( $translations as $target_id ) {
            $target_id = absint( $target_id );
            if ( $target_id && $source_id !== $target_id && true === $synchronizer->synchronize( $source_id, $target_id ) ) {
                $count++;
            }
        }

        return $count;
    }

    private static function default_language_vehicle_id( array $translations ) {
        $default_language = function_exists( 'pll_default_language' ) ? pll_default_language( 'slug' ) : 'it';

        return isset( $translations[ $default_language ] ) ? absint( $translations[ $default_language ] ) : 0;
    }
}
