<?php
/**
 * Local-only, idempotent Yoast metadata optimizer for the approved public
 * architecture. Run with RENTACAR_LOCAL_APPLY=1 after reviewing the dry run.
 */
defined( 'ABSPATH' ) || exit( 1 );

$arguments = isset( $assoc_args ) && is_array( $assoc_args ) ? $assoc_args : array();
$apply     = ! empty( $arguments['apply'] ) || '1' === getenv( 'RENTACAR_LOCAL_APPLY' );

$descriptions = array(
    'it' => array(
        'home'                 => 'Noleggio auto a Venezia e Treviso con ritiro in aeroporto e assistenza locale. Scopri la flotta e invia le date del tuo viaggio.',
        'fleet'                => 'Auto a noleggio a Venezia e Treviso: scopri la flotta e invia una richiesta per le tue date e il punto di ritiro.',
        'how_it_works'         => 'Scopri come richiedere un noleggio auto a Venezia o Treviso: scegli l’auto, inserisci le date e ricevi tutti i dettagli.',
        'rental_requirements'  => 'Consulta i requisiti per il noleggio auto a Venezia e Treviso: età, patente, deposito, chilometraggio, ritiro e riconsegna.',
        'terms'                => 'Termini e condizioni per il noleggio auto a Venezia e Treviso: richiesta, pagamento, deposito, assicurazione e riconsegna.',
        'guides'               => 'Guide pratiche per Venezia e Treviso: ritiro in aeroporto, guida, parcheggio e preparazione del tuo noleggio auto.',
        'venice_marco_polo'    => 'Noleggio auto all’Aeroporto di Venezia Marco Polo: scopri la flotta e invia una richiesta per il ritiro vicino al terminal.',
        'treviso_airport'      => 'Noleggio auto all’Aeroporto di Treviso: scopri la flotta e invia una richiesta per il ritiro vicino al terminal.',
        'faq'                  => 'Risposte alle domande frequenti sul noleggio auto a Venezia e Treviso: richiesta, ritiro, pagamento e condizioni.',
        'contact'              => 'Contatta Rent a Car Venezia per informazioni su noleggio auto, ritiro a Venezia e Treviso e richieste di disponibilità.',
        'privacy_policy'       => 'Informativa privacy di Rent a Car Venezia per richieste di contatto e noleggio auto.',
        'cookie_policy'        => 'Cookie Policy di Rent a Car Venezia: tecnologie utilizzate, preferenze e controllo del consenso.',
    ),
    'en' => array(
        'home'                 => 'Rent a car in Venice or Treviso with airport pickup, local support and a straightforward request. Explore the fleet and send your travel dates.',
        'fleet'                => 'Car rental in Venice and Treviso: explore the fleet and send a request with your dates and pickup location.',
        'how_it_works'         => 'Learn how to request car rental in Venice or Treviso: choose a car, add your dates and receive the details.',
        'rental_requirements'  => 'Read Venice and Treviso car rental requirements: age, driving licence, deposit, mileage, pickup and return.',
        'terms'                => 'Terms and conditions for car rental in Venice and Treviso: requests, payment, deposit, insurance and return.',
        'guides'               => 'Practical Venice and Treviso guides for airport pickup, driving, parking and preparing your car rental request.',
        'venice_marco_polo'    => 'Venice Marco Polo Airport car rental: explore the fleet and send a request for pickup near the terminal.',
        'treviso_airport'      => 'Treviso Airport car rental: explore the fleet and send a request for pickup near the terminal.',
        'faq'                  => 'Answers to frequently asked questions about car rental in Venice and Treviso: requests, pickup, payment and conditions.',
        'contact'              => 'Contact Rent a Car Venezia for car rental information, Venice and Treviso pickup, and availability requests.',
        'privacy_policy'       => 'Rent a Car Venezia privacy information for contact and car rental requests.',
        'cookie_policy'        => 'Rent a Car Venezia Cookie Policy: technologies used, preferences and consent controls.',
    ),
    'ro' => array(
        'home'                 => 'Închirieri auto în Veneția și Treviso cu preluare de la aeroport și asistență locală. Descoperiți flota și trimiteți datele călătoriei.',
        'fleet'                => 'Mașini de închiriat în Veneția și Treviso: descoperiți flota și trimiteți o solicitare cu datele și locația de preluare.',
        'how_it_works'         => 'Aflați cum trimiteți o solicitare de închiriere auto în Veneția sau Treviso: alegeți mașina și introduceți datele călătoriei.',
        'rental_requirements'  => 'Consultați condițiile pentru închiriere auto în Veneția și Treviso: vârstă, permis, depozit, kilometraj, preluare și returnare.',
        'terms'                => 'Termeni și condiții pentru închiriere auto în Veneția și Treviso: solicitări, plată, depozit, asigurare și returnare.',
        'guides'               => 'Ghiduri practice pentru Veneția și Treviso: preluare de la aeroport, condus, parcare și pregătirea solicitării de închiriere.',
        'venice_marco_polo'    => 'Închirieri auto la Aeroportul Veneția Marco Polo: descoperiți flota și trimiteți o solicitare pentru preluare lângă terminal.',
        'treviso_airport'      => 'Închirieri auto la Aeroportul Treviso: descoperiți flota și trimiteți o solicitare pentru preluare lângă terminal.',
        'faq'                  => 'Răspunsuri la întrebări frecvente despre închirierea auto în Veneția și Treviso: solicitări, preluare, plată și condiții.',
        'contact'              => 'Contactați Rent a Car Venezia pentru informații despre închirieri auto, preluare în Veneția și Treviso și solicitări de disponibilitate.',
        'privacy_policy'       => 'Informații privind confidențialitatea Rent a Car Venezia pentru solicitări de contact și închiriere auto.',
        'cookie_policy'        => 'Politica privind cookie-urile Rent a Car Venezia: tehnologii utilizate, preferințe și controale de consimțământ.',
    ),
    'ru' => array(
        'home'                 => 'Прокат автомобилей в Венеции и Тревизо с получением в аэропорту и местной поддержкой. Посмотрите автопарк и отправьте даты поездки.',
        'fleet'                => 'Прокат автомобилей в Венеции и Тревизо: посмотрите автопарк и отправьте запрос с датами и местом получения.',
        'how_it_works'         => 'Узнайте, как отправить запрос на прокат автомобиля в Венеции или Тревизо: выберите автомобиль и укажите даты поездки.',
        'rental_requirements'  => 'Условия проката авто в Венеции и Тревизо: возраст, водительские права, депозит, пробег, получение и возврат.',
        'terms'                => 'Условия проката авто в Венеции и Тревизо: запросы, оплата, депозит, страховка и возврат.',
        'guides'               => 'Практические путеводители по Венеции и Тревизо: получение в аэропорту, вождение, парковка и подготовка запроса.',
        'venice_marco_polo'    => 'Прокат авто в аэропорту Венеция Марко Поло: посмотрите автопарк и отправьте запрос на получение рядом с терминалом.',
        'treviso_airport'      => 'Прокат авто в аэропорту Тревизо: посмотрите автопарк и отправьте запрос на получение рядом с терминалом.',
        'faq'                  => 'Ответы на частые вопросы о прокате авто в Венеции и Тревизо: запросы, получение, оплата и условия.',
        'contact'              => 'Свяжитесь с Rent a Car Venezia по вопросам проката авто, получения в Венеции и Тревизо и запросов о доступности.',
        'privacy_policy'       => 'Политика конфиденциальности Rent a Car Venezia для запросов на контакт и прокат автомобиля.',
        'cookie_policy'        => 'Политика использования файлов cookie Rent a Car Venezia: используемые технологии, предпочтения и управление согласием.',
    ),
);

