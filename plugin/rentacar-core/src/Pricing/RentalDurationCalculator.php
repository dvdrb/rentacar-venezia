<?php
defined( 'ABSPATH' ) || exit;

final class Rentacar_Core_Rental_Duration_Calculator {
    public function calculate( $pickup_date, $pickup_time, $return_date, $return_time ) {
        $timezone = $this->timezone();
        $pickup = $this->date_time( $pickup_date, $pickup_time, $timezone );
        $return = $this->date_time( $return_date, $return_time, $timezone );

        if ( ! $pickup || ! $return || $return <= $pickup ) {
            return null;
        }

        $calendar_days = (int) $pickup->setTime( 0, 0 )->diff( $return->setTime( 0, 0 ) )->format( '%a' );
        $days = max( 1, $calendar_days );

        if ( $return->format( 'H:i' ) > $pickup->format( 'H:i' ) ) {
            $days++;
        }

        return $days;
    }

    private function timezone() {
        $timezone = get_option( 'timezone_string' );

        return new DateTimeZone( $timezone ? $timezone : 'UTC' );
    }

    private function date_time( $date, $time, DateTimeZone $timezone ) {
        if ( ! preg_match( '/^\d{4}-\d{2}-\d{2}$/', (string) $date ) || ! preg_match( '/^\d{2}:\d{2}$/', (string) $time ) ) {
            return null;
        }

        $date_time = DateTimeImmutable::createFromFormat( '!Y-m-d H:i', $date . ' ' . $time, $timezone );
        $errors = DateTimeImmutable::getLastErrors();

        return $date_time && ( false === $errors || ( 0 === $errors['warning_count'] && 0 === $errors['error_count'] ) ) ? $date_time : null;
    }
}
