<?php
/**
 * Template Name: Fleet catalogue
 * Template Post Type: page
 */
defined( 'ABSPATH' ) || exit;

$trip = rentacar_venezia_v2_trip_query();
$current_page = max( 1, get_query_var( 'paged' ) );
$query_args = rentacar_venezia_v2_fleet_query_args( $current_page );
$query_args['posts_per_page'] = -1;
$query_args['nopaging'] = true;
unset( $query_args['paged'] );

$vehicles_query = new WP_Query( $query_args );
$mapper = class_exists( 'Rentacar_Core_Vehicle_Mapper' ) ? new Rentacar_Core_Vehicle_Mapper() : null;
$vehicles = array();
if ( $mapper ) {
    foreach ( $vehicles_query->posts as $post ) {
        $vehicles[] = $mapper->map( $post );
    }
}
$vehicles = rentacar_venezia_v2_sort_fleet_vehicles( $vehicles );
$fleet_total = count( $vehicles );
$fleet_per_page = 12;
$fleet_vehicles = array_slice( $vehicles, ( $current_page - 1 ) * $fleet_per_page, $fleet_per_page );
$fleet_max_num_pages = max( 1, (int) ceil( $fleet_total / $fleet_per_page ) );
$fleet_page_content = rentacar_venezia_v2_fleet_page_content();

get_header();
?>
<main id="main-content" class="site-main site-main--fleet">
    <div class="rc-container">
        <header class="page-intro"><h1><?php echo esc_html( is_page() ? get_the_title() : __( 'Rental cars in Venice and Treviso', 'rentacar-venezia-v2' ) ); ?></h1><p><?php esc_html_e( 'Explore the vehicle fleet and choose the option that suits your trip.', 'rentacar-venezia-v2' ); ?></p><p class="fleet-result-count"><?php echo esc_html( sprintf( _n( '%s vehicle', '%s vehicles', $fleet_total, 'rentacar-venezia-v2' ), number_format_i18n( $fleet_total ) ) ); ?></p></header>
        <?php if ( '' !== trim( $fleet_page_content['before'] ) ) : ?>
            <section class="fleet-page-content fleet-page-content--before">
                <?php echo $fleet_page_content['before']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- rendered through the_content. ?>
            </section>
        <?php endif; ?>
        <?php get_template_part( 'template-parts/global/notice' ); ?>
        <?php if ( $fleet_vehicles ) : ?><div class="vehicle-grid vehicle-grid--catalogue"><?php foreach ( $fleet_vehicles as $vehicle ) : get_template_part( 'template-parts/vehicle/card', null, array( 'vehicle' => $vehicle, 'variant' => 'fleet' ) ); endforeach; ?></div><?php else : ?><section class="empty-state"><h2><?php esc_html_e( 'Our fleet is being updated.', 'rentacar-venezia-v2' ); ?></h2><p><?php esc_html_e( 'Please contact us on WhatsApp and we will help you find the right car.', 'rentacar-venezia-v2' ); ?></p><p class="empty-state__actions"><?php if ( rentacar_venezia_v2_whatsapp_url() ) : ?><a class="button button--whatsapp" href="<?php echo esc_url( rentacar_venezia_v2_whatsapp_url() ); ?>"><?php esc_html_e( 'WhatsApp', 'rentacar-venezia-v2' ); ?></a><?php endif; ?></p></section><?php endif; ?>
        <?php wp_reset_postdata(); ?>
        <?php
        $fleet_pagination = paginate_links(
            array(
                'total'    => $fleet_max_num_pages,
                'current'  => $current_page,
                'type'     => 'list',
                'prev_text' => __( 'Previous', 'rentacar-venezia-v2' ),
                'next_text' => __( 'Next', 'rentacar-venezia-v2' ),
                'add_args' => rentacar_venezia_v2_fleet_pagination_args( $trip ),
            )
        );
        if ( $fleet_pagination ) :
            ?>
            <nav class="pagination fleet-pagination" aria-label="<?php esc_attr_e( 'Fleet pages', 'rentacar-venezia-v2' ); ?>">
                <?php echo wp_kses_post( $fleet_pagination ); ?>
            </nav>
        <?php endif; ?>
        <?php if ( '' !== trim( $fleet_page_content['after'] ) ) : ?>
            <section class="fleet-page-content fleet-page-content--after">
                <?php echo $fleet_page_content['after']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- rendered through the_content. ?>
            </section>
        <?php endif; ?>
        <section class="fleet-final-cta"><h2><?php esc_html_e( 'Need help choosing a car?', 'rentacar-venezia-v2' ); ?></h2><p><?php esc_html_e( 'Read how the request works and the rental requirements before sending your dates.', 'rentacar-venezia-v2' ); ?></p><?php $how_it_works_url = rentacar_venezia_v2_managed_page_url( 'how_it_works' ); $rental_requirements_url = rentacar_venezia_v2_managed_page_url( 'rental_requirements' ); ?><?php if ( $how_it_works_url ) : ?><a class="text-link" href="<?php echo esc_url( $how_it_works_url ); ?>"><?php esc_html_e( 'How it works', 'rentacar-venezia-v2' ); ?></a><?php endif; ?><?php if ( $rental_requirements_url ) : ?> <a class="text-link" href="<?php echo esc_url( $rental_requirements_url ); ?>"><?php esc_html_e( 'Rental requirements', 'rentacar-venezia-v2' ); ?></a><?php endif; ?></section>
    </div>
</main>
<?php get_template_part( 'template-parts/enquiry/reservation-modal' ); ?>
<?php get_footer(); ?>
