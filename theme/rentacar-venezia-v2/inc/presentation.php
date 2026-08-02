<?php
defined( 'ABSPATH' ) || exit;

function rentacar_venezia_v2_vehicle_title( Rentacar_Core_Vehicle $vehicle ) {
    $title = preg_replace( '/\s+/', ' ', trim( (string) $vehicle->get( 'title' ) ) );

    return preg_replace( '/\s*\|\s*/', ' | ', $title );
}

/** Formats a policy amount that is stored as integer cents by Rentacar Core. */
function rentacar_venezia_v2_policy_price_label( $cents ) {
    return sprintf( '€%s', number_format_i18n( max( 0, (int) $cents ) / 100, 2 ) );
}

/**
 * Returns the after-hours fee statement from the same policy consumed by the
 * estimate endpoint. This prevents airport copy from drifting from pricing.
 */
function rentacar_venezia_v2_after_hours_policy_label() {
    if ( ! class_exists( 'Rentacar_Core_Rental_Policy' ) ) {
        return '';
    }

    $fees = Rentacar_Core_Rental_Policy::get()['after_hours'];
    $times = array(
        Rentacar_Core_Rental_Policy::minutes_to_time( $fees['early_start'] ),
        Rentacar_Core_Rental_Policy::minutes_to_time( $fees['normal_start'] ),
        Rentacar_Core_Rental_Policy::minutes_to_time( $fees['evening_start'] ),
        Rentacar_Core_Rental_Policy::minutes_to_time( $fees['night_start'] ),
    );
    $prices = array(
        rentacar_venezia_v2_policy_price_label( $fees['early_cents'] ),
        rentacar_venezia_v2_policy_price_label( $fees['evening_cents'] ),
        rentacar_venezia_v2_policy_price_label( $fees['night_cents'] ),
    );
    $language = function_exists( 'rentacar_venezia_v2_current_language' ) ? rentacar_venezia_v2_current_language() : 'en';
    $templates = array(
        'it' => '%2$s–%3$s incluso; %3$s–%4$s %6$s; %4$s–%1$s %7$s; %1$s–%2$s %5$s.',
        'en' => '%2$s–%3$s included; %3$s–%4$s %6$s; %4$s–%1$s %7$s; %1$s–%2$s %5$s.',
        'ro' => '%2$s–%3$s inclus; %3$s–%4$s %6$s; %4$s–%1$s %7$s; %1$s–%2$s %5$s.',
        'ru' => '%2$s–%3$s включено; %3$s–%4$s %6$s; %4$s–%1$s %7$s; %1$s–%2$s %5$s.',
    );

    return vsprintf( $templates[ $language ] ?? $templates['en'], array_merge( $times, $prices ) );
}

/** Returns the current estimator's inter-airport charge in the page language. */
function rentacar_venezia_v2_inter_airport_policy_label() {
    if ( ! class_exists( 'Rentacar_Core_Rental_Policy' ) ) {
        return '';
    }

    $price = rentacar_venezia_v2_policy_price_label( Rentacar_Core_Rental_Policy::get()['inter_airport_surcharge_cents'] );
    $language = function_exists( 'rentacar_venezia_v2_current_language' ) ? rentacar_venezia_v2_current_language() : 'en';
    $templates = array(
        'it' => 'Si applica un supplemento di %s quando ritiro e riconsegna avvengono in aeroporti diversi.',
        'en' => 'A %s charge applies when pickup and return airports differ.',
        'ro' => 'Se aplică o taxă de %s atunci când preluarea și returnarea au loc în aeroporturi diferite.',
        'ru' => 'Взимается доплата %s, если получение и возврат проходят в разных аэропортах.',
    );

    return sprintf( $templates[ $language ] ?? $templates['en'], $price );
}

function rentacar_venezia_v2_inter_airport_return_policy_label() {
    if ( ! class_exists( 'Rentacar_Core_Rental_Policy' ) ) {
        return '';
    }

    $price = rentacar_venezia_v2_policy_price_label( Rentacar_Core_Rental_Policy::get()['inter_airport_surcharge_cents'] );
    $language = function_exists( 'rentacar_venezia_v2_current_language' ) ? rentacar_venezia_v2_current_language() : 'en';
    $templates = array(
        'it' => 'Sì. Seleziona un aeroporto diverso per la riconsegna nel modulo di richiesta; il supplemento applicabile è indicato separatamente nel preventivo: %s.',
        'en' => 'Yes. Select a different return airport in the reservation form; the applicable charge is shown separately in the estimate: %s.',
        'ro' => 'Da. Selectați un alt aeroport pentru returnare în formularul de rezervare; taxa aplicabilă este afișată separat în estimare: %s.',
        'ru' => 'Да. Выберите другой аэропорт возврата в форме запроса; применимая доплата указывается отдельно в расчёте: %s.',
    );

    return sprintf( $templates[ $language ] ?? $templates['en'], $price );
}

