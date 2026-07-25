<?php
defined( 'ABSPATH' ) || exit;

$vehicles = class_exists( 'Rentacar_Core_Vehicle_Repository' ) ? ( new Rentacar_Core_Vehicle_Repository() )->query( array( 'posts_per_page' => 6, 'orderby' => 'date', 'order' => 'DESC' ) ) : array();
$trip = rentacar_venezia_v2_trip_query();
$locations = rentacar_venezia_v2_pickup_locations();
$public_locations = array_intersect_key( $locations, array_flip( array( 'venice_marco_polo', 'treviso_airport' ) ) );
$location_options = wp_list_pluck( $locations, 'value' );
$location_descriptions = array(
    'venice_marco_polo' => __( 'Meet us close to Venice Marco Polo Airport and begin your journey with a locally confirmed pickup.', 'rentacar-venezia-v2' ),
    'treviso_airport'   => __( 'A practical pickup point for Treviso Airport arrivals and trips across Veneto.', 'rentacar-venezia-v2' ),
    'treviso_office'    => __( 'Arrange collection from our Treviso office with clear timing confirmed by our team.', 'rentacar-venezia-v2' ),
);
$pickup_location = in_array( $trip['pickup_location'] ?? '', $location_options, true ) ? $trip['pickup_location'] : $location_options[0];
$dropoff_location = in_array( $trip['dropoff_location'] ?? '', $location_options, true ) ? $trip['dropoff_location'] : $location_options[0];
$whatsapp_url = rentacar_venezia_v2_whatsapp_url();
$homepage_id = (int) get_option( 'page_on_front' );
$homepage_content = $homepage_id && 'publish' === get_post_status( $homepage_id )
    ? rentacar_venezia_v2_render_page_content( $homepage_id, (string) get_post_field( 'post_content', $homepage_id ) )
    : '';

