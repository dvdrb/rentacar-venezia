<?php
defined( 'ABSPATH' ) || exit;

/** Authoritative, filterable reservation policy. Prices are stored in cents. */
final class Rentacar_Core_Rental_Policy {
    const OPTION = 'rentacar_core_rental_policy';
    const INTER_AIRPORT_SURCHARGE_CENTS = 2500;

    public static function defaults() {
        return array(
            'insurance' => array(
                'base' => array( 'enabled' => true, 'label' => 'Base insurance', 'daily_cents' => 0 ),
                'damage_750' => array( 'enabled' => true, 'label' => 'Damage protection up to €750', 'daily_cents' => 2000 ),
                'damage_1500' => array( 'enabled' => true, 'label' => 'Damage protection up to €1,500', 'daily_cents' => 3000 ),
                'full_casco' => array( 'enabled' => true, 'label' => 'Full Casco', 'daily_cents' => 4500 ),
            ),
            'after_hours' => array(
                'early_start'   => 330,
                'normal_start'  => 450,
                'evening_start' => 1170,
                'night_start'   => 1350,
                'early_cents'   => 2500,
                'evening_cents' => 2500,
                'night_cents'   => 5000,
            ),
            'deposits' => array( 'up_to_five_cents' => 35000, 'seven_to_nine_cents' => 50000 ),
            'mileage' => array( 'daily_km' => 150, 'excess_cents' => 10 ),
            'inter_airport_surcharge_cents' => self::INTER_AIRPORT_SURCHARGE_CENTS,
        );
    }

    public static function get() {
        $defaults = self::defaults();
        $saved = get_option( self::OPTION, array() );
        $saved = is_array( $saved ) ? $saved : array();

        foreach ( array( 'insurance', 'after_hours', 'deposits', 'mileage' ) as $section ) {
            $saved[ $section ] = isset( $saved[ $section ] ) && is_array( $saved[ $section ] )
                ? wp_parse_args( $saved[ $section ], $defaults[ $section ] )
                : $defaults[ $section ];
        }

        return wp_parse_args( $saved, $defaults );
    }

    public static function sanitize( $input ) {
        $defaults = self::defaults();
        $input = is_array( $input ) ? $input : array();
        $policy = self::get();

        foreach ( $defaults['insurance'] as $key => $default ) {
            $submitted = isset( $input['insurance'][ $key ] ) && is_array( $input['insurance'][ $key ] ) ? $input['insurance'][ $key ] : array();
            $policy['insurance'][ $key ] = array(
                'enabled'     => ! empty( $submitted['enabled'] ),
                'label'       => $default['label'],
                'daily_cents' => self::price_to_cents( $submitted['daily_price'] ?? ( $default['daily_cents'] / 100 ) ),
            );
        }

        $submitted_hours = isset( $input['after_hours'] ) && is_array( $input['after_hours'] ) ? $input['after_hours'] : array();
        $boundaries = array(
            'early_start'   => self::time_to_minutes( $submitted_hours['early_start'] ?? '' ),
            'normal_start'  => self::time_to_minutes( $submitted_hours['normal_start'] ?? '' ),
            'evening_start' => self::time_to_minutes( $submitted_hours['evening_start'] ?? '' ),
            'night_start'   => self::time_to_minutes( $submitted_hours['night_start'] ?? '' ),
        );
        if ( false === $boundaries['early_start'] || false === $boundaries['normal_start'] || false === $boundaries['evening_start'] || false === $boundaries['night_start'] || ! ( $boundaries['early_start'] < $boundaries['normal_start'] && $boundaries['normal_start'] < $boundaries['evening_start'] && $boundaries['evening_start'] < $boundaries['night_start'] ) ) {
            add_settings_error( self::OPTION, 'after_hours_boundaries', __( 'After-hours time boundaries must be in chronological order.', 'rentacar-core' ) );
            $boundaries = array_intersect_key( $policy['after_hours'], $boundaries );
        }
        $policy['after_hours'] = array_merge( $boundaries, array(
            'early_cents'   => self::price_to_cents( $submitted_hours['early_price'] ?? ( $defaults['after_hours']['early_cents'] / 100 ) ),
            'evening_cents' => self::price_to_cents( $submitted_hours['evening_price'] ?? ( $defaults['after_hours']['evening_cents'] / 100 ) ),
            'night_cents'   => self::price_to_cents( $submitted_hours['night_price'] ?? ( $defaults['after_hours']['night_cents'] / 100 ) ),
        ) );

        $deposits = isset( $input['deposits'] ) && is_array( $input['deposits'] ) ? $input['deposits'] : array();
        $policy['deposits'] = array(
            'up_to_five_cents'    => self::price_to_cents( $deposits['up_to_five'] ?? ( $defaults['deposits']['up_to_five_cents'] / 100 ) ),
            'seven_to_nine_cents' => self::price_to_cents( $deposits['seven_to_nine'] ?? ( $defaults['deposits']['seven_to_nine_cents'] / 100 ) ),
        );
        $mileage = isset( $input['mileage'] ) && is_array( $input['mileage'] ) ? $input['mileage'] : array();
        $policy['mileage'] = array(
            'daily_km'    => max( 0, absint( $mileage['daily_km'] ?? $defaults['mileage']['daily_km'] ) ),
            'excess_cents' => self::price_to_cents( $mileage['excess_price'] ?? ( $defaults['mileage']['excess_cents'] / 100 ) ),
        );
        $policy['inter_airport_surcharge_cents'] = self::price_to_cents( $input['inter_airport_surcharge'] ?? ( self::INTER_AIRPORT_SURCHARGE_CENTS / 100 ) );

        return $policy;
    }

