<?php
defined( 'ABSPATH' ) || exit;

$vehicles = class_exists( 'Rentacar_Core_Vehicle_Repository' ) ? ( new Rentacar_Core_Vehicle_Repository() )->query( array( 'posts_per_page' => 6, 'orderby' => 'date', 'order' => 'DESC' ) ) : array();
$hero_vehicle = $vehicles ? $vehicles[0] : null;
$hero_image_id = $hero_vehicle ? rentacar_venezia_v2_vehicle_image_id( $hero_vehicle ) : 0;
$whatsapp_url = rentacar_venezia_v2_whatsapp_url();

get_header();
?>
<main id="main-content" class="site-main site-main--home">
    <section class="hero">
        <div class="rc-container hero__grid">
            <div class="hero__copy">
                <p class="eyebrow"><?php esc_html_e( 'Rent a Car Venezia', 'rentacar-venezia-v2' ); ?></p>
                <h1><?php esc_html_e( 'Car rental in Venice and Treviso', 'rentacar-venezia-v2' ); ?></h1>
                <p class="hero__tagline"><?php esc_html_e( 'Choose a car. Send a request. We confirm personally.', 'rentacar-venezia-v2' ); ?></p>
                <p><?php esc_html_e( 'Select your preferred vehicle, complete one short reservation form, and our team will check availability and contact you.', 'rentacar-venezia-v2' ); ?></p>
                <div class="hero__actions"><a class="button" href="#fleet"><?php esc_html_e( 'Choose your car', 'rentacar-venezia-v2' ); ?></a><?php if ( $whatsapp_url ) : ?><a class="button button--whatsapp" href="<?php echo esc_url( $whatsapp_url ); ?>"><?php esc_html_e( 'WhatsApp', 'rentacar-venezia-v2' ); ?></a><?php endif; ?></div>
            </div>
            <?php if ( $hero_vehicle && $hero_image_id ) : ?><figure class="hero__vehicle"><?php echo wp_kses_post( wp_get_attachment_image( $hero_image_id, 'large', false, array( 'loading' => 'eager', 'fetchpriority' => 'high', 'sizes' => '(min-width: 960px) 42vw, 100vw', 'alt' => '' ) ) ); ?><figcaption><?php echo esc_html( rentacar_venezia_v2_vehicle_title( $hero_vehicle ) ); ?></figcaption></figure><?php endif; ?>
        </div>
    </section>
    <section class="trust-strip" aria-label="<?php esc_attr_e( 'Service highlights', 'rentacar-venezia-v2' ); ?>"><div class="rc-container trust-strip__items"><p><?php esc_html_e( 'Local assistance', 'rentacar-venezia-v2' ); ?></p><p><?php esc_html_e( 'Multilingual support', 'rentacar-venezia-v2' ); ?></p><p><?php esc_html_e( 'Availability confirmed personally', 'rentacar-venezia-v2' ); ?></p></div></section>
    <section id="fleet" class="section rc-container" aria-labelledby="featured-cars-title">
        <div class="section-heading"><div><p class="eyebrow"><?php esc_html_e( 'Explore the fleet', 'rentacar-venezia-v2' ); ?></p><h2 id="featured-cars-title"><?php esc_html_e( 'Choose your preferred car', 'rentacar-venezia-v2' ); ?></h2></div><a class="text-link" href="<?php echo esc_url( rentacar_venezia_v2_fleet_url() ); ?>"><?php esc_html_e( 'View all cars', 'rentacar-venezia-v2' ); ?></a></div>
        <?php get_template_part( 'template-parts/global/notice' ); ?>
        <?php if ( $vehicles ) : ?>
            <div class="vehicle-grid vehicle-grid--featured"><?php foreach ( $vehicles as $vehicle ) : get_template_part( 'template-parts/vehicle/card', null, array( 'vehicle' => $vehicle ) ); endforeach; ?></div>
        <?php else : ?>
            <section class="empty-state"><h3><?php esc_html_e( 'Vehicles will appear here when they are ready to show.', 'rentacar-venezia-v2' ); ?></h3></section>
        <?php endif; ?>
    </section>
    <section class="section section--muted"><div class="rc-container"><p class="eyebrow"><?php esc_html_e( 'How it works', 'rentacar-venezia-v2' ); ?></p><h2><?php esc_html_e( 'One clear request, personally confirmed.', 'rentacar-venezia-v2' ); ?></h2><ol class="steps"><li><strong><?php esc_html_e( 'Choose a vehicle', 'rentacar-venezia-v2' ); ?></strong><span><?php esc_html_e( 'Browse our real fleet and select the car you prefer.', 'rentacar-venezia-v2' ); ?></span></li><li><strong><?php esc_html_e( 'Send the reservation request', 'rentacar-venezia-v2' ); ?></strong><span><?php esc_html_e( 'Share your trip and contact details in one short form.', 'rentacar-venezia-v2' ); ?></span></li><li><strong><?php esc_html_e( 'We contact you', 'rentacar-venezia-v2' ); ?></strong><span><?php esc_html_e( 'Our team checks availability and confirms everything with you.', 'rentacar-venezia-v2' ); ?></span></li></ol></div></section>
    <section class="section rc-container local-panel"><div><p class="eyebrow"><?php esc_html_e( 'Venice and Treviso', 'rentacar-venezia-v2' ); ?></p><h2><?php esc_html_e( 'A local team for your next trip.', 'rentacar-venezia-v2' ); ?></h2></div><div><p><?php esc_html_e( 'Tell us where you need the car and when. We will review your request personally and explain the available options.', 'rentacar-venezia-v2' ); ?></p><a class="text-link" href="<?php echo esc_url( rentacar_venezia_v2_fleet_url() ); ?>"><?php esc_html_e( 'Explore the full fleet', 'rentacar-venezia-v2' ); ?></a></div></section>
    <?php if ( $whatsapp_url ) : ?><section class="section rc-container final-cta"><div><p class="eyebrow"><?php esc_html_e( 'Need help choosing?', 'rentacar-venezia-v2' ); ?></p><h2><?php esc_html_e( 'Talk to our local team.', 'rentacar-venezia-v2' ); ?></h2></div><a class="button button--whatsapp" href="<?php echo esc_url( $whatsapp_url ); ?>"><?php esc_html_e( 'Contact us on WhatsApp', 'rentacar-venezia-v2' ); ?></a></section><?php endif; ?>
</main>
<?php get_template_part( 'template-parts/enquiry/reservation-modal' ); get_footer();
