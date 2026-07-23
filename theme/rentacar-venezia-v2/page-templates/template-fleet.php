<?php
/**
 * Template Name: Fleet catalogue
 * Template Post Type: page
 */
defined( 'ABSPATH' ) || exit;

$transmission = isset( $_GET['transmission'] ) ? sanitize_text_field( wp_unslash( $_GET['transmission'] ) ) : '';
$passengers = isset( $_GET['passengers'] ) ? absint( $_GET['passengers'] ) : 0;
$doors = isset( $_GET['doors'] ) ? absint( $_GET['doors'] ) : 0;
$air_conditioning = isset( $_GET['air_conditioning'] ) ? '1' : '';
$sort = isset( $_GET['sort'] ) ? sanitize_key( wp_unslash( $_GET['sort'] ) ) : 'recommended';
$trip = rentacar_venezia_v2_trip_query();
$transmissions = rentacar_venezia_v2_vehicle_filter_values( 'gearbox' );
$passenger_values = rentacar_venezia_v2_vehicle_filter_values( 'max_passagers' );
$door_values = rentacar_venezia_v2_vehicle_filter_values( 'doors' );
$meta_query = array( 'relation' => 'AND' );

if ( $transmission ) {
    $meta_query[] = array( 'key' => 'gearbox', 'value' => $transmission );
}
if ( $passengers ) {
    $meta_query[] = array( 'key' => 'max_passagers', 'value' => $passengers, 'compare' => '>=', 'type' => 'NUMERIC' );
}
if ( $doors ) {
    $meta_query[] = array( 'key' => 'doors', 'value' => $doors, 'compare' => '>=', 'type' => 'NUMERIC' );
}
if ( $air_conditioning ) {
    $meta_query[] = array( 'key' => 'air_conditioning', 'value' => '1' );
}

$query_args = array(
    'post_type'           => 'cars',
    'post_status'         => 'publish',
    'posts_per_page'      => 12,
    'paged'               => max( 1, get_query_var( 'paged' ) ),
    'meta_query'          => $meta_query,
    'ignore_sticky_posts' => true,
);

if ( 'price-low' === $sort || 'price-high' === $sort ) {
    $query_args['meta_key'] = 'price';
    $query_args['orderby'] = 'meta_value_num';
    $query_args['order'] = 'price-high' === $sort ? 'DESC' : 'ASC';
} elseif ( 'passengers' === $sort ) {
    $query_args['meta_key'] = 'max_passagers';
    $query_args['orderby'] = 'meta_value_num';
    $query_args['order'] = 'DESC';
} else {
    $query_args['orderby'] = 'menu_order title';
    $query_args['order'] = 'ASC';
}

$vehicles_query = new WP_Query( $query_args );
$mapper = class_exists( 'Rentacar_Core_Vehicle_Mapper' ) ? new Rentacar_Core_Vehicle_Mapper() : null;

