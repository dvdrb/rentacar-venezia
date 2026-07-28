<?php
defined( 'ABSPATH' ) || exit;

final class Rentacar_Core_Reservation_Validator {
    public function validate( array $input ) {
        $errors = new WP_Error();
        $vehicle = get_post( absint( $input['vehicle_id'] ?? 0 ) );

        if ( ! $vehicle instanceof WP_Post || 'cars' !== $vehicle->post_type || 'publish' !== $vehicle->post_status ) {
            $errors->add( 'vehicle_id', __( 'Please select a valid vehicle.', 'rentacar-core' ) );
        }

        foreach ( array( 'pickup_date', 'pickup_time', 'return_date', 'return_time', 'pickup_location', 'return_location', 'full_name', 'email', 'insurance' ) as $required ) {
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

        $phone = ( new Rentacar_Core_Phone_Number_Service() )->normalize( $input['phone_country'] ?? '', $input['phone'] ?? '', $input['phone_calling_code'] ?? '' );
        if ( empty( $phone['valid'] ) ) {
            $errors->add( $phone['field'], Rentacar_Core_Phone_Number_Service::error_message( $phone['code'] ) );
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

        if ( ! Rentacar_Core_Rental_Policy::supports_reservation_time( $input['pickup_time'] ?? '' ) || ! Rentacar_Core_Rental_Policy::supports_reservation_time( $input['return_time'] ?? '' ) ) {
            $errors->add( 'times', __( 'Please choose pickup and return times in 15-minute intervals.', 'rentacar-core' ) );
        }

        $duration = ( new Rentacar_Core_Rental_Duration_Calculator() )->calculate( $input['pickup_date'] ?? '', $input['pickup_time'] ?? '', $input['return_date'] ?? '', $input['return_time'] ?? '' );
        if ( ! $duration ) {
            $errors->add( 'dates', __( 'Please enter a valid rental period.', 'rentacar-core' ) );
        } elseif ( $this->pickup_is_in_past( $input['pickup_date'], $input['pickup_time'] ) ) {
            $errors->add( 'pickup_date', __( 'Pickup cannot be in the past.', 'rentacar-core' ) );
        } elseif ( $duration < Rentacar_Core_Rental_Policy::minimum_rental_days() ) {
            $errors->add( 'dates', sprintf( __( 'The minimum rental period is %d billable days.', 'rentacar-core' ), Rentacar_Core_Rental_Policy::minimum_rental_days() ) );
        } elseif ( $duration > Rentacar_Core_Rental_Policy::maximum_rental_days() ) {
            $errors->add( 'dates', __( 'Please enter a valid rental period.', 'rentacar-core' ) );
        }

        return $errors->has_errors() ? $errors : true;
    }

    private function pickup_is_in_past( $date, $time ) {
        if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', (string) $date ) || ! preg_match( '/^\d{2}:\d{2}$/', (string) $time ) ) return false;
        return (string) $date . ' ' . (string) $time < wp_date( 'Y-m-d H:i' );
    }
}
