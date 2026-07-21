<?php
defined( 'ABSPATH' ) || exit;

final class Rentacar_Core_Pricing_Band_Collection {
    private $bands;

    public function __construct( array $bands ) {
        $this->bands = $bands;
    }

    public function all() {
        return $this->bands;
    }

    public function for_days( $days ) {
        foreach ( $this->bands as $band ) {
            if ( $band->applies_to( $days ) && null !== $band->daily_price ) {
                return $band;
            }
        }

        return null;
    }
}
