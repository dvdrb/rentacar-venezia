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
        // A payment-policy claim is not inferred from a vehicle record.
        'no_credit_card' => array( 'minimum' => PHP_INT_MAX, 'match' => 'manual_policy', 'manual_review' => true ),
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
        return array( 'title' => $label . ' — ' . rentacar_venezia_v2_business_value( 'public_name' ), 'eyebrow' => rentacar_venezia_v2_landing_location_type_label( $type ), 'intro' => $intros[ $type ][ $language ] ?? $intros[ $type ]['en'] ?? '' );
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
        $matched = ( 'automatic' === $intent_key && ( false !== strpos( $transmission, 'auto' ) || false !== strpos( $transmission, 'automatic' ) ) )
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
    return rentacar_venezia_v2_sort_fleet_vehicles( $matches );
}

function rentacar_venezia_v2_intent_is_eligible( $intent_key ) {
    $intent = rentacar_venezia_v2_rental_intents()[ $intent_key ] ?? array();
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
        update_post_meta( $id, 'rank_math_title', $copy['title'] . ' | ' . rentacar_venezia_v2_business_value( 'public_name' ) ); update_post_meta( $id, 'rank_math_description', $copy['intro'] );
    }
    if ( $id && $apply ) {
        // Existing airport/editor pages retain their title and body; only the
        // controlled template, keys and absent generated metadata are aligned.
        update_post_meta( $id, '_wp_page_template', $template ); update_post_meta( $id, '_rc_seo_indexable', '1' );
        if ( $meta_key ) update_post_meta( $id, $meta_key, $key );
        if ( ! get_post_meta( $id, '_rc_provisioning_key', true ) ) update_post_meta( $id, '_rc_provisioning_key', sanitize_key( ltrim( $meta_key, '_' ) ) . '_' . $key );
        if ( ! get_post_meta( $id, 'rank_math_title', true ) ) update_post_meta( $id, 'rank_math_title', $copy['title'] . ' | ' . rentacar_venezia_v2_business_value( 'public_name' ) );
        if ( ! get_post_meta( $id, 'rank_math_description', true ) ) update_post_meta( $id, 'rank_math_description', $copy['intro'] );
    }
    WP_CLI::log( sprintf( '%s %s: %s', strtoupper( $language ), $key, $id ? 'existing #' . $id : 'would create' ) );
    return $id;
}

function rentacar_venezia_v2_cli_provision_locations( $args, $assoc_args ) {
    $apply = ! empty( $assoc_args['apply'] ); $languages = array( 'it', 'en', 'ro', 'ru' ); $hub = array();
    foreach ( $languages as $language ) $hub[ $language ] = rentacar_venezia_v2_cli_upsert_landing_page( 'pickup_locations', $language, 'page-templates/template-pickup-locations.php', 0, $apply );
    rentacar_venezia_v2_cli_create_translation_group( array_filter( $hub ), $apply );
    foreach ( rentacar_venezia_v2_pickup_locations() as $key => $location ) { $group = array(); foreach ( $languages as $language ) $group[ $language ] = rentacar_venezia_v2_cli_upsert_landing_page( $key, $language, 'page-templates/template-location.php', $hub[ $language ] ?? 0, $apply, '_rentacar_location_key' ); rentacar_venezia_v2_cli_create_translation_group( array_filter( $group ), $apply ); }
    WP_CLI::success( $apply ? 'Location pages provisioned.' : 'Dry run only; add --apply to create missing location pages.' );
}

function rentacar_venezia_v2_cli_provision_intents( $args, $assoc_args ) {
    $apply = ! empty( $assoc_args['apply'] ); $languages = array( 'it', 'en', 'ro', 'ru' );
    foreach ( rentacar_venezia_v2_rental_intents() as $key => $intent ) { if ( ! rentacar_venezia_v2_intent_is_eligible( $key ) ) { WP_CLI::warning( $key . ': not provisioned (inventory threshold or verified policy requirement).' ); continue; } $group = array(); foreach ( $languages as $language ) $group[ $language ] = rentacar_venezia_v2_cli_upsert_landing_page( $key, $language, 'page-templates/template-rental-option.php', 0, $apply, '_rentacar_intent_key' ); rentacar_venezia_v2_cli_create_translation_group( array_filter( $group ), $apply ); }
    WP_CLI::success( $apply ? 'Eligible rental-option pages provisioned.' : 'Dry run only; add --apply to create eligible pages.' );
}
