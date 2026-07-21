<?php
defined( 'ABSPATH' ) || exit;

/**
 * A deny-by-default registry for public marketing/policy claims.
 *
 * Claims remain disabled until an owner explicitly approves the wording and
 * any necessary condition. The theme can safely ask this service whether a
 * claim is allowed without embedding business policy in presentation code.
 */
final class Rentacar_Core_Marketing_Claim_Registry {
    const OPTION = 'rentacar_core_marketing_claims';

    public static function register_setting() {
        register_setting(
            'rentacar_core_claims',
            self::OPTION,
            array(
                'type'              => 'array',
                'sanitize_callback' => array( __CLASS__, 'sanitize' ),
                'default'           => array(),
            )
        );
    }

    public static function definitions() {
        return array(
            'local_assistance'                 => self::definition( 'Local assistance' ),
            'whatsapp_contact'                 => self::definition( 'WhatsApp contact' ),
            'multilingual_support'             => self::definition( 'Multilingual support' ),
            'availability_confirmed_personally' => self::definition( 'Availability confirmed personally' ),
            'indicative_price'                 => self::definition( 'Indicative price' ),
            'no_credit_card'                   => self::definition( 'No credit card required' ),
            'no_deposit'                       => self::definition( 'No deposit required' ),
            'free_cancellation'                => self::definition( 'Free cancellation' ),
            'no_hidden_fees'                   => self::definition( 'No hidden fees' ),
            'unlimited_mileage'                => self::definition( 'Unlimited mileage' ),
            'free_airport_delivery'            => self::definition( 'Free airport delivery' ),
            'insurance_included'               => self::definition( 'Insurance included' ),
            'pay_on_arrival'                   => self::definition( 'Pay on arrival' ),
            'best_price_guarantee'             => self::definition( 'Best price guarantee' ),
            'service_24_7'                     => self::definition( '24/7 service' ),
        );
    }

    public static function get( $key ) {
        $definitions = self::definitions();

        if ( ! isset( $definitions[ $key ] ) ) {
            return null;
        }

        $configured = get_option( self::OPTION, array() );
        $claim = isset( $configured[ $key ] ) && is_array( $configured[ $key ] ) ? $configured[ $key ] : array();

        return array_merge( $definitions[ $key ], $claim, array( 'enabled' => ! empty( $claim['enabled'] ) ) );
    }

    public static function enabled( $key ) {
        $claim = self::get( $key );

        return ! empty( $claim['enabled'] ) ? $claim : false;
    }

    public static function sanitize( $claims ) {
        $sanitized = array();

        foreach ( self::definitions() as $key => $definition ) {
            if ( empty( $claims[ $key ] ) || ! is_array( $claims[ $key ] ) ) {
                continue;
            }

            $sanitized[ $key ] = array(
                'enabled'   => ! empty( $claims[ $key ]['enabled'] ),
                'label'     => sanitize_text_field( $claims[ $key ]['label'] ?? $definition['label'] ),
                'condition' => sanitize_text_field( $claims[ $key ]['condition'] ?? '' ),
                'locations' => array_values( array_filter( array_map( 'sanitize_key', (array) ( $claims[ $key ]['locations'] ?? array() ) ) ) ),
                'link'      => esc_url_raw( $claims[ $key ]['link'] ?? '' ),
            );
        }

        return $sanitized;
    }

    private static function definition( $label ) {
        return array(
            'enabled'   => false,
            'label'     => $label,
            'condition' => '',
            'locations' => array(),
            'link'      => '',
        );
    }
}
