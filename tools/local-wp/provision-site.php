<?php
/** Local-only idempotent page provisioner. Set RENTACAR_LOCAL_APPLY=1 to make changes. */
defined( 'ABSPATH' ) || exit( 1 );

$version = '2026-07-26.1';
$arguments = isset( $assoc_args ) && is_array( $assoc_args ) ? $assoc_args : array();
$apply = ! empty( $arguments['apply'] ) || '1' === getenv( 'RENTACAR_LOCAL_APPLY' );
$pages = array(
    'fleet' => array( 'title' => 'Fleet', 'slug' => 'fleet', 'template' => 'page-templates/template-fleet.php', 'content' => '<h2>Rental cars in Venice and Treviso</h2><p>Choose your preferred vehicle and send a request. Availability, final price and rental conditions are confirmed personally.</p>' ),
    'how_it_works' => array( 'title' => 'How it works', 'slug' => 'how-it-works', 'template' => 'page-templates/template-how-it-works.php', 'content' => '<h2>Send a rental request in clear steps</h2><ol><li>Choose your preferred vehicle.</li><li>Add pickup, return and airport flight details where applicable.</li><li>Select insurance and optional extras.</li><li>Send the request without payment.</li><li>Our team checks availability and confirms final price and rental conditions personally.</li><li>Confirm directly with our team before collection.</li></ol><p><strong>Submitting this request does not immediately confirm the reservation. We will check availability and contact you.</strong></p>' ),
    'rental_requirements' => array( 'title' => 'Rental requirements', 'slug' => 'rental-requirements', 'template' => 'page-templates/template-rental-requirements.php', 'content' => '<h2>Before you request a vehicle</h2><p>Drivers must be at least 23, hold a valid category B licence for at least three years, and present an original valid licence plus passport or national identity card. Non-EU/EEA licences may require an international driving permit or official Italian translation.</p><h2>Payment, deposit and mileage</h2><p>No payment is required to send a request. Payment and deposit are paid at pickup. Prices include VAT and RCA. Cards are accepted. The deposit is €350 for vehicles up to five seats and €500 for vehicles with seven to nine seats. 150 km are included for each rental day; additional kilometres cost €0.10 each.</p>' ),
    'faq' => array( 'title' => 'Frequently asked questions', 'slug' => 'faq', 'template' => 'page-templates/template-faq.php', 'content' => '<h2>Reservation process</h2><details><summary>Is my request a confirmed reservation?</summary><p>No. We check availability, final price and rental conditions personally before confirmation.</p></details><details><summary>Is payment required to send a request?</summary><p>No payment is required to send a request. Payment is made at pickup.</p></details><h2>Airport pickup</h2><details><summary>Do you need my flight number?</summary><p>For airport pickup, please provide a valid flight number so the team can monitor your arrival.</p></details>' ),
    'contact' => array( 'title' => 'Contact', 'slug' => 'contact', 'template' => 'page-templates/template-contact.php', 'content' => '<h2>Contact Rent a Car Venezia</h2><p>Phone and WhatsApp: <a href="tel:+393445068823">+39 344 506 8823</a><br>Email: <a href="mailto:info@rentacarvenezia.it">info@rentacarvenezia.it</a><br>Office hours: Monday–Friday, 08:00–17:00.</p><p>Requests can be sent online at any time. For a reservation request, please use the vehicle request flow.</p>' ),
    'terms' => array( 'title' => 'Terms and Conditions', 'slug' => 'terms-and-conditions', 'template' => 'page-templates/template-terms.php', 'content' => '<h2>Rental terms</h2><p>Requests are subject to availability and personal confirmation. Free cancellation is available up to 24 hours before pickup; later cancellation or no-show may result in consequences communicated in the confirmation.</p><h2>Vehicle use and return</h2><p>Drivers must obey traffic, ZTL, parking and toll rules. Cross-border travel requires advance authorization. Report accidents, theft or breakdowns immediately and do not arrange repairs without authorization. Return instructions are confirmed personally.</p><h2>Insurance and deposit</h2><p>Insurance options, exclusions and remaining responsibilities are confirmed in the rental contract. Damage-protection limits are not deductibles. Deposits are released after return inspection, subject to bank processing time.</p>' ),
    'guides' => array( 'title' => 'Guides', 'slug' => 'guides', 'template' => '', 'content' => '<h2>Venice and Treviso travel guides</h2><p>Practical guidance for airport pickup, driving, parking and rental preparation.</p>' ),
    'venice_marco_polo' => array( 'title' => 'Venice Marco Polo Airport car rental', 'slug' => 'venice-marco-polo-airport-car-rental', 'template' => 'page-templates/template-airport-location.php', 'location_key' => 'venice_marco_polo', 'content' => '<h2>Car rental at Venice Marco Polo Airport</h2><p>Pickup is arranged personally near Venice Marco Polo Airport, Viale Galileo Galilei 30, 30173 Venezia VE, Italy. Send your preferred vehicle and flight details; we confirm the practical details before the reservation is final.</p>' ),
    'treviso_airport' => array( 'title' => 'Treviso Airport car rental', 'slug' => 'treviso-airport-car-rental', 'template' => 'page-templates/template-airport-location.php', 'location_key' => 'treviso_airport', 'content' => '<h2>Car rental at Treviso Airport</h2><p>Pickup is arranged personally for Treviso Airport, Via Noalese 63/E, 31100 Treviso, Italy. Send your preferred vehicle and flight details; we confirm the practical details before the reservation is final.</p>' ),
);
$report = array();
$localized_pages = array(
    'en' => array(),
    'ro' => array(
        'fleet' => array( 'title' => 'Flota', 'slug' => 'flota', 'content' => '<h2>Mașini de închiriat în Veneția și Treviso</h2><p>Alegeți vehiculul preferat și trimiteți o solicitare. Disponibilitatea, prețul final și condițiile sunt confirmate personal.</p>' ),
        'how_it_works' => array( 'title' => 'Cum funcționează', 'slug' => 'cum-functioneaza', 'content' => '<h2>O solicitare simplă, confirmată personal</h2><p>Alegeți o mașină, introduceți detaliile călătoriei și trimiteți solicitarea fără plată. Echipa noastră confirmă personal disponibilitatea, prețul final și condițiile.</p>' ),
        'rental_requirements' => array( 'title' => 'Condiții de închiriere', 'slug' => 'conditii-inchiriere', 'content' => '<h2>Înainte de solicitare</h2><p>Șoferii trebuie să aibă cel puțin 23 de ani și permis categoria B de minimum trei ani. Plata și depozitul se achită la preluare; prețurile includ TVA și RCA.</p>' ),
        'terms' => array( 'title' => 'Termeni și condiții', 'slug' => 'termeni-conditii', 'content' => '<h2>Condiții de închiriere</h2><p>Solicitările sunt supuse disponibilității și confirmării personale. Anularea este gratuită până la 24 de ore înainte de preluare.</p>' ),
        'guides' => array( 'title' => 'Ghiduri', 'slug' => 'ghiduri', 'content' => '<h2>Ghiduri pentru Veneția și Treviso</h2><p>Informații practice pentru preluarea de la aeroport, condus și pregătirea solicitării de închiriere.</p>' ),
        'venice_marco_polo' => array( 'title' => 'Închirieri auto Aeroportul Veneția Marco Polo', 'slug' => 'inchirieri-auto-aeroport-venetia-marco-polo', 'content' => '<h2>Preluare la Aeroportul Veneția Marco Polo</h2><p>Preluarea este organizată personal lângă Viale Galileo Galilei 30, 30173 Venezia VE, Italia.</p>' ),
        'treviso_airport' => array( 'title' => 'Închirieri auto Aeroportul Treviso', 'slug' => 'inchirieri-auto-aeroport-treviso', 'content' => '<h2>Preluare la Aeroportul Treviso</h2><p>Preluarea este organizată personal la Via Noalese 63/E, 31100 Treviso, Italia.</p>' ),
    ),
    'ru' => array(
        'fleet' => array( 'title' => 'Автопарк', 'slug' => 'avtopark', 'content' => '<h2>Прокат автомобилей в Венеции и Тревизо</h2><p>Выберите автомобиль и отправьте запрос. Доступность, окончательная цена и условия подтверждаются лично.</p>' ),
        'how_it_works' => array( 'title' => 'Как это работает', 'slug' => 'kak-eto-rabotaet', 'content' => '<h2>Простой запрос с личным подтверждением</h2><p>Выберите автомобиль, укажите детали поездки и отправьте запрос без оплаты. Наша команда лично подтверждает доступность, цену и условия.</p>' ),
        'rental_requirements' => array( 'title' => 'Условия аренды', 'slug' => 'usloviya-arendy', 'content' => '<h2>Перед отправкой запроса</h2><p>Водителю должно быть не менее 23 лет, а права категории B должны быть выданы не менее трёх лет назад. Оплата и депозит производятся при получении; цены включают НДС и RCA.</p>' ),
        'terms' => array( 'title' => 'Условия и положения', 'slug' => 'usloviya-i-polozheniya', 'content' => '<h2>Условия аренды</h2><p>Все запросы зависят от доступности и личного подтверждения. Бесплатная отмена возможна не позднее чем за 24 часа до получения.</p>' ),
        'guides' => array( 'title' => 'Путеводители', 'slug' => 'putevoditeli', 'content' => '<h2>Путеводители по Венеции и Тревизо</h2><p>Практическая информация о получении в аэропорту, вождении и подготовке запроса на аренду.</p>' ),
        'venice_marco_polo' => array( 'title' => 'Прокат авто в аэропорту Венеция Марко Поло', 'slug' => 'prokat-avto-aeroport-veneciya-marko-polo', 'content' => '<h2>Получение в аэропорту Венеция Марко Поло</h2><p>Получение организуется лично рядом с Viale Galileo Galilei 30, 30173 Venezia VE, Италия.</p>' ),
        'treviso_airport' => array( 'title' => 'Прокат авто в аэропорту Тревизо', 'slug' => 'prokat-avto-aeroport-treviso', 'content' => '<h2>Получение в аэропорту Тревизо</h2><p>Получение организуется лично по адресу Via Noalese 63/E, 31100 Treviso, Италия.</p>' ),
    ),
);
foreach ( $pages as $key => $page ) {
    $existing = get_posts( array( 'post_type' => 'page', 'post_status' => 'any', 'posts_per_page' => 1, 'meta_key' => '_rc_provisioning_key', 'meta_value' => $key, 'fields' => 'ids' ) );
    if ( ! $existing ) $existing = get_posts( array( 'post_type' => 'page', 'post_status' => 'any', 'name' => $page['slug'], 'posts_per_page' => 1, 'fields' => 'ids' ) );
    $post = array( 'post_title' => $page['title'], 'post_name' => $page['slug'], 'post_status' => 'publish', 'post_type' => 'page' );
    if ( $existing ) {
        $post['ID'] = (int) $existing[0];
        $content_is_empty = '' === trim( (string) get_post_field( 'post_content', $post['ID'] ) );
        if ( $content_is_empty ) {
            $post['post_content'] = $page['content'];
        }
        if ( ! $apply ) {
            $report[] = $key . ': would update ' . $post['ID'] . ( $content_is_empty ? ' (empty content populated)' : ' (existing content preserved)' );
            continue;
        }
        $id = wp_update_post( wp_slash( $post ), true );
    } else {
        if ( ! $apply ) {
            $report[] = $key . ': would create';
            continue;
        }
        $post['post_content'] = $page['content'];
        $id = wp_insert_post( wp_slash( $post ), true );
    }
    if ( is_wp_error( $id ) ) { $report[] = $key . ': ' . $id->get_error_message(); continue; }
    update_post_meta( $id, '_rc_provisioning_key', $key );
    update_post_meta( $id, '_rc_provisioning_version', $version );
    if ( $page['template'] ) update_post_meta( $id, '_wp_page_template', $page['template'] );
    if ( ! empty( $page['location_key'] ) ) update_post_meta( $id, '_rentacar_location_key', $page['location_key'] );
    $report[] = $key . ': ' . $id;
}

