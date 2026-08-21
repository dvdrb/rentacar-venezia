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
        add_meta_box( 'rentacar-request-lifecycle', __( 'Reservation lifecycle', 'rentacar-core' ), array( __CLASS__, 'render_admin_lifecycle_meta_box' ), self::POST_TYPE, 'side', 'high' );
        add_meta_box( 'rentacar-request-review', __( 'Review follow-up', 'rentacar-core' ), array( __CLASS__, 'render_admin_review_meta_box' ), self::POST_TYPE, 'normal', 'default' );
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

    /** This status tracks the reservation outcome independently from email delivery. */
    public static function render_admin_lifecycle_meta_box( $post ) {
        $status = (string) get_post_meta( $post->ID, '_rentacar_lifecycle_status', true );
        $status = in_array( $status, array( 'reservation_submitted', 'reservation_confirmed', 'rental_completed' ), true ) ? $status : 'reservation_submitted';
        wp_nonce_field( 'rentacar_save_request_lifecycle', 'rentacar_request_lifecycle_nonce' );

        echo '<p><label for="rentacar-lifecycle-status" class="screen-reader-text">' . esc_html__( 'Reservation lifecycle', 'rentacar-core' ) . '</label>';
        echo '<select id="rentacar-lifecycle-status" name="rentacar_lifecycle_status">';
        foreach ( array( 'reservation_submitted' => __( 'Submitted', 'rentacar-core' ), 'reservation_confirmed' => __( 'Confirmed', 'rentacar-core' ), 'rental_completed' => __( 'Rental completed', 'rentacar-core' ) ) as $value => $label ) {
            echo '<option value="' . esc_attr( $value ) . '"' . selected( $status, $value, false ) . '>' . esc_html( $label ) . '</option>';
        }
        echo '</select></p>';
        echo '<p class="description">' . esc_html__( 'Email delivery status remains separate from the reservation lifecycle.', 'rentacar-core' ) . '</p>';
    }

    /** A copyable, location-specific review link; this interface never sends a message. */
    public static function render_admin_review_meta_box( $post ) {
        $status = (string) get_post_meta( $post->ID, '_rentacar_lifecycle_status', true );
        if ( 'rental_completed' !== $status ) {
            echo '<p>' . esc_html__( 'Available after the rental is marked completed.', 'rentacar-core' ) . '</p>';
            return;
        }

        $location = (string) get_post_meta( $post->ID, '_rentacar_review_location', true );
        $review_url = self::review_url( $location );
        $message = __( 'Thank you for renting with G&D Rent A Car. If you would like to share your experience, you can leave a review here:', 'rentacar-core' ) . ( $review_url ? ' ' . $review_url : '' );
        $requested = 'requested' === get_post_meta( $post->ID, '_rentacar_review_request_status', true );

        echo '<p>' . esc_html__( 'Use this only as a neutral, manual follow-up. Do not offer incentives or ask for a rating.', 'rentacar-core' ) . '</p>';
        if ( $review_url ) echo '<p><a href="' . esc_url( $review_url ) . '" target="_blank" rel="noopener">' . esc_html__( 'Open the location review link', 'rentacar-core' ) . '</a></p>';
        echo '<textarea class="large-text" rows="4" readonly>' . esc_textarea( $message ) . '</textarea>';
        echo '<p><label><input type="checkbox" name="rentacar_review_requested" value="1"' . checked( $requested, true, false ) . '> ' . esc_html__( 'Manual review request sent', 'rentacar-core' ) . '</label></p>';
    }

    public static function save_lifecycle( $post_id, $post ) {
        if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) return;
        if ( empty( $_POST['rentacar_request_lifecycle_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['rentacar_request_lifecycle_nonce'] ) ), 'rentacar_save_request_lifecycle' ) ) return;

        $requested = isset( $_POST['rentacar_lifecycle_status'] ) ? sanitize_key( wp_unslash( $_POST['rentacar_lifecycle_status'] ) ) : '';
        if ( ! in_array( $requested, array( 'reservation_submitted', 'reservation_confirmed', 'rental_completed' ), true ) ) return;

        $previous = (string) get_post_meta( $post_id, '_rentacar_lifecycle_status', true );
        update_post_meta( $post_id, '_rentacar_lifecycle_status', $requested );
        if ( 'reservation_confirmed' === $requested && 'reservation_confirmed' !== $previous ) update_post_meta( $post_id, '_rentacar_reservation_confirmed_at', current_time( 'mysql', true ) );
        if ( 'rental_completed' === $requested ) {
            if ( 'rental_completed' !== $previous ) update_post_meta( $post_id, '_rentacar_rental_completed_at', current_time( 'mysql', true ) );
            update_post_meta( $post_id, '_rentacar_review_eligible', '1' );
            update_post_meta( $post_id, '_rentacar_review_location', self::review_location( get_post_meta( $post_id, '_rentacar_pickup_location', true ) ) );
            $review_status = (string) get_post_meta( $post_id, '_rentacar_review_request_status', true );
            if ( ! empty( $_POST['rentacar_review_requested'] ) ) {
                update_post_meta( $post_id, '_rentacar_review_request_status', 'requested' );
                if ( 'requested' !== $review_status ) update_post_meta( $post_id, '_rentacar_review_requested_at', current_time( 'mysql', true ) );
            } elseif ( 'requested' !== $review_status ) {
                update_post_meta( $post_id, '_rentacar_review_request_status', 'not_requested' );
            }
        }

        if ( $requested !== $previous ) do_action( 'rentacar_core_reservation_lifecycle_changed', $post_id, $requested, $previous );
    }

    private static function review_location( $pickup_location ) {
        if ( function_exists( 'rentacar_venezia_v2_review_location_for_pickup' ) ) return rentacar_venezia_v2_review_location_for_pickup( $pickup_location );

        return false !== stripos( (string) $pickup_location, 'venice' ) ? 'venice_marco_polo' : 'treviso_airport';
    }

    private static function review_url( $location ) {
        if ( function_exists( 'rentacar_venezia_v2_location_review_url' ) ) return rentacar_venezia_v2_location_review_url( $location );

        return '';
    }

    public function create( Rentacar_Core_Reservation_Request $request ) {
        $reference = (string) $request->get( 'reference' );
        $post_id = wp_insert_post( array(
            'post_type' => self::POST_TYPE, 'post_status' => 'publish',
            'post_title' => sprintf( __( 'Reservation %s', 'rentacar-core' ), $reference ),
        ), true );
        if ( is_wp_error( $post_id ) ) return $post_id;

        $data = $request->to_array();
        $allowed = array( 'reference', 'vehicle_id', 'vehicle_title', 'vehicle_url', 'powertrain', 'language', 'pickup_location', 'pickup_location_label', 'pickup_date', 'pickup_time', 'return_location', 'return_location_label', 'return_date', 'return_time', 'full_name', 'phone', 'phone_country', 'phone_calling_code', 'phone_national', 'phone_e164', 'phone_display', 'email', 'message', 'insurance', 'extras', 'after_hours_pickup', 'after_hours_return', 'inter_airport_surcharge', 'estimate', 'submitted_at', 'acquisition_first_landing_page', 'acquisition_last_landing_page', 'acquisition_referrer', 'acquisition_utm_source', 'acquisition_utm_medium', 'acquisition_utm_campaign' );
        foreach ( $allowed as $key ) {
            if ( array_key_exists( $key, $data ) ) update_post_meta( $post_id, '_rentacar_' . $key, $data[ $key ] );
        }
        update_post_meta( $post_id, '_rentacar_operational_status', 'received' );
        update_post_meta( $post_id, '_rentacar_lifecycle_status', 'reservation_submitted' );
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
