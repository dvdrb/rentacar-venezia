<?php
defined( 'ABSPATH' ) || exit;

final class Rentacar_Core_Reservation_Extra_Settings {
    const RECIPIENT_OPTION = 'rentacar_core_reservation_recipient';

    public static function register_setting() {
        register_setting(
            'rentacar_core_reservation_extras',
            Rentacar_Core_Reservation_Extras::OPTION,
            array(
                'type'              => 'array',
                'sanitize_callback' => array( 'Rentacar_Core_Reservation_Extras', 'sanitize' ),
                'default'           => array(),
            )
        );
        register_setting(
            'rentacar_core_rental_policy',
            Rentacar_Core_Rental_Policy::OPTION,
            array(
                'type'              => 'array',
                'sanitize_callback' => array( 'Rentacar_Core_Rental_Policy', 'sanitize' ),
                'default'           => Rentacar_Core_Rental_Policy::defaults(),
            )
        );
        register_setting(
            'rentacar_core_email_delivery',
            self::RECIPIENT_OPTION,
            array(
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_email',
                'default'           => '',
            )
        );
    }

    public static function register_page() {
        add_options_page(
            __( 'Rentacar settings', 'rentacar-core' ),
            __( 'Rentacar settings', 'rentacar-core' ),
            'manage_options',
            'rentacar-core',
            array( __CLASS__, 'render_page' )
        );
    }

    public static function render_page() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        $extras = Rentacar_Core_Reservation_Extras::all();
        ?>
        <div class="wrap">
            <h1><?php esc_html_e( 'Rentacar settings', 'rentacar-core' ); ?></h1>
            <p><?php esc_html_e( 'These settings are the authoritative source for reservation estimates. Final availability and rental conditions are always confirmed personally.', 'rentacar-core' ); ?></p>
            <h2><?php esc_html_e( 'Reservation email delivery', 'rentacar-core' ); ?></h2>
            <?php self::render_delivery_form(); ?>
            <hr>
            <h2><?php esc_html_e( 'Reservation policy', 'rentacar-core' ); ?></h2>
            <?php self::render_policy_form(); ?>
            <hr>
            <h2><?php esc_html_e( 'Optional extras', 'rentacar-core' ); ?></h2>
            <form action="options.php" method="post">
                <?php settings_fields( 'rentacar_core_reservation_extras' ); ?>
                <table class="form-table" role="presentation">
                    <tbody>
                        <?php foreach ( Rentacar_Core_Reservation_Extras::definitions() as $key => $definition ) : $extra = $extras[ $key ]; ?>
                            <tr>
                                <th scope="row"><?php echo esc_html( $extra['label'] ); ?></th>
                                <td>
                                    <label><input type="checkbox" name="<?php echo esc_attr( Rentacar_Core_Reservation_Extras::OPTION . '[' . $key . '][enabled]' ); ?>" value="1" <?php checked( ! empty( $extra['enabled'] ) ); ?>> <?php esc_html_e( 'Enabled', 'rentacar-core' ); ?></label>
                                    <p><label><?php esc_html_e( 'Pricing type', 'rentacar-core' ); ?>
                                        <select name="<?php echo esc_attr( Rentacar_Core_Reservation_Extras::OPTION . '[' . $key . '][pricing_type]' ); ?>">
                                            <?php foreach ( array( 'per_day' => __( 'Per day', 'rentacar-core' ), 'fixed' => __( 'Fixed', 'rentacar-core' ), 'request_only' => __( 'Price confirmed by our team', 'rentacar-core' ) ) as $type => $label ) : ?>
                                                <option value="<?php echo esc_attr( $type ); ?>" <?php selected( $extra['pricing_type'], $type ); ?>><?php echo esc_html( $label ); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </label></p>
                                    <p><label><?php esc_html_e( 'Price (€)', 'rentacar-core' ); ?> <input type="number" min="0" step="0.01" name="<?php echo esc_attr( Rentacar_Core_Reservation_Extras::OPTION . '[' . $key . '][price]' ); ?>" value="<?php echo esc_attr( number_format( (float) $extra['price'], 2, '.', '' ) ); ?>"></label></p>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    private static function render_delivery_form() {
        $recipient = (string) get_option( self::RECIPIENT_OPTION, '' );
        ?>
        <form action="options.php" method="post">
            <?php settings_fields( 'rentacar_core_email_delivery' ); ?>
            <table class="form-table" role="presentation"><tbody><tr>
                <th scope="row"><label for="rentacar-core-reservation-recipient"><?php esc_html_e( 'Business recipient', 'rentacar-core' ); ?></label></th>
                <td><input id="rentacar-core-reservation-recipient" class="regular-text" name="<?php echo esc_attr( self::RECIPIENT_OPTION ); ?>" type="email" value="<?php echo esc_attr( $recipient ); ?>" autocomplete="email"><p class="description"><?php esc_html_e( 'Reservation notifications are sent here. Customer acknowledgements are sent to the email supplied in the request.', 'rentacar-core' ); ?></p></td>
            </tr></tbody></table>
            <?php submit_button( __( 'Save email delivery settings', 'rentacar-core' ) ); ?>
        </form>
        <?php
    }