if ( function_exists( 'pll_set_post_language' ) && function_exists( 'pll_save_post_translations' ) ) {
    foreach ( $pages as $key => $page ) {
        $source = get_posts( array( 'post_type' => 'page', 'post_status' => 'publish', 'posts_per_page' => 1, 'meta_key' => '_rc_provisioning_key', 'meta_value' => $key, 'fields' => 'ids' ) );
        if ( ! $source ) continue;
        $source_id = (int) $source[0];
        $translations = (array) pll_get_post_translations( $source_id );
        foreach ( array( 'en', 'ro', 'ru' ) as $language ) {
            if ( ! empty( $translations[ $language ] ) ) continue;
            $copy = isset( $localized_pages[ $language ][ $key ] ) ? $localized_pages[ $language ][ $key ] : array( 'title' => $page['title'], 'slug' => $page['slug'], 'content' => $page['content'] );
            if ( ! $apply ) { $report[] = $key . ': would create ' . $language . ' translation'; continue; }
            $id = wp_insert_post( wp_slash( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => $copy['title'], 'post_name' => $copy['slug'], 'post_content' => $copy['content'] ) ), true );
            if ( is_wp_error( $id ) ) { $report[] = $key . ': ' . $language . ' ' . $id->get_error_message(); continue; }
            pll_set_post_language( $id, $language ); update_post_meta( $id, '_rc_provisioning_key', $key ); update_post_meta( $id, '_rc_provisioning_version', $version ); if ( $page['template'] ) update_post_meta( $id, '_wp_page_template', $page['template'] ); if ( ! empty( $page['location_key'] ) ) update_post_meta( $id, '_rentacar_location_key', $page['location_key'] ); $translations[ $language ] = $id; pll_save_post_translations( $translations ); $report[] = $key . ': created ' . $language . ' ' . $id;
        }
    }
}

