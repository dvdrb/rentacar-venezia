<?php
/**
 * Template Name: Fleet catalogue
 * Template Post Type: page
 */
defined( 'ABSPATH' ) || exit;

$trip = rentacar_venezia_v2_trip_query();
$sort = isset( $_GET['sort'] ) ? sanitize_key( wp_unslash( $_GET['sort'] ) ) : 'recommended';
$sort = in_array( $sort, array( 'recommended', 'price_asc', 'price_desc' ), true ) ? $sort : 'recommended';

$query_args = array(
    'post_type'           => 'cars',
    'post_status'         => 'publish',
    'posts_per_page'      => 12,
    'paged'               => max( 1, get_query_var( 'paged' ) ),
    'ignore_sticky_posts' => true,
    'orderby'             => 'menu_order title',
    'order'               => 'ASC',
);
if ( 'price_asc' === $sort || 'price_desc' === $sort ) {
    $query_args['rentacar_starting_price_sort'] = 'price_asc' === $sort ? 'ASC' : 'DESC';
}

$vehicles_query = new WP_Query( $query_args );
$mapper = class_exists( 'Rentacar_Core_Vehicle_Mapper' ) ? new Rentacar_Core_Vehicle_Mapper() : null;
$fleet_page_content = rentacar_venezia_v2_fleet_page_content();

get_header();
?>
<main id="main-content" class="site-main site-main--fleet">
    <div class="rc-container">
        <header class="page-intro"><h1><?php echo esc_html( is_page() ? get_the_title() : __( 'Rental cars in Venice and Treviso', 'rentacar-venezia-v2' ) ); ?></h1><p><?php esc_html_e( 'Explore the vehicle fleet and choose the option that suits your trip.', 'rentacar-venezia-v2' ); ?></p><p class="fleet-result-count"><?php echo esc_html( sprintf( _n( '%s vehicle', '%s vehicles', (int) $vehicles_query->found_posts, 'rentacar-venezia-v2' ), number_format_i18n( (int) $vehicles_query->found_posts ) ) ); ?></p></header>
        <?php if ( '' !== trim( $fleet_page_content['before'] ) ) : ?>
            <section class="fleet-page-content fleet-page-content--before">
                <?php echo $fleet_page_content['before']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- rendered through the_content. ?>
            </section>
        <?php endif; ?>
        <?php get_template_part( 'template-parts/global/notice' ); ?>
        <form class="fleet-sort" method="get">
            <?php foreach ( $trip as $key => $value ) : ?><input type="hidden" name="<?php echo esc_attr( $key ); ?>" value="<?php echo esc_attr( $value ); ?>"><?php endforeach; ?>
            <label for="fleet-sort"><?php esc_html_e( 'Sort fleet', 'rentacar-venezia-v2' ); ?></label>
            <select id="fleet-sort" name="sort">
                <option value="recommended"<?php selected( $sort, 'recommended' ); ?>><?php esc_html_e( 'Recommended', 'rentacar-venezia-v2' ); ?></option>
                <option value="price_asc"<?php selected( $sort, 'price_asc' ); ?>><?php esc_html_e( 'Price: low to high', 'rentacar-venezia-v2' ); ?></option>
                <option value="price_desc"<?php selected( $sort, 'price_desc' ); ?>><?php esc_html_e( 'Price: high to low', 'rentacar-venezia-v2' ); ?></option>
            </select>
            <button class="button button--secondary" type="submit"><?php esc_html_e( 'Apply', 'rentacar-venezia-v2' ); ?></button>
        </form>
        <?php if ( $mapper && $vehicles_query->have_posts() ) : ?><div class="vehicle-grid vehicle-grid--catalogue"><?php while ( $vehicles_query->have_posts() ) : $vehicles_query->the_post(); get_template_part( 'template-parts/vehicle/card', null, array( 'vehicle' => $mapper->map( get_post() ), 'variant' => 'fleet' ) ); endwhile; ?></div><?php else : ?><section class="empty-state"><h2><?php esc_html_e( 'Our fleet is being updated.', 'rentacar-venezia-v2' ); ?></h2><p><?php esc_html_e( 'Please contact us on WhatsApp and we will help you find the right car.', 'rentacar-venezia-v2' ); ?></p><p class="empty-state__actions"><?php if ( rentacar_venezia_v2_whatsapp_url() ) : ?><a class="button button--whatsapp" href="<?php echo esc_url( rentacar_venezia_v2_whatsapp_url() ); ?>"><?php esc_html_e( 'WhatsApp', 'rentacar-venezia-v2' ); ?></a><?php endif; ?></p></section><?php endif; ?>
        <?php wp_reset_postdata(); ?>
        <?php
        $fleet_pagination = paginate_links(
            array(
                'total'    => $vehicles_query->max_num_pages,
                'current'  => max( 1, get_query_var( 'paged' ) ),
                'type'     => 'list',
                'prev_text' => __( 'Previous', 'rentacar-venezia-v2' ),
                'next_text' => __( 'Next', 'rentacar-venezia-v2' ),
                'add_args' => array_merge( array_filter( $trip ), array( 'sort' => $sort ) ),
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
        <section class="fleet-final-cta"><h2><?php esc_html_e( 'Need help choosing a car?', 'rentacar-venezia-v2' ); ?></h2><p><?php esc_html_e( 'Read how the request works and the rental requirements before sending your dates.', 'rentacar-venezia-v2' ); ?></p><a class="text-link" href="<?php echo esc_url( home_url( '/how-it-works/' ) ); ?>"><?php esc_html_e( 'How it works', 'rentacar-venezia-v2' ); ?></a> <a class="text-link" href="<?php echo esc_url( home_url( '/rental-requirements/' ) ); ?>"><?php esc_html_e( 'Rental requirements', 'rentacar-venezia-v2' ); ?></a></section>
    </div>
</main>
<?php get_template_part( 'template-parts/enquiry/reservation-modal' ); ?>
<?php get_footer(); ?>
