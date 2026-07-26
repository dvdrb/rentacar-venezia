<?php
defined( 'ABSPATH' ) || exit;

final class Rentacar_Core_Business_Notification {
    public function send( Rentacar_Core_Reservation_Request $request, $recipient ) {
        $subject = sprintf( '[%s] Reservation request %s', get_bloginfo( 'name' ), $request->get( 'reference' ) );
        $details = array(
            'Vehicle' => $request->get( 'vehicle_title' ),
            'Pickup' => $request->get( 'pickup_location' ) . ' — ' . $request->get( 'pickup_date' ) . ' ' . $request->get( 'pickup_time' ),
            'Return' => $request->get( 'return_location' ) . ' — ' . $request->get( 'return_date' ) . ' ' . $request->get( 'return_time' ),
            'Insurance' => $request->get( 'insurance' ) ?: '—',
            'Indicative estimate' => $request->get( 'estimate_summary' ),
        );

        if ( (float) $request->get( 'inter_airport_surcharge', 0 ) > 0 ) {
            $details['Inter-airport transfer'] = '€' . number_format_i18n( (float) $request->get( 'inter_airport_surcharge' ), 2 );
        }
        if ( (float) $request->get( 'after_hours_pickup', 0 ) > 0 ) {
            $details['After-hours pickup surcharge'] = '€' . number_format_i18n( (float) $request->get( 'after_hours_pickup' ), 2 );
        }

        $extras = Rentacar_Core_Reservation_Extras::notification_lines( (array) $request->get( 'extras', array() ) );
        $message = Rentacar_Core_Reservation_Email_Template::render( 'New reservation request', 'A customer has submitted a reservation request. Review the details below and contact them to confirm availability, final price and rental conditions.', $request->get( 'reference' ), array(
            array( 'title' => 'Rental details', 'rows' => $details ),
            array( 'title' => 'Customer details', 'rows' => array( 'Name' => $request->get( 'full_name' ), 'Phone' => $request->get( 'phone' ), 'Email' => $request->get( 'email' ), 'Similar vehicle accepted' => $request->get( 'similar_vehicle' ) ? 'Yes' : 'No', 'Language' => $request->get( 'language' ), 'Submitted' => $request->get( 'submitted_at' ), 'Message' => $request->get( 'message' ) ?: '—' ) ),
            array( 'title' => 'Selected extras', 'rows' => array( 'Extras' => $extras ? implode( "\n", $extras ) : 'None' ) ),
        ) );

        return wp_mail( $recipient, $subject, $message, Rentacar_Core_Reservation_Email_Template::headers( array( 'Reply-To: ' . $request->get( 'full_name' ) . ' <' . $request->get( 'email' ) . '>' ) ) );
    }
}
