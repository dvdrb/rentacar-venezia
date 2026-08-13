<?php
defined( 'ABSPATH' ) || exit;

/**
 * Contact-page content remains WordPress-editor-owned. These helpers provide
 * a narrowly scoped migration for legacy business copy without moving that
 * content into templates or touching unrelated page body content.
 */
function rentacar_venezia_v2_contact_content_strings( $language, array $business ) {
    $strings = array(
        'it' => array(
            'heading' => 'Contatti G&amp;D Rent A Car',
            'contact' => 'Telefono e WhatsApp',
            'email' => 'Email',
            'hours' => 'Orari dell’ufficio',
            'weekday' => 'Dal lunedì al venerdì',
            'weekend' => 'Sabato e domenica',
            'request' => 'Puoi inviare una richiesta online in qualsiasi momento. Per inviarla non sono richiesti né pagamento né deposito; disponibilità, prezzo finale e condizioni vengono confermati personalmente dal team.',
        ),
        'en' => array(
            'heading' => 'Contact G&amp;D Rent A Car',
            'contact' => 'Phone and WhatsApp',
            'email' => 'Email',
            'hours' => 'Office hours',
            'weekday' => 'Monday–Friday',
            'weekend' => 'Saturday and Sunday',
            'request' => 'You can send an online request at any time. No payment or deposit is required to send it; availability, final price and conditions are personally confirmed by the team.',
        ),
        'ro' => array(
            'heading' => 'Contacte G&amp;D Rent A Car',
            'contact' => 'Telefon și WhatsApp',
            'email' => 'E-mail',
            'hours' => 'Programul biroului',
            'weekday' => 'Luni–vineri',
            'weekend' => 'Sâmbătă și duminică',
            'request' => 'Poți trimite o cerere online în orice moment. Nu sunt necesare nici plata, nici garanția pentru trimiterea cererii; disponibilitatea, prețul final și condițiile sunt confirmate personal de echipă.',
        ),
        'ru' => array(
            'heading' => 'Контакты G&amp;D Rent A Car',
            'contact' => 'Телефон и WhatsApp',
            'email' => 'Электронная почта',
            'hours' => 'Часы работы офиса',
            'weekday' => 'Понедельник–пятница',
            'weekend' => 'Суббота и воскресенье',
            'request' => 'Онлайн-заявку можно отправить в любое время. Для отправки не требуются ни оплата, ни депозит; наличие автомобиля, окончательная цена и условия подтверждаются командой лично.',
        ),
    );
    $strings = $strings[ $language ] ?? $strings['en'];
    $strings['phone_display'] = $business['phone_display'];
    $strings['phone'] = $business['phone'];
    $strings['email_address'] = $business['email'];
    $strings['weekday_hours'] = rentacar_venezia_v2_contact_content_hours( $language, $business['weekday_hours'] );
    $strings['weekend_hours'] = rentacar_venezia_v2_contact_content_hours( $language, $business['weekend_hours'] );

    return $strings;
}

function rentacar_venezia_v2_contact_content_hours( $language, $hours ) {
    $translations = array(
        'Monday–Friday, 24/24' => array( 'it' => '24/24', 'en' => '24/24', 'ro' => '24/24', 'ru' => '24/24' ),
        'Saturday–Sunday, 07:00–23:00' => array( 'it' => '07:00–23:00', 'en' => '07:00–23:00', 'ro' => '07:00–23:00', 'ru' => '07:00–23:00' ),
    );

    return $translations[ $hours ][ $language ] ?? $hours;
}

function rentacar_venezia_v2_contact_content_expected_blocks( $language, array $business ) {
    $strings = rentacar_venezia_v2_contact_content_strings( $language, $business );

    return array(
        'heading' => '<h2>' . $strings['heading'] . '</h2>',
        'contact' => '<p>' . $strings['contact'] . ': <a href="tel:' . esc_attr( $strings['phone'] ) . '">' . esc_html( $strings['phone_display'] ) . '</a><br>' . $strings['email'] . ': <a href="mailto:' . esc_attr( $strings['email_address'] ) . '">' . esc_html( $strings['email_address'] ) . '</a></p>',
        'hours'   => '<h3>' . $strings['hours'] . '</h3><ul><li>' . $strings['weekday'] . ': ' . esc_html( $strings['weekday_hours'] ) . '</li><li>' . $strings['weekend'] . ': ' . esc_html( $strings['weekend_hours'] ) . '</li></ul>',
        'request' => '<p>' . $strings['request'] . '</p>',
    );
}

function rentacar_venezia_v2_contact_content_issues( $content ) {
    $content = (string) $content;
    $patterns = array(
        'localhost_email' => '~(?:mailto:)?info@rentacar-venezia-local\.local~i',
        'legacy_brand'    => '~Rent\s+a\s+Car\s+Venezia~i',
        'legacy_hours'    => '~(?:Monday|Lunedì|Luni|Понедельник)[^<]{0,80}(?:08:00|17:00)~iu',
        'airport_branch'  => '~(?:office|branch|address|ufficio|sede|adres[ăa]|офис|адрес)[^<]{0,160}(?:Marco Polo|Treviso Airport|Aeroporto di Treviso|аэропорт)~iu',
    );
    $issues = array();
    foreach ( $patterns as $key => $pattern ) {
        if ( preg_match( $pattern, $content ) ) $issues[] = $key;
    }

    return $issues;
}

