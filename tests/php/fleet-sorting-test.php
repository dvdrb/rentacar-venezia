<?php
/** Focused PHP 7.4 checks for fleet sort query construction and URL preservation. */
define( 'ABSPATH', __DIR__ . '/' );

function absint( $value ) { return abs( (int) $value ); }
function sanitize_key( $value ) { return preg_replace( '/[^a-z0-9_\-]/', '', strtolower( (string) $value ) ); }
function is_admin() { return false; }

require_once dirname( __DIR__, 2 ) . '/theme/rentacar-venezia-v2/inc/presentation.php';
require_once dirname( __DIR__, 2 ) . '/plugin/rentacar-core/src/Vehicles/VehicleMaintenance.php';

function fleet_sort_assert( $condition, $message ) { if ( ! $condition ) { fwrite( STDERR, "FAIL: {$message}\n" ); exit( 1 ); } }

$ascending = rentacar_venezia_v2_fleet_query_args( 3 );
fleet_sort_assert( 'ASC' === $ascending['rentacar_starting_price_sort'] && 3 === $ascending['paged'], 'The catalogue always applies low-to-high pricing before the requested page.' );

$pagination = rentacar_venezia_v2_fleet_pagination_args( array( 'pickup_date' => '2027-04-10', 'return_date' => '2027-04-13', 'pickup_time' => '' ) );
fleet_sort_assert( array( 'pickup_date' => '2027-04-10', 'return_date' => '2027-04-13' ) === $pagination, 'Pagination preserves the active trip query without exposing a sort parameter.' );

global $wpdb;
$wpdb = (object) array( 'postmeta' => 'wp_postmeta', 'posts' => 'wp_posts' );

class Rentacar_Fleet_Sort_Query {
    private $direction;
    public function __construct( $direction ) { $this->direction = $direction; }
    public function get( $key ) { return 'rentacar_starting_price_sort' === $key ? $this->direction : ( 'post_type' === $key ? 'cars' : null ); }
}

$ascending_clauses = Rentacar_Core_Vehicle_Maintenance::sort_fleet_by_starting_price( array( 'join' => '', 'orderby' => '' ), new Rentacar_Fleet_Sort_Query( 'ASC' ) );
fleet_sort_assert( false !== strpos( $ascending_clauses['orderby'], 'CAST(rentacar_starting_price.meta_value AS DECIMAL(12,2)) ASC' ), 'Ascending prices are compared numerically, so €9 sorts before €100.' );
fleet_sort_assert( false !== strpos( $ascending_clauses['orderby'], 'THEN 0 ELSE 1 END ASC' ), 'Missing or invalid starting-price metadata is placed after priced vehicles.' );

echo "Fleet sorting checks passed.\n";
