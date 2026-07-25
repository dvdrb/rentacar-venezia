<?php
defined( 'ABSPATH' ) || exit;

$vehicles = class_exists( 'Rentacar_Core_Vehicle_Repository' ) ? ( new Rentacar_Core_Vehicle_Repository() )->query( array( 'posts_per_page' => 6, 'orderby' => 'date', 'order' => 'DESC' ) ) : array();
$trip = rentacar_venezia_v2_trip_query();
$location_options = array(
    'Airport Venice Marco Polo',
    'Treviso Airport Arrivals',
    'Treviso Office',
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
                <p class="eyebrow"><?php esc_html_e( 'Rent a Car Venezia', 'rentacar-venezia-v2' ); ?></p>
                <h1><?php esc_html_e( 'Car rental in Venice and Treviso', 'rentacar-venezia-v2' ); ?></h1>
                <p class="hero__tagline"><?php esc_html_e( 'Choose a car. Send a request. We confirm personally.', 'rentacar-venezia-v2' ); ?></p>
                <p><?php esc_html_e( 'Tell us where you need the car and when. We will review your request personally and explain the available options.', 'rentacar-venezia-v2' ); ?></p>
                <div class="hero__actions"><a class="button" href="#trip-filter"><?php esc_html_e( 'Choose your car', 'rentacar-venezia-v2' ); ?></a><?php if ( $whatsapp_url ) : ?><a class="button button--whatsapp" href="<?php echo esc_url( $whatsapp_url ); ?>"><?php esc_html_e( 'WhatsApp', 'rentacar-venezia-v2' ); ?></a><?php endif; ?></div>
            </div>
        </div>
    </section>
    <section id="trip-filter" class="trip-filter-section" aria-labelledby="trip-filter-title">
        <div class="rc-container">
            <form class="trip-form" method="get" action="<?php echo esc_url( rentacar_venezia_v2_fleet_url() ); ?>">
                <fieldset>
                    <legend id="trip-filter-title"><?php esc_html_e( 'Filter and sort cars', 'rentacar-venezia-v2' ); ?></legend>
                    <div class="trip-form__grid">
                        <label><?php esc_html_e( 'Pickup location', 'rentacar-venezia-v2' ); ?><select name="pickup_location"><?php foreach ( $location_options as $location ) : ?><option value="<?php echo esc_attr( $location ); ?>"<?php selected( $pickup_location, $location ); ?>><?php echo esc_html( $location ); ?></option><?php endforeach; ?></select></label>
                        <label><?php esc_html_e( 'Return location', 'rentacar-venezia-v2' ); ?><select name="dropoff_location"><?php foreach ( $location_options as $location ) : ?><option value="<?php echo esc_attr( $location ); ?>"<?php selected( $dropoff_location, $location ); ?>><?php echo esc_html( $location ); ?></option><?php endforeach; ?></select></label>
                        <label><?php esc_html_e( 'Pickup date', 'rentacar-venezia-v2' ); ?><input name="pickup_date" type="date" value="<?php echo esc_attr( $trip['pickup_date'] ?? '' ); ?>"></label>
                        <label><?php esc_html_e( 'Pickup time', 'rentacar-venezia-v2' ); ?><input name="pickup_time" type="time" value="<?php echo esc_attr( $trip['pickup_time'] ?? '' ); ?>"></label>
                        <label><?php esc_html_e( 'Return date', 'rentacar-venezia-v2' ); ?><input name="return_date" type="date" value="<?php echo esc_attr( $trip['return_date'] ?? '' ); ?>"></label>
                        <label><?php esc_html_e( 'Return time', 'rentacar-venezia-v2' ); ?><input name="return_time" type="time" value="<?php echo esc_attr( $trip['return_time'] ?? '' ); ?>"></label>
                    </div>
                </fieldset>
                <button class="button" type="submit"><?php esc_html_e( 'Apply filters', 'rentacar-venezia-v2' ); ?></button>
            </form>
            <p class="trip-filter-section__help"><?php esc_html_e( 'The dates help us prepare your request. Availability is confirmed personally.', 'rentacar-venezia-v2' ); ?></p>
        </div>
    </section>
    <section class="trust-strip" aria-label="<?php esc_attr_e( 'Service highlights', 'rentacar-venezia-v2' ); ?>"><div class="rc-container trust-strip__items"><p class="trust-strip__item"><span aria-hidden="true"><svg viewBox="0 0 20 20"><path d="M3 12h14l-2-5H5l-2 5Zm2 0v3m10-3v3M6 15h.01M14 15h.01"></path></svg></span><?php esc_html_e( 'Car rental in Venice and Treviso', 'rentacar-venezia-v2' ); ?></p><p class="trust-strip__item"><span aria-hidden="true"><svg viewBox="0 0 20 20"><path d="M10 18s6-5.1 6-10a6 6 0 1 0-12 0c0 4.9 6 10 6 10Z"></path><circle cx="10" cy="8" r="2"></circle></svg></span><?php esc_html_e( 'Venice Marco Polo Airport pickup', 'rentacar-venezia-v2' ); ?></p><p class="trust-strip__item"><span aria-hidden="true"><svg viewBox="0 0 20 20"><path d="M3 10h14M10 3v14M7 16h6"></path></svg></span><?php esc_html_e( 'Treviso Airport arrivals', 'rentacar-venezia-v2' ); ?></p><p class="trust-strip__item"><span aria-hidden="true"><svg viewBox="0 0 20 20"><path d="m4 10 3.5 3.5L16 5"></path></svg></span><?php esc_html_e( 'Availability and final price confirmed personally', 'rentacar-venezia-v2' ); ?></p></div></section>
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
    <section class="section process-section" aria-labelledby="rental-process-title"><div class="rc-container process-section__layout"><header class="process-section__header"><p class="eyebrow"><?php esc_html_e( 'How car rental works in Venice and Treviso', 'rentacar-venezia-v2' ); ?></p><h2 id="rental-process-title"><?php esc_html_e( 'Car rental in Venice and Treviso, in three clear steps.', 'rentacar-venezia-v2' ); ?></h2><p><?php esc_html_e( 'Choose a car, send your request with pickup details, then receive personal confirmation from our local team.', 'rentacar-venezia-v2' ); ?></p><a class="text-link process-section__link" href="<?php echo esc_url( rentacar_venezia_v2_fleet_url() ); ?>"><?php esc_html_e( 'Explore the full fleet', 'rentacar-venezia-v2' ); ?></a></header><ol class="steps"><li><span class="steps__number" aria-hidden="true">01</span><div class="steps__content"><h3><?php esc_html_e( 'Choose the right car', 'rentacar-venezia-v2' ); ?></h3><p><?php esc_html_e( 'Browse our fleet for Venice and Treviso and select the vehicle that suits your journey.', 'rentacar-venezia-v2' ); ?></p></div></li><li><span class="steps__number" aria-hidden="true">02</span><div class="steps__content"><h3><?php esc_html_e( 'Send your rental request', 'rentacar-venezia-v2' ); ?></h3><p><?php esc_html_e( 'Add your pickup and return location, dates and contact details in one simple request.', 'rentacar-venezia-v2' ); ?></p></div></li><li><span class="steps__number" aria-hidden="true">03</span><div class="steps__content"><h3><?php esc_html_e( 'Receive personal confirmation', 'rentacar-venezia-v2' ); ?></h3><p><?php esc_html_e( 'We check availability, final price and rental conditions, then contact you directly.', 'rentacar-venezia-v2' ); ?></p></div></li></ol></div></section>
    <section class="section rc-container local-panel"><div><p class="eyebrow"><?php esc_html_e( 'Venice and Treviso', 'rentacar-venezia-v2' ); ?></p><h2><?php esc_html_e( 'A local team for your next trip.', 'rentacar-venezia-v2' ); ?></h2></div><div><p><?php esc_html_e( 'Tell us where you need the car and when. We will review your request personally and explain the available options.', 'rentacar-venezia-v2' ); ?></p><a class="text-link" href="<?php echo esc_url( rentacar_venezia_v2_fleet_url() ); ?>"><?php esc_html_e( 'Explore the full fleet', 'rentacar-venezia-v2' ); ?></a></div></section>
    <?php if ( '' !== trim( $homepage_content ) ) : ?>
        <section class="section homepage-content" aria-label="<?php esc_attr_e( 'More about car rental in Venice and Treviso', 'rentacar-venezia-v2' ); ?>">
            <div class="rc-container homepage-content__inner">
                <?php echo $homepage_content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- rendered through the_content. ?>
            </div>
        </section>
    <?php endif; ?>
    <?php if ( $whatsapp_url ) : ?><section class="section rc-container final-cta"><div><p class="eyebrow"><?php esc_html_e( 'Need help choosing?', 'rentacar-venezia-v2' ); ?></p><h2><?php esc_html_e( 'Talk to our local team.', 'rentacar-venezia-v2' ); ?></h2></div><a class="button button--whatsapp" href="<?php echo esc_url( $whatsapp_url ); ?>"><?php esc_html_e( 'Contact us on WhatsApp', 'rentacar-venezia-v2' ); ?></a></section><?php endif; ?>
</main>
<?php get_template_part( 'template-parts/enquiry/reservation-modal' ); get_footer();
