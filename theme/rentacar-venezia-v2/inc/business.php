<?php
defined( 'ABSPATH' ) || exit;

/** Approved public identity and contact record shared by theme and core filters. */
function rentacar_venezia_v2_business_defaults() {
    return array(
        'public_name'    => 'G&D Rent A Car',
        'street_address' => 'Via Montello, 7',
        'postal_code'    => '31100',
        'locality'       => 'Treviso',
        'region'         => 'TV',
        'country'        => 'IT',
        'phone'          => '+393445068823',
        'phone_display'  => '+39 344 506 8823',
        'email'          => 'info@rentacarvenezia.it',
        'whatsapp'       => '393445068823',
        'weekday_hours'  => 'Monday–Friday, 24/24',
        'weekend_hours'  => 'Saturday–Sunday, 07:00–23:00',
        'venice_review_url'  => 'https://search.google.com/local/writereview?placeid=ChIJX5MLBACzfkcRkpzxcPjF0es',
        'treviso_review_url' => 'https://search.google.com/local/writereview?placeid=ChIJ_4ELE5U3eUcRjwKQKULkwKA',
    );
}

/** Restore the approved pre-24/7 public hours when the former value is saved. */
function rentacar_venezia_v2_restore_business_hours() {
    $saved = get_option( 'rentacar_venezia_business', array() );

    if ( ! is_array( $saved ) || 'Monday–Sunday, 24/24' !== ( $saved['weekday_hours'] ?? '' ) || '' !== ( $saved['weekend_hours'] ?? '' ) ) {
        return;
    }

    $defaults = rentacar_venezia_v2_business_defaults();
    $saved['weekday_hours'] = $defaults['weekday_hours'];
    $saved['weekend_hours'] = $defaults['weekend_hours'];
    update_option( 'rentacar_venezia_business', $saved );
}
add_action( 'init', 'rentacar_venezia_v2_restore_business_hours', 1 );

function rentacar_venezia_v2_business_data() {
    $saved = get_option( 'rentacar_venezia_business', array() );
    $data = wp_parse_args( is_array( $saved ) ? $saved : array(), rentacar_venezia_v2_business_defaults() );

    // Keep the public NAP correct while older local option values remain in
    // place; the next settings save persists the approved address.
    if ( 'Via Montello, 7/A' === $data['street_address'] ) {
        $data['street_address'] = rentacar_venezia_v2_business_defaults()['street_address'];
    }
    unset( $data['legal_name'] );

    return $data;
}

function rentacar_venezia_v2_business_value( $key ) {
    $data = rentacar_venezia_v2_business_data();

    return isset( $data[ $key ] ) ? (string) $data[ $key ] : '';
}

function rentacar_venezia_v2_business_whatsapp_url() {
    $number = preg_replace( '/\D+/', '', rentacar_venezia_v2_business_value( 'whatsapp' ) );

    return $number ? 'https://wa.me/' . $number : '';
}

function rentacar_venezia_v2_sanitize_business_data( $input ) {
    $defaults = rentacar_venezia_v2_business_defaults();
    $input = is_array( $input ) ? $input : array();
    $data = array();

    foreach ( array( 'public_name', 'street_address', 'postal_code', 'locality', 'region', 'country', 'phone_display', 'weekday_hours', 'weekend_hours' ) as $key ) {
        $data[ $key ] = sanitize_text_field( $input[ $key ] ?? $defaults[ $key ] );
    }
    $data['phone'] = preg_replace( '/[^+0-9]/', '', (string) ( $input['phone'] ?? $defaults['phone'] ) );
    $data['email'] = sanitize_email( $input['email'] ?? $defaults['email'] );
    $data['whatsapp'] = preg_replace( '/\D+/', '', (string) ( $input['whatsapp'] ?? $defaults['whatsapp'] ) );
    $data['venice_review_url'] = esc_url_raw( $input['venice_review_url'] ?? $defaults['venice_review_url'] );
    $data['treviso_review_url'] = esc_url_raw( $input['treviso_review_url'] ?? $defaults['treviso_review_url'] );

    return wp_parse_args( $data, $defaults );
}

function rentacar_venezia_v2_register_business_settings() {
    register_setting( 'rentacar_venezia_business', 'rentacar_venezia_business', 'rentacar_venezia_v2_sanitize_business_data' );
    add_options_page( __( 'Business details', 'rentacar-venezia-v2' ), __( 'Business details', 'rentacar-venezia-v2' ), 'manage_options', 'rentacar-venezia-business', 'rentacar_venezia_v2_render_business_settings' );
}
add_action( 'admin_init', 'rentacar_venezia_v2_register_business_settings' );

