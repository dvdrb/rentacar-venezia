<?php
defined( 'ABSPATH' ) || exit;

final class Rentacar_Core_Reservation_Request {
    private $data;

    public function __construct( array $data ) {
        $this->data = $data;
    }

    public function get( $key, $default = null ) {
        return array_key_exists( $key, $this->data ) ? $this->data[ $key ] : $default;
    }

    public function to_array() {
        return $this->data;
    }
}
