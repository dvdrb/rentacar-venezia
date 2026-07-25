<?php
defined( 'ABSPATH' ) || exit;

/** Authoritative, filterable reservation policy. Prices are stored in cents. */
final class Rentacar_Core_Rental_Policy {
    const OPTION = 'rentacar_core_rental_policy';

    public static function defaults() {
        return array(
            'insurance' => array(
                'base' => array( 'enabled' => true, 'label' => 'Base insurance', 'daily_cents' => 0 ),
                'damage_750' => array( 'enabled' => true, 'label' => 'Damage protection up to €750', 'daily_cents' => 2000 ),
                'damage_1500' => array( 'enabled' => true, 'label' => 'Damage protection up to €1,500', 'daily_cents' => 3000 ),
                'full_casco' => array( 'enabled' => true, 'label' => 'Full Casco', 'daily_cents' => 4500 ),
            ),
            'after_hours' => array( 'early_cents' => 2500, 'evening_cents' => 2500, 'night_cents' => 5000 ),
            'deposits' => array( 'up_to_five_cents' => 35000, 'seven_to_nine_cents' => 50000 ),
            'mileage' => array( 'daily_km' => 150, 'excess_cents' => 10 ),
        );
    }

    public static function get() { return wp_parse_args( get_option( self::OPTION, array() ), self::defaults() ); }
    public static function insurance( $key ) { $all = self::get()['insurance']; return isset( $all[ $key ] ) && ! empty( $all[ $key ]['enabled'] ) ? $all[ $key ] : null; }
    public static function after_hours_cents( $time ) {
        $time = preg_replace( '/[^0-9:]/', '', (string) $time );
        if ( ! preg_match( '/^(?:[01][0-9]|2[0-3]):[0-5][0-9]$/', $time ) ) return 0;
        $minutes = (int) substr( $time, 0, 2 ) * 60 + (int) substr( $time, 3, 2 ); $fees = self::get()['after_hours'];
        if ( $minutes >= 1350 || $minutes < 330 ) return (int) $fees['night_cents'];
        if ( $minutes < 450 ) return (int) $fees['early_cents'];
        if ( $minutes >= 1170 ) return (int) $fees['evening_cents'];
        return 0;
    }
    public static function deposit_cents( $passengers ) { $deposits = self::get()['deposits']; return (int) ( (int) $passengers >= 7 ? $deposits['seven_to_nine_cents'] : $deposits['up_to_five_cents'] ); }
}
