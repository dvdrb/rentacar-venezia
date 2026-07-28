<?php
defined( 'ABSPATH' ) || exit;

/** Handles the public general-contact form without retaining customer data. */
final class Rentacar_Core_Contact_Controller {
    public static function handle() {
        $input = self::input();

        if ( ! isset( $input['rentacar_contact_nonce'] ) || ! wp_verify_nonce( $input['rentacar_contact_nonce'], 'rentacar_submit_contact' ) ) {
            self::redirect( 'error' );
        }

        $phone = ( new Rentacar_Core_Phone_Number_Service() )->normalize( $input['phone_country'], $input['phone'], $input['phone_calling_code'] );
        if ( '' !== $input['website'] || ! $input['privacy'] || '' === $input['name'] || empty( $phone['valid'] ) || ! is_email( $input['email'] ) || '' === $input['topic'] || '' === $input['message'] ) {
            self::redirect( empty( $phone['valid'] ) ? 'invalid_phone' : 'error', empty( $phone['valid'] ) ? $phone['code'] : '' );
        }
        $input = array_merge( $input, $phone );

        if ( ! ( new Rentacar_Core_Reservation_Rate_Limiter() )->allows( $input['email'] ) ) {
            self::redirect( 'rate_limit' );
        }

        $recipient = apply_filters( 'rentacar_core_contact_recipient', apply_filters( 'rentacar_core_reservation_recipient', '' ) );
        if ( ! is_email( $recipient ) ) {
            self::redirect( 'unconfigured' );
        }

        $subject = sprintf( '[%s] General contact: %s', get_bloginfo( 'name' ), $input['topic'] );
        $message = self::email_message( $input );
        $headers = array( 'Reply-To: ' . $input['name'] . ' <' . $input['email'] . '>' );

        self::redirect( wp_mail( $recipient, $subject, $message, $headers ) ? 'sent' : 'delivery' );
    }

    /** Shared by the controller and focused tests; never receives raw phone input. */
    public static function email_message( array $input ) {
        return implode( "\n", array(
            'Name: ' . $input['name'],
            'Phone: ' . $input['phone_display'] . ' (' . $input['phone_e164'] . ')',
            'Email: ' . $input['email'],
            'Topic: ' . $input['topic'],
            '',
            $input['message'],
        ) );
    }

    private static function input() {
        $raw = wp_unslash( $_POST );

        return array(
            'name'                  => sanitize_text_field( $raw['name'] ?? '' ),
            'phone'                 => sanitize_text_field( $raw['phone'] ?? '' ),
            'phone_country'         => sanitize_key( $raw['phone_country'] ?? '' ),
            'phone_calling_code'    => sanitize_text_field( $raw['phone_calling_code'] ?? '' ),
            'email'                 => sanitize_email( $raw['email'] ?? '' ),
            'topic'                 => sanitize_text_field( $raw['topic'] ?? '' ),
            'message'               => sanitize_textarea_field( $raw['message'] ?? '' ),
            'privacy'               => ! empty( $raw['privacy'] ),
            'website'               => sanitize_text_field( $raw['website'] ?? '' ),
            'rentacar_contact_nonce'=> sanitize_text_field( $raw['rentacar_contact_nonce'] ?? '' ),
        );
    }

    private static function redirect( $status, $phone_error = '' ) {
        $args = array( 'contact_status' => sanitize_key( $status ) );
        if ( $phone_error ) {
            $args['contact_phone_error'] = sanitize_key( $phone_error );
        }
        wp_safe_redirect( add_query_arg( $args, self::return_url() ) );
        exit;
    }

    private static function return_url() {
        return remove_query_arg( 'contact_status', wp_get_referer() ?: home_url( '/' ) );
    }
}
