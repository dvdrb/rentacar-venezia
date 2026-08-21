<?php
defined( 'ABSPATH' ) || exit;

/**
 * Small, deliberately curated landing-page registry. It contains product
 * intent and presentation only; prices, availability and vehicle facts always
 * come from Rentacar Core at render time.
 */
function rentacar_venezia_v2_rental_intents() {
    return array(
        'economy' => array( 'minimum' => 2, 'match' => 'economy' ),
        'automatic' => array( 'minimum' => 1, 'match' => 'automatic' ),
        'seven_seat' => array( 'minimum' => 1, 'match' => 'seven_seat' ),
        'nine_seat' => array( 'minimum' => 1, 'match' => 'nine_seat' ),
        'family' => array( 'minimum' => 2, 'match' => 'family' ),
        'no_credit_card' => array( 'minimum' => 1, 'match' => 'manual_policy', 'policy_keys' => array( 'no_credit_card_to_reserve', 'no_advance_reservation_deposit', 'security_deposit_at_pickup' ) ),
    );
}

function rentacar_venezia_v2_landing_copy( $key, $language = null ) {
    $language = $language ?: rentacar_venezia_v2_current_language();
    $location = rentacar_venezia_v2_pickup_locations()[ $key ] ?? array();
    if ( $location ) {
        $label = rentacar_venezia_v2_location_label( $key, $language );
        $type = $location['type'] ?? '';
        $intros = array(
            'airport' => array( 'it' => 'Per chi arriva in aereo e desidera organizzare il ritiro con un servizio locale.', 'en' => 'For travellers arriving by air who want to arrange pickup with a local service.', 'ro' => 'Pentru călătorii care sosesc cu avionul și doresc să organizeze preluarea cu un serviciu local.', 'ru' => 'Для путешественников, прибывающих самолётом и желающих организовать получение с местным сервисом.' ),
            'station' => array( 'it' => 'Un’opzione per chi arriva in treno e vuole proseguire il viaggio in auto.', 'en' => 'An option for travellers arriving by train and continuing their journey by car.', 'ro' => 'O opțiune pentru călătorii care sosesc cu trenul și își continuă drumul cu mașina.', 'ru' => 'Вариант для путешественников, прибывающих поездом и продолжающих путь на автомобиле.' ),
            'city_access' => array( 'it' => 'Un punto di servizio pratico per organizzare il noleggio nell’area di Venezia.', 'en' => 'A practical service area for arranging your rental in Venice.', 'ro' => 'O zonă practică de servicii pentru organizarea închirierii în Veneția.', 'ru' => 'Практичная сервисная зона для организации аренды в Венеции.' ),
            'hotel' => array( 'it' => 'Indica il nome e l’indirizzo dell’hotel nella richiesta: i dettagli vengono confermati personalmente.', 'en' => 'Include your hotel name and address in the request; the details are confirmed personally.', 'ro' => 'Indicați numele și adresa hotelului în solicitare; detaliile sunt confirmate personal.', 'ru' => 'Укажите название и адрес отеля в запросе; детали подтверждаются лично.' ),
        );
        $metadata = array(
            'venice_marco_polo' => array( 'it' => array( 'meta_title' => 'Noleggio auto Aeroporto Venezia Marco Polo | G&D Rent A Car', 'meta_description' => 'Noleggio auto per chi arriva all’Aeroporto di Venezia Marco Polo. Scegli un veicolo e invia la richiesta: i dettagli del ritiro vengono confermati personalmente.' ), 'en' => array( 'meta_title' => 'Venice Airport Car Rental | No Credit Card to Reserve | G&D', 'meta_description' => 'Car rental at Venice Marco Polo Airport with direct local assistance. Reserve without a credit card or advance reservation deposit; a security deposit is required at pickup.' ), 'ro' => array( 'meta_title' => 'Închirieri auto Aeroportul Veneția Marco Polo | G&D Rent A Car', 'meta_description' => 'Închirieri auto pentru călătorii care sosesc la Aeroportul Veneția Marco Polo. Alegeți un vehicul și trimiteți solicitarea; detaliile preluării sunt confirmate personal.' ), 'ru' => array( 'meta_title' => 'Прокат авто в аэропорту Венеция Марко Поло | G&D Rent A Car', 'meta_description' => 'Прокат авто для прибывающих в аэропорт Венеция Марко Поло. Выберите автомобиль и отправьте запрос; детали получения подтверждаются лично.' ) ),
            'treviso_airport' => array( 'it' => array( 'meta_title' => 'Noleggio auto Aeroporto di Treviso | G&D Rent A Car', 'meta_description' => 'Noleggio auto per chi arriva all’Aeroporto di Treviso. Esplora la flotta e invia le date: disponibilità, prezzo e ritiro sono confermati personalmente.' ), 'en' => array( 'meta_title' => 'Treviso Airport car rental | G&D Rent A Car', 'meta_description' => 'Car rental for travellers arriving at Treviso Airport. Explore the fleet and send your dates; availability, price and pickup are confirmed personally.' ), 'ro' => array( 'meta_title' => 'Închirieri auto Aeroportul Treviso | G&D Rent A Car', 'meta_description' => 'Închirieri auto pentru călătorii care sosesc la Aeroportul Treviso. Explorați flota și trimiteți datele; disponibilitatea, prețul și preluarea sunt confirmate personal.' ), 'ru' => array( 'meta_title' => 'Прокат авто в аэропорту Тревизо | G&D Rent A Car', 'meta_description' => 'Прокат авто для прибывающих в аэропорт Тревизо. Посмотрите автопарк и отправьте даты; наличие, цена и получение подтверждаются лично.' ) ),
            'treviso_station' => array( 'it' => array( 'meta_title' => 'Noleggio auto alla Stazione di Treviso | G&D Rent A Car', 'meta_description' => 'Noleggio auto per chi arriva in treno alla Stazione di Treviso e vuole proseguire il viaggio in auto. Invia una richiesta per confermare i dettagli.' ), 'en' => array( 'meta_title' => 'Treviso Train Station car rental | G&D Rent A Car', 'meta_description' => 'Car rental for travellers arriving by train at Treviso Station and continuing by road. Send a request to have the practical details confirmed.' ), 'ro' => array( 'meta_title' => 'Închirieri auto la Gara Treviso | G&D Rent A Car', 'meta_description' => 'Închirieri auto pentru călătorii care sosesc cu trenul la Gara Treviso și continuă drumul cu mașina. Trimiteți o solicitare pentru confirmarea detaliilor.' ), 'ru' => array( 'meta_title' => 'Прокат авто у вокзала Тревизо | G&D Rent A Car', 'meta_description' => 'Прокат авто для прибывающих поездом на вокзал Тревизо и продолжающих путь на автомобиле. Отправьте запрос для подтверждения деталей.' ) ),
            'venezia_mestre_station' => array( 'it' => array( 'meta_title' => 'Noleggio auto alla Stazione Venezia Mestre | G&D Rent A Car', 'meta_description' => 'Organizza il noleggio auto nell’area della Stazione Venezia Mestre prima di proseguire verso Venezia o altre destinazioni. I dettagli sono confermati personalmente.' ), 'en' => array( 'meta_title' => 'Venezia Mestre Station car rental | G&D Rent A Car', 'meta_description' => 'Arrange car rental in the Venezia Mestre Station area before continuing to Venice or elsewhere. The practical details are confirmed personally.' ), 'ro' => array( 'meta_title' => 'Închirieri auto la Gara Venezia Mestre | G&D Rent A Car', 'meta_description' => 'Organizați închirierea auto în zona Gării Venezia Mestre înainte de a continua spre Veneția sau alte destinații. Detaliile sunt confirmate personal.' ), 'ru' => array( 'meta_title' => 'Прокат авто у станции Венеция-Местре | G&D Rent A Car', 'meta_description' => 'Организуйте прокат авто в районе станции Венеция-Местре перед поездкой в Венецию или другие места. Практические детали подтверждаются лично.' ) ),
            'venezia_piazzale_roma' => array( 'it' => array( 'meta_title' => 'Noleggio auto a Piazzale Roma, Venezia | G&D Rent A Car', 'meta_description' => 'Organizza il noleggio auto nell’area di Piazzale Roma, punto di accesso a Venezia. Invia la richiesta e ricevi conferma personale dei dettagli.' ), 'en' => array( 'meta_title' => 'Car rental at Piazzale Roma, Venice | G&D Rent A Car', 'meta_description' => 'Arrange car rental in the Piazzale Roma area, a practical access point for Venice. Send a request and receive personal confirmation of the details.' ), 'ro' => array( 'meta_title' => 'Închirieri auto la Piazzale Roma, Veneția | G&D Rent A Car', 'meta_description' => 'Organizați închirierea auto în zona Piazzale Roma, un punct practic de acces spre Veneția. Trimiteți solicitarea pentru confirmarea personală a detaliilor.' ), 'ru' => array( 'meta_title' => 'Прокат авто на Пьяццале Рома, Венеция | G&D Rent A Car', 'meta_description' => 'Организуйте прокат авто в районе Пьяццале Рома — удобной точки доступа в Венецию. Отправьте запрос для личного подтверждения деталей.' ) ),
            'treviso_hotel' => array( 'it' => array( 'meta_title' => 'Noleggio auto con ritiro in hotel a Treviso | G&D Rent A Car', 'meta_description' => 'Richiedi il noleggio auto per il tuo hotel a Treviso. Indica nome e indirizzo dell’hotel: il team conferma personalmente i dettagli del ritiro.' ), 'en' => array( 'meta_title' => 'Treviso hotel car rental pickup | G&D Rent A Car', 'meta_description' => 'Request car rental for your hotel in Treviso. Include the hotel name and address; our team personally confirms the pickup details.' ), 'ro' => array( 'meta_title' => 'Închirieri auto cu preluare la hotel în Treviso | G&D Rent A Car', 'meta_description' => 'Solicitați închirierea auto pentru hotelul din Treviso. Indicați numele și adresa hotelului; echipa confirmă personal detaliile preluării.' ), 'ru' => array( 'meta_title' => 'Прокат авто с получением у отеля в Тревизо | G&D Rent A Car', 'meta_description' => 'Запросите прокат авто для отеля в Тревизо. Укажите название и адрес отеля; команда лично подтвердит детали получения.' ) ),
            'venice_hotel' => array( 'it' => array( 'meta_title' => 'Noleggio auto con ritiro in hotel a Venezia | G&D Rent A Car', 'meta_description' => 'Richiedi il noleggio auto per il tuo hotel a Venezia. Indica nome e indirizzo dell’hotel: il team conferma personalmente i dettagli del servizio.' ), 'en' => array( 'meta_title' => 'Venice hotel car rental pickup | G&D Rent A Car', 'meta_description' => 'Request car rental for your hotel in Venice. Include the hotel name and address; our team personally confirms the service details.' ), 'ro' => array( 'meta_title' => 'Închirieri auto cu preluare la hotel în Veneția | G&D Rent A Car', 'meta_description' => 'Solicitați închirierea auto pentru hotelul din Veneția. Indicați numele și adresa hotelului; echipa confirmă personal detaliile serviciului.' ), 'ru' => array( 'meta_title' => 'Прокат авто с получением у отеля в Венеции | G&D Rent A Car', 'meta_description' => 'Запросите прокат авто для отеля в Венеции. Укажите название и адрес отеля; команда лично подтвердит детали сервиса.' ) ),
        );
        $selected = $metadata[ $key ][ $language ] ?? $metadata[ $key ]['en'];
        return array_merge( array( 'title' => $label, 'eyebrow' => rentacar_venezia_v2_landing_location_type_label( $type ), 'intro' => $intros[ $type ][ $language ] ?? $intros[ $type ]['en'] ?? '' ), $selected );
    }
    $copy = array(
        'pickup_locations' => array(
            'it' => array( 'title' => 'Punti di ritiro auto a Venezia e Treviso', 'eyebrow' => 'Ritiro auto', 'intro' => 'Scegli il punto di ritiro più adatto al tuo viaggio. Dopo la richiesta, confermiamo personalmente i dettagli pratici.' ),
            'en' => array( 'title' => 'Car rental pickup locations in Venice and Treviso', 'eyebrow' => 'Car pickup', 'intro' => 'Choose the pickup option that suits your journey. After your request, we confirm the practical details with you personally.' ),
            'ro' => array( 'title' => 'Locații de preluare a mașinii în Veneția și Treviso', 'eyebrow' => 'Preluare auto', 'intro' => 'Alegeți opțiunea de preluare potrivită călătoriei. După solicitare, confirmăm personal detaliile practice.' ),
            'ru' => array( 'title' => 'Места получения автомобиля в Венеции и Тревизо', 'eyebrow' => 'Получение автомобиля', 'intro' => 'Выберите вариант получения, подходящий вашей поездке. После запроса мы лично подтвердим все практические детали.' ),
        ),
        'economy' => array( 'it' => array( 'title' => 'Auto economiche a noleggio', 'intro' => 'Una selezione di auto con le tariffe giornaliere indicative più accessibili della flotta.' ), 'en' => array( 'title' => 'Economy rental cars', 'intro' => 'A selection of cars with the fleet’s most accessible indicative daily rates.' ), 'ro' => array( 'title' => 'Mașini economice de închiriat', 'intro' => 'O selecție de mașini cu cele mai accesibile tarife zilnice orientative din flotă.' ), 'ru' => array( 'title' => 'Экономичные автомобили напрокат', 'intro' => 'Подборка автомобилей с наиболее доступными ориентировочными дневными тарифами в автопарке.' ) ),
        'automatic' => array( 'it' => array( 'title' => 'Auto automatiche a noleggio', 'intro' => 'Consulta le auto della flotta con cambio automatico e invia le date per ricevere conferma.' ), 'en' => array( 'title' => 'Automatic rental cars', 'intro' => 'Browse the fleet’s automatic cars and send your dates to receive confirmation.' ), 'ro' => array( 'title' => 'Mașini automate de închiriat', 'intro' => 'Consultați mașinile automate din flotă și trimiteți datele pentru confirmare.' ), 'ru' => array( 'title' => 'Автомобили с автоматической коробкой', 'intro' => 'Посмотрите автомобили с автоматической коробкой в автопарке и отправьте даты для подтверждения.' ) ),
        'seven_seat' => array( 'it' => array( 'title' => 'Auto a 7 posti a noleggio', 'intro' => 'Veicoli della flotta con almeno sette posti per viaggi in famiglia o in gruppo.' ), 'en' => array( 'title' => '7-seat rental cars', 'intro' => 'Fleet vehicles with at least seven seats for family or group travel.' ), 'ro' => array( 'title' => 'Mașini de închiriat cu 7 locuri', 'intro' => 'Vehicule din flotă cu cel puțin șapte locuri pentru familii sau grupuri.' ), 'ru' => array( 'title' => 'Автомобили напрокат на 7 мест', 'intro' => 'Автомобили из автопарка минимум на семь мест для семейных и групповых поездок.' ) ),
        'nine_seat' => array( 'it' => array( 'title' => 'Auto a 9 posti a noleggio', 'intro' => 'Veicoli della flotta con almeno nove posti per gruppi che viaggiano insieme.' ), 'en' => array( 'title' => '9-seat rental cars', 'intro' => 'Fleet vehicles with at least nine seats for groups travelling together.' ), 'ro' => array( 'title' => 'Mașini de închiriat cu 9 locuri', 'intro' => 'Vehicule din flotă cu cel puțin nouă locuri pentru grupuri care călătoresc împreună.' ), 'ru' => array( 'title' => 'Автомобили напрокат на 9 мест', 'intro' => 'Автомобили из автопарка минимум на девять мест для групп, путешествующих вместе.' ) ),
        'family' => array( 'it' => array( 'title' => 'Auto familiari a noleggio', 'intro' => 'Una selezione di auto spaziose della flotta per chi viaggia con famiglia o bagagli.' ), 'en' => array( 'title' => 'Family rental cars', 'intro' => 'A selection of spacious fleet cars for travellers with family or luggage.' ), 'ro' => array( 'title' => 'Mașini de familie de închiriat', 'intro' => 'O selecție de mașini spațioase din flotă pentru călătorii cu familia sau bagaje.' ), 'ru' => array( 'title' => 'Семейные автомобили напрокат', 'intro' => 'Подборка просторных автомобилей из автопарка для поездок с семьёй или багажом.' ) ),
        'no_credit_card' => array( 'it' => array( 'title' => 'Noleggio auto senza carta di credito', 'intro' => 'Prenota senza carta di credito e senza deposito anticipato. Il deposito cauzionale viene richiesto al ritiro.', 'meta_title' => 'Noleggio auto senza carta di credito | G&D Rent A Car', 'meta_description' => 'Prenota senza carta di credito e senza deposito anticipato. Il deposito cauzionale viene richiesto al ritiro.' ), 'en' => array( 'title' => 'Car rental without a credit card', 'intro' => 'Reserve without a credit card or advance reservation deposit. A security deposit is required at pickup.', 'meta_title' => 'Car Rental Without a Credit Card | G&D Rent A Car', 'meta_description' => 'Reserve car rental in Venice or Treviso without a credit card or advance reservation deposit. A security deposit is required at pickup.' ), 'ro' => array( 'title' => 'Închirieri auto fără card de credit', 'intro' => 'Rezervați fără card de credit și fără avans la rezervare. La preluarea mașinii este necesar un depozit de garanție.', 'meta_title' => 'Închirieri auto fără card de credit | G&D Rent A Car', 'meta_description' => 'Rezervați fără card de credit și fără avans la rezervare. La preluarea mașinii este necesar un depozit de garanție.' ), 'ru' => array( 'title' => 'Прокат авто без кредитной карты', 'intro' => 'Бронируйте без кредитной карты и без предоплаты при бронировании. При получении автомобиля требуется залог.', 'meta_title' => 'Прокат авто без кредитной карты | G&D Rent A Car', 'meta_description' => 'Бронируйте без кредитной карты и без предоплаты. При получении автомобиля требуется залог.' ) ),
    );
    return $copy[ $key ][ $language ] ?? ( $copy[ $key ]['en'] ?? array() );
}

