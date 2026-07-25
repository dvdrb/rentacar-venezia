<?php
defined( 'ABSPATH' ) || exit;

final class Rentacar_Core_Business_Notification {
    public function send( Rentacar_Core_Reservation_Request $request, $recipient ) {
        $subject = sprintf( '[%s] Reservation request %s', get_bloginfo( 'name' ), $request->get( 'reference' ) );
        $lines = array(
            'Reference: ' . $request->get( 'reference' ),
            'Vehicle: ' . $request->get( 'vehicle_title' ),
            'Pickup: ' . $request->get( 'pickup_location' ) . ' — ' . $request->get( 'pickup_date' ) . ' ' . $request->get( 'pickup_time' ),
            'Return: ' . $request->get( 'return_location' ) . ' — ' . $request->get( 'return_date' ) . ' ' . $request->get( 'return_time' ),
            'Name: ' . $request->get( 'full_name' ),
            'Phone: ' . $request->get( 'phone' ),
            'Email: ' . $request->get( 'email' ),
            'Similar vehicle accepted: ' . ( $request->get( 'similar_vehicle' ) ? 'Yes' : 'No' ),
            'Estimate: ' . $request->get( 'estimate_summary' ),
            'Language: ' . $request->get( 'language' ),
            'Submitted: ' . $request->get( 'submitted_at' ),
            'Message: ' . ( $request->get( 'message' ) ? $request->get( 'message' ) : '—' ),
        );

        return wp_mail( $recipient, $subject, implode( "\n", $lines ) );
    }
}
