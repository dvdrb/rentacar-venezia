<?php
defined( 'ABSPATH' ) || exit;

$vehicles = class_exists( 'Rentacar_Core_Vehicle_Repository' ) ? ( new Rentacar_Core_Vehicle_Repository() )->query( array( 'posts_per_page' => 6, 'orderby' => 'date', 'order' => 'DESC' ) ) : array();
$trip = rentacar_venezia_v2_trip_query();
$locations = rentacar_venezia_v2_pickup_locations();
$location_options = array_values( wp_list_pluck( $locations, 'value' ) );
$default_location = $location_options ? reset( $location_options ) : '';
$location_descriptions = array(
    'venice_marco_polo' => __( 'Meet us close to Venice Marco Polo Airport and begin your journey with a locally confirmed pickup.', 'rentacar-venezia-v2' ),
    'treviso_airport'   => __( 'A practical pickup point for Treviso Airport arrivals and trips across Veneto.', 'rentacar-venezia-v2' ),
);
$pickup_location = in_array( $trip['pickup_location'] ?? '', $location_options, true ) ? $trip['pickup_location'] : $default_location;
$whatsapp_url = rentacar_venezia_v2_whatsapp_url();
$homepage_id = (int) get_option( 'page_on_front' );
$homepage_content = $homepage_id && 'publish' === get_post_status( $homepage_id )
    ? rentacar_venezia_v2_render_page_content( $homepage_id, (string) get_post_field( 'post_content', $homepage_id ) )
    : '';

