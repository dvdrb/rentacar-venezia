<?php
defined( 'ABSPATH' ) || exit;

final class Rentacar_Core_Customer_Acknowledgement {
    public function send( Rentacar_Core_Reservation_Request $request ) {
        $subject = sprintf( __( 'We received your reservation request %s', 'rentacar-core' ), $request->get( 'reference' ) );
        $extras = Rentacar_Core_Reservation_Extras::customer_labels( (array) $request->get( 'extras', array() ) );
        $details = array(
            'Vehicle' => $request->get( 'vehicle_title' ),
            'Pickup' => $request->get( 'pickup_location' ) . ' — ' . $request->get( 'pickup_date' ) . ' ' . $request->get( 'pickup_time' ),
            'Return' => $request->get( 'return_location' ) . ' — ' . $request->get( 'return_date' ) . ' ' . $request->get( 'return_time' ),
            'Selected extras' => $extras ? implode( ', ', $extras ) : __( 'None', 'rentacar-core' ),
            'After-hours pickup surcharge' => (float) $request->get( 'after_hours_pickup', 0 ) > 0 ? '€' . number_format_i18n( (float) $request->get( 'after_hours_pickup' ), 2 ) : __( 'None', 'rentacar-core' ),
            'Indicative estimate' => $request->get( 'estimate_summary' ),
        );
        $message = Rentacar_Core_Reservation_Email_Template::render( 'Request received', 'We received your reservation request. Our team will check the selected vehicle and contact you to confirm availability, the final price and rental conditions. This is not a confirmed reservation.', $request->get( 'reference' ), array( array( 'title' => 'Your request', 'rows' => $details ) ) );

        return wp_mail( $request->get( 'email' ), $subject, $message, Rentacar_Core_Reservation_Email_Template::headers() );
    }
}
