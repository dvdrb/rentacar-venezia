<?php
defined( 'ABSPATH' ) || exit;

$vehicle = $args['vehicle'] ?? null;
if ( ! $vehicle instanceof Rentacar_Core_Vehicle ) {
    return;
}

$price_band = $vehicle->get( 'pricing_bands' )->for_days( 1 );
?>
<article class="vehicle-card">
    <a class="vehicle-card__image" href="<?php echo esc_url( $vehicle->get( 'permalink' ) ); ?>">
        <?php
        if ( $vehicle->get( 'featured_image_id' ) ) {
            echo wp_kses_post( wp_get_attachment_image( $vehicle->get( 'featured_image_id' ), 'medium_large', false, array( 'loading' => 'lazy', 'sizes' => '(min-width: 960px) 25vw, (min-width: 640px) 50vw, 100vw' ) ) );
        }
        ?>
    </a>
    <div class="vehicle-card__body"><h3><a href="<?php echo esc_url( $vehicle->get( 'permalink' ) ); ?>"><?php echo esc_html( $vehicle->get( 'title' ) ); ?></a></h3>
        <p class="vehicle-card__specs"><?php echo esc_html( implode( ' · ', array_filter( array( $vehicle->get( 'transmission' ), $vehicle->get( 'passengers' ) ? sprintf( _n( '%s passenger', '%s passengers', $vehicle->get( 'passengers' ), 'rentacar-venezia-v2' ), $vehicle->get( 'passengers' ) ) : '' ) ) ) ); ?></p>
        <?php if ( $price_band ) : ?><p class="vehicle-card__price"><?php echo esc_html( sprintf( __( 'From €%s / day', 'rentacar-venezia-v2' ), number_format_i18n( $price_band->daily_price, 0 ) ) ); ?></p><?php endif; ?>
        <a class="button" href="<?php echo esc_url( $vehicle->get( 'permalink' ) ); ?>"><?php esc_html_e( 'View details', 'rentacar-venezia-v2' ); ?></a>
    </div>
</article>
