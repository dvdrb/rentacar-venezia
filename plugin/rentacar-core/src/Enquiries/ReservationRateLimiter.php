<?php
defined( 'ABSPATH' ) || exit;

final class Rentacar_Core_Reservation_Rate_Limiter {
    public function allows( $email ) {
        $key = 'reservation_' . md5( strtolower( trim( $email ) ) );

        return wp_cache_add( $key, 1, 'rentacar_core', 30 );
    }
}