get_header();
?>
<main id="main-content" class="site-main site-main--home">
    <section class="hero">
        <picture class="hero__media">
            <source media="(max-width: 767px)" srcset="<?php echo esc_url( get_theme_file_uri( '/assets/images/hero/hero-venice-mobile.webp' ) ); ?>">
            <img src="<?php echo esc_url( get_theme_file_uri( '/assets/images/hero/hero-venice-desktop.webp' ) ); ?>" alt="" width="1672" height="941" fetchpriority="high" decoding="async">
        </picture>
        <div class="hero__overlay" aria-hidden="true"></div>
        <div class="rc-container hero__inner">
            <div class="hero__copy">
                <p class="eyebrow"><?php esc_html_e( 'Local car rental in Venice and Treviso', 'rentacar-venezia-v2' ); ?></p>
                <h1><?php esc_html_e( 'Car rental in Venice and Treviso, with personal confirmation', 'rentacar-venezia-v2' ); ?></h1>
                <p class="hero__tagline"><?php esc_html_e( 'Choose a car. Send a request. We confirm personally.', 'rentacar-venezia-v2' ); ?></p>
                <p><?php esc_html_e( 'Choose your preferred vehicle and send your dates without making a payment. Our local team will confirm availability, final price and rental conditions.', 'rentacar-venezia-v2' ); ?></p>
                <div class="hero__actions"><a class="button" href="#trip-filter"><?php esc_html_e( 'Choose your car', 'rentacar-venezia-v2' ); ?></a><?php if ( $whatsapp_url ) : ?><a class="button button--whatsapp" href="<?php echo esc_url( $whatsapp_url ); ?>"><?php esc_html_e( 'WhatsApp', 'rentacar-venezia-v2' ); ?></a><?php endif; ?></div>
            </div>
            <aside class="hero-location-card" aria-label="<?php esc_attr_e( 'Pickup locations', 'rentacar-venezia-v2' ); ?>">
                <p><?php esc_html_e( 'Pickup locations', 'rentacar-venezia-v2' ); ?></p>
                <ul><?php foreach ( $public_locations as $location ) : ?><li><?php echo esc_html( $location['label'] ); ?></li><?php endforeach; ?></ul>
            </aside>
        </div>
    </section>
    <section id="trip-filter" class="trip-filter-section" aria-labelledby="trip-filter-title">
        <div class="rc-container">
            <form class="trip-form" method="get" action="<?php echo esc_url( rentacar_venezia_v2_fleet_url() ); ?>" data-trip-form>
                <fieldset>
                    <legend id="trip-filter-title"><?php esc_html_e( 'Plan your trip', 'rentacar-venezia-v2' ); ?></legend>
                    <div class="trip-form__quick-locations" aria-label="<?php esc_attr_e( 'Quick pickup selection', 'rentacar-venezia-v2' ); ?>">
                        <?php foreach ( $locations as $key => $location ) : ?><button type="button" data-trip-location="<?php echo esc_attr( $location['value'] ); ?>" aria-pressed="<?php echo $pickup_location === $location['value'] ? 'true' : 'false'; ?>"><?php echo esc_html( $location['label'] ); ?></button><?php endforeach; ?>
                    </div>
                    <div class="trip-form__grid">
                        <label><?php esc_html_e( 'Pickup location', 'rentacar-venezia-v2' ); ?><select name="pickup_location"><?php foreach ( $location_options as $location ) : ?><option value="<?php echo esc_attr( $location ); ?>"<?php selected( $pickup_location, $location ); ?>><?php echo esc_html( $location ); ?></option><?php endforeach; ?></select></label>
                        <label><?php esc_html_e( 'Pickup date', 'rentacar-venezia-v2' ); ?><input name="pickup_date" type="date" value="<?php echo esc_attr( $trip['pickup_date'] ?? '' ); ?>"></label>
                        <label><?php esc_html_e( 'Return date', 'rentacar-venezia-v2' ); ?><input name="return_date" type="date" value="<?php echo esc_attr( $trip['return_date'] ?? '' ); ?>"></label>
                    </div>
                </fieldset>
                <details class="trip-form__advanced"><summary><?php esc_html_e( 'More trip details', 'rentacar-venezia-v2' ); ?></summary><div class="trip-form__advanced-fields"><label><?php esc_html_e( 'Pickup time', 'rentacar-venezia-v2' ); ?><input name="pickup_time" type="time" value="<?php echo esc_attr( $trip['pickup_time'] ?? '' ); ?>"></label><label><?php esc_html_e( 'Return time', 'rentacar-venezia-v2' ); ?><input name="return_time" type="time" value="<?php echo esc_attr( $trip['return_time'] ?? '' ); ?>"></label><label class="check-label trip-form__return-toggle"><input type="checkbox" data-return-different<?php checked( $pickup_location !== $dropoff_location ); ?>> <?php esc_html_e( 'Return to a different location', 'rentacar-venezia-v2' ); ?></label><label data-return-location><?php esc_html_e( 'Return location', 'rentacar-venezia-v2' ); ?><select name="dropoff_location"><?php foreach ( $location_options as $location ) : ?><option value="<?php echo esc_attr( $location ); ?>"<?php selected( $dropoff_location, $location ); ?>><?php echo esc_html( $location ); ?></option><?php endforeach; ?></select></label></div></details>
                <button class="button" type="submit"><?php esc_html_e( 'See suitable cars', 'rentacar-venezia-v2' ); ?></button>
            </form>
            <p class="trip-filter-section__help"><?php esc_html_e( 'The dates help us prepare your request. Availability is confirmed personally.', 'rentacar-venezia-v2' ); ?></p>
        </div>
    </section>
    <section class="trust-strip" aria-label="<?php esc_attr_e( 'Service highlights', 'rentacar-venezia-v2' ); ?>">
        <div class="rc-container">
            <ul class="trust-strip__list">
                <li class="trust-strip__item"><span class="trust-strip__icon" aria-hidden="true"><svg viewBox="0 0 20 20"><path d="M10 18s6-5.1 6-10a6 6 0 1 0-12 0c0 4.9 6 10 6 10Z"></path><circle cx="10" cy="8" r="2"></circle></svg></span><span class="trust-strip__text"><?php esc_html_e( 'Pickup at Venice Marco Polo and Treviso Airport', 'rentacar-venezia-v2' ); ?></span></li>
                <li class="trust-strip__item"><span class="trust-strip__icon" aria-hidden="true"><svg viewBox="0 0 20 20"><path d="M5 2.5h7l3 3V17.5H5zM12 2.5v3h3M7.5 11l1.7 1.7 3.5-3.5"></path></svg></span><span class="trust-strip__text"><?php esc_html_e( 'No payment required to send a request', 'rentacar-venezia-v2' ); ?></span></li>
                <li class="trust-strip__item"><span class="trust-strip__icon" aria-hidden="true"><svg viewBox="0 0 20 20"><circle cx="8" cy="6" r="3"></circle><path d="M2.5 17c.7-3 2.5-4.5 5.5-4.5 1.8 0 3.2.5 4.1 1.6M13 15.5l1.7 1.7 3-3.4"></path></svg></span><span class="trust-strip__text"><?php esc_html_e( 'Availability, final price and rental conditions confirmed personally', 'rentacar-venezia-v2' ); ?></span></li>
            </ul>
        </div>
    </section>
    <section id="fleet" class="section fleet-section" aria-labelledby="featured-cars-title">
        <div class="rc-container">
        <div class="section-heading"><div><p class="eyebrow"><?php esc_html_e( 'Explore the fleet', 'rentacar-venezia-v2' ); ?></p><h2 id="featured-cars-title"><?php esc_html_e( 'Choose your preferred car', 'rentacar-venezia-v2' ); ?></h2></div><a class="text-link" href="<?php echo esc_url( rentacar_venezia_v2_fleet_url() ); ?>"><?php esc_html_e( 'View all cars', 'rentacar-venezia-v2' ); ?></a></div>
        <?php get_template_part( 'template-parts/global/notice' ); ?>
        <?php if ( $vehicles ) : ?>
            <div class="vehicle-grid vehicle-grid--featured"><?php foreach ( $vehicles as $vehicle ) : get_template_part( 'template-parts/vehicle/card', null, array( 'vehicle' => $vehicle ) ); endforeach; ?></div>
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
                <?php foreach ( $public_locations as $key => $location ) : $location_image_id = rentacar_venezia_v2_location_page_image_id( $key ); ?>
                    <a class="arrival-card reveal-on-scroll" href="<?php echo esc_url( rentacar_venezia_v2_location_page_url( $key ) ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Choose %s pickup', 'rentacar-venezia-v2' ), $location['label'] ) ); ?>">
                        <span class="arrival-card__media">
                            <?php if ( $location_image_id ) : ?>
                                <?php echo wp_get_attachment_image( $location_image_id, 'medium_large', false, array( 'loading' => 'lazy', 'decoding' => 'async' ) ); ?>
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
    <section class="section process-section" aria-labelledby="rental-process-title">
        <div class="rc-container process-section__layout">
            <header class="process-section__header">
                <p class="eyebrow"><?php esc_html_e( 'How car rental works in Venice and Treviso', 'rentacar-venezia-v2' ); ?></p>
                <h2 id="rental-process-title"><?php esc_html_e( 'Car rental in Venice and Treviso, in three clear steps.', 'rentacar-venezia-v2' ); ?></h2>
                <p><?php esc_html_e( 'Choose a car, send your request with pickup details, then receive personal confirmation from our local team.', 'rentacar-venezia-v2' ); ?></p>
                <a class="text-link process-section__link" href="<?php echo esc_url( rentacar_venezia_v2_fleet_url() ); ?>"><?php esc_html_e( 'Explore the full fleet', 'rentacar-venezia-v2' ); ?></a>
            </header>
            <ol class="steps">
                <li class="reveal-on-scroll"><span class="steps__number" aria-hidden="true">01</span><span class="steps__icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M3 16.5 5.5 10h13l2.5 6.5M4 16.5h16v3H4zM7 19.5v2M17 19.5v2M7 13h.01M17 13h.01"/></svg></span><div class="steps__content"><h3><?php esc_html_e( 'Choose the right car', 'rentacar-venezia-v2' ); ?></h3><p><?php esc_html_e( 'Browse our fleet for Venice and Treviso and select the vehicle that suits your journey.', 'rentacar-venezia-v2' ); ?></p></div></li>
                <li class="reveal-on-scroll"><span class="steps__number" aria-hidden="true">02</span><span class="steps__icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M6 3h9l3 3v15H6zM15 3v4h3M9 13h6M9 17h4"/></svg></span><div class="steps__content"><h3><?php esc_html_e( 'Send your rental request', 'rentacar-venezia-v2' ); ?></h3><p><?php esc_html_e( 'Add your pickup and return location, dates and contact details in one simple request.', 'rentacar-venezia-v2' ); ?></p></div></li>
                <li class="reveal-on-scroll"><span class="steps__number" aria-hidden="true">03</span><span class="steps__icon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M5 5h14v11H9l-4 4zM9 10h6M9 13h4"/></svg></span><div class="steps__content"><h3><?php esc_html_e( 'Receive personal confirmation', 'rentacar-venezia-v2' ); ?></h3><p><?php esc_html_e( 'We check availability, final price and rental conditions, then contact you directly.', 'rentacar-venezia-v2' ); ?></p></div></li>
            </ol>
        </div>
    </section>
    <section class="section benefits-section" aria-labelledby="benefits-title">
        <div class="rc-container">
            <header class="benefits-section__heading"><p class="eyebrow"><?php esc_html_e( 'Why choose us', 'rentacar-venezia-v2' ); ?></p><h2 id="benefits-title"><?php esc_html_e( 'Straightforward rental support from a local team.', 'rentacar-venezia-v2' ); ?></h2></header>
            <ul class="benefits-grid">
                <li class="reveal-on-scroll"><svg aria-hidden="true" viewBox="0 0 24 24"><path d="M4 20V5l8-3 8 3v15M8 9h.01M16 9h.01M8 13h.01M16 13h.01M10 20v-3h4v3"/></svg><h3><?php esc_html_e( 'Local assistance', 'rentacar-venezia-v2' ); ?></h3><p><?php esc_html_e( 'Speak with a local team that understands Venice and Treviso pickup needs.', 'rentacar-venezia-v2' ); ?></p></li>
                <li class="reveal-on-scroll"><svg aria-hidden="true" viewBox="0 0 24 24"><path d="M6 3h9l3 3v15H6zM15 3v4h3M9 13l2 2 4-4"/></svg><h3><?php esc_html_e( 'No payment to send a request', 'rentacar-venezia-v2' ); ?></h3><p><?php esc_html_e( 'Send your dates and preferred car first; the team confirms the details with you.', 'rentacar-venezia-v2' ); ?></p></li>
                <li class="reveal-on-scroll"><svg aria-hidden="true" viewBox="0 0 24 24"><path d="M12 21s7-5.2 7-11a7 7 0 1 0-14 0c0 5.8 7 11 7 11Z"/><circle cx="12" cy="10" r="2"/></svg><h3><?php esc_html_e( 'Venice and Treviso pickup', 'rentacar-venezia-v2' ); ?></h3><p><?php esc_html_e( 'Choose a practical arrival point and receive pickup instructions personally.', 'rentacar-venezia-v2' ); ?></p></li>
                <li class="reveal-on-scroll"><svg aria-hidden="true" viewBox="0 0 24 24"><path d="M5 12.5 9.5 17 19 7.5"/></svg><h3><?php esc_html_e( 'Personal confirmation', 'rentacar-venezia-v2' ); ?></h3><p><?php esc_html_e( 'Availability, final price and rental conditions are checked before confirmation.', 'rentacar-venezia-v2' ); ?></p></li>
            </ul>
        </div>
    </section>
    <?php if ( '' !== trim( $homepage_content ) ) : ?>
        <section class="section homepage-content" aria-label="<?php esc_attr_e( 'More about car rental in Venice and Treviso', 'rentacar-venezia-v2' ); ?>">
            <div class="rc-container homepage-content__inner">
                <?php echo $homepage_content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- rendered through the_content. ?>
            </div>
        </section>
    <?php endif; ?>
    <section class="section rc-container final-cta" aria-labelledby="final-cta-title"><div><p class="eyebrow"><?php esc_html_e( 'Ready when you are', 'rentacar-venezia-v2' ); ?></p><h2 id="final-cta-title"><?php esc_html_e( 'Ready to choose your car?', 'rentacar-venezia-v2' ); ?></h2><p><?php esc_html_e( 'Send your dates and preferred vehicle. We will confirm the details personally.', 'rentacar-venezia-v2' ); ?></p><small><?php esc_html_e( 'Submitting a request does not immediately confirm the reservation.', 'rentacar-venezia-v2' ); ?></small></div><div class="final-cta__actions"><a class="button" href="<?php echo esc_url( rentacar_venezia_v2_fleet_url() ); ?>"><?php esc_html_e( 'Choose your car', 'rentacar-venezia-v2' ); ?></a><?php if ( $whatsapp_url ) : ?><a class="button button--whatsapp" href="<?php echo esc_url( $whatsapp_url ); ?>"><?php esc_html_e( 'Contact us on WhatsApp', 'rentacar-venezia-v2' ); ?></a><?php endif; ?></div></section>
</main>
<?php get_template_part( 'template-parts/enquiry/reservation-modal' ); get_footer();
