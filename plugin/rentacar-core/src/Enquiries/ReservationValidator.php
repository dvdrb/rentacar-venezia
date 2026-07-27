<?php
defined( 'ABSPATH' ) || exit;

final class Rentacar_Core_Reservation_Validator {
    public function validate( array $input ) {
        $errors = new WP_Error();
        $vehicle = get_post( absint( $input['vehicle_id'] ?? 0 ) );

        if ( ! $vehicle instanceof WP_Post || 'cars' !== $vehicle->post_type || 'publish' !== $vehicle->post_status ) {
            $errors->add( 'vehicle_id', __( 'Please select a valid vehicle.', 'rentacar-core' ) );
        }

        foreach ( array( 'pickup_date', 'pickup_time', 'return_date', 'return_time', 'pickup_location', 'return_location', 'full_name', 'phone', 'email', 'insurance' ) as $required ) {
            if ( empty( $input[ $required ] ) ) {
                $errors->add( $required, __( 'Please complete this field.', 'rentacar-core' ) );
            }
        }

        $locations = apply_filters( 'rentacar_core_reservation_locations', array() );
        if ( is_array( $locations ) && $locations ) {
            foreach ( array( 'pickup_location', 'return_location' ) as $location_key ) {
                if ( ! in_array( $input[ $location_key ] ?? '', $locations, true ) ) {
                    $errors->add( $location_key, __( 'Please select a configured location.', 'rentacar-core' ) );
                }
            }
        }

        if ( ! empty( $input['email'] ) && ! is_email( $input['email'] ) ) {
            $errors->add( 'email', __( 'Please enter a valid email address.', 'rentacar-core' ) );
        }

        $phone = preg_replace( '/[^0-9+]/', '', (string) ( $input['phone'] ?? '' ) );
        if ( strlen( preg_replace( '/\D/', '', $phone ) ) < 6 ) {
            $errors->add( 'phone', __( 'Please enter a valid phone number.', 'rentacar-core' ) );
        }

        if ( empty( $input['terms'] ) ) $errors->add( 'terms', __( 'Please accept the terms and conditions.', 'rentacar-core' ) );
        if ( ! Rentacar_Core_Rental_Policy::insurance( $input['insurance'] ?? '' ) ) $errors->add( 'insurance', __( 'Please select a configured insurance package.', 'rentacar-core' ) );
        foreach ( Rentacar_Core_Reservation_Extras::validate_selection( (array) ( $input['extras'] ?? array() ) ) as $message ) {
            $errors->add( 'extras', $message );
        }

        if ( ! empty( $input['website'] ) ) {
            $errors->add( 'website', __( 'Unable to send this request.', 'rentacar-core' ) );
        }

        $started_at = absint( $input['started_at'] ?? 0 );
        if ( $started_at && ( time() - $started_at < 3 || time() - $started_at > DAY_IN_SECONDS ) ) {
            $errors->add( 'started_at', __( 'Please review the form and try again.', 'rentacar-core' ) );
        }

        if ( strlen( (string) ( $input['message'] ?? '' ) ) > 2000 || strlen( (string) ( $input['pickup_location'] ?? '' ) ) > 120 || strlen( (string) ( $input['return_location'] ?? '' ) ) > 120 ) {
            $errors->add( 'size', __( 'One or more values are too long.', 'rentacar-core' ) );
        }

        $duration = ( new Rentacar_Core_Rental_Duration_Calculator() )->calculate( $input['pickup_date'] ?? '', $input['pickup_time'] ?? '', $input['return_date'] ?? '', $input['return_time'] ?? '' );
        if ( ! $duration || $duration > 60 ) {
            $errors->add( 'dates', __( 'Please enter a valid rental period.', 'rentacar-core' ) );
        }

        return $errors->has_errors() ? $errors : true;
    }
}
