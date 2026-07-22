<?php
defined( 'ABSPATH' ) || exit;

get_header();

$vehicle = class_exists( 'Rentacar_Core_Vehicle_Repository' ) ? ( new Rentacar_Core_Vehicle_Repository() )->find( get_queried_object_id() ) : null;
$trip = rentacar_venezia_v2_trip_query();

if ( ! $vehicle ) {
    get_template_part( 'index' );
    get_footer();
    return;
}

$gallery = $vehicle->get( 'vehicle_gallery' );
$image_ids = $gallery->all_image_ids();
$from_band = $vehicle->get( 'pricing_bands' )->for_days( 1 );
?>
<main id="main-content" class="site-main vehicle-page">
    <div class="rc-container">
        <nav class="breadcrumbs" aria-label="<?php esc_attr_e( 'Breadcrumbs', 'rentacar-venezia-v2' ); ?>">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'rentacar-venezia-v2' ); ?></a><span aria-hidden="true">/</span><a href="<?php echo esc_url( rentacar_venezia_v2_fleet_url() ); ?>"><?php esc_html_e( 'Fleet', 'rentacar-venezia-v2' ); ?></a><span aria-hidden="true">/</span><span aria-current="page"><?php echo esc_html( $vehicle->get( 'title' ) ); ?></span>
        </nav>
        <div class="vehicle-page__grid">
            <section class="vehicle-page__content">
                <div class="vehicle-gallery" aria-label="<?php esc_attr_e( 'Vehicle gallery', 'rentacar-venezia-v2' ); ?>">
                    <?php foreach ( $image_ids as $index => $image_id ) : ?>
                        <figure class="vehicle-gallery__image<?php echo 0 === $index ? ' vehicle-gallery__image--primary' : ''; ?>">
                            <?php echo wp_kses_post( wp_get_attachment_image( $image_id, 0 === $index ? 'large' : 'medium_large', false, array( 'loading' => 0 === $index ? 'eager' : 'lazy', 'sizes' => 0 === $index ? '(min-width: 960px) 65vw, 100vw' : '(min-width: 960px) 30vw, 50vw' ) ) ); ?>
                        </figure>
                    <?php endforeach; ?>
                </div>
                <header class="vehicle-page__heading">
                    <p class="eyebrow"><?php esc_html_e( 'Choose your preferred car', 'rentacar-venezia-v2' ); ?></p>
                    <h1><?php echo esc_html( $vehicle->get( 'title' ) ); ?></h1>
                    <ul class="specification-list" aria-label="<?php esc_attr_e( 'Vehicle specifications', 'rentacar-venezia-v2' ); ?>">
                        <li><?php echo esc_html( $vehicle->get( 'transmission' ) ); ?></li>
                        <li><?php echo esc_html( sprintf( _n( '%s passenger', '%s passengers', $vehicle->get( 'passengers' ), 'rentacar-venezia-v2' ), $vehicle->get( 'passengers' ) ) ); ?></li>
                        <li><?php echo esc_html( sprintf( _n( '%s door', '%s doors', $vehicle->get( 'doors' ), 'rentacar-venezia-v2' ), $vehicle->get( 'doors' ) ) ); ?></li>
                        <?php if ( $vehicle->get( 'air_conditioning' ) ) : ?><li><?php esc_html_e( 'Air conditioning', 'rentacar-venezia-v2' ); ?></li><?php endif; ?>
                    </ul>
                </header>
                <section class="vehicle-copy"><h2><?php esc_html_e( 'Price information', 'rentacar-venezia-v2' ); ?></h2><?php if ( $from_band ) : ?><p class="vehicle-from-price"><?php echo esc_html( sprintf( __( 'From €%s per day', 'rentacar-venezia-v2' ), number_format_i18n( $from_band->daily_price, 0 ) ) ); ?></p><?php endif; ?><p><?php esc_html_e( 'The final price is confirmed by our team after checking your trip details and availability.', 'rentacar-venezia-v2' ); ?></p></section>
            </section>
            <aside class="request-card" data-request-card data-vehicle-id="<?php echo esc_attr( $vehicle->get( 'id' ) ); ?>">
                <p class="eyebrow"><?php esc_html_e( 'Availability request', 'rentacar-venezia-v2' ); ?></p>
                <h2><?php esc_html_e( 'Ask availability for this car', 'rentacar-venezia-v2' ); ?></h2>
                <form data-request-form novalidate>
                    <fieldset><legend><?php esc_html_e( 'Your trip', 'rentacar-venezia-v2' ); ?></legend>
                        <label><?php esc_html_e( 'Pickup location', 'rentacar-venezia-v2' ); ?><input name="pickup_location" required value="<?php echo esc_attr( $trip['pickup_location'] ?? '' ); ?>"></label>
                        <label><?php esc_html_e( 'Drop-off location', 'rentacar-venezia-v2' ); ?><input name="dropoff_location" required value="<?php echo esc_attr( $trip['dropoff_location'] ?? '' ); ?>"></label>
                        <div class="request-card__dates"><label><?php esc_html_e( 'Pickup date', 'rentacar-venezia-v2' ); ?><input name="pickup_date" type="date" required value="<?php echo esc_attr( $trip['pickup_date'] ?? '' ); ?>"></label><label><?php esc_html_e( 'Pickup time', 'rentacar-venezia-v2' ); ?><input name="pickup_time" type="time" required value="<?php echo esc_attr( $trip['pickup_time'] ?? '' ); ?>"></label><label><?php esc_html_e( 'Return date', 'rentacar-venezia-v2' ); ?><input name="return_date" type="date" required value="<?php echo esc_attr( $trip['return_date'] ?? '' ); ?>"></label><label><?php esc_html_e( 'Return time', 'rentacar-venezia-v2' ); ?><input name="return_time" type="time" required value="<?php echo esc_attr( $trip['return_time'] ?? '' ); ?>"></label></div>
                    </fieldset>
                    <fieldset><legend><?php esc_html_e( 'Your details', 'rentacar-venezia-v2' ); ?></legend>
                        <label><?php esc_html_e( 'Full name', 'rentacar-venezia-v2' ); ?><input name="full_name" autocomplete="name" required></label>
                        <label><?php esc_html_e( 'Phone / WhatsApp', 'rentacar-venezia-v2' ); ?><input name="phone" autocomplete="tel" required></label>
                        <label><?php esc_html_e( 'Email', 'rentacar-venezia-v2' ); ?><input name="email" type="email" autocomplete="email" required></label>
                        <label><?php esc_html_e( 'Message (optional)', 'rentacar-venezia-v2' ); ?><textarea name="message" rows="3"></textarea></label>
                        <label class="check-label"><input name="similar_vehicle" type="checkbox" value="yes"> <?php esc_html_e( 'I accept a similar vehicle if this exact model is unavailable.', 'rentacar-venezia-v2' ); ?></label>
                        <label class="check-label"><input name="privacy" type="checkbox" required> <?php esc_html_e( 'I agree that my details will be used only to reply to this availability request.', 'rentacar-venezia-v2' ); ?></label>
                    </fieldset>
                    <button class="button" type="submit"><?php esc_html_e( 'Review availability request', 'rentacar-venezia-v2' ); ?></button>
                    <p class="form-help"><?php esc_html_e( 'Sending a request does not reserve the vehicle.', 'rentacar-venezia-v2' ); ?></p>
                </form>
                <section class="request-review" data-request-review hidden aria-live="polite">
                    <h3><?php esc_html_e( 'Review your request', 'rentacar-venezia-v2' ); ?></h3>
                    <div data-estimate-result></div>
                    <p><?php esc_html_e( 'WhatsApp opens with a prepared message. You must press Send to contact our team.', 'rentacar-venezia-v2' ); ?></p>
                    <a class="button button--whatsapp" data-whatsapp-link hidden target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Continue on WhatsApp', 'rentacar-venezia-v2' ); ?></a>
                    <p class="request-review__disabled" data-whatsapp-unconfigured><?php esc_html_e( 'WhatsApp request delivery is pending business-number configuration. No email will be sent from this form.', 'rentacar-venezia-v2' ); ?></p>
                </section>
            </aside>
        </div>
    </div>
</main>
<?php get_footer(); ?>
