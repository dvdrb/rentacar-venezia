<?php
defined( 'ABSPATH' ) || exit;

/** Handles the public general-contact form without retaining customer data. */
final class Rentacar_Core_Contact_Controller {
    public static function handle() {
        $input = self::input();

        if ( ! isset( $input['rentacar_contact_nonce'] ) || ! wp_verify_nonce( $input['rentacar_contact_nonce'], 'rentacar_submit_contact' ) ) {
            self::redirect( 'error' );
        }

        if ( '' !== $input['website'] || ! $input['privacy'] || '' === $input['name'] || '' === $input['phone'] || ! is_email( $input['email'] ) || '' === $input['topic'] || '' === $input['message'] ) {
            self::redirect( 'error' );
        }

        if ( ! ( new Rentacar_Core_Reservation_Rate_Limiter() )->allows( $input['email'] ) ) {
            self::redirect( 'rate_limit' );
        }

        $recipient = apply_filters( 'rentacar_core_contact_recipient', apply_filters( 'rentacar_core_reservation_recipient', '' ) );
        if ( ! is_email( $recipient ) ) {
            self::redirect( 'unconfigured' );
        }

        $subject = sprintf( '[%s] General contact: %s', get_bloginfo( 'name' ), $input['topic'] );
        $message = implode( "\n", array(
            'Name: ' . $input['name'],
            'Phone: ' . $input['phone'],
            'Email: ' . $input['email'],
            'Topic: ' . $input['topic'],
            '',
            $input['message'],
        ) );
        $headers = array( 'Reply-To: ' . $input['name'] . ' <' . $input['email'] . '>' );

        self::redirect( wp_mail( $recipient, $subject, $message, $headers ) ? 'sent' : 'delivery' );
    }

    private static function input() {
        $raw = wp_unslash( $_POST );

        return array(
            'name'                  => sanitize_text_field( $raw['name'] ?? '' ),
            'phone'                 => sanitize_text_field( $raw['phone'] ?? '' ),
            'email'                 => sanitize_email( $raw['email'] ?? '' ),
            'topic'                 => sanitize_text_field( $raw['topic'] ?? '' ),
            'message'               => sanitize_textarea_field( $raw['message'] ?? '' ),
            'privacy'               => ! empty( $raw['privacy'] ),
            'website'               => sanitize_text_field( $raw['website'] ?? '' ),
            'rentacar_contact_nonce'=> sanitize_text_field( $raw['rentacar_contact_nonce'] ?? '' ),
        );
    }

    private static function redirect( $status ) {
        wp_safe_redirect( add_query_arg( 'contact_status', sanitize_key( $status ), self::return_url() ) );
        exit;
    }

    private static function return_url() {
        return remove_query_arg( 'contact_status', wp_get_referer() ?: home_url( '/' ) );
    }
}
