<?php
defined( 'ABSPATH' ) || exit;

final class Rentacar_Core_Business_Notification {
    public function send( Rentacar_Core_Reservation_Request $request, $recipient ) {
        $subject = sprintf( '[%s] Reservation request %s', get_bloginfo( 'name' ), $request->get( 'reference' ) );
        $details = array(
            'Request status' => 'received',
            'Submitted' => $request->get( 'submitted_at' ),
            'Customer language' => $request->get( 'language' ),
            'Vehicle' => $request->get( 'vehicle_title' ),
            'Vehicle ID' => $request->get( 'vehicle_id' ),
            'Vehicle URL' => $request->get( 'vehicle_url' ),
            'Powertrain' => $request->get( 'powertrain', 'other' ),
            'Pickup' => $request->get( 'pickup_location' ) . ' — ' . $request->get( 'pickup_date' ) . ' ' . $request->get( 'pickup_time' ),
            'Return' => $request->get( 'return_location' ) . ' — ' . $request->get( 'return_date' ) . ' ' . $request->get( 'return_time' ),
            'Rental duration' => self::estimate_value( $request, 'days', '—' ) . ' days',
        );

        if ( (float) $request->get( 'inter_airport_surcharge', 0 ) > 0 ) {
            $details['Inter-airport transfer'] = '€' . number_format_i18n( (float) $request->get( 'inter_airport_surcharge' ), 2 );
        }
        if ( (float) $request->get( 'after_hours_pickup', 0 ) > 0 ) {
            $details['After-hours pickup surcharge'] = '€' . number_format_i18n( (float) $request->get( 'after_hours_pickup' ), 2 );
        }

        $estimate = (array) $request->get( 'estimate', array() );
        $pricing = array(
            'Daily vehicle rate' => self::money( $estimate['daily_price'] ?? null ),
            'Vehicle subtotal' => self::money( $estimate['base_total'] ?? null ),
            'Insurance' => ! empty( $estimate['insurance']['label'] ) ? $estimate['insurance']['label'] . ' — ' . self::money( $estimate['insurance']['amount'] ?? null ) : '—',
            'After-hours pickup' => self::money( $estimate['after_hours_pickup'] ?? 0 ),
            'Different-airport transfer' => self::money( $estimate['inter_airport_surcharge'] ?? 0 ),
            'Indicative rental total' => self::money( $estimate['estimate_total'] ?? null ),
            'Refundable deposit' => self::money( $estimate['deposit'] ?? null ),
            'Included kilometres' => isset( $estimate['included_km'] ) ? absint( $estimate['included_km'] ) . ' km' : '—',
            'Excess-kilometre rate' => self::money( $estimate['excess_km_rate'] ?? null ) . '/km',
            'Disclaimer' => $estimate['disclaimer'] ?? '—',
        );
        foreach ( (array) ( $estimate['extras'] ?? array() ) as $extra ) $pricing[ $extra['label'] ?? __( 'Extra', 'rentacar-core' ) ] = self::money( $extra['subtotal'] ?? null );
        $extras = Rentacar_Core_Reservation_Extras::notification_lines( (array) $request->get( 'extras', array() ) );
        $message = Rentacar_Core_Reservation_Email_Template::render( 'New reservation request', 'A customer has submitted a reservation request. Review the details below and contact them to confirm availability, final price and rental conditions.', $request->get( 'reference' ), array(
            array( 'title' => 'Rental details', 'rows' => $details ),
            array( 'title' => 'Price breakdown', 'rows' => $pricing ),
            array( 'title' => 'Customer details', 'rows' => array( 'Name' => $request->get( 'full_name' ), 'Phone' => $request->get( 'phone_display', $request->get( 'phone' ) ) . ' (' . $request->get( 'phone_e164', $request->get( 'phone' ) ) . ')', 'Email' => $request->get( 'email' ), 'Similar vehicle accepted' => $request->get( 'similar_vehicle' ) ? 'Yes' : 'No', 'Language' => $request->get( 'language' ), 'Submitted' => $request->get( 'submitted_at' ), 'Message' => $request->get( 'message' ) ?: '—' ) ),
            array( 'title' => 'Selected extras', 'rows' => array( 'Extras' => $extras ? implode( "\n", $extras ) : 'None' ) ),
        ), '', 'en' );

        $headers = Rentacar_Core_Reservation_Email_Template::headers();
        $email = (string) $request->get( 'email' );
        if ( false !== filter_var( $email, FILTER_VALIDATE_EMAIL ) ) $headers[] = 'Reply-To: ' . preg_replace( '/[\r\n]+/', ' ', (string) $request->get( 'full_name' ) ) . ' <' . $email . '>';
        return wp_mail( $recipient, $subject, $message, $headers );
    }

    private static function estimate_value( Rentacar_Core_Reservation_Request $request, $key, $default ) { $estimate = (array) $request->get( 'estimate', array() ); return $estimate[ $key ] ?? $default; }
    private static function money( $value ) { return null === $value ? '—' : '€' . number_format_i18n( (float) $value, 2 ); }
}