    public static function minutes_to_time( $minutes ) {
        $minutes = max( 0, min( 1439, absint( $minutes ) ) );
        return sprintf( '%02d:%02d', (int) floor( $minutes / 60 ), $minutes % 60 );
    }

    private static function time_to_minutes( $time ) {
        $time = preg_replace( '/[^0-9:]/', '', (string) $time );
        if ( ! preg_match( '/^(?:[01][0-9]|2[0-3]):[0-5][0-9]$/', $time ) ) {
            return false;
        }
        return (int) substr( $time, 0, 2 ) * 60 + (int) substr( $time, 3, 2 );
    }

    private static function price_to_cents( $price ) {
        return max( 0, (int) round( (float) str_replace( ',', '.', (string) $price ) * 100 ) );
    }
    public static function insurance( $key ) { $all = self::get()['insurance']; return isset( $all[ $key ] ) && ! empty( $all[ $key ]['enabled'] ) ? $all[ $key ] : null; }
    public static function after_hours_cents( $time ) {
        $time = preg_replace( '/[^0-9:]/', '', (string) $time );
        if ( ! preg_match( '/^(?:[01][0-9]|2[0-3]):[0-5][0-9]$/', $time ) ) return 0;
        $minutes = (int) substr( $time, 0, 2 ) * 60 + (int) substr( $time, 3, 2 ); $fees = self::get()['after_hours'];
        if ( $minutes >= (int) $fees['night_start'] || $minutes < (int) $fees['early_start'] ) return (int) $fees['night_cents'];
        if ( $minutes < (int) $fees['normal_start'] ) return (int) $fees['early_cents'];
        if ( $minutes >= (int) $fees['evening_start'] ) return (int) $fees['evening_cents'];
        return 0;
    }
    public static function inter_airport_surcharge_cents( $pickup_location, $return_location ) {
        $airports = apply_filters( 'rentacar_core_airport_locations', array( 'Airport Venice Marco Polo', 'Treviso Airport Arrivals' ) );
        if ( ! is_array( $airports ) || $pickup_location === $return_location || ! in_array( $pickup_location, $airports, true ) || ! in_array( $return_location, $airports, true ) ) {
            return 0;
        }

        return max( 0, (int) apply_filters( 'rentacar_core_inter_airport_surcharge_cents', self::get()['inter_airport_surcharge_cents'], $pickup_location, $return_location ) );
    }
    public static function deposit_cents( $passengers ) { $deposits = self::get()['deposits']; return (int) ( (int) $passengers >= 7 ? $deposits['seven_to_nine_cents'] : $deposits['up_to_five_cents'] ); }
}