    private static function render_policy_form() {
        $policy = Rentacar_Core_Rental_Policy::get();
        $option = Rentacar_Core_Rental_Policy::OPTION;
        ?>
        <form action="options.php" method="post">
            <?php settings_fields( 'rentacar_core_rental_policy' ); ?>
            <table class="form-table" role="presentation">
                <tbody>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Insurance packages', 'rentacar-core' ); ?></th>
                        <td>
                            <?php foreach ( $policy['insurance'] as $key => $insurance ) : ?>
                                <p>
                                    <strong><?php echo esc_html( $insurance['label'] ); ?></strong><br>
                                    <label><input type="checkbox" name="<?php echo esc_attr( $option . '[insurance][' . $key . '][enabled]' ); ?>" value="1" <?php checked( ! empty( $insurance['enabled'] ) ); ?>> <?php esc_html_e( 'Enabled', 'rentacar-core' ); ?></label>
                                    <label style="margin-left:16px"><?php esc_html_e( 'Price per day (€)', 'rentacar-core' ); ?> <input type="number" min="0" step="0.01" name="<?php echo esc_attr( $option . '[insurance][' . $key . '][daily_price]' ); ?>" value="<?php echo esc_attr( number_format( $insurance['daily_cents'] / 100, 2, '.', '' ) ); ?>"></label>
                                </p>
                            <?php endforeach; ?>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'After-hours pickup', 'rentacar-core' ); ?></th>
                        <td>
                            <p><?php esc_html_e( 'Set the four daily boundaries in chronological order. The night rate applies from the night start through the early start on the following day.', 'rentacar-core' ); ?></p>
                            <?php foreach ( array( 'early_start' => __( 'Early start', 'rentacar-core' ), 'normal_start' => __( 'Normal-hours start', 'rentacar-core' ), 'evening_start' => __( 'Evening start', 'rentacar-core' ), 'night_start' => __( 'Night start', 'rentacar-core' ) ) as $key => $label ) : ?>
                                <label style="display:inline-block; margin:0 16px 10px 0"><?php echo esc_html( $label ); ?> <input type="time" name="<?php echo esc_attr( $option . '[after_hours][' . $key . ']' ); ?>" value="<?php echo esc_attr( Rentacar_Core_Rental_Policy::minutes_to_time( $policy['after_hours'][ $key ] ) ); ?>"></label>
                            <?php endforeach; ?>
                            <p>
                                <?php foreach ( array( 'early_price' => __( 'Early rate (€)', 'rentacar-core' ), 'evening_price' => __( 'Evening rate (€)', 'rentacar-core' ), 'night_price' => __( 'Night rate (€)', 'rentacar-core' ) ) as $key => $label ) : $cents_key = str_replace( '_price', '_cents', $key ); ?>
                                    <label style="display:inline-block; margin:0 16px 10px 0"><?php echo esc_html( $label ); ?> <input type="number" min="0" step="0.01" name="<?php echo esc_attr( $option . '[after_hours][' . $key . ']' ); ?>" value="<?php echo esc_attr( number_format( $policy['after_hours'][ $cents_key ] / 100, 2, '.', '' ) ); ?>"></label>
                                <?php endforeach; ?>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Deposits and mileage', 'rentacar-core' ); ?></th>
                        <td>
                            <label><?php esc_html_e( 'Deposit up to 5 passengers (€)', 'rentacar-core' ); ?> <input type="number" min="0" step="0.01" name="<?php echo esc_attr( $option . '[deposits][up_to_five]' ); ?>" value="<?php echo esc_attr( number_format( $policy['deposits']['up_to_five_cents'] / 100, 2, '.', '' ) ); ?>"></label><br>
                            <label><?php esc_html_e( 'Deposit for 7–9 passengers (€)', 'rentacar-core' ); ?> <input type="number" min="0" step="0.01" name="<?php echo esc_attr( $option . '[deposits][seven_to_nine]' ); ?>" value="<?php echo esc_attr( number_format( $policy['deposits']['seven_to_nine_cents'] / 100, 2, '.', '' ) ); ?>"></label><br>
                            <label><?php esc_html_e( 'Included km per day', 'rentacar-core' ); ?> <input type="number" min="0" step="1" name="<?php echo esc_attr( $option . '[mileage][daily_km]' ); ?>" value="<?php echo esc_attr( $policy['mileage']['daily_km'] ); ?>"></label><br>
                            <label><?php esc_html_e( 'Excess km price (€)', 'rentacar-core' ); ?> <input type="number" min="0" step="0.01" name="<?php echo esc_attr( $option . '[mileage][excess_price]' ); ?>" value="<?php echo esc_attr( number_format( $policy['mileage']['excess_cents'] / 100, 2, '.', '' ) ); ?>"></label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Different-airport transfer', 'rentacar-core' ); ?></th>
                        <td><label><?php esc_html_e( 'Surcharge (€)', 'rentacar-core' ); ?> <input type="number" min="0" step="0.01" name="<?php echo esc_attr( $option . '[inter_airport_surcharge]' ); ?>" value="<?php echo esc_attr( number_format( $policy['inter_airport_surcharge_cents'] / 100, 2, '.', '' ) ); ?>"></label></td>
                    </tr>
                </tbody>
            </table>
            <?php submit_button( __( 'Save reservation policy', 'rentacar-core' ) ); ?>
        </form>
        <?php
    }
}
