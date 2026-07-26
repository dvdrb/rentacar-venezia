<?php
defined( 'ABSPATH' ) || exit;

$success            = isset( $_GET['reservation_sent'], $_GET['reservation_ref'] ) && '1' === sanitize_text_field( wp_unslash( $_GET['reservation_sent'] ) );
$reference          = $success ? sanitize_text_field( wp_unslash( $_GET['reservation_ref'] ) ) : '';
$trip               = rentacar_venezia_v2_trip_query();
$privacy_policy_url = function_exists( 'get_privacy_policy_url' ) ? get_privacy_policy_url() : '';
$reservation_extras = class_exists( 'Rentacar_Core_Reservation_Extras' ) ? Rentacar_Core_Reservation_Extras::enabled() : array();
$insurance_packages = class_exists( 'Rentacar_Core_Rental_Policy' ) ? Rentacar_Core_Rental_Policy::get()['insurance'] : array();
$locations          = rentacar_venezia_v2_pickup_locations();
$location_values    = array_values( wp_list_pluck( $locations, 'value' ) );
$default_location   = $location_values ? reset( $location_values ) : '';
$pickup_location    = in_array( $trip['pickup_location'] ?? '', $location_values, true ) ? $trip['pickup_location'] : $default_location;
$return_location    = in_array( $trip['dropoff_location'] ?? '', $location_values, true ) ? $trip['dropoff_location'] : $pickup_location;
$inter_airport_fee  = class_exists( 'Rentacar_Core_Rental_Policy' ) && count( $location_values ) > 1 ? Rentacar_Core_Rental_Policy::inter_airport_surcharge_cents( $location_values[0], $location_values[1] ) / 100 : 0;
?>
<section class="reservation-modal reservation-modal--inline" data-reservation-modal data-reservation-initial-open="<?php echo $success ? '1' : '0'; ?>" aria-labelledby="reservation-modal-title" tabindex="-1">
  <div class="reservation-modal__backdrop" data-reservation-close></div>
  <div class="reservation-modal__panel">
    <header class="reservation-modal__header">
      <h2 id="reservation-modal-title"><?php esc_html_e( 'Reservation request', 'rentacar-venezia-v2' ); ?></h2>
      <button class="reservation-modal__close" type="button" data-reservation-close aria-label="<?php esc_attr_e( 'Close reservation form', 'rentacar-venezia-v2' ); ?>">×</button>
    </header>
    <div data-reservation-form-wrap<?php echo $success ? ' hidden' : ''; ?>>
      <div class="reservation-modal__grid">
        <aside class="reservation-summary" data-reservation-summary>
          <div class="reservation-summary__image" data-reservation-image></div>
          <h3 data-reservation-title><?php esc_html_e( 'Selected vehicle', 'rentacar-venezia-v2' ); ?></h3>
          <p data-reservation-specifications></p>
          <p data-reservation-prices></p>
          <p><?php esc_html_e( 'Indicative prices only. Availability and final price are confirmed by our team.', 'rentacar-venezia-v2' ); ?></p>
        </aside>
        <form class="reservation-form" data-reservation-form data-airport-locations="<?php echo esc_attr( wp_json_encode( $location_values ) ); ?>" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post" novalidate>
          <input type="hidden" name="action" value="rentacar_submit_reservation">
          <input type="hidden" name="vehicle_id" data-reservation-vehicle-id value="">
          <input type="hidden" name="started_at" value="<?php echo esc_attr( time() ); ?>">
          <?php wp_nonce_field( 'rentacar_submit_reservation', 'rentacar_reservation_nonce' ); ?>
          <p id="reservation-errors" class="reservation-form__error" data-reservation-errors tabindex="-1" aria-live="assertive"></p>
          <div class="honeypot" aria-hidden="true"><label><?php esc_html_e( 'Website', 'rentacar-venezia-v2' ); ?><input name="website" tabindex="-1" autocomplete="off"></label></div>
          <p class="reservation-form__intro"><?php esc_html_e( 'A few details are all we need. We confirm availability and the final price personally.', 'rentacar-venezia-v2' ); ?></p>

          <fieldset class="reservation-form__section">
            <legend><?php esc_html_e( 'Your trip', 'rentacar-venezia-v2' ); ?></legend>
            <div class="reservation-form__two">
              <label><?php esc_html_e( 'Pickup date', 'rentacar-venezia-v2' ); ?><input name="pickup_date" type="date" required value="<?php echo esc_attr( $trip['pickup_date'] ?? '' ); ?>"></label>
              <label><?php esc_html_e( 'Pickup time', 'rentacar-venezia-v2' ); ?><input name="pickup_time" type="time" required value="<?php echo esc_attr( $trip['pickup_time'] ?? '' ); ?>"></label>
              <label><?php esc_html_e( 'Return date', 'rentacar-venezia-v2' ); ?><input name="return_date" type="date" required value="<?php echo esc_attr( $trip['return_date'] ?? '' ); ?>"></label>
              <label><?php esc_html_e( 'Return time', 'rentacar-venezia-v2' ); ?><input name="return_time" type="time" required value="<?php echo esc_attr( $trip['return_time'] ?? '' ); ?>"></label>
              <label><?php esc_html_e( 'Pickup location', 'rentacar-venezia-v2' ); ?><select name="pickup_location" required data-reservation-pickup-location><?php foreach ( $locations as $location ) : ?><option value="<?php echo esc_attr( $location['value'] ); ?>"<?php selected( $pickup_location, $location['value'] ); ?>><?php echo esc_html( $location['label'] ); ?></option><?php endforeach; ?></select></label>
              <label class="check-label reservation-form__different-return"><input type="checkbox" data-reservation-return-different><span><?php esc_html_e( 'Return to a different location', 'rentacar-venezia-v2' ); ?></span></label>
              <label data-reservation-return-location><?php esc_html_e( 'Return location', 'rentacar-venezia-v2' ); ?><select name="return_location" required data-reservation-return-location><?php foreach ( $locations as $location ) : ?><option value="<?php echo esc_attr( $location['value'] ); ?>"<?php selected( $return_location, $location['value'] ); ?>><?php echo esc_html( $location['label'] ); ?></option><?php endforeach; ?></select></label>
            </div>
          </fieldset>
          <?php if ( $inter_airport_fee ) : ?><p class="reservation-location-fee"><?php echo esc_html( sprintf( __( 'A €%s transfer fee applies when pickup and return airports differ.', 'rentacar-venezia-v2' ), number_format_i18n( $inter_airport_fee, 2 ) ) ); ?></p><?php endif; ?>

          <fieldset class="reservation-flight" data-reservation-flight>
            <legend><?php esc_html_e( 'Your flight', 'rentacar-venezia-v2' ); ?></legend>
            <p><?php esc_html_e( 'We monitor the flight when a valid flight number is provided. Please also contact us if your flight or arrival plans change.', 'rentacar-venezia-v2' ); ?></p>
            <div class="reservation-form__two">
              <label><?php esc_html_e( 'Airline (optional)', 'rentacar-venezia-v2' ); ?><input name="airline" autocomplete="organization" data-reservation-airline></label>
              <label><?php esc_html_e( 'Flight number', 'rentacar-venezia-v2' ); ?><input name="flight_number" maxlength="24" data-reservation-flight-number></label>
            </div>
          </fieldset>

          <fieldset class="reservation-form__section">
            <legend><?php esc_html_e( 'How can we reach you?', 'rentacar-venezia-v2' ); ?></legend>
            <label><?php esc_html_e( 'Full name', 'rentacar-venezia-v2' ); ?><input name="full_name" autocomplete="name" required></label>
            <label><?php esc_html_e( 'Phone or WhatsApp', 'rentacar-venezia-v2' ); ?><input name="phone" autocomplete="tel" required></label>
            <label><?php esc_html_e( 'Email', 'rentacar-venezia-v2' ); ?><input name="email" type="email" autocomplete="email" required></label>
          </fieldset>

          <details class="reservation-form__options">
            <summary><?php esc_html_e( 'Protection and optional extras', 'rentacar-venezia-v2' ); ?></summary>
            <div class="reservation-form__options-body">
              <fieldset class="reservation-insurance">
                <legend><?php esc_html_e( 'Insurance', 'rentacar-venezia-v2' ); ?></legend>
                <?php foreach ( $insurance_packages as $key => $package ) : if ( empty( $package['enabled'] ) ) { continue; } ?>
                  <label class="check-label"><input name="insurance" type="radio" value="<?php echo esc_attr( $key ); ?>" required<?php checked( 'base', $key ); ?>><span><?php echo esc_html( $package['label'] ); ?> — <?php echo esc_html( sprintf( '€%s %s', number_format_i18n( $package['daily_cents'] / 100, 2 ), __( 'per rental day', 'rentacar-venezia-v2' ) ) ); ?></span></label>
                <?php endforeach; ?>
                <p class="reservation-form__hint"><?php esc_html_e( 'Coverage, exclusions and any remaining customer responsibility are confirmed in the rental contract. Prices include VAT; base rental prices include RCA.', 'rentacar-venezia-v2' ); ?></p>
              </fieldset>
              <?php if ( $reservation_extras ) : ?>
                <fieldset class="reservation-extras"><legend><?php esc_html_e( 'Optional extras', 'rentacar-venezia-v2' ); ?></legend>
                  <?php foreach ( $reservation_extras as $extra ) : ?><label class="check-label reservation-extras__item"><input name="extras[]" type="checkbox" value="<?php echo esc_attr( $extra['key'] ); ?>"><span><strong><?php echo esc_html( $extra['label'] ); ?></strong><small><?php esc_html_e( 'Price confirmed by our team', 'rentacar-venezia-v2' ); ?></small></span></label><?php endforeach; ?>
                </fieldset>
              <?php endif; ?>
              <label class="check-label reservation-form__similar"><input name="similar_vehicle" type="checkbox" value="1"><span><?php esc_html_e( 'I accept a similar vehicle if the selected model is unavailable.', 'rentacar-venezia-v2' ); ?></span></label>
              <label><?php esc_html_e( 'Message (optional)', 'rentacar-venezia-v2' ); ?><textarea name="message" rows="3"></textarea></label>
            </div>
          </details>
          <section class="reservation-estimate" aria-live="polite" data-reservation-estimate hidden><h3><?php esc_html_e( 'Indicative estimate', 'rentacar-venezia-v2' ); ?></h3><div data-reservation-estimate-content></div><p><?php esc_html_e( 'Deposit is paid separately at pickup. Availability, final price and rental conditions are confirmed personally.', 'rentacar-venezia-v2' ); ?></p></section>
          <fieldset class="reservation-form__consents">
            <legend class="screen-reader-text"><?php esc_html_e( 'Request confirmations', 'rentacar-venezia-v2' ); ?></legend>
            <label class="check-label"><input name="terms" type="checkbox" value="1" required><span><?php esc_html_e( 'I accept the Terms and Conditions.', 'rentacar-venezia-v2' ); ?></span></label>
            <label class="check-label reservation-form__privacy"><input name="privacy" type="checkbox" value="1" required><span><?php esc_html_e( 'I agree that my details will be used only to respond to this request.', 'rentacar-venezia-v2' ); ?><?php if ( $privacy_policy_url ) : ?> <a href="<?php echo esc_url( $privacy_policy_url ); ?>"><?php esc_html_e( 'Privacy Policy', 'rentacar-venezia-v2' ); ?></a><?php endif; ?></span></label>
          </fieldset>
          <p class="reservation-form__disclaimer"><?php esc_html_e( 'Submitting this request does not immediately confirm the reservation. We will check availability and contact you.', 'rentacar-venezia-v2' ); ?></p>
          <button class="button" type="submit"><?php esc_html_e( 'Send reservation request', 'rentacar-venezia-v2' ); ?></button>
        </form>
      </div>
    </div>
    <div class="reservation-success" data-reservation-success<?php echo $success ? '' : ' hidden'; ?> aria-live="polite"><span aria-hidden="true">✓</span><h2 tabindex="-1"><?php esc_html_e( 'Request received', 'rentacar-venezia-v2' ); ?></h2><p><?php esc_html_e( 'We received your reservation request. Our team will check the selected vehicle and contact you to confirm availability, the final price and rental conditions.', 'rentacar-venezia-v2' ); ?></p><p data-reservation-reference><?php echo $reference ? esc_html( sprintf( __( 'Reference: %s', 'rentacar-venezia-v2' ), $reference ) ) : ''; ?></p><button class="button" type="button" data-reservation-close><?php esc_html_e( 'Close', 'rentacar-venezia-v2' ); ?></button></div>
  </div>
</section>
