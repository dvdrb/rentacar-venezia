<?php
defined( 'ABSPATH' ) || exit;

final class Rentacar_Core_Customer_Acknowledgement {
    public function send( Rentacar_Core_Reservation_Request $request ) {
        $language = $request->get( 'language', 'en' );
        $t = function( $key ) use ( $language ) { return Rentacar_Core_Reservation_Translations::get( $language, $key ); };
        $estimate = (array) $request->get( 'estimate', array() );
        $subject = sprintf( $t( 'subject' ), $request->get( 'reference' ) );
        $extras = array_map( function( $label ) use ( $language ) { return Rentacar_Core_Reservation_Translations::label( $language, $label ); }, Rentacar_Core_Reservation_Extras::customer_labels( (array) $request->get( 'extras', array() ) ) );
        $details = array(
            $t( 'vehicle' ) => $request->get( 'vehicle_title' ),
            $t( 'pickup' ) => $request->get( 'pickup_location' ) . ' — ' . $request->get( 'pickup_date' ) . ' ' . $request->get( 'pickup_time' ),
            $t( 'return' ) => $request->get( 'return_location' ) . ' — ' . $request->get( 'return_date' ) . ' ' . $request->get( 'return_time' ),
            $t( 'rental_duration' ) => absint( $estimate['days'] ?? 0 ) . ' ' . $t( 'days' ),
            Rentacar_Core_Reservation_Translations::label( $language, 'Phone' ) => $request->get( 'phone_display', $request->get( 'phone' ) ),
            $t( 'selected_insurance' ) => Rentacar_Core_Reservation_Translations::label( $language, $estimate['insurance']['label'] ?? $t( 'none' ) ),
            $t( 'selected_extras' ) => $extras ? implode( ', ', $extras ) : $t( 'none' ),
            $t( 'after_hours_pickup_surcharge' ) => (float) $request->get( 'after_hours_pickup', 0 ) > 0 ? '€' . number_format_i18n( (float) $request->get( 'after_hours_pickup' ), 2 ) : $t( 'none' ),
            $t( 'your_message' ) => $request->get( 'message' ) ?: $t( 'none' ),
        );
        $pricing = array( $t( 'vehicle_subtotal' ) => self::money( $estimate['base_total'] ?? null ), $t( 'insurance' ) => self::money( $estimate['insurance']['amount'] ?? null ), $t( 'indicative_rental_total' ) => self::money( $estimate['estimate_total'] ?? null ), $t( 'refundable_deposit' ) => self::money( $estimate['deposit'] ?? null ), $t( 'included_kilometres' ) => isset( $estimate['included_km'] ) ? absint( $estimate['included_km'] ) . ' km' : '—', $t( 'excess_kilometre_rate' ) => self::money( $estimate['excess_km_rate'] ?? null ) . '/km' );
        foreach ( (array) ( $estimate['extras'] ?? array() ) as $extra ) $pricing[ Rentacar_Core_Reservation_Translations::label( $language, $extra['label'] ?? $t( 'extra' ) ) ] = self::money( $extra['subtotal'] ?? null );
        $message = Rentacar_Core_Reservation_Email_Template::render( $t( 'heading' ), $t( 'intro' ), $request->get( 'reference' ), array( array( 'title' => $t( 'request' ), 'rows' => $details ), array( 'title' => $t( 'estimate' ), 'rows' => $pricing ) ), '', $language, $t( 'reference' ) );

        $sent = wp_mail( $request->get( 'email' ), $subject, $message, Rentacar_Core_Reservation_Email_Template::headers() );
        return $sent;
    }

    private static function money( $value ) { return null === $value ? '—' : '€' . number_format_i18n( (float) $value, 2 ); }
}
