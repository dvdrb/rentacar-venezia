<?php
defined( 'ABSPATH' ) || exit;

final class Rentacar_Core_Pricing_Band {
    public $from_days;
    public $to_days;
    public $daily_price;

    public function __construct( $from_days, $to_days, $daily_price ) {
        $this->from_days = max( 0, (int) $from_days );
        $this->to_days = null === $to_days || '' === $to_days ? null : max( $this->from_days, (int) $to_days );
        $this->daily_price = is_numeric( $daily_price ) ? (float) $daily_price : null;
    }

    public function applies_to( $days ) {
        $days = (int) $days;
        return $days >= $this->from_days && ( null === $this->to_days || $days <= $this->to_days );
    }
}
