<?php
defined( 'ABSPATH' ) || exit;

/** Maintains derived vehicle metadata and controlled technical classifications. */
final class Rentacar_Core_Vehicle_Maintenance {
    const STARTING_PRICE_META = '_rentacar_starting_price';
    const POWERTRAIN_META = '_rentacar_powertrain';
    const POWERTRAINS = array( 'petrol', 'diesel', 'hybrid', 'plug_in_hybrid', 'electric', 'other' );

    public static function register() {
        add_action( 'save_post_cars', array( __CLASS__, 'update_starting_price' ), 30, 1 );
        add_action( 'add_meta_boxes_cars', array( __CLASS__, 'add_powertrain_box' ) );
        add_action( 'save_post_cars', array( __CLASS__, 'save_powertrain' ), 20, 1 );
        add_action( 'added_post_meta', array( __CLASS__, 'refresh_starting_price_on_pricing_change' ), 20, 4 );
        add_action( 'updated_post_meta', array( __CLASS__, 'refresh_starting_price_on_pricing_change' ), 20, 4 );
        add_filter( 'posts_clauses', array( __CLASS__, 'sort_fleet_by_starting_price' ), 30, 2 );
        add_action( 'admin_notices', array( __CLASS__, 'pricing_warning' ) );
    }

    public static function update_starting_price( $post_id ) {
        if ( wp_is_post_revision( $post_id ) || 'cars' !== get_post_type( $post_id ) ) return array( 'status' => 'skipped', 'updated' => false );

        $result = self::starting_price_result( $post_id );
        $current = get_post_meta( $post_id, self::STARTING_PRICE_META, true );

        if ( 'valid' === $result['status'] ) {
            if ( (string) $current !== (string) $result['price'] ) {
                update_post_meta( $post_id, self::STARTING_PRICE_META, $result['price'] );
                $result['updated'] = true;
            }
            return $result;
        }

        if ( '' !== (string) $current ) {
            delete_post_meta( $post_id, self::STARTING_PRICE_META );
            $result['updated'] = true;
        }

        return $result;
    }

    /** Returns only a trustworthy starting price; invalid price bands never create derived metadata. */
    public static function starting_price_result( $post_id ) {
        $vehicle = ( new Rentacar_Core_Vehicle_Repository() )->find( $post_id );
        if ( ! $vehicle ) return array( 'status' => 'missing', 'price' => null, 'issues' => array( 'vehicle_not_found' ), 'updated' => false );

        $bands = $vehicle->get( 'pricing_bands' );
        $issues = $bands->audit( Rentacar_Core_Rental_Policy::minimum_rental_days(), Rentacar_Core_Rental_Policy::maximum_rental_days() );
        if ( $issues ) return array( 'status' => 'invalid', 'price' => null, 'issues' => array_unique( wp_list_pluck( $issues, 'code' ) ), 'updated' => false );

        $prices = array();
        foreach ( $bands->all() as $band ) {
            if ( null !== $band->daily_price && $band->daily_price > 0 ) $prices[] = (float) $band->daily_price;
        }

        return $prices
            ? array( 'status' => 'valid', 'price' => min( $prices ), 'issues' => array(), 'updated' => false )
            : array( 'status' => 'missing', 'price' => null, 'issues' => array( 'missing_price' ), 'updated' => false );
    }

    public static function refresh_starting_price_on_pricing_change( $meta_id, $post_id, $meta_key, $meta_value ) {
        if ( in_array( $meta_key, array( 'price_1_days_1', 'price_1_days_2', 'price', 'price_2_days_1', 'price_2_days_2', 'price2', 'price_3_days_1', 'price_3_days_2', 'price3', 'price4' ), true ) ) self::update_starting_price( $post_id );
    }

    public static function pricing_warning() {
        $post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0;
        if ( ! $post_id || 'cars' !== get_post_type( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) return;
        $vehicle = ( new Rentacar_Core_Vehicle_Repository() )->find( $post_id );
        $issues = $vehicle ? $vehicle->get( 'pricing_bands' )->audit( Rentacar_Core_Rental_Policy::minimum_rental_days(), Rentacar_Core_Rental_Policy::maximum_rental_days() ) : array();
        if ( ! $issues ) return;
        echo '<div class="notice notice-warning"><p>' . esc_html( sprintf( __( 'Rentacar Core: pricing audit found %d issue(s) for this vehicle. Run “wp rentacar pricing audit” for details; stored prices were not changed.', 'rentacar-core' ), count( $issues ) ) ) . '</p></div>';
    }

    public static function add_powertrain_box() {
        add_meta_box( 'rentacar-powertrain', __( 'Powertrain', 'rentacar-core' ), array( __CLASS__, 'render_powertrain_box' ), 'cars', 'side', 'default' );
    }

    public static function render_powertrain_box( $post ) {
        wp_nonce_field( 'rentacar_save_powertrain', 'rentacar_powertrain_nonce' );
        $current = self::normalize_powertrain( get_post_meta( $post->ID, self::POWERTRAIN_META, true ) );
        echo '<label for="rentacar-powertrain-field" class="screen-reader-text">' . esc_html__( 'Powertrain', 'rentacar-core' ) . '</label><select id="rentacar-powertrain-field" name="rentacar_powertrain">';
        foreach ( self::POWERTRAINS as $value ) echo '<option value="' . esc_attr( $value ) . '"' . selected( $current, $value, false ) . '>' . esc_html( ucfirst( str_replace( '_', ' ', $value ) ) ) . '</option>';
        echo '</select><p class="description">' . esc_html__( 'Set this controlled value; it is not inferred on public pages.', 'rentacar-core' ) . '</p>';
    }

    public static function save_powertrain( $post_id ) {
        if ( ! isset( $_POST['rentacar_powertrain_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['rentacar_powertrain_nonce'] ) ), 'rentacar_save_powertrain' ) || ! current_user_can( 'edit_post', $post_id ) ) return;
        update_post_meta( $post_id, self::POWERTRAIN_META, self::normalize_powertrain( $_POST['rentacar_powertrain'] ?? 'other' ) );
    }

    public static function normalize_powertrain( $value ) { $value = sanitize_key( $value ); return in_array( $value, self::POWERTRAINS, true ) ? $value : 'other'; }

    public static function sort_fleet_by_starting_price( $clauses, $query ) {
        $direction = $query->get( 'rentacar_starting_price_sort' );
        if ( is_admin() || ! in_array( $direction, array( 'ASC', 'DESC' ), true ) || 'cars' !== $query->get( 'post_type' ) ) return $clauses;
        global $wpdb;
        $alias = 'rentacar_starting_price';
        $clauses['join'] .= " LEFT JOIN {$wpdb->postmeta} AS {$alias} ON ({$wpdb->posts}.ID = {$alias}.post_id AND {$alias}.meta_key = '" . self::STARTING_PRICE_META . "')";
        $clauses['orderby'] = "CASE WHEN CAST({$alias}.meta_value AS DECIMAL(12,2)) > 0 THEN 0 ELSE 1 END ASC, CAST({$alias}.meta_value AS DECIMAL(12,2)) {$direction}, {$wpdb->posts}.post_title ASC";
        return $clauses;
    }
}
