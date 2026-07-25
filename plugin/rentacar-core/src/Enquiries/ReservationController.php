<?php
defined( 'ABSPATH' ) || exit;

/** Handles public reservation requests without persisting customer data. */
final class Rentacar_Core_Reservation_Controller {
    public static function handle() {
        $input = self::input();

        if ( ! isset( $input['rentacar_reservation_nonce'] ) || ! wp_verify_nonce( $input['rentacar_reservation_nonce'], 'rentacar_submit_reservation' ) ) {
            self::respond_error( new WP_Error( 'nonce', __( 'Your session has expired. Please try again.', 'rentacar-core' ) ) );
        }

        $validation = ( new Rentacar_Core_Reservation_Validator() )->validate( $input );
        if ( is_wp_error( $validation ) ) {
            self::respond_error( $validation );
        }

        if ( ! ( new Rentacar_Core_Reservation_Rate_Limiter() )->allows( $input['email'] ) ) {
            self::respond_error( new WP_Error( 'rate_limit', __( 'Please wait a moment before sending another request.', 'rentacar-core' ) ) );
        }

        $recipient = apply_filters( 'rentacar_core_reservation_recipient', '' );
        if ( ! is_email( $recipient ) ) {
            self::respond_error( new WP_Error( 'recipient', __( 'Reservation requests are not configured yet. Please contact us directly.', 'rentacar-core' ) ) );
        }

        $vehicle = ( new Rentacar_Core_Vehicle_Repository() )->find( $input['vehicle_id'] );
        $estimate = ( new Rentacar_Core_Estimate_Service() )->estimate( $input['vehicle_id'], $input['pickup_date'], $input['pickup_time'], $input['return_date'], $input['return_time'], $input['extras'], $input['insurance'] );
        $input['reference'] = Rentacar_Core_Reservation_Reference::generate();
        $input['vehicle_title'] = $vehicle ? $vehicle->get( 'title' ) : '';
        $input['estimate_summary'] = self::estimate_summary( $estimate );
        $input['extras'] = $estimate ? $estimate->get( 'extras', array() ) : array();
        $input['submitted_at'] = wp_date( 'c' );
        $request = new Rentacar_Core_Reservation_Request( $input );

        $business_sent = ( new Rentacar_Core_Business_Notification() )->send( $request, $recipient );
        $customer_sent = ( new Rentacar_Core_Customer_Acknowledgement() )->send( $request );
        if ( ! $business_sent || ! $customer_sent ) {
            self::respond_error( new WP_Error( 'delivery', __( 'We could not send the request. Please contact us directly.', 'rentacar-core' ) ) );
        }

        $response = array(
            'reference' => $request->get( 'reference' ),
            'message'   => __( 'We received your reservation request. Our team will check the selected vehicle and contact you to confirm availability, the final price and rental conditions.', 'rentacar-core' ),
            'whatsapp_url' => esc_url_raw( apply_filters( 'rentacar_core_whatsapp_url', '' ) ),
        );

        if ( self::wants_json( $input ) ) {
            wp_send_json_success( $response );
        }

        wp_safe_redirect( add_query_arg( array( 'reservation_sent' => '1', 'reservation_ref' => rawurlencode( $request->get( 'reference' ) ) ), self::return_url() ) );
        exit;
    }

    public static function admin_notice() {
        if ( ! current_user_can( 'manage_options' ) || is_email( apply_filters( 'rentacar_core_reservation_recipient', '' ) ) ) {
            return;
        }
        echo '<div class="notice notice-warning"><p>' . esc_html__( 'Rentacar Core: reservation email delivery is disabled until a recipient is configured.', 'rentacar-core' ) . '</p></div>';
    }

    private static function input() {
        $raw = wp_unslash( $_POST );
        return array(
            'vehicle_id' => absint( $raw['vehicle_id'] ?? 0 ), 'pickup_date' => sanitize_text_field( $raw['pickup_date'] ?? '' ), 'pickup_time' => sanitize_text_field( $raw['pickup_time'] ?? '' ),
            'return_date' => sanitize_text_field( $raw['return_date'] ?? '' ), 'return_time' => sanitize_text_field( $raw['return_time'] ?? '' ),
            'pickup_location' => sanitize_text_field( $raw['pickup_location'] ?? '' ), 'return_location' => sanitize_text_field( $raw['return_location'] ?? '' ),
            'full_name' => sanitize_text_field( trim( ( $raw['full_name'] ?? '' ) ?: trim( ( $raw['first_name'] ?? '' ) . ' ' . ( $raw['last_name'] ?? '' ) ) ) ), 'first_name' => sanitize_text_field( $raw['first_name'] ?? '' ), 'last_name' => sanitize_text_field( $raw['last_name'] ?? '' ), 'phone' => sanitize_text_field( $raw['phone'] ?? '' ), 'email' => sanitize_email( $raw['email'] ?? '' ),
            'similar_vehicle' => ! empty( $raw['similar_vehicle'] ), 'message' => sanitize_textarea_field( $raw['message'] ?? '' ), 'privacy' => ! empty( $raw['privacy'] ), 'terms' => ! empty( $raw['terms'] ), 'insurance' => sanitize_key( $raw['insurance'] ?? '' ), 'airline' => sanitize_text_field( $raw['airline'] ?? '' ), 'flight_number' => strtoupper( sanitize_text_field( $raw['flight_number'] ?? '' ) ),
            'website' => sanitize_text_field( $raw['website'] ?? '' ), 'started_at' => absint( $raw['started_at'] ?? 0 ),
            'extras' => self::extra_keys( $raw['extras'] ?? array() ),
            'rentacar_ajax' => ! empty( $raw['rentacar_ajax'] ), 'rentacar_reservation_nonce' => sanitize_text_field( $raw['rentacar_reservation_nonce'] ?? '' ),
            'language' => sanitize_key( function_exists( 'rentacar_venezia_v2_current_language' ) ? rentacar_venezia_v2_current_language() : get_locale() ),
        );
    }

    private static function estimate_summary( $estimate ) {
        if ( ! $estimate || ! $estimate->get( 'available' ) ) {
            return __( 'Price to be confirmed', 'rentacar-core' );
        }
        return sprintf( '€%1$s (%2$s days; selected extras included where priced; indicative)', number_format_i18n( $estimate->get( 'estimate_total' ), 2 ), absint( $estimate->get( 'days' ) ) );
    }

    private static function extra_keys( $extras ) {
        if ( ! is_array( $extras ) ) {
            return array();
        }

        $keys = array();
        foreach ( $extras as $extra ) {
            if ( is_scalar( $extra ) ) {
                $keys[] = sanitize_key( $extra );
            }
        }

        return array_values( array_unique( array_filter( $keys ) ) );
    }

    private static function wants_json( array $input ) { return ! empty( $input['rentacar_ajax'] ); }
    private static function return_url() { return remove_query_arg( array( 'reservation_sent', 'reservation_ref', 'reservation_error' ), wp_get_referer() ?: home_url( '/' ) ); }
    private static function respond_error( WP_Error $error ) {
        if ( self::wants_json( self::input() ) ) {
            wp_send_json_error( array( 'errors' => $error->errors ), 422 );
        }
        wp_safe_redirect( add_query_arg( 'reservation_error', '1', self::return_url() ) );
        exit;
    }
}