function rentacar_venezia_v2_intent_vehicles( $intent_key ) {
    if ( ! class_exists( 'Rentacar_Core_Vehicle_Repository' ) ) return array();
    $vehicles = ( new Rentacar_Core_Vehicle_Repository() )->query();
    $matches = array();
    foreach ( $vehicles as $vehicle ) {
        $price = rentacar_venezia_v2_vehicle_starting_price( $vehicle );
        $transmission = strtolower( (string) $vehicle->get( 'transmission' ) );
        $passengers = (int) $vehicle->get( 'passengers' );
        $matched = ( 'no_credit_card' === $intent_key ) || ( 'automatic' === $intent_key && ( false !== strpos( $transmission, 'auto' ) || false !== strpos( $transmission, 'automatic' ) ) )
            || ( 'seven_seat' === $intent_key && $passengers >= 7 )
            || ( 'nine_seat' === $intent_key && $passengers >= 9 )
            || ( 'family' === $intent_key && $passengers >= 5 )
            || ( 'economy' === $intent_key && null !== $price );
        if ( $matched ) $matches[] = $vehicle;
    }
    if ( 'economy' === $intent_key ) {
        usort( $matches, function( $a, $b ) { return rentacar_venezia_v2_vehicle_starting_price( $a ) <=> rentacar_venezia_v2_vehicle_starting_price( $b ); } );
        $matches = array_slice( $matches, 0, 6 );
    }
    // Keep intent pages useful as curated discovery pages on narrow screens;
    // the fleet CTA remains the complete inventory route.
    return array_slice( rentacar_venezia_v2_sort_fleet_vehicles( $matches ), 0, 12 );
}

