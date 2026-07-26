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
$fleet_page_content = rentacar_venezia_v2_fleet_page_content();
$active_filters = array_filter( array( 'transmission' => $transmission, 'passengers' => $passengers, 'doors' => $doors, 'air_conditioning' => $air_conditioning, 'sort' => 'recommended' !== $sort ? $sort : '' ) );

get_header();
?>
<main id="main-content" class="site-main site-main--fleet">
    <div class="rc-container">
        <header class="page-intro"><p class="eyebrow"><?php esc_html_e( 'Cars', 'rentacar-venezia-v2' ); ?></p><h1><?php echo esc_html( is_page() ? get_the_title() : __( 'Rental cars in Venice and Treviso', 'rentacar-venezia-v2' ) ); ?></h1><p><?php esc_html_e( 'Explore the vehicle fleet and choose the option that suits your trip.', 'rentacar-venezia-v2' ); ?></p><p class="fleet-result-count"><?php echo esc_html( sprintf( _n( '%s vehicle', '%s vehicles', (int) $vehicles_query->found_posts, 'rentacar-venezia-v2' ), number_format_i18n( (int) $vehicles_query->found_posts ) ) ); ?></p></header>
        <?php if ( '' !== trim( $fleet_page_content['before'] ) ) : ?>
            <section class="fleet-page-content fleet-page-content--before">
                <?php echo $fleet_page_content['before']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- rendered through the_content. ?>
            </section>
        <?php endif; ?>
        <?php get_template_part( 'template-parts/global/notice' ); ?>
        <details class="fleet-filters" open>
            <summary><?php esc_html_e( 'Filter and sort cars', 'rentacar-venezia-v2' ); ?></summary>
            <form class="fleet-filters__form" method="get" action="<?php echo esc_url( rentacar_venezia_v2_fleet_url() ); ?>" data-fleet-filters>
                <?php foreach ( $trip as $key => $value ) : ?><input type="hidden" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $value ); ?>"><?php endforeach; ?>
                <?php if ( $transmissions ) : ?><label><?php esc_html_e( 'Transmission', 'rentacar-venezia-v2' ); ?><select name="transmission"><option value=""><?php esc_html_e( 'Any transmission', 'rentacar-venezia-v2' ); ?></option><?php foreach ( $transmissions as $gearbox ) : ?><option value="<?php echo esc_attr( $gearbox ); ?>"<?php selected( $transmission, $gearbox ); ?>><?php echo esc_html( rentacar_venezia_v2_vehicle_transmission_label( $gearbox ) ); ?></option><?php endforeach; ?></select></label><?php endif; ?>
                <?php if ( $passenger_values ) : ?><label><?php esc_html_e( 'Passengers', 'rentacar-venezia-v2' ); ?><select name="passengers"><option value="0"><?php esc_html_e( 'Any capacity', 'rentacar-venezia-v2' ); ?></option><?php foreach ( $passenger_values as $count ) : ?><option value="<?php echo esc_attr( $count ); ?>"<?php selected( $passengers, $count ); ?>><?php echo esc_html( $count . '+' ); ?></option><?php endforeach; ?></select></label><?php endif; ?>
                <?php if ( $door_values ) : ?><label><?php esc_html_e( 'Doors', 'rentacar-venezia-v2' ); ?><select name="doors"><option value="0"><?php esc_html_e( 'Any doors', 'rentacar-venezia-v2' ); ?></option><?php foreach ( $door_values as $count ) : ?><option value="<?php echo esc_attr( $count ); ?>"<?php selected( $doors, $count ); ?>><?php echo esc_html( $count . '+' ); ?></option><?php endforeach; ?></select></label><?php endif; ?>
                <label class="fleet-filters__check"><input name="air_conditioning" type="checkbox" value="1"<?php checked( $air_conditioning, '1' ); ?>> <?php esc_html_e( 'Air conditioning', 'rentacar-venezia-v2' ); ?></label>
                <label><?php esc_html_e( 'Sort by', 'rentacar-venezia-v2' ); ?><select name="sort"><option value="recommended"<?php selected( $sort, 'recommended' ); ?>><?php esc_html_e( 'Recommended', 'rentacar-venezia-v2' ); ?></option><option value="price-low"<?php selected( $sort, 'price-low' ); ?>><?php esc_html_e( 'Price: low to high', 'rentacar-venezia-v2' ); ?></option><option value="price-high"<?php selected( $sort, 'price-high' ); ?>><?php esc_html_e( 'Price: high to low', 'rentacar-venezia-v2' ); ?></option><option value="passengers"<?php selected( $sort, 'passengers' ); ?>><?php esc_html_e( 'Passenger capacity', 'rentacar-venezia-v2' ); ?></option></select></label>
                <div class="fleet-filters__actions"><button class="button" type="submit"><?php esc_html_e( 'Apply filters', 'rentacar-venezia-v2' ); ?></button><a class="button button--secondary" href="<?php echo esc_url( rentacar_venezia_v2_fleet_url() ); ?>"><?php esc_html_e( 'Clear filters', 'rentacar-venezia-v2' ); ?></a></div>
            </form>
        </details>
        <?php if ( $active_filters ) : ?><nav class="fleet-active-filters" aria-label="<?php esc_attr_e( 'Active filters', 'rentacar-venezia-v2' ); ?>"><?php foreach ( $active_filters as $key => $value ) : $url = add_query_arg( array_diff_key( $active_filters, array( $key => true ) ), rentacar_venezia_v2_fleet_url() ); ?><a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( ucfirst( str_replace( '-', ' ', (string) $value ) ) ); ?> <span aria-hidden="true">×</span></a><?php endforeach; ?></nav><?php endif; ?>
        <nav class="fleet-airport-links" aria-label="<?php esc_attr_e( 'Airport pickup options', 'rentacar-venezia-v2' ); ?>"><a href="<?php echo esc_url( rentacar_venezia_v2_location_page_url( 'venice_marco_polo' ) ); ?>"><?php esc_html_e( 'Venice Marco Polo Airport pickup', 'rentacar-venezia-v2' ); ?></a><a href="<?php echo esc_url( rentacar_venezia_v2_location_page_url( 'treviso_airport' ) ); ?>"><?php esc_html_e( 'Treviso Airport pickup', 'rentacar-venezia-v2' ); ?></a></nav>
        <?php if ( $mapper && $vehicles_query->have_posts() ) : ?><div class="vehicle-grid vehicle-grid--catalogue"><?php while ( $vehicles_query->have_posts() ) : $vehicles_query->the_post(); get_template_part( 'template-parts/vehicle/card', null, array( 'vehicle' => $mapper->map( get_post() ), 'variant' => 'fleet' ) ); endwhile; ?></div><?php else : ?><section class="empty-state"><h2><?php esc_html_e( 'No exact match was found.', 'rentacar-venezia-v2' ); ?></h2><p><?php esc_html_e( 'Keep your dates and try clearing one or more filters, or ask us for help on WhatsApp.', 'rentacar-venezia-v2' ); ?></p><p class="empty-state__actions"><a class="button button--secondary" href="<?php echo esc_url( rentacar_venezia_v2_fleet_url() ); ?>"><?php esc_html_e( 'Clear filters', 'rentacar-venezia-v2' ); ?></a><?php if ( rentacar_venezia_v2_whatsapp_url() ) : ?><a class="button button--whatsapp" href="<?php echo esc_url( rentacar_venezia_v2_whatsapp_url() ); ?>"><?php esc_html_e( 'WhatsApp', 'rentacar-venezia-v2' ); ?></a><?php endif; ?></p></section><?php endif; ?>
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
        <?php if ( '' !== trim( $fleet_page_content['after'] ) ) : ?>
            <section class="fleet-page-content fleet-page-content--after">
                <?php echo $fleet_page_content['after']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- rendered through the_content. ?>
            </section>
        <?php endif; ?>
        <section class="fleet-final-cta"><h2><?php esc_html_e( 'Need help choosing a car?', 'rentacar-venezia-v2' ); ?></h2><p><?php esc_html_e( 'Read how the request works and the rental requirements before sending your dates.', 'rentacar-venezia-v2' ); ?></p><a class="text-link" href="<?php echo esc_url( home_url( '/how-it-works/' ) ); ?>"><?php esc_html_e( 'How it works', 'rentacar-venezia-v2' ); ?></a> <a class="text-link" href="<?php echo esc_url( home_url( '/rental-requirements/' ) ); ?>"><?php esc_html_e( 'Rental requirements', 'rentacar-venezia-v2' ); ?></a></section>
    </div>
</main>
<?php get_template_part( 'template-parts/enquiry/reservation-modal' ); ?>
<?php get_footer(); ?>