$home_titles = array(
    'it' => 'Noleggio auto Venezia e Treviso | Rent a Car Venezia',
    'en' => 'Car Rental Venice & Treviso | Rent a Car Venezia',
    'ro' => 'Închirieri auto Veneția și Treviso | Rent a Car Venezia',
    'ru' => 'Прокат авто в Венеции и Тревизо | Rent a Car Venezia',
);

/** Update once, then preserve a title or description changed by an editor. */
function rentacar_local_update_generated_seo_meta( $post_id, $field, $value, $apply ) {
    $meta_key   = '_yoast_wpseo_' . $field;
    $marker_key = '_rc_seo_generated_' . $field;
    $current    = (string) get_post_meta( $post_id, $meta_key, true );
    $previous   = (string) get_post_meta( $post_id, $marker_key, true );

    if ( '' !== $previous && $current !== $previous ) {
        return 'owner value preserved';
    }

    if ( $apply ) {
        update_post_meta( $post_id, $meta_key, $value );
        update_post_meta( $post_id, $marker_key, $value );
    }

    return '' === $current ? 'would add' : 'would replace generated value';
}

$front_ids = array_filter( array_unique( array_merge( array( (int) get_option( 'page_on_front' ) ), function_exists( 'pll_get_post_translations' ) ? array_values( (array) pll_get_post_translations( (int) get_option( 'page_on_front' ) ) ) : array() ) ) );
$pages     = get_posts( array( 'post_type' => 'page', 'post_status' => 'publish', 'posts_per_page' => -1 ) );
$report    = array();

foreach ( $pages as $page ) {
    $language = function_exists( 'pll_get_post_language' ) ? (string) pll_get_post_language( $page->ID, 'slug' ) : 'it';
    $key      = in_array( (int) $page->ID, $front_ids, true ) ? 'home' : (string) get_post_meta( $page->ID, '_rc_provisioning_key', true );
    if ( '' === $key ) {
        $template_keys = array(
            'page-templates/template-contact.php' => 'contact',
            'page-templates/template-faq.php'     => 'faq',
        );
        $template = (string) get_post_meta( $page->ID, '_wp_page_template', true );
        $key      = isset( $template_keys[ $template ] ) ? $template_keys[ $template ] : '';
    }
    if ( empty( $descriptions[ $language ][ $key ] ) ) {
        continue;
    }

    $title = 'home' === $key ? $home_titles[ $language ] : $page->post_title . ' | Rent a Car Venezia';
    $title_status = rentacar_local_update_generated_seo_meta( $page->ID, 'title', $title, $apply );
    $description_status = rentacar_local_update_generated_seo_meta( $page->ID, 'metadesc', $descriptions[ $language ][ $key ], $apply );
    if ( $apply ) {
        update_post_meta( $page->ID, '_rc_seo_indexable', '1' );
    }
    $report[] = sprintf( '%d (%s/%s): title %s; description %s', $page->ID, $language, $key, $title_status, $description_status );
}

if ( defined( 'WP_CLI' ) && WP_CLI ) {
    WP_CLI::success( ( $apply ? 'Applied' : 'Dry run' ) . ': ' . implode( '; ', $report ) );
}