function rentacar_venezia_v2_intent_is_eligible( $intent_key ) {
    $intent = rentacar_venezia_v2_rental_intents()[ $intent_key ] ?? array();
    if ( ! empty( $intent['policy_keys'] ) ) { if ( ! class_exists( 'Rentacar_Core_Marketing_Claim_Registry' ) ) return false; foreach ( $intent['policy_keys'] as $claim ) if ( ! Rentacar_Core_Marketing_Claim_Registry::enabled( $claim ) ) return false; }
    return empty( $intent['manual_review'] ) && count( rentacar_venezia_v2_intent_vehicles( $intent_key ) ) >= (int) ( $intent['minimum'] ?? PHP_INT_MAX );
}

function rentacar_venezia_v2_intent_page_id( $intent_key, $language = null ) {
    $pages = get_posts( array( 'post_type' => 'page', 'post_status' => 'publish', 'posts_per_page' => 1, 'fields' => 'ids', 'meta_key' => '_rentacar_intent_key', 'meta_value' => $intent_key, 'suppress_filters' => true, 'no_found_rows' => true ) );
    $page_id = $pages ? (int) $pages[0] : 0;
    return $page_id && function_exists( 'rentacar_venezia_v2_translated_post_id' ) ? rentacar_venezia_v2_translated_post_id( $page_id, $language ) : $page_id;
}