/** Visible requirements text for values the reservation policy actually owns. */
function rentacar_venezia_v2_payment_deposit_policy_label() {
    if ( ! class_exists( 'Rentacar_Core_Rental_Policy' ) ) {
        return '';
    }

    $policy = Rentacar_Core_Rental_Policy::get();
    $up_to_five = rentacar_venezia_v2_policy_price_label( $policy['deposits']['up_to_five_cents'] );
    $seven_to_nine = rentacar_venezia_v2_policy_price_label( $policy['deposits']['seven_to_nine_cents'] );
    $language = function_exists( 'rentacar_venezia_v2_current_language' ) ? rentacar_venezia_v2_current_language() : 'en';
    $templates = array(
        'it' => 'Non è richiesto alcun pagamento per inviare una richiesta. Il pagamento e il deposito cauzionale separato sono dovuti al ritiro: %s fino a cinque posti e %s da sette a nove posti.',
        'en' => 'No payment is required to send a request. Payment and the separate security deposit are due at pickup: %s up to five seats and %s for seven to nine seats.',
        'ro' => 'Nu este necesară nicio plată pentru a trimite o solicitare. Plata și garanția separată sunt datorate la preluare: %s pentru până la cinci locuri și %s pentru șapte până la nouă locuri.',
        'ru' => 'Для отправки запроса оплата не требуется. Оплата и отдельный депозит вносятся при получении: %s до пяти мест и %s для семи–девяти мест.',
    );

    return sprintf( $templates[ $language ] ?? $templates['en'], $up_to_five, $seven_to_nine );
}

function rentacar_venezia_v2_mileage_policy_label() {
    if ( ! class_exists( 'Rentacar_Core_Rental_Policy' ) ) {
        return '';
    }

    $policy = Rentacar_Core_Rental_Policy::get();
    $daily_km = absint( $policy['mileage']['daily_km'] );
    $excess = rentacar_venezia_v2_policy_price_label( $policy['mileage']['excess_cents'] );
    $language = function_exists( 'rentacar_venezia_v2_current_language' ) ? rentacar_venezia_v2_current_language() : 'en';
    $templates = array(
        'it' => 'Ogni giorno di noleggio include %s km. La distanza aggiuntiva costa %s/km. Le condizioni sul carburante sono confermate nel contratto di noleggio.',
        'en' => 'Each rental day includes %s km. Additional distance is %s/km. Fuel conditions are confirmed in the rental agreement.',
        'ro' => 'Fiecare zi de închiriere include %s km. Distanța suplimentară costă %s/km. Condițiile privind combustibilul sunt confirmate în contractul de închiriere.',
        'ru' => 'Каждый день аренды включает %s км. Дополнительный пробег стоит %s/км. Условия по топливу подтверждаются в договоре аренды.',
    );

    return sprintf( $templates[ $language ] ?? $templates['en'], number_format_i18n( $daily_km ), $excess );
}

/**
 * Return a human-readable, locale-aware label for the controlled vehicle
 * powertrain field. The value itself is maintained by Rentacar Core and is
 * intentionally separate from editorial vehicle titles.
 *
 * @param string $powertrain Controlled Rentacar Core powertrain value.
 * @return string
 */
function rentacar_venezia_v2_vehicle_powertrain_label( $powertrain ) {
    $labels = array(
        'petrol'          => array( 'en' => 'Petrol', 'it' => 'Benzina', 'ro' => 'Benzină', 'ru' => 'Бензин' ),
        'diesel'          => array( 'en' => 'Diesel', 'it' => 'Diesel', 'ro' => 'Diesel', 'ru' => 'Дизель' ),
        'hybrid'          => array( 'en' => 'Hybrid', 'it' => 'Ibrida', 'ro' => 'Hibrid', 'ru' => 'Гибрид' ),
        'plug_in_hybrid'  => array( 'en' => 'Plug-in hybrid', 'it' => 'Ibrida plug-in', 'ro' => 'Hibrid plug-in', 'ru' => 'Подключаемый гибрид' ),
        'electric'        => array( 'en' => 'Electric', 'it' => 'Elettrica', 'ro' => 'Electrică', 'ru' => 'Электрический' ),
    );
    $powertrain = sanitize_key( $powertrain );
    if ( empty( $labels[ $powertrain ] ) ) {
        return '';
    }

    $language = function_exists( 'rentacar_venezia_v2_current_language' ) ? rentacar_venezia_v2_current_language() : 'en';

    return $labels[ $powertrain ][ $language ] ?? $labels[ $powertrain ]['en'];
}

