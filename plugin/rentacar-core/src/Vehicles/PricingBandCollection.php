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
        $matches = array();
        foreach ( $this->bands as $band ) {
            if ( $band->applies_to( $days ) && null !== $band->daily_price && $band->daily_price > 0 ) {
                $matches[] = $band;
            }
        }

        return 1 === count( $matches ) ? $matches[0] : null;
    }

    /** Returns machine-readable integrity findings without changing stored prices. */
    public function audit( $minimum_days, $maximum_days ) {
        $issues = array();
        $coverage = array();

        foreach ( $this->bands as $index => $band ) {
            $key = 'band_' . ( $index + 1 );
            if ( $band->from_days < $minimum_days ) $issues[] = array( 'code' => 'range_starts_below_minimum', 'band' => $key );
            if ( null === $band->daily_price || $band->daily_price <= 0 ) $issues[] = array( 'code' => 'invalid_price', 'band' => $key );
            if ( $band->invalid_range ) $issues[] = array( 'code' => 'end_before_start', 'band' => $key );
            if ( null === $band->to_days && $index !== count( $this->bands ) - 1 ) $issues[] = array( 'code' => 'malformed_open_range', 'band' => $key );
            for ( $day = max( $minimum_days, $band->from_days ); $day <= $maximum_days && ( null === $band->to_days || $day <= $band->to_days ); $day++ ) {
                if ( null !== $band->daily_price && $band->daily_price > 0 ) $coverage[ $day ][] = $key;
            }
        }
        for ( $day = $minimum_days; $day <= $maximum_days; $day++ ) {
            $count = count( $coverage[ $day ] ?? array() );
            if ( 0 === $count ) $issues[] = array( 'code' => 'gap', 'day' => $day );
            if ( $count > 1 ) $issues[] = array( 'code' => 'overlap', 'day' => $day, 'bands' => $coverage[ $day ] );
        }

        return $issues;
    }
}
