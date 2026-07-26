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

    public function estimate( $vehicle_id, $pickup_date, $pickup_time, $return_date, $return_time, array $extra_keys = array(), $insurance_key = 'base', $pickup_location = '', $return_location = '' ) {
        $vehicle = $this->vehicles->find( $vehicle_id );
        $days = $this->duration->calculate( $pickup_date, $pickup_time, $return_date, $return_time );

        if ( ! $vehicle || ! $days ) {
            return null;
        }

        $band = $vehicle->get( 'pricing_bands' )->for_days( $days );
        $extras = Rentacar_Core_Reservation_Extras::calculate( $extra_keys, $days );
        $insurance = Rentacar_Core_Rental_Policy::insurance( $insurance_key );
        $insurance_total = $insurance ? (int) $insurance['daily_cents'] / 100 * $days : 0;
        $after_hours = Rentacar_Core_Rental_Policy::after_hours_cents( $pickup_time ) / 100;
        $inter_airport_surcharge = Rentacar_Core_Rental_Policy::inter_airport_surcharge_cents( $pickup_location, $return_location ) / 100;

        if ( ! $band ) {
            return new Rentacar_Core_Estimate(
                array(
                    'vehicle_id'   => $vehicle->get( 'id' ),
                    'days'         => $days,
                    'available'    => false,
                    'extras'       => $extras['items'],
                    'extras_total' => $extras['total'],
                    'disclaimer'   => self::DISCLAIMER,
                    'unconfigured' => array( 'base_price', 'insurance', 'extras', 'night_charges', 'taxes' ),
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
        if ( $insurance ) $line_items[] = array( 'label' => $insurance['label'], 'amount' => $insurance_total );
        if ( $after_hours ) $line_items[] = array( 'label' => 'After-hours pickup surcharge', 'amount' => $after_hours );
        if ( $inter_airport_surcharge ) $line_items[] = array( 'label' => 'Inter-airport transfer', 'amount' => $inter_airport_surcharge );

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
                'insurance' => $insurance ? array( 'key' => $insurance_key, 'label' => $insurance['label'], 'amount' => $insurance_total ) : null,
                'after_hours_pickup' => $after_hours,
                'inter_airport_surcharge' => $inter_airport_surcharge,
                'included_km' => $days * (int) Rentacar_Core_Rental_Policy::get()['mileage']['daily_km'],
                'excess_km_rate' => (int) Rentacar_Core_Rental_Policy::get()['mileage']['excess_cents'] / 100,
                'deposit' => Rentacar_Core_Rental_Policy::deposit_cents( $vehicle->get( 'passengers' ) ) / 100,
                'estimate_total' => round( $base_total + $extras['total'] + $insurance_total + $after_hours + $inter_airport_surcharge, 2 ),
                'line_items'   => $line_items,
                'disclaimer'   => self::DISCLAIMER,
                'unconfigured' => array( 'insurance', 'extras', 'night_charges', 'taxes' ),
            )
        );
    }
}
