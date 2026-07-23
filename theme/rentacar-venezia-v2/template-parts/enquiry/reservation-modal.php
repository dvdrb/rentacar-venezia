<?php
defined( 'ABSPATH' ) || exit;
$success = isset( $_GET['reservation_sent'], $_GET['reservation_ref'] ) && '1' === sanitize_text_field( wp_unslash( $_GET['reservation_sent'] ) );
$reference = $success ? sanitize_text_field( wp_unslash( $_GET['reservation_ref'] ) ) : '';
$trip = rentacar_venezia_v2_trip_query();
?>
<section class="reservation-modal" data-reservation-modal role="dialog" aria-modal="true" aria-labelledby="reservation-modal-title"<?php echo $success ? '' : ' hidden'; ?> tabindex="-1">
  <div class="reservation-modal__backdrop" data-reservation-close></div>
  <div class="reservation-modal__panel">
    <button class="reservation-modal__close" type="button" data-reservation-close aria-label="<?php esc_attr_e( 'Close reservation form', 'rentacar-venezia-v2' ); ?>">×</button>
    <div data-reservation-form-wrap<?php echo $success ? ' hidden' : ''; ?>>
      <h2 id="reservation-modal-title"><?php esc_html_e( 'Reservation request', 'rentacar-venezia-v2' ); ?></h2>
      <div class="reservation-modal__grid">
        <aside class="reservation-summary" data-reservation-summary>
          <div class="reservation-summary__image" data-reservation-image></div><h3 data-reservation-title><?php esc_html_e( 'Selected vehicle', 'rentacar-venezia-v2' ); ?></h3><p data-reservation-specifications></p><p data-reservation-prices></p>
          <p><?php esc_html_e( 'Indicative prices only. Availability and final price are confirmed by our team.', 'rentacar-venezia-v2' ); ?></p>
        </aside>
        <form class="reservation-form" data-reservation-form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post" novalidate>
          <input type="hidden" name="action" value="rentacar_submit_reservation"><input type="hidden" name="vehicle_id" data-reservation-vehicle-id value=""><input type="hidden" name="started_at" value="<?php echo esc_attr( time() ); ?>">
          <?php wp_nonce_field( 'rentacar_submit_reservation', 'rentacar_reservation_nonce' ); ?>
          <p id="reservation-errors" class="reservation-form__error" data-reservation-errors tabindex="-1" aria-live="assertive"></p>
          <div class="honeypot" aria-hidden="true"><label><?php esc_html_e( 'Website', 'rentacar-venezia-v2' ); ?><input name="website" tabindex="-1" autocomplete="off"></label></div>
          <fieldset><legend><?php esc_html_e( 'Rental details', 'rentacar-venezia-v2' ); ?></legend><div class="reservation-form__two">
            <label><?php esc_html_e( 'Pickup date', 'rentacar-venezia-v2' ); ?><input name="pickup_date" type="date" required value="<?php echo esc_attr( $trip['pickup_date'] ?? '' ); ?>"></label><label><?php esc_html_e( 'Pickup time', 'rentacar-venezia-v2' ); ?><input name="pickup_time" type="time" required value="<?php echo esc_attr( $trip['pickup_time'] ?? '' ); ?>"></label>
            <label><?php esc_html_e( 'Return date', 'rentacar-venezia-v2' ); ?><input name="return_date" type="date" required value="<?php echo esc_attr( $trip['return_date'] ?? '' ); ?>"></label><label><?php esc_html_e( 'Return time', 'rentacar-venezia-v2' ); ?><input name="return_time" type="time" required value="<?php echo esc_attr( $trip['return_time'] ?? '' ); ?>"></label>
            <label><?php esc_html_e( 'Pickup location', 'rentacar-venezia-v2' ); ?><input name="pickup_location" required value="<?php echo esc_attr( $trip['pickup_location'] ?? '' ); ?>"></label><label><?php esc_html_e( 'Return location', 'rentacar-venezia-v2' ); ?><input name="return_location" required value="<?php echo esc_attr( $trip['dropoff_location'] ?? '' ); ?>"></label>
          </div></fieldset>
          <fieldset><legend><?php esc_html_e( 'Your details', 'rentacar-venezia-v2' ); ?></legend>
            <label><?php esc_html_e( 'Full name', 'rentacar-venezia-v2' ); ?><input name="full_name" autocomplete="name" required></label><label><?php esc_html_e( 'Phone or WhatsApp', 'rentacar-venezia-v2' ); ?><input name="phone" autocomplete="tel" required></label><label><?php esc_html_e( 'Email', 'rentacar-venezia-v2' ); ?><input name="email" type="email" autocomplete="email" required></label>
            <label class="check-label"><input name="similar_vehicle" type="checkbox" value="1"> <?php esc_html_e( 'I accept a similar vehicle if the selected model is unavailable.', 'rentacar-venezia-v2' ); ?></label><label><?php esc_html_e( 'Message (optional)', 'rentacar-venezia-v2' ); ?><textarea name="message" rows="3"></textarea></label>
            <label class="check-label"><input name="privacy" type="checkbox" value="1" required> <?php esc_html_e( 'I agree that my details will be used only to respond to this request.', 'rentacar-venezia-v2' ); ?></label>
          </fieldset>
          <p class="form-help"><?php esc_html_e( 'Submitting this request does not immediately confirm the reservation. We will check availability and contact you.', 'rentacar-venezia-v2' ); ?></p>
          <button class="button" type="submit"><?php esc_html_e( 'Send reservation request', 'rentacar-venezia-v2' ); ?></button>
        </form>
      </div>
    </div>
    <div class="reservation-success" data-reservation-success<?php echo $success ? '' : ' hidden'; ?> aria-live="polite"><span aria-hidden="true">✓</span><h2 tabindex="-1"><?php esc_html_e( 'Request sent', 'rentacar-venezia-v2' ); ?></h2><p><?php esc_html_e( 'We received your reservation request. Our team will check the selected vehicle and contact you to confirm availability, the final price and rental conditions.', 'rentacar-venezia-v2' ); ?></p><p data-reservation-reference><?php echo $reference ? esc_html( sprintf( __( 'Reference: %s', 'rentacar-venezia-v2' ), $reference ) ) : ''; ?></p><button class="button" type="button" data-reservation-close><?php esc_html_e( 'Close', 'rentacar-venezia-v2' ); ?></button></div>
  </div>
</section>