function rentacar_venezia_v2_render_business_settings() {
    $data = rentacar_venezia_v2_business_data();
    $fields = array(
        'public_name' => __( 'Public brand name', 'rentacar-venezia-v2' ),
        'street_address' => __( 'Street address', 'rentacar-venezia-v2' ), 'postal_code' => __( 'Postal code', 'rentacar-venezia-v2' ), 'locality' => __( 'City', 'rentacar-venezia-v2' ), 'region' => __( 'Region', 'rentacar-venezia-v2' ), 'country' => __( 'Country code', 'rentacar-venezia-v2' ),
        'phone' => __( 'Phone (E.164)', 'rentacar-venezia-v2' ), 'phone_display' => __( 'Public phone display', 'rentacar-venezia-v2' ),
        'email' => __( 'Public email / reservation recipient', 'rentacar-venezia-v2' ), 'whatsapp' => __( 'WhatsApp number', 'rentacar-venezia-v2' ),
        'weekday_hours' => __( 'Weekday hours', 'rentacar-venezia-v2' ), 'weekend_hours' => __( 'Weekend hours', 'rentacar-venezia-v2' ),
        'venice_review_url' => __( 'Venice review URL', 'rentacar-venezia-v2' ), 'treviso_review_url' => __( 'Treviso review URL', 'rentacar-venezia-v2' ),
    );
    ?>
    <div class="wrap"><h1><?php esc_html_e( 'Business details', 'rentacar-venezia-v2' ); ?></h1><p><?php esc_html_e( 'These approved values drive the footer, public contact actions, reservation recipients and structured data.', 'rentacar-venezia-v2' ); ?></p><form method="post" action="options.php"><?php settings_fields( 'rentacar_venezia_business' ); ?><table class="form-table" role="presentation"><?php foreach ( $fields as $key => $label ) : ?><tr><th scope="row"><label for="rentacar-business-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label></th><td><input class="regular-text" id="rentacar-business-<?php echo esc_attr( $key ); ?>" name="rentacar_venezia_business[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( $data[ $key ] ); ?>"></td></tr><?php endforeach; ?></table><?php submit_button(); ?></form></div>
    <?php
}

/**
 * Verified, public-facing business premises. These are intentionally separate
 * from pickup locations: stations, hotels and city-access pages are service
 * areas, not branches.
 */
function rentacar_venezia_v2_business_locations() {
    return apply_filters(
        'rentacar_venezia_v2_business_locations',
        array(
            'treviso' => array(
                'key'                         => 'treviso',
                'public_name'                 => 'G&D Rent A Car',
                'street_address'              => 'Via Montello, 7',
                'postal_code'                 => '31100',
                'locality'                    => 'Treviso',
                'region'                      => 'TV',
                'country'                     => 'IT',
                'phone'                       => '+393445068823',
                'phone_display'               => '+39 344 506 8823',
                'opening_hours_source'        => 'business',
                'google_business_profile_url' => 'https://www.google.com/maps/search/?api=1&query_place_id=ChIJ_4ELE5U3eUcRjwKQKULkwKA',
                'related_pickup_location'     => '',
            ),
            'venice_marco_polo' => array(
                'key'                         => 'venice_marco_polo',
                'public_name'                 => 'G&D Rent A Car',
                'street_address'              => 'Airport, Viale Galileo Galilei, 30/1',
                'postal_code'                 => '30173',
                'locality'                    => 'Venice',
                'region'                      => 'VE',
                'country'                     => 'IT',
                'phone'                       => '+393445068823',
                'phone_display'               => '+39 344 506 8823',
                'google_business_profile_url' => 'https://www.google.com/maps/search/?api=1&query_place_id=ChIJX5MLBACzfkcRkpzxcPjF0es',
                'related_pickup_location'     => 'venice_marco_polo',
            ),
        )
    );
}

/** Returns the related public page without inventing a branch-only route. */
function rentacar_venezia_v2_business_location_url( $location_key ) {
    $location = rentacar_venezia_v2_business_locations()[ $location_key ] ?? array();
    $pickup_key = $location['related_pickup_location'] ?? '';

    if ( $pickup_key && function_exists( 'rentacar_venezia_v2_location_page_url' ) ) {
        $url = rentacar_venezia_v2_location_page_url( $pickup_key );
        if ( $url ) {
            return $url;
        }
    }

    return home_url( '/' );
}

function rentacar_venezia_v2_location_review_url( $location_key ) {
    $key = 'treviso_airport' === $location_key ? 'treviso_review_url' : ( 'venice_marco_polo' === $location_key ? 'venice_review_url' : '' );

    return $key ? rentacar_venezia_v2_business_value( $key ) : '';
}
