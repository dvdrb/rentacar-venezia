<?php
defined( 'ABSPATH' ) || exit;

$success            = isset( $_GET['reservation_sent'], $_GET['reservation_ref'] ) && '1' === sanitize_text_field( wp_unslash( $_GET['reservation_sent'] ) );
$reference          = $success ? sanitize_text_field( wp_unslash( $_GET['reservation_ref'] ) ) : '';
$trip               = rentacar_venezia_v2_trip_query();
$reservation_extras = class_exists( 'Rentacar_Core_Reservation_Extras' ) ? Rentacar_Core_Reservation_Extras::enabled() : array();
$insurance_packages = class_exists( 'Rentacar_Core_Rental_Policy' ) ? Rentacar_Core_Rental_Policy::get()['insurance'] : array();
$locations          = rentacar_venezia_v2_pickup_locations();
$location_values    = array_values( wp_list_pluck( $locations, 'value' ) );
$default_location   = $location_values ? reset( $location_values ) : '';
$pickup_location    = in_array( $trip['pickup_location'] ?? '', $location_values, true ) ? $trip['pickup_location'] : $default_location;
$return_location    = in_array( $trip['dropoff_location'] ?? '', $location_values, true ) ? $trip['dropoff_location'] : $pickup_location;
$inter_airport_fee  = class_exists( 'Rentacar_Core_Rental_Policy' ) && count( $location_values ) > 1 ? Rentacar_Core_Rental_Policy::inter_airport_surcharge_cents( $location_values[0], $location_values[1] ) / 100 : 0;
$minimum_rental_days = class_exists( 'Rentacar_Core_Rental_Policy' ) ? Rentacar_Core_Rental_Policy::minimum_rental_days() : 3;
$reservation_times = array();
for ( $minutes = 0; $minutes < 1440; $minutes += 15 ) {
    $reservation_times[] = sprintf( '%02d:%02d', (int) floor( $minutes / 60 ), $minutes % 60 );
}
$pickup_time = in_array( $trip['pickup_time'] ?? '', $reservation_times, true ) ? $trip['pickup_time'] : '';
$return_time = in_array( $trip['return_time'] ?? '', $reservation_times, true ) ? $trip['return_time'] : '';
?>
<section class="reservation-modal reservation-modal--inline" data-reservation-modal data-reservation-initial-open="<?php echo $success ? '1' : '0'; ?>" aria-labelledby="reservation-modal-title" tabindex="-1">
  <div class="reservation-modal__backdrop" data-reservation-close></div>
  <div class="reservation-modal__panel<?php echo $success ? ' reservation-modal__panel--success' : ''; ?>">
    <header class="reservation-modal__header">
      <h2 id="reservation-modal-title" data-reservation-modal-title data-request-title="<?php echo esc_attr( __( 'Reservation request', 'rentacar-venezia-v2' ) ); ?>" data-success-title="<?php echo esc_attr( __( 'Request received', 'rentacar-venezia-v2' ) ); ?>" tabindex="-1"><?php echo esc_html( $success ? __( 'Request received', 'rentacar-venezia-v2' ) : __( 'Reservation request', 'rentacar-venezia-v2' ) ); ?></h2>
      <button class="reservation-modal__close" type="button" data-reservation-close aria-label="<?php esc_attr_e( 'Close reservation form', 'rentacar-venezia-v2' ); ?>">×</button>
    </header>
    <div data-reservation-form-wrap<?php echo $success ? ' hidden' : ''; ?>>
        <div class="reservation-modal__grid">
          <aside class="reservation-summary" data-reservation-summary>
          <p class="reservation-summary__label"><?php esc_html_e( 'Your car', 'rentacar-venezia-v2' ); ?></p>
          <div class="reservation-summary__details">
            <h3 data-reservation-title><?php esc_html_e( 'Selected vehicle', 'rentacar-venezia-v2' ); ?></h3>
            <p data-reservation-specifications></p>
            <p class="reservation-summary__prices" data-reservation-prices></p>
          </div>
        </aside>
        <form class="reservation-form" data-reservation-form data-hotel-locations="<?php echo esc_attr( wp_json_encode( rentacar_venezia_v2_hotel_location_values() ) ); ?>" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post" novalidate>
          <input type="hidden" name="action" value="rentacar_submit_reservation">
          <input type="hidden" name="vehicle_id" data-reservation-vehicle-id value="">
          <input type="hidden" name="started_at" value="<?php echo esc_attr( time() ); ?>">
          <?php wp_nonce_field( 'rentacar_submit_reservation', 'rentacar_reservation_nonce' ); ?>
          <p id="reservation-errors" class="reservation-form__error" data-reservation-errors tabindex="-1" aria-live="assertive"></p>
          <div class="honeypot" aria-hidden="true"><label><?php esc_html_e( 'Website', 'rentacar-venezia-v2' ); ?><input name="website" tabindex="-1" autocomplete="off"></label></div>
          <p class="reservation-form__progress" data-reservation-progress aria-live="polite"><?php esc_html_e( '1 of 2 · Trip', 'rentacar-venezia-v2' ); ?></p>

          <div class="reservation-step is-active" data-reservation-step="1">
          <fieldset class="reservation-form__section">
            <legend><?php esc_html_e( 'Your trip', 'rentacar-venezia-v2' ); ?></legend>
            <div class="reservation-form__two">
              <label><?php esc_html_e( 'Pickup date', 'rentacar-venezia-v2' ); ?><input name="pickup_date" type="date" min="<?php echo esc_attr( wp_date( 'Y-m-d' ) ); ?>" required value="<?php echo esc_attr( $trip['pickup_date'] ?? '' ); ?>"></label>
              <label><?php esc_html_e( 'Pickup time', 'rentacar-venezia-v2' ); ?><select name="pickup_time" required><option value="" disabled<?php selected( '', $pickup_time ); ?>><?php esc_html_e( 'Select a time', 'rentacar-venezia-v2' ); ?></option><?php foreach ( $reservation_times as $time ) : ?><option value="<?php echo esc_attr( $time ); ?>"<?php selected( $pickup_time, $time ); ?>><?php echo esc_html( $time ); ?></option><?php endforeach; ?></select></label>
              <label><?php esc_html_e( 'Return date', 'rentacar-venezia-v2' ); ?><input name="return_date" type="date" required value="<?php echo esc_attr( $trip['return_date'] ?? '' ); ?>"></label>
              <label><?php esc_html_e( 'Return time', 'rentacar-venezia-v2' ); ?><select name="return_time" required><option value="" disabled<?php selected( '', $return_time ); ?>><?php esc_html_e( 'Select a time', 'rentacar-venezia-v2' ); ?></option><?php foreach ( $reservation_times as $time ) : ?><option value="<?php echo esc_attr( $time ); ?>"<?php selected( $return_time, $time ); ?>><?php echo esc_html( $time ); ?></option><?php endforeach; ?></select></label>
              <label><?php esc_html_e( 'Pickup location', 'rentacar-venezia-v2' ); ?><select name="pickup_location" required data-reservation-pickup-location><?php foreach ( $locations as $location ) : ?><option value="<?php echo esc_attr( $location['value'] ); ?>"<?php selected( $pickup_location, $location['value'] ); ?>><?php echo esc_html( $location['label'] ); ?></option><?php endforeach; ?></select></label>
              <label class="check-label reservation-form__different-return"><input type="checkbox" data-reservation-return-different><span><?php esc_html_e( 'Return to a different location', 'rentacar-venezia-v2' ); ?></span></label>
              <label data-reservation-return-location><?php esc_html_e( 'Return location', 'rentacar-venezia-v2' ); ?><select name="return_location" required data-reservation-return-location><?php foreach ( $locations as $location ) : ?><option value="<?php echo esc_attr( $location['value'] ); ?>"<?php selected( $return_location, $location['value'] ); ?>><?php echo esc_html( $location['label'] ); ?></option><?php endforeach; ?></select></label>
            </div>
          </fieldset>
          <?php if ( $inter_airport_fee ) : ?><p class="reservation-location-fee" data-reservation-location-fee hidden><?php echo esc_html( sprintf( __( 'A €%s transfer fee applies when pickup and return airports differ.', 'rentacar-venezia-v2' ), number_format_i18n( $inter_airport_fee, 2 ) ) ); ?></p><?php endif; ?>
          <details class="reservation-form__options">
            <summary><?php esc_html_e( 'Add extras or insurance (optional)', 'rentacar-venezia-v2' ); ?></summary>
            <div class="reservation-form__options-body">
              <fieldset class="reservation-insurance">
                <legend><?php esc_html_e( 'Insurance', 'rentacar-venezia-v2' ); ?></legend>
                <?php foreach ( $insurance_packages as $key => $package ) : if ( empty( $package['enabled'] ) ) { continue; } ?>
                  <label class="check-label"><input name="insurance" type="radio" value="<?php echo esc_attr( $key ); ?>" required<?php checked( 'base', $key ); ?>><span><?php echo esc_html( $package['label'] ); ?> — <?php echo esc_html( sprintf( '€%s %s', number_format_i18n( $package['daily_cents'] / 100, 2 ), __( 'per rental day', 'rentacar-venezia-v2' ) ) ); ?></span></label>
                <?php endforeach; ?>
              </fieldset>
              <?php if ( $reservation_extras ) : ?>
                <fieldset class="reservation-extras"><legend><?php esc_html_e( 'Optional extras', 'rentacar-venezia-v2' ); ?></legend>
                  <?php foreach ( $reservation_extras as $extra ) : ?>
                    <?php $extra_price = 'per_day' === $extra['pricing_type'] ? sprintf( __( '€%s per day', 'rentacar-venezia-v2' ), number_format_i18n( $extra['price'], 2 ) ) : ( 'fixed' === $extra['pricing_type'] ? sprintf( __( '€%s fixed', 'rentacar-venezia-v2' ), number_format_i18n( $extra['price'], 2 ) ) : __( 'Price confirmed by our team', 'rentacar-venezia-v2' ) ); ?>
                    <label class="check-label reservation-extras__item"><input name="extras[]" type="checkbox" value="<?php echo esc_attr( $extra['key'] ); ?>"><span><strong><?php echo esc_html( $extra['label'] ); ?></strong><small><?php echo esc_html( $extra_price ); ?></small></span></label>
                  <?php endforeach; ?>
                </fieldset>
              <?php endif; ?>
            </div>
          </details>
          <div class="reservation-form__step-actions"><span></span><button class="button" type="button" data-reservation-continue><?php esc_html_e( 'Continue', 'rentacar-venezia-v2' ); ?></button></div>
          </div>

          <div class="reservation-step" data-reservation-step="2">
          <fieldset class="reservation-form__section">
            <legend><?php esc_html_e( 'How can we reach you?', 'rentacar-venezia-v2' ); ?></legend>
            <label><?php esc_html_e( 'Full name', 'rentacar-venezia-v2' ); ?><input name="full_name" autocomplete="name" required></label>
            <?php get_template_part( 'template-parts/forms/phone-field', null, array( 'id' => 'reservation-phone' ) ); ?>
            <label><?php esc_html_e( 'Email', 'rentacar-venezia-v2' ); ?><input name="email" type="email" autocomplete="email" required></label>
          </fieldset>

          <p class="reservation-form__hotel-details" data-reservation-hotel-details hidden><?php echo esc_html( rentacar_venezia_v2_hotel_details_instruction() ); ?></p>
          <details class="reservation-form__optional-note"><summary><?php esc_html_e( 'Add a note (optional)', 'rentacar-venezia-v2' ); ?></summary><label><?php esc_html_e( 'Message', 'rentacar-venezia-v2' ); ?><textarea name="message" rows="3"></textarea></label></details>
          <section class="reservation-estimate" aria-live="polite" data-reservation-estimate hidden><h3><?php esc_html_e( 'Your estimate', 'rentacar-venezia-v2' ); ?></h3><div data-reservation-estimate-content></div></section>
          <fieldset class="reservation-form__consents">
            <legend class="screen-reader-text"><?php esc_html_e( 'Request confirmations', 'rentacar-venezia-v2' ); ?></legend>
            <label class="check-label"><input name="terms" type="checkbox" value="1" required><span><?php esc_html_e( 'I accept the Terms and Conditions.', 'rentacar-venezia-v2' ); ?></span></label>
          </fieldset>
          <div class="reservation-form__step-actions"><button class="button button--secondary" type="button" data-reservation-back><?php esc_html_e( 'Edit trip', 'rentacar-venezia-v2' ); ?></button><button class="button" type="submit"><?php esc_html_e( 'Send request', 'rentacar-venezia-v2' ); ?></button></div>
          </div>
        </form>
      </div>
    </div>
    <div class="reservation-success" data-reservation-success<?php echo $success ? '' : ' hidden'; ?> aria-live="polite">
      <span class="reservation-success__icon" aria-hidden="true">✓</span>
      <div class="reservation-success__content">
        <p><?php esc_html_e( 'We received your reservation request. Our team will check the selected vehicle and contact you to confirm availability, the final price and rental conditions.', 'rentacar-venezia-v2' ); ?></p>
        <p class="reservation-success__reference" data-reservation-reference><?php echo $reference ? esc_html( sprintf( __( 'Reference: %s', 'rentacar-venezia-v2' ), $reference ) ) : ''; ?></p>
      </div>
      <button class="button" type="button" data-reservation-close><?php esc_html_e( 'Close', 'rentacar-venezia-v2' ); ?></button>
    </div>
  </div>
</section>