if ( function_exists( 'wp_create_nav_menu' ) && function_exists( 'pll_get_post' ) ) {
    $menu_keys = array(
        'primary' => array( 'fleet', 'venice_marco_polo', 'treviso_airport', 'how_it_works', 'faq', 'guides', 'contact' ),
        'footer'  => array( 'fleet', 'how_it_works', 'rental_requirements', 'faq', 'guides', 'contact', 'terms' ),
    );
    foreach ( array( 'it', 'en', 'ro', 'ru' ) as $language ) {
        foreach ( $menu_keys as $location => $keys ) {
            $menu_name = 'Rentacar ' . ucfirst( $location ) . ' ' . strtoupper( $language );
            $menu = wp_get_nav_menu_object( $menu_name );
            if ( ! $menu && ! $apply ) { $report[] = 'would create ' . $menu_name; continue; }
            $menu_id = $menu ? (int) $menu->term_id : wp_create_nav_menu( $menu_name );
            if ( is_wp_error( $menu_id ) ) { $report[] = $menu_name . ': ' . $menu_id->get_error_message(); continue; }
            if ( function_exists( 'pll_set_term_language' ) ) pll_set_term_language( $menu_id, $language );
            if ( ! wp_get_nav_menu_items( $menu_id ) ) foreach ( $keys as $position => $key ) {
                $source = get_posts( array( 'post_type' => 'page', 'post_status' => 'publish', 'posts_per_page' => 1, 'meta_key' => '_rc_provisioning_key', 'meta_value' => $key, 'fields' => 'ids' ) );
                $page_id = $source ? (int) pll_get_post( $source[0], $language ) : 0;
                if ( $page_id ) wp_update_nav_menu_item( $menu_id, 0, array( 'menu-item-object-id' => $page_id, 'menu-item-object' => 'page', 'menu-item-type' => 'post_type', 'menu-item-status' => 'publish', 'menu-item-position' => $position + 1 ) );
            }
            $locations = get_theme_mod( 'nav_menu_locations', array() ); $locations[ $location . '___' . $language ] = $menu_id; if ( 'it' === $language ) $locations[ $location ] = $menu_id; set_theme_mod( 'nav_menu_locations', $locations ); $report[] = $menu_name . ': ' . $menu_id;
        }
    }
}
WP_CLI::success( ( $apply ? 'Applied: ' : 'Dry run: ' ) . implode( '; ', $report ) );
