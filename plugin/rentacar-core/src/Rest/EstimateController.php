<?php
defined( 'ABSPATH' ) || exit;

final class Rentacar_Core_Estimate_Controller {
    public static function register_routes() {
        register_rest_route(
            'rentacar/v1',
            '/estimate',
            array(
                'methods'             => WP_REST_Server::CREATABLE,
                'callback'            => array( __CLASS__, 'handle' ),
                'permission_callback' => '__return_true',
                'args'                => array(
                    'vehicle_id'  => array( 'required' => true, 'sanitize_callback' => 'absint' ),
                    'pickup_date' => array( 'required' => true, 'sanitize_callback' => 'sanitize_text_field' ),
                    'pickup_time' => array( 'required' => true, 'sanitize_callback' => 'sanitize_text_field' ),
                    'return_date' => array( 'required' => true, 'sanitize_callback' => 'sanitize_text_field' ),
                    'return_time' => array( 'required' => true, 'sanitize_callback' => 'sanitize_text_field' ),
                    'pickup_location' => array( 'sanitize_callback' => 'sanitize_text_field' ),
                    'return_location' => array( 'sanitize_callback' => 'sanitize_text_field' ),
                    'insurance'   => array( 'sanitize_callback' => 'sanitize_key' ),
                    'extras'      => array( 'sanitize_callback' => array( __CLASS__, 'extra_keys' ) ),
                ),
            )
        );
    }

    public static function handle( WP_REST_Request $request ) {
        $rate_key = 'estimate_' . md5( wp_json_encode( $request->get_params() ) );

        if ( ! wp_cache_add( $rate_key, 1, 'rentacar_core', 2 ) ) {
            return new WP_Error( 'rentacar_estimate_rate_limited', __( 'Please wait a moment before requesting another estimate.', 'rentacar-core' ), array( 'status' => 429 ) );
        }

        if ( ! Rentacar_Core_Rental_Policy::supports_reservation_time( $request->get_param( 'pickup_time' ) ) || ! Rentacar_Core_Rental_Policy::supports_reservation_time( $request->get_param( 'return_time' ) ) ) {
            return new WP_Error( 'rentacar_invalid_time', __( 'Please choose pickup and return times in 15-minute intervals.', 'rentacar-core' ), array( 'status' => 400 ) );
        }

        $estimate = ( new Rentacar_Core_Estimate_Service() )->estimate(
            $request->get_param( 'vehicle_id' ),
            $request->get_param( 'pickup_date' ),
            $request->get_param( 'pickup_time' ),
            $request->get_param( 'return_date' ),
            $request->get_param( 'return_time' ),
            (array) $request->get_param( 'extras' ),
            $request->get_param( 'insurance' ) ?: 'base',
            $request->get_param( 'pickup_location' ),
            $request->get_param( 'return_location' )
        );

        if ( ! $estimate ) {
            return new WP_Error( 'rentacar_invalid_trip', __( 'Please enter a valid pickup and return date and time.', 'rentacar-core' ), array( 'status' => 400 ) );
        }

        return new WP_REST_Response( $estimate->to_array(), 200 );
    }

    public static function extra_keys( $extras ) { return is_array( $extras ) ? array_values( array_filter( array_map( 'sanitize_key', $extras ) ) ) : array(); }
}