function rentacar_venezia_v2_intent_page_url( $intent_key, $language = null ) {
    $page_id = rentacar_venezia_v2_intent_page_id( $intent_key, $language );
    return $page_id ? get_permalink( $page_id ) : '';
}

function rentacar_venezia_v2_landing_related_locations( $exclude = '' ) {
    $items = array(); foreach ( rentacar_venezia_v2_pickup_locations() as $key => $location ) { if ( $key !== $exclude && rentacar_venezia_v2_location_page_url( $key ) ) $items[ $key ] = $location; }
    return $items;
}

function rentacar_venezia_v2_landing_guides( $relation_key = '' ) {
    $query = new WP_Query( array( 'post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => 3, 'meta_key' => '_rc_seo_indexable', 'meta_value' => '1', 'meta_query' => $relation_key ? array( array( 'key' => '_rentacar_related_keys', 'value' => $relation_key, 'compare' => 'LIKE' ) ) : array() ) );
    return $query->posts;
}

function rentacar_venezia_v2_render_landing_links( $items, $heading ) {
    if ( ! $items ) return;
    echo '<section class="landing-links"><h2>' . esc_html( $heading ) . '</h2><div class="landing-links__grid">';
    foreach ( $items as $key => $item ) echo '<a href="' . esc_url( rentacar_venezia_v2_location_page_url( $key ) ) . '"><strong>' . esc_html( $item['label'] ) . '</strong><span>' . esc_html( rentacar_venezia_v2_landing_location_type_label( $item['type'] ?? '' ) ) . '</span></a>';
    echo '</div></section>';
}

function rentacar_venezia_v2_landing_location_type_label( $type ) {
    $labels = array( 'airport' => __( 'Airport pickup', 'rentacar-venezia-v2' ), 'station' => __( 'Station pickup', 'rentacar-venezia-v2' ), 'city_access' => __( 'City access pickup', 'rentacar-venezia-v2' ), 'hotel' => __( 'Hotel pickup', 'rentacar-venezia-v2' ) );
    return $labels[ $type ] ?? '';
}

/** Safe, idempotent LocalWP provisioner. It creates no content unless --apply is explicit. */
function rentacar_venezia_v2_register_landing_page_cli() {
    if ( defined( 'WP_CLI' ) && WP_CLI ) {
        WP_CLI::add_command( 'rentacar seo locations', 'rentacar_venezia_v2_cli_provision_locations' );
        WP_CLI::add_command( 'rentacar seo intents', 'rentacar_venezia_v2_cli_provision_intents' );
        WP_CLI::add_command( 'rentacar seo noindex-legacy-pages', 'rentacar_venezia_v2_cli_noindex_legacy_pages' );
    }
}
add_action( 'cli_init', 'rentacar_venezia_v2_register_landing_page_cli', 20 );

function rentacar_venezia_v2_cli_create_translation_group( $records, $apply ) {
    if ( ! function_exists( 'pll_save_post_translations' ) || count( $records ) < 2 ) return;
    if ( $apply ) pll_save_post_translations( $records );
}

function rentacar_venezia_v2_cli_upsert_landing_page( $key, $language, $template, $parent_id, $apply, $meta_key = '' ) {
    $copy = rentacar_venezia_v2_landing_copy( $key, $language );
    $lookup_key = $meta_key ?: '_rc_provisioning_key';
    $lookup_value = $meta_key ? $key : $key;
    $existing = get_posts( array( 'post_type' => 'page', 'post_status' => 'any', 'posts_per_page' => 1, 'fields' => 'ids', 'meta_key' => $lookup_key, 'meta_value' => $lookup_value, 'lang' => $language, 'suppress_filters' => false, 'no_found_rows' => true ) );
    $id = $existing ? (int) $existing[0] : 0;
    if ( ! $id && $apply ) {
        $id = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => $copy['title'], 'post_name' => sanitize_title( $copy['title'] ), 'post_parent' => $parent_id ) );
        if ( function_exists( 'pll_set_post_language' ) ) pll_set_post_language( $id, $language );
        update_post_meta( $id, '_wp_page_template', $template ); update_post_meta( $id, '_rc_provisioning_key', $meta_key ? sanitize_key( ltrim( $meta_key, '_' ) ) . '_' . $key : $key ); update_post_meta( $id, '_rc_seo_indexable', '1' );
        if ( $meta_key ) update_post_meta( $id, $meta_key, $key );
        update_post_meta( $id, 'rank_math_title', $copy['meta_title'] ?? ( $copy['title'] . ' | ' . rentacar_venezia_v2_business_value( 'public_name' ) ) ); update_post_meta( $id, 'rank_math_description', $copy['meta_description'] ?? $copy['intro'] );
    }
    if ( $id && $apply ) {
        // Existing airport/editor pages retain their title and body; only the
        // controlled template, keys and absent generated metadata are aligned.
        update_post_meta( $id, '_wp_page_template', $template ); update_post_meta( $id, '_rc_seo_indexable', '1' );
        if ( $meta_key ) update_post_meta( $id, $meta_key, $key );
        if ( ! get_post_meta( $id, '_rc_provisioning_key', true ) ) update_post_meta( $id, '_rc_provisioning_key', sanitize_key( ltrim( $meta_key, '_' ) ) . '_' . $key );
        if ( ! get_post_meta( $id, 'rank_math_title', true ) ) update_post_meta( $id, 'rank_math_title', $copy['meta_title'] ?? ( $copy['title'] . ' | ' . rentacar_venezia_v2_business_value( 'public_name' ) ) );
        if ( ! get_post_meta( $id, 'rank_math_description', true ) ) update_post_meta( $id, 'rank_math_description', $copy['meta_description'] ?? $copy['intro'] );
    }
    WP_CLI::log( sprintf( '%s %s: %s', strtoupper( $language ), $key, $id ? 'existing #' . $id : 'would create' ) );
    return $id;
}

