<?php
defined( 'ABSPATH' ) || exit;

final class Rentacar_Core_Reservation_Reference {
    public static function generate() {
        return 'RAV-' . wp_date( 'Ymd' ) . '-' . strtoupper( wp_generate_password( 6, false, false ) );
    }
}