/** Small locale map for machine-readable vehicle properties. */
function rentacar_venezia_v2_vehicle_schema_text( $key ) {
    $strings = array(
        'fuel_type'            => array( 'en' => 'Fuel type', 'it' => 'Tipo di carburante', 'ro' => 'Tip combustibil', 'ru' => 'Тип топлива' ),
        'passenger_capacity'   => array( 'en' => 'Passenger capacity', 'it' => 'Capacità passeggeri', 'ro' => 'Capacitate pasageri', 'ru' => 'Вместимость пассажиров' ),
        'doors'                => array( 'en' => 'Doors', 'it' => 'Porte', 'ro' => 'Uși', 'ru' => 'Двери' ),
        'air_conditioning'     => array( 'en' => 'Air conditioning', 'it' => 'Aria condizionata', 'ro' => 'Aer condiționat', 'ru' => 'Кондиционер' ),
        'yes'                  => array( 'en' => 'Yes', 'it' => 'Sì', 'ro' => 'Da', 'ru' => 'Да' ),
        'rental_vehicle'       => array( 'en' => 'Rental vehicle', 'it' => 'Veicolo a noleggio', 'ro' => 'Vehicul de închiriat', 'ru' => 'Арендный автомобиль' ),
        'indicative_offer'     => array( 'en' => 'Indicative daily price for rentals of %s. Availability and final price are confirmed personally.', 'it' => 'Prezzo giornaliero indicativo per noleggi di %s. Disponibilità e prezzo finale sono confermati personalmente.', 'ro' => 'Preț zilnic orientativ pentru închirieri de %s. Disponibilitatea și prețul final sunt confirmate personal.', 'ru' => 'Ориентировочная дневная цена для аренды на %s. Наличие и окончательная цена подтверждаются лично.' ),
    );
    if ( empty( $strings[ $key ] ) ) {
        return '';
    }
    $language = function_exists( 'rentacar_venezia_v2_current_language' ) ? rentacar_venezia_v2_current_language() : 'en';

    return $strings[ $key ][ $language ] ?? $strings[ $key ]['en'];
}

function rentacar_venezia_v2_vehicle_specs( Rentacar_Core_Vehicle $vehicle ) {
    $specs = array_filter(
        array(
            rentacar_venezia_v2_vehicle_transmission_label( $vehicle->get( 'transmission' ) ),
            rentacar_venezia_v2_vehicle_powertrain_label( $vehicle->get( 'powertrain' ) ),
            $vehicle->get( 'passengers' ) ? sprintf( _n( '%s passenger', '%s passengers', $vehicle->get( 'passengers' ), 'rentacar-venezia-v2' ), number_format_i18n( $vehicle->get( 'passengers' ) ) ) : '',
            $vehicle->get( 'doors' ) ? sprintf( _n( '%s door', '%s doors', $vehicle->get( 'doors' ), 'rentacar-venezia-v2' ), number_format_i18n( $vehicle->get( 'doors' ) ) ) : '',
            $vehicle->get( 'air_conditioning' ) ? __( 'Air conditioning', 'rentacar-venezia-v2' ) : '',
        )
    );

    return array_values( $specs );
}

function rentacar_venezia_v2_vehicle_bands( Rentacar_Core_Vehicle $vehicle ) {
    $valid = array();
    foreach ( $vehicle->get( 'pricing_bands' )->all() as $band ) {
        if ( $band->from_days < 1 || null === $band->daily_price || $band->daily_price <= 0 || ( null !== $band->to_days && $band->to_days < $band->from_days ) ) {
            continue;
        }
        $valid[] = $band;
    }
    return $valid;
}

function rentacar_venezia_v2_vehicle_starting_price( Rentacar_Core_Vehicle $vehicle ) {
    $prices = array();

    foreach ( rentacar_venezia_v2_vehicle_bands( $vehicle ) as $band ) {
        $prices[] = (float) $band->daily_price;
    }

    return $prices ? min( $prices ) : null;
}

/** Keeps the catalogue's fixed low-to-high price order in the database query before pagination. */
function rentacar_venezia_v2_fleet_query_args( $paged ) {
    $args = array(
        'post_type'           => 'cars',
        'post_status'         => 'publish',
        'posts_per_page'      => 12,
        'paged'               => max( 1, absint( $paged ) ),
        'ignore_sticky_posts' => true,
        'orderby'             => 'menu_order title',
        'order'               => 'ASC',
    );

    $args['rentacar_starting_price_sort'] = 'ASC';

    return $args;
}