function rentacar_venezia_v2_contact_content_migrate( $content, $language, array $business ) {
    $blocks = rentacar_venezia_v2_contact_content_expected_blocks( $language, $business );
    $content = (string) $content;
    $before = $content;

    $content = preg_replace( '~<h2\b[^>]*>.*?</h2>~isu', $blocks['heading'], $content, 1, $heading_count );
    if ( ! $heading_count ) $content = $blocks['heading'] . $content;
    $content = preg_replace( '~<p\b[^>]*>.*?(?:mailto:|@)[\s\S]*?</p>~isu', $blocks['contact'], $content, 1, $contact_count );
    $content = preg_replace( '~<h3\b[^>]*>.*?(?:hours|orari|program|часы).*?</h3>\s*<ul\b[^>]*>.*?</ul>~isu', $blocks['hours'], $content, 1, $hours_count );
    $content = preg_replace( '~<p\b[^>]*>[^<]*(?:online request|richiesta online|cerere online|Онлайн-заявк)[^<]*</p>~isu', $blocks['request'], $content, 1, $request_count );
    $content = preg_replace( '~(?:mailto:)?info@rentacar-venezia-local\.local~i', $business['email'], $content );
    $content = preg_replace( '~<p\b[^>]*>[^<]*(?:office|branch|address|ufficio|sede|adres[ăa]|офис|адрес)[^<]*(?:Marco Polo|Treviso Airport|Aeroporto di Treviso|аэропорт)[^<]*</p>~iu', '', $content );

    return array(
        'content' => $content,
        'changed' => $content !== $before,
        'before_issues' => rentacar_venezia_v2_contact_content_issues( $before ),
        'after_issues' => rentacar_venezia_v2_contact_content_issues( $content ),
        'blocks' => array_filter( array( 'heading' => $heading_count, 'contact' => $contact_count, 'hours' => $hours_count, 'request' => $request_count ) ),
    );
}

function rentacar_venezia_v2_contact_content_summary( $content ) {
    $summary = trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( (string) $content ) ) );

    return function_exists( 'wp_html_excerpt' ) ? wp_html_excerpt( $summary, 220, '…' ) : substr( $summary, 0, 220 );
}

function rentacar_venezia_v2_contact_content_pages() {
    $pages = get_posts( array( 'post_type' => 'page', 'post_status' => 'publish', 'posts_per_page' => -1, 'meta_key' => '_wp_page_template', 'meta_value' => 'page-templates/template-contact.php', 'orderby' => 'ID', 'order' => 'ASC', 'suppress_filters' => true, 'lang' => 'all' ) );
    $result = array();
    foreach ( $pages as $page ) {
        $language = function_exists( 'pll_get_post_language' ) ? pll_get_post_language( $page->ID, 'slug' ) : '';
        if ( in_array( $language, array( 'it', 'en', 'ro', 'ru' ), true ) && empty( $result[ $language ] ) ) $result[ $language ] = $page;
    }

    return $result;
}

function rentacar_venezia_v2_contact_content_cli( $args, $assoc_args ) {
    $apply = ! empty( $assoc_args['apply'] );
    $business = rentacar_venezia_v2_business_data();
    $pages = rentacar_venezia_v2_contact_content_pages();
    $rows = array();

    foreach ( array( 'it', 'en', 'ro', 'ru' ) as $language ) {
        if ( empty( $pages[ $language ] ) ) {
            WP_CLI::warning( strtoupper( $language ) . ': no published Contact template page found; skipped.' );
            continue;
        }
        $page = $pages[ $language ];
        $result = rentacar_venezia_v2_contact_content_migrate( $page->post_content, $language, $business );
        $rows[] = array( 'language' => strtoupper( $language ), 'id' => $page->ID, 'changed' => $result['changed'] ? 'yes' : 'no', 'before_issues' => implode( ',', $result['before_issues'] ) ?: 'none', 'after_issues' => implode( ',', $result['after_issues'] ) ?: 'none', 'blocks' => implode( ',', array_keys( $result['blocks'] ) ) ?: 'none', 'before' => rentacar_venezia_v2_contact_content_summary( $page->post_content ), 'after' => rentacar_venezia_v2_contact_content_summary( $result['content'] ) );
        if ( $apply && $result['changed'] ) wp_update_post( array( 'ID' => $page->ID, 'post_content' => $result['content'] ) );
    }

    WP_CLI\Utils\format_items( 'table', $rows, array( 'language', 'id', 'changed', 'before_issues', 'after_issues', 'blocks', 'before', 'after' ) );
    WP_CLI::success( $apply ? 'Contact content migration applied.' : 'Dry run only; add --apply to update only the identified Contact translation pages.' );
}

if ( defined( 'WP_CLI' ) && WP_CLI ) add_action( 'cli_init', function() { WP_CLI::add_command( 'rentacar contacts migrate-content', 'rentacar_venezia_v2_contact_content_cli' ); } );
