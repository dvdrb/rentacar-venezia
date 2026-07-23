<?php
defined( 'ABSPATH' ) || exit;

final class Rentacar_Core_Reservation_Rate_Limiter {
    public function allows( $email ) {
        $key = 'reservation_' . md5( strtolower( trim( $email ) ) );

        if ( false !== get_transient( $key ) ) {
            return false;
        }

        set_transient( $key, 1, 30 );

        return true;
    }
}