function rentacar_venezia_v2_cli_provision_locations( $args, $assoc_args ) {
    $apply = ! empty( $assoc_args['apply'] ); $languages = array( 'it', 'en', 'ro', 'ru' ); $hub = array();
    foreach ( $languages as $language ) $hub[ $language ] = rentacar_venezia_v2_cli_upsert_landing_page( 'pickup_locations', $language, 'page-templates/template-pickup-locations.php', 0, $apply );
    rentacar_venezia_v2_cli_create_translation_group( array_filter( $hub ), $apply );
    foreach ( rentacar_venezia_v2_pickup_locations() as $key => $location ) { $group = array(); foreach ( $languages as $language ) $group[ $language ] = rentacar_venezia_v2_cli_upsert_landing_page( $key, $language, 'page-templates/template-location.php', $hub[ $language ] ?? 0, $apply, '_rentacar_location_key' ); rentacar_venezia_v2_cli_create_translation_group( array_filter( $group ), $apply ); }
    if ( $apply && function_exists( 'rentacar_venezia_v2_invalidate_rank_math_sitemap_cache' ) ) rentacar_venezia_v2_invalidate_rank_math_sitemap_cache();
    WP_CLI::success( $apply ? 'Location pages provisioned.' : 'Dry run only; add --apply to create missing location pages.' );
}

