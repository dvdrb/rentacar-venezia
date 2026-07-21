<?php
defined( 'ABSPATH' ) || exit;

get_header();

$vehicles = array();
if ( class_exists( 'Rentacar_Core_Vehicle_Repository' ) ) {
    $vehicles = ( new Rentacar_Core_Vehicle_Repository() )->query(
        array(
            'posts_per_page' => 6,
            'orderby'        => 'date',
            'order'          => 'DESC',
        )
    );
}
?>
<main id="main-content" class="site-main site-main--home">
    <section class="hero">
        <div class="rc-container hero__grid">
            <div class="hero__copy">
                <p class="eyebrow"><?php esc_html_e( 'Rent a car Venezia', 'rentacar-venezia-v2' ); ?></p>
                <h1><?php esc_html_e( 'Car Rental in Venice', 'rentacar-venezia-v2' ); ?></h1>
                <p class="hero__tagline"><?php esc_html_e( 'Simple. Local. Personal.', 'rentacar-venezia-v2' ); ?></p>
                <p><?php esc_html_e( 'Tell us where and when you need the car. We check availability and confirm the vehicle and final price personally.', 'rentacar-venezia-v2' ); ?></p>
            </div>
            <form class="trip-form" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="<?php esc_attr_e( 'Tell us about your trip', 'rentacar-venezia-v2' ); ?>">
                <input type="hidden" name="rc_trip" value="1">
                <fieldset>
                    <legend><?php esc_html_e( 'Tell us about your trip', 'rentacar-venezia-v2' ); ?></legend>
                    <div class="trip-form__grid">
                        <label><?php esc_html_e( 'Pickup location', 'rentacar-venezia-v2' ); ?><input name="pickup_location" type="text" autocomplete="off"></label>
                        <label><?php esc_html_e( 'Drop-off location', 'rentacar-venezia-v2' ); ?><input name="dropoff_location" type="text" autocomplete="off"></label>
                        <label><?php esc_html_e( 'Pickup date', 'rentacar-venezia-v2' ); ?><input name="pickup_date" type="date"></label>
                        <label><?php esc_html_e( 'Pickup time', 'rentacar-venezia-v2' ); ?><input name="pickup_time" type="time"></label>
                        <label><?php esc_html_e( 'Return date', 'rentacar-venezia-v2' ); ?><input name="return_date" type="date"></label>
                        <label><?php esc_html_e( 'Return time', 'rentacar-venezia-v2' ); ?><input name="return_time" type="time"></label>
                    </div>
                </fieldset>
                <button class="button" type="submit"><?php esc_html_e( 'Find a suitable car', 'rentacar-venezia-v2' ); ?></button>
                <p class="form-help"><?php esc_html_e( 'This is a request for availability, not a reservation.', 'rentacar-venezia-v2' ); ?></p>
            </form>
        </div>
    </section>

    <section class="section rc-container" aria-labelledby="popular-cars-title">
        <div class="section-heading"><div><p class="eyebrow"><?php esc_html_e( 'Explore our fleet', 'rentacar-venezia-v2' ); ?></p><h2 id="popular-cars-title"><?php esc_html_e( 'Popular cars', 'rentacar-venezia-v2' ); ?></h2></div></div>
        <?php get_template_part( 'template-parts/global/notice' ); ?>
        <div class="vehicle-grid">
            <?php foreach ( $vehicles as $vehicle ) : ?>
                <?php get_template_part( 'template-parts/vehicle/card', null, array( 'vehicle' => $vehicle ) ); ?>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="section section--muted">
        <div class="rc-container"><p class="eyebrow"><?php esc_html_e( 'How it works', 'rentacar-venezia-v2' ); ?></p><h2><?php esc_html_e( 'A personal availability check, in four clear steps.', 'rentacar-venezia-v2' ); ?></h2>
            <ol class="steps"><li><strong><?php esc_html_e( 'Tell us your trip', 'rentacar-venezia-v2' ); ?></strong><span><?php esc_html_e( 'Share pickup and return details.', 'rentacar-venezia-v2' ); ?></span></li><li><strong><?php esc_html_e( 'Choose your preferred car', 'rentacar-venezia-v2' ); ?></strong><span><?php esc_html_e( 'Browse the vehicle catalogue.', 'rentacar-venezia-v2' ); ?></span></li><li><strong><?php esc_html_e( 'Send your request', 'rentacar-venezia-v2' ); ?></strong><span><?php esc_html_e( 'Continue on WhatsApp when ready.', 'rentacar-venezia-v2' ); ?></span></li><li><strong><?php esc_html_e( 'We confirm availability', 'rentacar-venezia-v2' ); ?></strong><span><?php esc_html_e( 'Our team confirms the model, price and conditions.', 'rentacar-venezia-v2' ); ?></span></li></ol>
        </div>
    </section>
</main>
<?php get_footer(); ?>
