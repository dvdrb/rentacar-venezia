<?php
defined( 'ABSPATH' ) || exit;

function rentacar_venezia_v2_vehicle_title( Rentacar_Core_Vehicle $vehicle ) {
    $title = preg_replace( '/\s+/', ' ', trim( (string) $vehicle->get( 'title' ) ) );

    return preg_replace( '/\s*\|\s*/', ' | ', $title );
}

function rentacar_venezia_v2_vehicle_specs( Rentacar_Core_Vehicle $vehicle ) {
    $specs = array_filter(
        array(
            $vehicle->get( 'transmission' ),
            $vehicle->get( 'passengers' ) ? sprintf( _n( '%s passenger', '%s passengers', $vehicle->get( 'passengers' ), 'rentacar-venezia-v2' ), number_format_i18n( $vehicle->get( 'passengers' ) ) ) : '',
            $vehicle->get( 'doors' ) ? sprintf( _n( '%s door', '%s doors', $vehicle->get( 'doors' ), 'rentacar-venezia-v2' ), number_format_i18n( $vehicle->get( 'doors' ) ) ) : '',
            $vehicle->get( 'air_conditioning' ) ? __( 'Air conditioning', 'rentacar-venezia-v2' ) : '',
        )
    );

    return array_values( $specs );
}

function rentacar_venezia_v2_vehicle_bands( Rentacar_Core_Vehicle $vehicle ) {
    $valid = array();
    foreach ( $vehicle->get( 'pricing_bands' )->all() as $band ) {
        if ( $band->from_days < 1 || null === $band->daily_price || $band->daily_price <= 0 || ( null !== $band->to_days && $band->to_days < $band->from_days ) ) {
            continue;
        }
        $valid[] = $band;
    }
    return $valid;
}

function rentacar_venezia_v2_price_range_label( Rentacar_Core_Pricing_Band $band ) {
    return null === $band->to_days
        ? sprintf( __( '%s+ days', 'rentacar-venezia-v2' ), number_format_i18n( $band->from_days ) )
        : sprintf( __( '%1$s–%2$s days', 'rentacar-venezia-v2' ), number_format_i18n( $band->from_days ), number_format_i18n( $band->to_days ) );
}

function rentacar_venezia_v2_price_label( Rentacar_Core_Pricing_Band $band ) {
    return sprintf( __( '€%s/day', 'rentacar-venezia-v2' ), number_format_i18n( $band->daily_price, 0 ) );
}

function rentacar_venezia_v2_vehicle_image_id( Rentacar_Core_Vehicle $vehicle ) {
    $gallery = $vehicle->get( 'vehicle_gallery' );
    $ids = $gallery ? $gallery->all_image_ids() : array();
    return $ids ? (int) $ids[0] : 0;
}

/**
 * Return the breadcrumb items for ordinary WordPress content without making
 * assumptions about page IDs, translated URLs or menu structure.
 */
function rentacar_venezia_v2_breadcrumb_items( $post_id = 0 ) {
    $post_id = $post_id ? absint( $post_id ) : get_queried_object_id();
    $items = array(
        array(
            'label' => __( 'Home', 'rentacar-venezia-v2' ),
            'url'   => home_url( '/' ),
        ),
    );

    if ( ! $post_id ) {
        return $items;
    }

    foreach ( array_reverse( get_post_ancestors( $post_id ) ) as $ancestor_id ) {
        $items[] = array(
            'label' => get_the_title( $ancestor_id ),
            'url'   => get_permalink( $ancestor_id ),
        );
    }

    $items[] = array(
        'label' => get_the_title( $post_id ),
        'url'   => '',
    );

    return $items;
}

/**
 * Build fleet controls from values used by published vehicles in the current
 * WordPress language context. Only the established vehicle meta keys are
 * allowed here; no unbounded arbitrary meta lookup is exposed to templates.
 */
function rentacar_venezia_v2_vehicle_filter_values( $meta_key ) {
    static $values = array();
    $allowed_keys = array( 'gearbox', 'max_passagers', 'doors' );

    if ( ! in_array( $meta_key, $allowed_keys, true ) ) {
        return array();
    }

    if ( isset( $values[ $meta_key ] ) ) {
        return $values[ $meta_key ];
    }

    $ids = get_posts(
        array(
            'post_type'              => 'cars',
            'post_status'            => 'publish',
            'posts_per_page'         => -1,
            'fields'                 => 'ids',
            'orderby'                => 'title',
            'order'                  => 'ASC',
            'no_found_rows'          => true,
            'ignore_sticky_posts'    => true,
            'update_post_term_cache' => false,
        )
    );
    $found = array();

    foreach ( $ids as $id ) {
        $value = get_post_meta( $id, $meta_key, true );
        if ( 'gearbox' === $meta_key ) {
            $value = sanitize_text_field( $value );
            if ( '' !== $value ) {
                $found[ $value ] = $value;
            }
            continue;
        }

        $value = absint( $value );
        if ( $value > 0 ) {
            $found[ $value ] = $value;
        }
    }

    if ( 'gearbox' === $meta_key ) {
        natcasesort( $found );
    } else {
        ksort( $found, SORT_NUMERIC );
    }

    $values[ $meta_key ] = array_values( $found );

    return $values[ $meta_key ];
}