function rentacar_venezia_v2_cli_provision_intents( $args, $assoc_args ) {
    $apply = ! empty( $assoc_args['apply'] ); $languages = array( 'it', 'en', 'ro', 'ru' );
    foreach ( rentacar_venezia_v2_rental_intents() as $key => $intent ) { if ( ! rentacar_venezia_v2_intent_is_eligible( $key ) ) { WP_CLI::warning( $key . ': not provisioned (inventory threshold or verified policy requirement).' ); continue; } $group = array(); foreach ( $languages as $language ) $group[ $language ] = rentacar_venezia_v2_cli_upsert_landing_page( $key, $language, 'page-templates/template-rental-option.php', 0, $apply, '_rentacar_intent_key' ); rentacar_venezia_v2_cli_create_translation_group( array_filter( $group ), $apply ); }
    if ( $apply && function_exists( 'rentacar_venezia_v2_invalidate_rank_math_sitemap_cache' ) ) rentacar_venezia_v2_invalidate_rank_math_sitemap_cache();
    WP_CLI::success( $apply ? 'Eligible rental-option pages provisioned.' : 'Dry run only; add --apply to create eligible pages.' );
}

/**
 * Persists the render-time noindex decision for legacy pages so Rank Math can
 * exclude them before generating its cached sitemap query.
 */