get_header();
?>
<main id="main-content" class="site-main site-main--home">
    <section class="hero hero--split">
        <img class="hero__background-image" src="<?php echo esc_url( get_theme_file_uri( 'assets/images/hero/hero-airport-background.webp' ) ); ?>" width="1672" height="941" alt="<?php esc_attr_e( 'Rental car for Venice and Treviso', 'rentacar-venezia-v2' ); ?>" fetchpriority="high">
        <div class="rc-container hero__inner">
            <div class="hero__copy">
                <p class="eyebrow"><?php esc_html_e( 'Car rental at Venice and Treviso airports', 'rentacar-venezia-v2' ); ?></p>
                <h1><?php esc_html_e( 'Car Rental in Venice & Treviso', 'rentacar-venezia-v2' ); ?></h1>
                <p><?php esc_html_e( 'Choose your car and send a reservation request with no credit card required. Airport pickup and direct local assistance.', 'rentacar-venezia-v2' ); ?></p>
                <div class="hero__actions"><a class="button" href="<?php echo esc_url( rentacar_venezia_v2_fleet_url() ); ?>"><?php esc_html_e( 'Explore our cars', 'rentacar-venezia-v2' ); ?></a><?php if ( $whatsapp_url ) : ?><a class="button button--whatsapp" href="<?php echo esc_url( $whatsapp_url ); ?>"><?php esc_html_e( 'Ask on WhatsApp', 'rentacar-venezia-v2' ); ?></a><?php endif; ?></div>
            </div>
            <form id="trip-filter" class="trip-form hero__trip-form" method="get" action="<?php echo esc_url( rentacar_venezia_v2_fleet_url() ); ?>" data-trip-form>
                <fieldset>
                    <legend><?php esc_html_e( 'Find your car', 'rentacar-venezia-v2' ); ?></legend>
                    <div class="trip-form__grid">
                        <label><?php esc_html_e( 'Pickup location', 'rentacar-venezia-v2' ); ?><select name="pickup_location"><?php foreach ( $locations as $location ) : ?><option value="<?php echo esc_attr( $location['value'] ); ?>"<?php selected( $pickup_location, $location['value'] ); ?>><?php echo esc_html( $location['label'] ); ?></option><?php endforeach; ?></select></label>
                        <label><?php esc_html_e( 'Pickup date', 'rentacar-venezia-v2' ); ?><input name="pickup_date" type="date" min="<?php echo esc_attr( wp_date( 'Y-m-d' ) ); ?>" value="<?php echo esc_attr( $trip['pickup_date'] ?? '' ); ?>"></label>
                        <label><?php esc_html_e( 'Return date', 'rentacar-venezia-v2' ); ?><input name="return_date" type="date" min="<?php echo esc_attr( wp_date( 'Y-m-d', strtotime( '+' . ( class_exists( 'Rentacar_Core_Rental_Policy' ) ? Rentacar_Core_Rental_Policy::minimum_rental_days() : 3 ) . ' days' ) ) ); ?>" value="<?php echo esc_attr( $trip['return_date'] ?? '' ); ?>"></label>
                    </div>
                </fieldset>
                <button class="button" type="submit"><?php esc_html_e( 'Search cars', 'rentacar-venezia-v2' ); ?></button>
            </form>
        </div>
    </section>
    <section class="trust-strip" aria-label="<?php esc_attr_e( 'Service highlights', 'rentacar-venezia-v2' ); ?>">
        <div class="rc-container">
            <ul class="trust-strip__list">
                <li class="trust-strip__item">
                    <span class="trust-strip__icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M12 21s7-6.1 7-12A7 7 0 1 0 5 9c0 5.9 7 12 7 12Z"/><circle cx="12" cy="9" r="2.25"/></svg></span>
                    <span class="trust-strip__text"><?php esc_html_e( 'Venice and Treviso airport pickup', 'rentacar-venezia-v2' ); ?></span>
                </li>
                <li class="trust-strip__item">
                    <span class="trust-strip__icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="m5 12 4 4L19 6"/><path d="M20 12v6a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1h9"/></svg></span>
                    <span class="trust-strip__text"><?php esc_html_e( 'Fast and simple reservation', 'rentacar-venezia-v2' ); ?></span>
                </li>
                <li class="trust-strip__item">
                    <span class="trust-strip__icon" aria-hidden="true"><svg viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 10h18"/><path d="M7 15h3"/></svg></span>
                    <span class="trust-strip__text"><?php esc_html_e( 'No credit card required to send a request', 'rentacar-venezia-v2' ); ?></span>
                </li>
                <li class="trust-strip__item">
                    <span class="trust-strip__icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M20 15a4 4 0 0 1-4 4H9l-5 3v-7a4 4 0 0 1-2-3.5V8a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4Z"/><path d="M7.5 11.5h.01M12 11.5h.01M16.5 11.5h.01"/></svg></span>
                    <span class="trust-strip__text"><?php esc_html_e( 'Direct local assistance', 'rentacar-venezia-v2' ); ?></span>
                </li>
            </ul>
        </div>
    </section>
    <section id="fleet" class="section fleet-section" aria-labelledby="featured-cars-title">
        <div class="rc-container">
        <div class="section-heading"><div><p class="eyebrow"><?php esc_html_e( 'Explore the fleet', 'rentacar-venezia-v2' ); ?></p><h2 id="featured-cars-title"><?php esc_html_e( 'Choose your preferred car', 'rentacar-venezia-v2' ); ?></h2></div><a class="text-link" href="<?php echo esc_url( rentacar_venezia_v2_fleet_url() ); ?>"><?php esc_html_e( 'View all cars', 'rentacar-venezia-v2' ); ?></a></div>
        <?php get_template_part( 'template-parts/global/notice' ); ?>
        <?php if ( $vehicles ) : ?>
            <div class="vehicle-grid vehicle-grid--featured"><?php foreach ( $vehicles as $vehicle ) : get_template_part( 'template-parts/vehicle/card', null, array( 'vehicle' => $vehicle, 'variant' => 'featured' ) ); endforeach; ?></div>
        <?php else : ?>
            <section class="empty-state"><h3><?php esc_html_e( 'Vehicles will appear here when they are ready to show.', 'rentacar-venezia-v2' ); ?></h3></section>
        <?php endif; ?>
        </div>
    </section>
    <section class="section arrivals-section" aria-labelledby="arrivals-title">
        <div class="rc-container">
            <header class="arrivals-section__heading">
                <p class="eyebrow"><?php esc_html_e( 'Pickup locations', 'rentacar-venezia-v2' ); ?></p>
                <h2 id="arrivals-title"><?php esc_html_e( 'Where are you arriving?', 'rentacar-venezia-v2' ); ?></h2>
                <p><?php esc_html_e( 'Choose the pickup point that suits your trip. We confirm the practical details personally before your reservation is final.', 'rentacar-venezia-v2' ); ?></p>
            </header>
            <div class="arrivals-grid">
                <?php foreach ( $locations as $key => $location ) : $location_image_id = rentacar_venezia_v2_location_page_image_id( $key ); $location_theme_image = rentacar_venezia_v2_location_theme_image( $key ); ?>
                    <a class="arrival-card reveal-on-scroll" href="<?php echo esc_url( rentacar_venezia_v2_location_page_url( $key ) ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Choose %s pickup', 'rentacar-venezia-v2' ), $location['label'] ) ); ?>">
                        <span class="arrival-card__media">
                            <?php if ( $location_image_id ) : ?>
                                <?php echo wp_get_attachment_image( $location_image_id, 'medium_large', false, array( 'loading' => 'lazy', 'decoding' => 'async' ) ); ?>
                            <?php elseif ( $location_theme_image ) : ?>
                                <img src="<?php echo esc_url( $location_theme_image['url'] ); ?>" width="<?php echo esc_attr( $location_theme_image['width'] ); ?>" height="<?php echo esc_attr( $location_theme_image['height'] ); ?>" alt="" loading="lazy" decoding="async">
                            <?php else : ?>
                                <svg viewBox="0 0 320 180" aria-hidden="true" focusable="false"><path d="M0 145 72 91l56 40 63-78 129 92v35H0Z" fill="currentColor" opacity=".16"/><circle cx="239" cy="52" r="25" fill="currentColor" opacity=".14"/><path d="M78 145V93l36-23 36 23v52M184 145V75h56v70" fill="none" stroke="currentColor" stroke-width="6" stroke-linejoin="round"/></svg>
                            <?php endif; ?>
                        </span>
                        <span class="arrival-card__body"><span class="arrival-card__eyebrow"><?php esc_html_e( 'Local service', 'rentacar-venezia-v2' ); ?></span><span class="arrival-card__title"><?php echo esc_html( $location['label'] ); ?></span><span class="arrival-card__description"><?php echo esc_html( $location_descriptions[ $key ] ?? '' ); ?></span><span class="arrival-card__link"><?php esc_html_e( 'Choose this pickup', 'rentacar-venezia-v2' ); ?><span aria-hidden="true">→</span></span></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <section class="section benefits-section" aria-labelledby="benefits-title">
        <div class="rc-container">
            <div class="benefits-section__layout"><div><header class="benefits-section__heading"><p class="eyebrow"><?php esc_html_e( 'Why rent with us', 'rentacar-venezia-v2' ); ?></p><h2 id="benefits-title"><?php esc_html_e( 'Direct support for every journey', 'rentacar-venezia-v2' ); ?></h2></header><ul class="benefits-grid"><li><h3><?php esc_html_e( 'Easy reservation', 'rentacar-venezia-v2' ); ?></h3><p><?php esc_html_e( 'Enter your trip details, choose a car and send one clear request.', 'rentacar-venezia-v2' ); ?></p></li><li><h3><?php esc_html_e( 'Direct communication', 'rentacar-venezia-v2' ); ?></h3><p><?php esc_html_e( 'Speak with a local team when you need practical help.', 'rentacar-venezia-v2' ); ?></p></li><li><h3><?php esc_html_e( 'Cars for every journey', 'rentacar-venezia-v2' ); ?></h3><p><?php esc_html_e( 'Choose compact, family and larger options from the real fleet.', 'rentacar-venezia-v2' ); ?></p></li></ul></div><aside class="assistance-panel"><p class="eyebrow"><?php esc_html_e( 'Need help?', 'rentacar-venezia-v2' ); ?></p><h3><?php esc_html_e( 'Talk to our local team', 'rentacar-venezia-v2' ); ?></h3><p><?php esc_html_e( 'Use WhatsApp for a quick question before choosing your car.', 'rentacar-venezia-v2' ); ?></p><?php if ( $whatsapp_url ) : ?><a class="button button--whatsapp" href="<?php echo esc_url( $whatsapp_url ); ?>"><?php esc_html_e( 'Contact on WhatsApp', 'rentacar-venezia-v2' ); ?></a><?php endif; ?></aside></div>
        </div>
    </section>
    <?php if ( '' !== trim( $homepage_content ) ) : ?>
        <section class="section homepage-content" aria-label="<?php esc_attr_e( 'More about car rental in Venice and Treviso', 'rentacar-venezia-v2' ); ?>">
            <div class="rc-container homepage-content__inner">
                <?php echo $homepage_content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- rendered through the_content. ?>
            </div>
        </section>
    <?php endif; ?>
    <section class="section rc-container final-cta" aria-labelledby="final-cta-title"><div><p class="eyebrow"><?php esc_html_e( 'Ready when you are', 'rentacar-venezia-v2' ); ?></p><h2 id="final-cta-title"><?php esc_html_e( 'Ready to choose your car?', 'rentacar-venezia-v2' ); ?></h2><p><?php esc_html_e( 'Send your dates and preferred vehicle. Our team will check availability, final price and rental conditions personally.', 'rentacar-venezia-v2' ); ?></p><small><?php esc_html_e( 'Submitting this request does not immediately confirm the reservation. We will check availability and contact you.', 'rentacar-venezia-v2' ); ?></small></div><div class="final-cta__actions"><a class="button" href="<?php echo esc_url( rentacar_venezia_v2_fleet_url() ); ?>"><?php esc_html_e( 'View all cars', 'rentacar-venezia-v2' ); ?></a><?php if ( $whatsapp_url ) : ?><a class="button button--whatsapp" href="<?php echo esc_url( $whatsapp_url ); ?>"><?php esc_html_e( 'Contact us on WhatsApp', 'rentacar-venezia-v2' ); ?></a><?php endif; ?></div></section>
</main>
<?php get_template_part( 'template-parts/enquiry/reservation-modal' ); get_footer();
