<?php
defined( 'ABSPATH' ) || exit;

$repository = class_exists( 'Rentacar_Core_Vehicle_Repository' ) ? new Rentacar_Core_Vehicle_Repository() : null;
$vehicle = $repository ? $repository->find( get_queried_object_id() ) : null;
$image_ids = $vehicle ? $vehicle->get( 'vehicle_gallery' )->all_image_ids() : array();
$image_url = $vehicle ? wp_get_attachment_image_url( rentacar_venezia_v2_vehicle_image_id( $vehicle ), 'medium_large' ) : '';
$specifications = $vehicle ? rentacar_venezia_v2_vehicle_specs( $vehicle ) : array();
$bands = $vehicle ? rentacar_venezia_v2_vehicle_bands( $vehicle ) : array();
$price_labels = array();
$related = $repository && $vehicle ? $repository->query(
    array(
        'posts_per_page' => 3,
        'post__not_in'   => array( $vehicle->get( 'id' ) ),
    )
) : array();

foreach ( $bands as $band ) {
    $price_labels[] = rentacar_venezia_v2_price_range_label( $band ) . ' ' . rentacar_venezia_v2_price_label( $band );
}

get_header();
?>
<main id="main-content" class="site-main vehicle-page">
    <div class="rc-container">
        <?php if ( ! $vehicle ) : ?>
            <section class="empty-state">
                <h1><?php esc_html_e( 'This vehicle is not available to view.', 'rentacar-venezia-v2' ); ?></h1>
                <p><?php esc_html_e( 'Please browse the fleet to choose another vehicle.', 'rentacar-venezia-v2' ); ?></p>
                <a class="button" href="<?php echo esc_url( rentacar_venezia_v2_fleet_url() ); ?>"><?php esc_html_e( 'View the fleet', 'rentacar-venezia-v2' ); ?></a>
            </section>
        <?php else : ?>
            <nav class="breadcrumbs" aria-label="<?php esc_attr_e( 'Breadcrumbs', 'rentacar-venezia-v2' ); ?>">
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'rentacar-venezia-v2' ); ?></a>
                <span aria-hidden="true">/</span>
                <a href="<?php echo esc_url( rentacar_venezia_v2_fleet_url() ); ?>"><?php esc_html_e( 'Fleet', 'rentacar-venezia-v2' ); ?></a>
                <span aria-hidden="true">/</span>
                <span aria-current="page"><?php echo esc_html( rentacar_venezia_v2_vehicle_title( $vehicle ) ); ?></span>
            </nav>

            <div class="vehicle-page__grid">
                <section class="vehicle-page__content">
                    <header class="vehicle-page__heading">
                        <p class="eyebrow"><?php esc_html_e( 'Our fleet', 'rentacar-venezia-v2' ); ?></p>
                        <h1><?php echo esc_html( rentacar_venezia_v2_vehicle_title( $vehicle ) ); ?></h1>
                        <?php if ( $specifications ) : ?>
                            <ul class="specification-list">
                                <?php foreach ( $specifications as $specification ) : ?>
                                    <li><?php echo esc_html( $specification ); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </header>

                    <div class="vehicle-gallery" aria-label="<?php esc_attr_e( 'Vehicle gallery', 'rentacar-venezia-v2' ); ?>">
                        <?php if ( $image_ids ) : ?>
                            <?php foreach ( $image_ids as $index => $image_id ) : ?>
                                <figure class="vehicle-gallery__image<?php echo 0 === $index ? ' vehicle-gallery__image--primary' : ''; ?>">
                                    <?php
                                    echo wp_kses_post(
                                        wp_get_attachment_image(
                                            $image_id,
                                            0 === $index ? 'large' : 'medium_large',
                                            false,
                                            array(
                                                'loading'       => 0 === $index ? 'eager' : 'lazy',
                                                'fetchpriority' => 0 === $index ? 'high' : 'auto',
                                                'sizes'         => '(min-width: 960px) 50vw, 100vw',
                                                'alt'           => '',
                                            )
                                        )
                                    );
                                    ?>
                                </figure>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <div class="vehicle-gallery__image vehicle-gallery__image--primary vehicle-card__image-placeholder"><?php esc_html_e( 'Vehicle image unavailable', 'rentacar-venezia-v2' ); ?></div>
                        <?php endif; ?>
                    </div>

                    <section class="vehicle-copy" aria-labelledby="vehicle-prices-title">
                        <h2 id="vehicle-prices-title"><?php esc_html_e( 'Indicative price bands', 'rentacar-venezia-v2' ); ?></h2>
                        <?php if ( $bands ) : ?>
                            <dl class="vehicle-card__prices vehicle-page__prices">
                                <?php foreach ( $bands as $band ) : ?>
                                    <div>
                                        <dt><?php echo esc_html( rentacar_venezia_v2_price_range_label( $band ) ); ?></dt>
                                        <dd><?php echo esc_html( rentacar_venezia_v2_price_label( $band ) ); ?></dd>
                                    </div>
                                <?php endforeach; ?>
                            </dl>
                        <?php else : ?>
                            <p class="vehicle-card__price-pending"><?php esc_html_e( 'Price to be confirmed', 'rentacar-venezia-v2' ); ?></p>
                        <?php endif; ?>
                        <p><?php esc_html_e( 'Indicative prices only. Availability and final price are confirmed by our team.', 'rentacar-venezia-v2' ); ?></p>
                    </section>

                    <?php if ( trim( get_the_content() ) ) : ?>
                        <section class="vehicle-copy vehicle-copy--description" aria-labelledby="vehicle-description-title">
                            <h2 id="vehicle-description-title"><?php esc_html_e( 'About this vehicle', 'rentacar-venezia-v2' ); ?></h2>
                            <div class="content-page__body"><?php the_content(); ?></div>
                        </section>
                    <?php endif; ?>
                </section>

                <aside class="request-card" aria-labelledby="reservation-card-title">
                    <p class="eyebrow"><?php esc_html_e( 'Reservation request', 'rentacar-venezia-v2' ); ?></p>
                    <h2 id="reservation-card-title"><?php esc_html_e( 'Choose this vehicle', 'rentacar-venezia-v2' ); ?></h2>
                    <p><?php esc_html_e( 'Send one short request and our team will check availability personally.', 'rentacar-venezia-v2' ); ?></p>
                    <?php get_template_part( 'template-parts/global/notice' ); ?>
                    <button class="button reservation-trigger" type="button" data-reservation-trigger data-vehicle-id="<?php echo esc_attr( $vehicle->get( 'id' ) ); ?>" data-vehicle-title="<?php echo esc_attr( rentacar_venezia_v2_vehicle_title( $vehicle ) ); ?>" data-vehicle-image="<?php echo esc_url( $image_url ); ?>" data-vehicle-specifications="<?php echo esc_attr( implode( ' · ', $specifications ) ); ?>" data-vehicle-price-bands="<?php echo esc_attr( implode( ' · ', $price_labels ) ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Reservation for %s', 'rentacar-venezia-v2' ), rentacar_venezia_v2_vehicle_title( $vehicle ) ) ); ?>"><?php esc_html_e( 'Reservation', 'rentacar-venezia-v2' ); ?></button>
                </aside>
            </div>

            <?php if ( $related ) : ?>
                <section class="section related-vehicles" aria-labelledby="related-vehicles-title">
                    <div class="section-heading"><div><p class="eyebrow"><?php esc_html_e( 'More options', 'rentacar-venezia-v2' ); ?></p><h2 id="related-vehicles-title"><?php esc_html_e( 'Other vehicles in our fleet', 'rentacar-venezia-v2' ); ?></h2></div></div>
                    <div class="vehicle-grid">
                        <?php foreach ( $related as $related_vehicle ) : ?>
                            <?php get_template_part( 'template-parts/vehicle/card', null, array( 'vehicle' => $related_vehicle ) ); ?>
                        <?php endforeach; ?>
                    </div>
                </section>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</main>
<?php get_template_part( 'template-parts/enquiry/reservation-modal' ); ?>
<?php get_footer(); ?>
