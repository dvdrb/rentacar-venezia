<?php
defined( 'ABSPATH' ) || exit;

/** Read-only audits and explicit, safe derived-metadata maintenance. */
final class Rentacar_Core_Cli_Commands {
    public static function register() {
        if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) return;
        WP_CLI::add_command( 'rentacar pricing audit', array( __CLASS__, 'pricing_audit' ) );
        WP_CLI::add_command( 'rentacar vehicles backfill-starting-price', array( __CLASS__, 'backfill_starting_price' ) );
        WP_CLI::add_command( 'rentacar vehicles audit-powertrain', array( __CLASS__, 'audit_powertrain' ) );
        WP_CLI::add_command( 'rentacar vehicles infer-powertrain', array( __CLASS__, 'infer_powertrain' ) );
    }

    public static function pricing_audit() {
        $rows = array(); $repo = new Rentacar_Core_Vehicle_Repository();
        foreach ( get_posts( array( 'post_type' => 'cars', 'post_status' => 'any', 'posts_per_page' => -1 ) ) as $post ) {
            $vehicle = $repo->find( $post->ID ); $bands = $vehicle->get( 'pricing_bands' ); $issues = $bands->audit( Rentacar_Core_Rental_Policy::minimum_rental_days(), Rentacar_Core_Rental_Policy::maximum_rental_days() );
            $rows[] = array( 'id' => $post->ID, 'name' => $vehicle->get( 'title' ), 'language' => $vehicle->get( 'language' ), 'ranges' => self::ranges( $bands ), 'starting_price' => self::starting_price( $bands ), 'issues' => implode( ', ', array_unique( wp_list_pluck( $issues, 'code' ) ) ) ?: 'ok' );
        }
        WP_CLI\Utils\format_items( 'table', $rows, array( 'id', 'name', 'language', 'ranges', 'starting_price', 'issues' ) );
    }

    public static function backfill_starting_price( $args, $assoc_args ) {
        $apply = ! empty( $assoc_args['apply'] ); $count = 0;
        foreach ( get_posts( array( 'post_type' => 'cars', 'post_status' => 'any', 'posts_per_page' => -1, 'fields' => 'ids' ) ) as $post_id ) {
            if ( $apply ) Rentacar_Core_Vehicle_Maintenance::update_starting_price( $post_id ); $count++;
        }
        WP_CLI::success( sprintf( '%s starting prices for %d vehicles.', $apply ? 'Updated' : 'Would update', $count ) );
    }

    public static function audit_powertrain() {
        $rows = array(); foreach ( get_posts( array( 'post_type' => 'cars', 'post_status' => 'any', 'posts_per_page' => -1 ) ) as $post ) $rows[] = array( 'id' => $post->ID, 'name' => $post->post_title, 'powertrain' => get_post_meta( $post->ID, Rentacar_Core_Vehicle_Maintenance::POWERTRAIN_META, true ) ?: 'missing' );
        WP_CLI\Utils\format_items( 'table', $rows, array( 'id', 'name', 'powertrain' ) );
    }

    public static function infer_powertrain( $args, $assoc_args ) {
        $apply = ! empty( $assoc_args['apply'] ); $rows = array();
        foreach ( get_posts( array( 'post_type' => 'cars', 'post_status' => 'any', 'posts_per_page' => -1 ) ) as $post ) { $suggestion = self::suggest_powertrain( $post->post_title ); if ( $apply && $suggestion ) update_post_meta( $post->ID, Rentacar_Core_Vehicle_Maintenance::POWERTRAIN_META, $suggestion ); $rows[] = array( 'id' => $post->ID, 'name' => $post->post_title, 'suggestion' => $suggestion ?: 'review required' ); }
        WP_CLI\Utils\format_items( 'table', $rows, array( 'id', 'name', 'suggestion' ) );
        WP_CLI::success( $apply ? 'Applied suggestions; review the results.' : 'Dry run only; add --apply to save suggestions.' );
    }

    private static function ranges( $bands ) { $out = array(); foreach ( $bands->all() as $band ) $out[] = $band->from_days . '-' . ( null === $band->to_days ? '∞' : $band->to_days ) . ': €' . ( null === $band->daily_price ? 'invalid' : $band->daily_price ); return implode( '; ', $out ); }
    private static function starting_price( $bands ) { $prices = array(); foreach ( $bands->all() as $band ) if ( null !== $band->daily_price && $band->daily_price > 0 ) $prices[] = $band->daily_price; return $prices ? min( $prices ) : ''; }
    private static function suggest_powertrain( $title ) { $title = strtolower( (string) $title ); if ( false !== strpos( $title, 'plug-in' ) || false !== strpos( $title, 'phev' ) ) return 'plug_in_hybrid'; if ( false !== strpos( $title, 'hybrid' ) ) return 'hybrid'; if ( false !== strpos( $title, 'electric' ) || false !== strpos( $title, 'e-' ) ) return 'electric'; return ''; }
}
