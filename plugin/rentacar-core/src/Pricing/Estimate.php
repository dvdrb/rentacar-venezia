<?php
defined( 'ABSPATH' ) || exit;

final class Rentacar_Core_Estimate {
    private $data;

    public function __construct( array $data ) {
        $this->data = $data;
    }

    public function to_array() {
        return $this->data;
    }

    public function get( $key, $default = null ) {
        return array_key_exists( $key, $this->data ) ? $this->data[ $key ] : $default;
    }
}
