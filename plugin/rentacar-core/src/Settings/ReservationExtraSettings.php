<?php
defined( 'ABSPATH' ) || exit;

final class Rentacar_Core_Reservation_Extra_Settings {
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
}
