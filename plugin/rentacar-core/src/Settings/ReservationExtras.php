<?php
defined( 'ABSPATH' ) || exit;

/**
 * Authoritative optional-extra configuration and pricing. Browser-submitted
 * keys are only a selection; labels, prices and subtotals always come here.
 */
final class Rentacar_Core_Reservation_Extras {
    const OPTION = 'rentacar_core_reservation_extras';

    public static function definitions() {
        return array(
            'child_seat' => array(
                'key'          => 'child_seat',
                'enabled'      => true,
                'label'        => __( 'Child seat', 'rentacar-core' ),
                'pricing_type' => 'per_day',
                'price'        => 5.0,
                'max_quantity' => 1,
            ),
            'additional_driver' => array(
                'key'          => 'additional_driver',
                'enabled'      => true,
                'label'        => __( 'Additional driver', 'rentacar-core' ),
                'pricing_type' => 'per_day',
                'price'        => 5.0,
                'max_quantity' => 1,
            ),
            'authorization_abroad' => array(
                'key'          => 'authorization_abroad',
                'enabled'      => true,
                'label'        => __( 'Authorization for abroad', 'rentacar-core' ),
                'pricing_type' => 'fixed',
                'price'        => 80.0,
                'max_quantity' => 1,
            ),
        );
    }

    public static function all() {
        $configured = get_option( self::OPTION, array() );
        $extras = array();

        foreach ( self::definitions() as $key => $definition ) {
            $saved = isset( $configured[ $key ] ) && is_array( $configured[ $key ] ) ? $configured[ $key ] : array();
            $extras[ $key ] = array_merge( $definition, $saved, array(
                'key'          => $key,
                'label'        => $definition['label'],
                'enabled'      => ! empty( $saved['enabled'] ) || ( ! array_key_exists( $key, $configured ) && $definition['enabled'] ),
                'pricing_type' => self::pricing_type( $saved['pricing_type'] ?? $definition['pricing_type'] ),
                'price'        => self::price( $saved['price'] ?? $definition['price'] ),
                'max_quantity' => max( 1, absint( $saved['max_quantity'] ?? $definition['max_quantity'] ) ),
            ) );
        }

        return apply_filters( 'rentacar_core_reservation_extras', $extras );
    }

    public static function enabled() {
        return array_filter( self::all(), function( $extra ) { return ! empty( $extra['enabled'] ); } );
    }

    public static function sanitize( $extras ) {
        $sanitized = array();

        foreach ( self::definitions() as $key => $definition ) {
            $submitted = isset( $extras[ $key ] ) && is_array( $extras[ $key ] ) ? $extras[ $key ] : array();
            $sanitized[ $key ] = array(
                'enabled'      => ! empty( $submitted['enabled'] ),
                'pricing_type' => self::pricing_type( $submitted['pricing_type'] ?? $definition['pricing_type'] ),
                'price'        => self::price( $submitted['price'] ?? $definition['price'] ),
                'max_quantity' => $definition['max_quantity'],
            );
        }

        return $sanitized;
    }

    public static function validate_selection( array $keys ) {
        $errors = array();
        $configured = self::all();

        foreach ( array_unique( $keys ) as $key ) {
            if ( ! isset( $configured[ $key ] ) ) {
                $errors[] = __( 'Please choose a valid optional extra.', 'rentacar-core' );
            } elseif ( empty( $configured[ $key ]['enabled'] ) ) {
                $errors[] = __( 'This optional extra is not currently available.', 'rentacar-core' );
            }
        }

        return array_values( array_unique( $errors ) );
    }

    public static function calculate( array $keys, $days ) {
        $configured = self::enabled();
        $items = array();
        $total = 0.0;

        foreach ( array_unique( $keys ) as $key ) {
            if ( ! isset( $configured[ $key ] ) ) {
                continue;
            }

            $extra = $configured[ $key ];
            $subtotal = null;
            if ( 'per_day' === $extra['pricing_type'] ) {
                $subtotal = round( $extra['price'] * max( 1, absint( $days ) ), 2 );
            } elseif ( 'fixed' === $extra['pricing_type'] ) {
                $subtotal = $extra['price'];
            }

            if ( null !== $subtotal ) {
                $total += $subtotal;
            }

            $items[] = array(
                'key'          => $extra['key'],
                'label'        => $extra['label'],
                'pricing_type' => $extra['pricing_type'],
                'unit_price'   => $extra['price'],
                'subtotal'     => $subtotal,
            );
        }

        return array(
            'items' => $items,
            'total' => round( $total, 2 ),
        );
    }

    public static function pricing_type( $type ) {
        return in_array( $type, array( 'per_day', 'fixed', 'request_only' ), true ) ? $type : 'request_only';
    }

    public static function notification_lines( array $items ) {
        if ( ! $items ) {
            return array( __( 'Extras: none', 'rentacar-core' ) );
        }

        $lines = array( __( 'Extras:', 'rentacar-core' ) );
        foreach ( $items as $item ) {
            $line = sprintf(
                '%1$s — %2$s; €%3$s',
                $item['label'],
                $item['pricing_type'],
                number_format_i18n( (float) $item['unit_price'], 2 )
            );
            $lines[] = null === $item['subtotal']
                ? $line . '; ' . __( 'Price to be confirmed', 'rentacar-core' )
                : $line . '; ' . sprintf( __( 'Subtotal: €%s', 'rentacar-core' ), number_format_i18n( (float) $item['subtotal'], 2 ) );
        }

        return $lines;
    }

    public static function customer_labels( array $items ) {
        return array_values( array_filter( array_map( function( $item ) { return isset( $item['label'] ) ? $item['label'] : ''; }, $items ) ) );
    }

    private static function price( $price ) {
        return round( max( 0, (float) $price ), 2 );
    }
}
