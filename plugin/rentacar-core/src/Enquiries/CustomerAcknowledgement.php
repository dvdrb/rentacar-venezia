<?php
defined( 'ABSPATH' ) || exit;

final class Rentacar_Core_Customer_Acknowledgement {
    public function send( Rentacar_Core_Reservation_Request $request ) {
        $subject = sprintf( __( 'We received your reservation request %s', 'rentacar-core' ), $request->get( 'reference' ) );
        $extras = Rentacar_Core_Reservation_Extras::customer_labels( (array) $request->get( 'extras', array() ) );
        $message = sprintf( "We received your reservation request. Our team will check the selected vehicle and contact you to confirm availability, the final price and rental conditions. This is not a confirmed reservation.\n\nReference: %s\nVehicle: %s\nPickup: %s — %s %s\nReturn: %s — %s %s\nSelected extras: %s\nIndicative estimate: %s", $request->get( 'reference' ), $request->get( 'vehicle_title' ), $request->get( 'pickup_location' ), $request->get( 'pickup_date' ), $request->get( 'pickup_time' ), $request->get( 'return_location' ), $request->get( 'return_date' ), $request->get( 'return_time' ), $extras ? implode( ', ', $extras ) : __( 'None', 'rentacar-core' ), $request->get( 'estimate_summary' ) );

        return wp_mail( $request->get( 'email' ), $subject, $message );
    }
}