function rentacar_venezia_v2_cli_noindex_legacy_pages( $args, $assoc_args ) {
    $apply = ! empty( $assoc_args['apply'] );
    $page_ids = get_posts( array( 'post_type' => 'page', 'post_status' => 'publish', 'posts_per_page' => -1, 'fields' => 'ids', 'suppress_filters' => true, 'no_found_rows' => true ) );
    $updated = 0;

    foreach ( $page_ids as $page_id ) {
        $page_id = absint( $page_id );
        if ( ! $page_id || rentacar_venezia_v2_is_indexable_page_id( $page_id ) ) continue;

        $robots = get_post_meta( $page_id, 'rank_math_robots', true );
        $robots = is_array( $robots ) ? $robots : array_filter( array_map( 'trim', explode( ',', (string) $robots ) ) );
        if ( in_array( 'noindex', $robots, true ) ) continue;

        WP_CLI::log( sprintf( '%s #%d: %s', $apply ? 'noindexed' : 'would noindex', $page_id, get_the_title( $page_id ) ) );
        if ( ! $apply ) continue;

        $robots[] = 'noindex';
        $robots[] = 'follow';
        $robots = array_values( array_unique( array_diff( $robots, array( 'index', 'nofollow' ) ) ) );
        update_post_meta( $page_id, 'rank_math_robots', $robots );
        $updated++;
    }

    if ( $apply && $updated && function_exists( 'rentacar_venezia_v2_invalidate_rank_math_sitemap_cache' ) ) rentacar_venezia_v2_invalidate_rank_math_sitemap_cache();
    WP_CLI::success( $apply ? sprintf( 'Persisted noindex,follow for %d legacy pages.', $updated ) : 'Dry run only; add --apply to persist legacy noindex metadata.' );
}