get_header();
?>
<main id="main-content" class="site-main">
    <div class="rc-container">
        <header class="page-intro"><p class="eyebrow"><?php esc_html_e( 'Cars', 'rentacar-venezia-v2' ); ?></p><h1><?php esc_html_e( 'Our fleet', 'rentacar-venezia-v2' ); ?></h1><p><?php esc_html_e( 'Explore the vehicle fleet and choose the option that suits your trip.', 'rentacar-venezia-v2' ); ?></p></header>
        <?php get_template_part( 'template-parts/global/notice' ); ?>
        <details class="fleet-filters" open>
            <summary><?php esc_html_e( 'Filter and sort cars', 'rentacar-venezia-v2' ); ?></summary>
            <form class="fleet-filters__form" method="get" action="<?php echo esc_url( rentacar_venezia_v2_fleet_url() ); ?>">
                <?php foreach ( $trip as $key => $value ) : ?><input type="hidden" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $value ); ?>"><?php endforeach; ?>
                <?php if ( $transmissions ) : ?><label><?php esc_html_e( 'Transmission', 'rentacar-venezia-v2' ); ?><select name="transmission"><option value=""><?php esc_html_e( 'Any transmission', 'rentacar-venezia-v2' ); ?></option><?php foreach ( $transmissions as $gearbox ) : ?><option value="<?php echo esc_attr( $gearbox ); ?>"<?php selected( $transmission, $gearbox ); ?>><?php echo esc_html( $gearbox ); ?></option><?php endforeach; ?></select></label><?php endif; ?>
                <?php if ( $passenger_values ) : ?><label><?php esc_html_e( 'Passengers', 'rentacar-venezia-v2' ); ?><select name="passengers"><option value="0"><?php esc_html_e( 'Any capacity', 'rentacar-venezia-v2' ); ?></option><?php foreach ( $passenger_values as $count ) : ?><option value="<?php echo esc_attr( $count ); ?>"<?php selected( $passengers, $count ); ?>><?php echo esc_html( $count . '+' ); ?></option><?php endforeach; ?></select></label><?php endif; ?>
                <?php if ( $door_values ) : ?><label><?php esc_html_e( 'Doors', 'rentacar-venezia-v2' ); ?><select name="doors"><option value="0"><?php esc_html_e( 'Any doors', 'rentacar-venezia-v2' ); ?></option><?php foreach ( $door_values as $count ) : ?><option value="<?php echo esc_attr( $count ); ?>"<?php selected( $doors, $count ); ?>><?php echo esc_html( $count . '+' ); ?></option><?php endforeach; ?></select></label><?php endif; ?>
                <label class="fleet-filters__check"><input name="air_conditioning" type="checkbox" value="1"<?php checked( $air_conditioning, '1' ); ?>> <?php esc_html_e( 'Air conditioning', 'rentacar-venezia-v2' ); ?></label>
                <label><?php esc_html_e( 'Sort by', 'rentacar-venezia-v2' ); ?><select name="sort"><option value="recommended"<?php selected( $sort, 'recommended' ); ?>><?php esc_html_e( 'Recommended', 'rentacar-venezia-v2' ); ?></option><option value="price-low"<?php selected( $sort, 'price-low' ); ?>><?php esc_html_e( 'Price: low to high', 'rentacar-venezia-v2' ); ?></option><option value="price-high"<?php selected( $sort, 'price-high' ); ?>><?php esc_html_e( 'Price: high to low', 'rentacar-venezia-v2' ); ?></option><option value="passengers"<?php selected( $sort, 'passengers' ); ?>><?php esc_html_e( 'Passenger capacity', 'rentacar-venezia-v2' ); ?></option></select></label>
                <div class="fleet-filters__actions"><button class="button" type="submit"><?php esc_html_e( 'Apply filters', 'rentacar-venezia-v2' ); ?></button><a class="button button--secondary" href="<?php echo esc_url( rentacar_venezia_v2_fleet_url() ); ?>"><?php esc_html_e( 'Clear filters', 'rentacar-venezia-v2' ); ?></a></div>
            </form>
        </details>
        <?php if ( $mapper && $vehicles_query->have_posts() ) : ?><div class="vehicle-grid vehicle-grid--catalogue"><?php while ( $vehicles_query->have_posts() ) : $vehicles_query->the_post(); get_template_part( 'template-parts/vehicle/card', null, array( 'vehicle' => $mapper->map( get_post() ) ) ); endwhile; ?></div><?php else : ?><section class="empty-state"><h2><?php esc_html_e( 'No vehicles match these filters.', 'rentacar-venezia-v2' ); ?></h2><p><?php esc_html_e( 'Try changing or clearing one of the filters.', 'rentacar-venezia-v2' ); ?></p></section><?php endif; ?>
        <?php wp_reset_postdata(); ?>
        <?php
        echo wp_kses_post(
            paginate_links(
                array(
                    'total'    => $vehicles_query->max_num_pages,
                    'current'  => max( 1, get_query_var( 'paged' ) ),
                    'add_args' => array_filter( array_merge( compact( 'transmission', 'passengers', 'doors', 'air_conditioning', 'sort' ), $trip ) ),
                )
            )
        );
        ?>
    </div>
</main>
<?php get_template_part( 'template-parts/enquiry/reservation-modal' ); ?>
<?php get_footer(); ?>