/** Retains an active trip query while paginating the catalogue. */
function rentacar_venezia_v2_fleet_pagination_args( array $trip ) {
    return array_filter( $trip );
}

/**
 * Presentation-only escape hatch for source images with excessive internal
 * whitespace. A theme or child theme may return one safe modifier class
 * without touching vehicle records.
 */
function rentacar_venezia_v2_vehicle_image_presentation_class( Rentacar_Core_Vehicle $vehicle ) {
    $class = apply_filters( 'rentacar_venezia_v2_vehicle_image_presentation_class', '', $vehicle );

    return is_string( $class ) ? sanitize_html_class( $class ) : '';
}

function rentacar_venezia_v2_price_range_label( Rentacar_Core_Pricing_Band $band ) {
    return null === $band->to_days
        ? sprintf( __( '%s+ days', 'rentacar-venezia-v2' ), number_format_i18n( $band->from_days ) )
        : sprintf( __( '%1$s–%2$s days', 'rentacar-venezia-v2' ), number_format_i18n( $band->from_days ), number_format_i18n( $band->to_days ) );
}

function rentacar_venezia_v2_price_label( Rentacar_Core_Pricing_Band $band ) {
    return sprintf( __( '€%s/day', 'rentacar-venezia-v2' ), number_format_i18n( $band->daily_price, 0 ) );
}

function rentacar_venezia_v2_vehicle_image_id( Rentacar_Core_Vehicle $vehicle ) {
    $gallery = $vehicle->get( 'vehicle_gallery' );
    $ids = $gallery ? $gallery->all_image_ids() : array();
    return $ids ? (int) $ids[0] : 0;
}

/** Resolve WordPress-managed legal pages through the active language provider. */
function rentacar_venezia_v2_managed_page_id( $key ) {
    static $pages = array();
    $key = sanitize_key( $key );

    if ( isset( $pages[ $key ] ) ) {
        return $pages[ $key ];
    }

    $ids = get_posts(
        array(
            'post_type'              => 'page',
            'post_status'            => 'publish',
            'posts_per_page'         => 1,
            'fields'                 => 'ids',
            'meta_key'               => '_rc_provisioning_key',
            'meta_value'             => $key,
            'no_found_rows'          => true,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
        )
    );

    return $pages[ $key ] = $ids ? (int) $ids[0] : 0;
}

function rentacar_venezia_v2_managed_page_url( $key ) {
    $page_id = rentacar_venezia_v2_managed_page_id( $key );
    $page_id = $page_id ? rentacar_venezia_v2_translated_post_id( $page_id ) : 0;

    return $page_id ? get_permalink( $page_id ) : '';
}

function rentacar_venezia_v2_localized_privacy_policy_url() {
    $page_id = (int) get_option( 'wp_page_for_privacy_policy' );
    if ( ! $page_id ) {
        $page_id = rentacar_venezia_v2_managed_page_id( 'privacy_policy' );
    }
    $page_id = $page_id ? rentacar_venezia_v2_translated_post_id( $page_id ) : 0;

    return $page_id ? get_permalink( $page_id ) : '';
}

/**
 * Build fleet controls from values used by published vehicles in the current
 * WordPress language context. Only the established vehicle meta keys are
 * allowed here; no unbounded arbitrary meta lookup is exposed to templates.
 */
function rentacar_venezia_v2_vehicle_filter_values( $meta_key ) {
    static $values = array();
    $allowed_keys = array( 'gearbox', 'max_passagers', 'doors' );

    if ( ! in_array( $meta_key, $allowed_keys, true ) ) {
        return array();
    }

    if ( isset( $values[ $meta_key ] ) ) {
        return $values[ $meta_key ];
    }

    $ids = get_posts(
        array(
            'post_type'              => 'cars',
            'post_status'            => 'publish',
            'posts_per_page'         => -1,
            'fields'                 => 'ids',
            'orderby'                => 'title',
            'order'                  => 'ASC',
            'no_found_rows'          => true,
            'ignore_sticky_posts'    => true,
            'update_post_term_cache' => false,
        )
    );
    $found = array();

    foreach ( $ids as $id ) {
        $value = get_post_meta( $id, $meta_key, true );
        if ( 'gearbox' === $meta_key ) {
            $value = sanitize_text_field( $value );
            if ( '' !== $value ) {
                $found[ $value ] = $value;
            }
            continue;
        }

        $value = absint( $value );
        if ( $value > 0 ) {
            $found[ $value ] = $value;
        }
    }

    if ( 'gearbox' === $meta_key ) {
        natcasesort( $found );
    } else {
        ksort( $found, SORT_NUMERIC );
    }

    $values[ $meta_key ] = array_values( $found );

    return $values[ $meta_key ];
}
