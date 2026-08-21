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
            // Deprecated compatibility key. The public policy is represented
            // by the three explicit statements below; generic no-deposit
            // language remains deliberately unavailable.
            'no_credit_card'                   => self::definition( 'No credit card required' ),
            'no_deposit'                       => self::definition( 'No deposit required' ),
            'no_credit_card_to_reserve'        => self::definition( 'No credit card required to make a reservation', true ),
            'no_advance_reservation_deposit'   => self::definition( 'No advance reservation payment or deposit required', true ),
            'security_deposit_at_pickup'       => self::definition( 'A security deposit is required at vehicle pickup', true ),
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
        $key = self::canonical_key( $key );
        $definitions = self::definitions();

        if ( ! isset( $definitions[ $key ] ) ) {
            return null;
        }

        if ( 'no_deposit' === $key ) return $definitions[ $key ];

        $configured = get_option( self::OPTION, array() );
        $claim = isset( $configured[ $key ] ) && is_array( $configured[ $key ] ) ? $configured[ $key ] : array();
        // Retain an explicit legacy approval while existing installations
        // migrate to the more precise policy statements.
        if ( ! $claim && 'no_credit_card_to_reserve' === $key && isset( $configured['no_credit_card'] ) && is_array( $configured['no_credit_card'] ) ) $claim = $configured['no_credit_card'];

        return array_merge( $definitions[ $key ], $claim, array( 'enabled' => array_key_exists( 'enabled', $claim ) ? ! empty( $claim['enabled'] ) : ! empty( $definitions[ $key ]['enabled'] ) ) );
    }

    public static function enabled( $key ) {
        $claim = self::get( $key );

        return ! empty( $claim['enabled'] ) ? $claim : false;
    }

    public static function sanitize( $claims ) {
        $sanitized = array();

        if ( isset( $claims['no_credit_card'] ) && ! isset( $claims['no_credit_card_to_reserve'] ) ) $claims['no_credit_card_to_reserve'] = $claims['no_credit_card'];

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

    public static function copy( $key, $language ) {
        $key = self::canonical_key( $key );
        $language = in_array( $language, array( 'it', 'en', 'ro', 'ru' ), true ) ? $language : 'en';
        $copy = array(
            'no_credit_card_to_reserve' => array(
                'it' => 'Prenota senza carta di credito', 'en' => 'Reserve without a credit card', 'ro' => 'Rezervați fără card de credit', 'ru' => 'Бронируйте без кредитной карты',
            ),
            'no_advance_reservation_deposit' => array(
                'it' => 'Nessun deposito anticipato per la prenotazione', 'en' => 'No advance reservation deposit', 'ro' => 'Fără avans la rezervare', 'ru' => 'Без предоплаты при бронировании',
            ),
            'security_deposit_at_pickup' => array(
                'it' => 'Il deposito cauzionale viene richiesto al momento del ritiro', 'en' => 'A security deposit is required at pickup', 'ro' => 'La preluarea mașinii este necesar un depozit de garanție', 'ru' => 'При получении автомобиля требуется залог',
            ),
        );
        $summaries = array(
            'it' => 'Prenota senza carta di credito e senza deposito anticipato. Il deposito cauzionale viene richiesto al momento del ritiro.',
            'en' => 'Reserve without a credit card or advance reservation deposit. A security deposit is required at pickup.',
            'ro' => 'Rezervați fără card de credit și fără avans la rezervare. La preluarea mașinii este necesar un depozit de garanție.',
            'ru' => 'Бронируйте без кредитной карты и без предоплаты при бронировании. При получении автомобиля требуется залог.',
        );
        return array( 'label' => $copy[ $key ][ $language ] ?? '', 'summary' => $summaries[ $language ] );
    }

    public static function policy_summary( $language ) {
        $copy = self::copy( 'no_credit_card_to_reserve', $language );

        return $copy['summary'];
    }

    private static function canonical_key( $key ) { return 'no_credit_card' === $key ? 'no_credit_card_to_reserve' : $key; }

    private static function definition( $label, $enabled = false ) {
        return array(
            'enabled'   => $enabled,
            'label'     => $label,
            'condition' => '',
            'locations' => array(),
            'link'      => '',
        );
    }
}
