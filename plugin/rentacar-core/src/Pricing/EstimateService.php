<?php
defined( 'ABSPATH' ) || exit;

final class Rentacar_Core_Estimate_Service {
    const DISCLAIMER = 'This is an indicative estimate. Availability and final price are confirmed by our team.';

    private $vehicles;
    private $duration;

    public function __construct( Rentacar_Core_Vehicle_Repository $vehicles = null, Rentacar_Core_Rental_Duration_Calculator $duration = null ) {
        $this->vehicles = $vehicles ? $vehicles : new Rentacar_Core_Vehicle_Repository();
        $this->duration = $duration ? $duration : new Rentacar_Core_Rental_Duration_Calculator();
    }

    public function estimate( $vehicle_id, $pickup_date, $pickup_time, $return_date, $return_time, array $extra_keys = array() ) {
        $vehicle = $this->vehicles->find( $vehicle_id );
        $days = $this->duration->calculate( $pickup_date, $pickup_time, $return_date, $return_time );

        if ( ! $vehicle || ! $days ) {
            return null;
        }

        $band = $vehicle->get( 'pricing_bands' )->for_days( $days );
        $extras = Rentacar_Core_Reservation_Extras::calculate( $extra_keys, $days );

        if ( ! $band ) {
            return new Rentacar_Core_Estimate(
                array(
                    'vehicle_id'   => $vehicle->get( 'id' ),
                    'days'         => $days,
                    'available'    => false,
                    'extras'       => $extras['items'],
                    'extras_total' => $extras['total'],
                    'disclaimer'   => self::DISCLAIMER,
                    'unconfigured' => array( 'base_price', 'insurance', 'extras', 'location_charges', 'night_charges', 'taxes' ),
                )
            );
        }

        $daily_price = round( (float) $band->daily_price, 2 );
        $base_total = round( $daily_price * $days, 2 );

        $line_items = array(
            array(
                'label'  => 'Vehicle base rate',
                'amount' => $base_total,
            ),
        );

        foreach ( $extras['items'] as $extra ) {
            if ( null !== $extra['subtotal'] ) {
                $line_items[] = array(
                    'label'  => $extra['label'],
                    'amount' => $extra['subtotal'],
                );
            }
        }

        return new Rentacar_Core_Estimate(
            array(
                'vehicle_id'   => $vehicle->get( 'id' ),
                'days'         => $days,
                'available'    => true,
                'currency'     => 'EUR',
                'daily_price'  => $daily_price,
                'base_total'   => $base_total,
                'extras'       => $extras['items'],
                'extras_total' => $extras['total'],
                'estimate_total' => round( $base_total + $extras['total'], 2 ),
                'line_items'   => $line_items,
                'disclaimer'   => self::DISCLAIMER,
                'unconfigured' => array( 'insurance', 'extras', 'location_charges', 'night_charges', 'taxes' ),
            )
        );
    }
}
