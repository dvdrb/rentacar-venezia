<?php
defined( 'ABSPATH' ) || exit;

get_header();

$vehicles = class_exists( 'Rentacar_Core_Vehicle_Repository' ) ? ( new Rentacar_Core_Vehicle_Repository() )->query( array( 'posts_per_page' => 6, 'orderby' => 'date', 'order' => 'DESC' ) ) : array();
$whatsapp_url = rentacar_venezia_v2_whatsapp_url();
?>
<main id="main-content" class="site-main site-main--home">
    <section class="hero">
        <div class="rc-container hero__grid hero__grid--simple">
            <div class="hero__copy">
                <p class="eyebrow"><?php esc_html_e( 'Rent a Car Venezia', 'rentacar-venezia-v2' ); ?></p>
                <h1><?php esc_html_e( 'Car rental in Venice and Treviso', 'rentacar-venezia-v2' ); ?></h1>
                <p class="hero__tagline"><?php esc_html_e( 'Choose a car. Send a request. We confirm personally.', 'rentacar-venezia-v2' ); ?></p>
                <p><?php esc_html_e( 'Select your preferred vehicle, complete one short reservation form, and our team will check availability and contact you.', 'rentacar-venezia-v2' ); ?></p>
                <div class="hero__actions"><a class="button" href="<?php echo esc_url( rentacar_venezia_v2_fleet_url() ); ?>"><?php esc_html_e( 'View cars', 'rentacar-venezia-v2' ); ?></a><?php if ( $whatsapp_url ) : ?><a class="button button--whatsapp" href="<?php echo esc_url( $whatsapp_url ); ?>"><?php esc_html_e( 'WhatsApp', 'rentacar-venezia-v2' ); ?></a><?php endif; ?></div>
            </div>
        </div>
    </section>
    <section class="trust-strip" aria-label="<?php esc_attr_e( 'Service highlights', 'rentacar-venezia-v2' ); ?>"><div class="rc-container trust-strip__items"><p><?php esc_html_e( 'Local assistance', 'rentacar-venezia-v2' ); ?></p><p><?php esc_html_e( 'Multilingual support', 'rentacar-venezia-v2' ); ?></p><p><?php esc_html_e( 'Availability confirmed personally', 'rentacar-venezia-v2' ); ?></p></div></section>
    <section class="section rc-container" aria-labelledby="popular-cars-title">
        <div class="section-heading"><div><p class="eyebrow"><?php esc_html_e( 'Explore our fleet', 'rentacar-venezia-v2' ); ?></p><h2 id="popular-cars-title"><?php esc_html_e( 'Choose your preferred vehicle', 'rentacar-venezia-v2' ); ?></h2></div><a class="text-link" href="<?php echo esc_url( rentacar_venezia_v2_fleet_url() ); ?>"><?php esc_html_e( 'View all cars', 'rentacar-venezia-v2' ); ?></a></div>
        <?php get_template_part( 'template-parts/global/notice' ); ?>
        <div class="vehicle-grid"><?php foreach ( $vehicles as $vehicle ) : get_template_part( 'template-parts/vehicle/card', null, array( 'vehicle' => $vehicle ) ); endforeach; ?></div>
    </section>
    <section class="section section--muted"><div class="rc-container"><p class="eyebrow"><?php esc_html_e( 'How it works', 'rentacar-venezia-v2' ); ?></p><h2><?php esc_html_e( 'One clear request, personally confirmed.', 'rentacar-venezia-v2' ); ?></h2><ol class="steps"><li><strong><?php esc_html_e( 'Choose a vehicle', 'rentacar-venezia-v2' ); ?></strong><span><?php esc_html_e( 'Browse our fleet and select your preferred car.', 'rentacar-venezia-v2' ); ?></span></li><li><strong><?php esc_html_e( 'Send the reservation request', 'rentacar-venezia-v2' ); ?></strong><span><?php esc_html_e( 'Enter your rental and contact details in one short form.', 'rentacar-venezia-v2' ); ?></span></li><li><strong><?php esc_html_e( 'We contact you', 'rentacar-venezia-v2' ); ?></strong><span><?php esc_html_e( 'We check availability and confirm everything with you.', 'rentacar-venezia-v2' ); ?></span></li></ol></div></section>
    <section class="section rc-container airport-information"><div><p class="eyebrow"><?php esc_html_e( 'Venice and Treviso', 'rentacar-venezia-v2' ); ?></p><h2><?php esc_html_e( 'Plan your airport car rental with a local team.', 'rentacar-venezia-v2' ); ?></h2></div><p><?php esc_html_e( 'Tell us where you need the car and when. We will review your request personally and explain the available options.', 'rentacar-venezia-v2' ); ?></p></section>
</main>
<?php get_template_part( 'template-parts/enquiry/reservation-modal' ); ?>
<?php get_footer(); ?>
