<?php
defined( 'ABSPATH' ) || exit;

/** Private WordPress-native persistence for requests that may outlive mail delivery. */
final class Rentacar_Core_Reservation_Store {
    const POST_TYPE = 'rentacar_request';

    public static function register_post_type() {
        register_post_type( self::POST_TYPE, array(
            'labels' => array( 'name' => __( 'Reservation requests', 'rentacar-core' ), 'singular_name' => __( 'Reservation request', 'rentacar-core' ) ),
            'public' => false, 'publicly_queryable' => false, 'show_in_rest' => false,
            'show_ui' => true, 'show_in_menu' => 'edit.php?post_type=cars', 'exclude_from_search' => true,
            'rewrite' => false, 'query_var' => false, 'supports' => array( 'title' ), 'capability_type' => 'post',
        ) );
    }

    public static function register_admin_meta_box() {
        add_meta_box( 'rentacar-request-phone', __( 'Customer telephone', 'rentacar-core' ), array( __CLASS__, 'render_admin_phone_meta_box' ), self::POST_TYPE, 'normal', 'default' );
    }

    public static function render_admin_phone_meta_box( $post ) {
        $display = (string) get_post_meta( $post->ID, '_rentacar_phone_display', true );
        $e164 = (string) get_post_meta( $post->ID, '_rentacar_phone_e164', true );
        $country = (string) get_post_meta( $post->ID, '_rentacar_phone_country', true );
        if ( '' === $display && '' === $e164 ) {
            $display = (string) get_post_meta( $post->ID, '_rentacar_phone', true );
        }
        echo '<p><strong>' . esc_html__( 'Telephone / WhatsApp', 'rentacar-core' ) . '</strong><br>' . esc_html( $display ?: $e164 ?: '—' ) . '</p>';
        if ( $e164 && $e164 !== $display ) {
            echo '<p><code>' . esc_html( $e164 ) . '</code>' . ( $country ? ' · ' . esc_html( $country ) : '' ) . '</p>';
        }
    }

    public function create( Rentacar_Core_Reservation_Request $request ) {
        $reference = (string) $request->get( 'reference' );
        $post_id = wp_insert_post( array(
            'post_type' => self::POST_TYPE, 'post_status' => 'publish',
            'post_title' => sprintf( __( 'Reservation %s', 'rentacar-core' ), $reference ),
        ), true );
        if ( is_wp_error( $post_id ) ) return $post_id;

        $data = $request->to_array();
        $allowed = array( 'reference', 'vehicle_id', 'vehicle_title', 'vehicle_url', 'powertrain', 'language', 'pickup_location', 'pickup_date', 'pickup_time', 'return_location', 'return_date', 'return_time', 'full_name', 'phone', 'phone_country', 'phone_calling_code', 'phone_national', 'phone_e164', 'phone_display', 'email', 'message', 'insurance', 'extras', 'estimate', 'submitted_at' );
        foreach ( $allowed as $key ) {
            if ( array_key_exists( $key, $data ) ) update_post_meta( $post_id, '_rentacar_' . $key, $data[ $key ] );
        }
        update_post_meta( $post_id, '_rentacar_operational_status', 'received' );
        update_post_meta( $post_id, '_rentacar_business_email_status', 'pending' );
        update_post_meta( $post_id, '_rentacar_customer_email_status', 'pending' );
        return $post_id;
    }

    public function record_delivery( $post_id, $business_sent, $customer_sent ) {
        update_post_meta( $post_id, '_rentacar_business_email_status', $business_sent ? 'sent' : 'failed' );
        update_post_meta( $post_id, '_rentacar_customer_email_status', $customer_sent ? 'sent' : 'failed' );
        $status = $business_sent && $customer_sent ? 'email_sent' : ( $business_sent || $customer_sent ? 'email_partially_failed' : 'email_failed' );
        update_post_meta( $post_id, '_rentacar_operational_status', $status );
        return $status;
    }
}
