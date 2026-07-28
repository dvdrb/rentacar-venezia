<?php
defined( 'ABSPATH' ) || exit;

$field_id = isset( $args['id'] ) ? sanitize_html_class( $args['id'] ) : 'phone';
$locale = function_exists( 'rentacar_venezia_v2_current_language' ) ? rentacar_venezia_v2_current_language() : 'en';
$locale = in_array( $locale, array( 'en', 'it', 'ro', 'ru' ), true ) ? $locale : 'en';
$countries = class_exists( 'Rentacar_Core_Phone_Number_Service' ) ? Rentacar_Core_Phone_Number_Service::country_options( $locale ) : array();
$phone_error = ! empty( $args['error_code'] ) && class_exists( 'Rentacar_Core_Phone_Number_Service' ) ? Rentacar_Core_Phone_Number_Service::error_message( $args['error_code'] ) : '';
$phone_error_id = $field_id . '-error';
$selected_country = 'IT';
$selected = array( 'country' => 'IT', 'calling_code' => '+39', 'name' => 'Italy', 'flag' => '🇮🇹' );
foreach ( $countries as $country ) {
    if ( $selected_country === $country['country'] ) {
        $selected = $country;
        break;
    }
}
?>
<fieldset class="international-phone" data-phone-field>
  <legend id="<?php echo esc_attr( $field_id ); ?>-label"><?php esc_html_e( 'Phone or WhatsApp', 'rentacar-venezia-v2' ); ?></legend>
  <div class="international-phone__native" data-phone-native>
    <label for="<?php echo esc_attr( $field_id ); ?>-country"><?php esc_html_e( 'Select country', 'rentacar-venezia-v2' ); ?></label>
    <select id="<?php echo esc_attr( $field_id ); ?>-country" name="phone_country" required data-phone-country<?php echo $phone_error ? ' aria-invalid="true" aria-describedby="' . esc_attr( $phone_error_id ) . '"' : ''; ?>>
      <?php foreach ( $countries as $country ) : ?>
        <option value="<?php echo esc_attr( $country['country'] ); ?>" data-calling-code="<?php echo esc_attr( $country['calling_code'] ); ?>"<?php selected( $selected_country, $country['country'] ); ?>><?php echo esc_html( $country['flag'] . ' ' . $country['name'] . ' (' . $country['calling_code'] . ')' ); ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="international-phone__inline">
    <div class="international-phone__enhanced" data-phone-enhanced hidden>
      <button class="international-phone__selector" type="button" data-phone-trigger data-default-country-label="<?php esc_attr_e( 'Select country', 'rentacar-venezia-v2' ); ?>" aria-haspopup="dialog" aria-expanded="false" aria-controls="<?php echo esc_attr( $field_id ); ?>-countries">
        <span class="international-phone__flag" data-phone-flag aria-hidden="true"><?php echo esc_html( $selected['flag'] ); ?></span>
        <span class="international-phone__code" data-phone-code><?php echo esc_html( $selected['calling_code'] ); ?></span>
        <span class="screen-reader-text" data-phone-country-name><?php echo esc_html( $selected['name'] ); ?></span>
      </button>
      <div id="<?php echo esc_attr( $field_id ); ?>-countries" class="international-phone__dialog" data-phone-dialog role="dialog" aria-label="<?php esc_attr_e( 'Select country', 'rentacar-venezia-v2' ); ?>" hidden>
        <div class="international-phone__search"><label class="screen-reader-text" for="<?php echo esc_attr( $field_id ); ?>-search"><?php esc_html_e( 'Search countries', 'rentacar-venezia-v2' ); ?></label><input id="<?php echo esc_attr( $field_id ); ?>-search" type="search" data-phone-search placeholder="<?php esc_attr_e( 'Search countries', 'rentacar-venezia-v2' ); ?>" autocomplete="off"></div>
        <p class="international-phone__empty" data-phone-empty hidden><?php esc_html_e( 'No countries found', 'rentacar-venezia-v2' ); ?></p>
        <div class="international-phone__options" data-phone-options role="listbox" aria-label="<?php esc_attr_e( 'Select country', 'rentacar-venezia-v2' ); ?>">
          <?php foreach ( $countries as $country ) : ?>
            <button type="button" class="international-phone__option" role="option" data-phone-option data-country="<?php echo esc_attr( $country['country'] ); ?>" data-calling-code="<?php echo esc_attr( $country['calling_code'] ); ?>" data-search="<?php echo esc_attr( strtolower( $country['search'] . ' ' . $country['country'] . ' ' . $country['calling_code'] ) ); ?>" aria-selected="<?php echo $selected_country === $country['country'] ? 'true' : 'false'; ?>"><span aria-hidden="true"><?php echo esc_html( $country['flag'] ); ?></span><span><?php echo esc_html( $country['name'] ); ?></span><span><?php echo esc_html( $country['calling_code'] ); ?></span></button>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
    <div class="international-phone__number"><label class="screen-reader-text" for="<?php echo esc_attr( $field_id ); ?>-number"><?php esc_html_e( 'Phone number', 'rentacar-venezia-v2' ); ?></label><input id="<?php echo esc_attr( $field_id ); ?>-number" name="phone" type="tel" inputmode="tel" autocomplete="tel-national" required data-phone-number aria-invalid="<?php echo $phone_error ? 'true' : 'false'; ?>" aria-describedby="<?php echo esc_attr( $field_id ); ?>-help<?php echo $phone_error ? ' ' . esc_attr( $phone_error_id ) : ''; ?>"><input name="phone_calling_code" type="hidden" value="<?php echo esc_attr( $selected['calling_code'] ); ?>" data-phone-calling-code></div>
  </div>
  <small id="<?php echo esc_attr( $field_id ); ?>-help" class="screen-reader-text"><?php esc_html_e( 'Country calling code', 'rentacar-venezia-v2' ); ?>: <span data-phone-help-code><?php echo esc_html( $selected['calling_code'] ); ?></span></small>
  <?php if ( $phone_error ) : ?><p id="<?php echo esc_attr( $phone_error_id ); ?>" class="international-phone__error" role="alert"><?php echo esc_html( $phone_error ); ?></p><?php endif; ?>
</fieldset>
