<?php
defined( 'ABSPATH' ) || exit;

$vehicle = $args['vehicle'] ?? null;
if ( ! $vehicle instanceof Rentacar_Core_Vehicle ) {
    return;
}

$valid_bands = array();
foreach ( $vehicle->get( 'pricing_bands' )->all() as $band ) {
    if ( $band->from_days < 1 || null === $band->daily_price || $band->daily_price <= 0 || ( null !== $band->to_days && $band->to_days < $band->from_days ) ) {
        continue;
    }

    $valid_bands[] = $band;
}

$image_url = $vehicle->get( 'featured_image_id' ) ? wp_get_attachment_image_url( $vehicle->get( 'featured_image_id' ), 'medium_large' ) : '';
$price_labels = array();
foreach ( $valid_bands as $band ) {
    $price_labels[] = null === $band->to_days ? sprintf( __( '%1$s+ days €%2$s/day', 'rentacar-venezia-v2' ), $band->from_days, number_format_i18n( $band->daily_price, 0 ) ) : sprintf( __( '%1$s–%2$s days €%3$s/day', 'rentacar-venezia-v2' ), $band->from_days, $band->to_days, number_format_i18n( $band->daily_price, 0 ) );
}
$specifications = array_filter(
    array(
        $vehicle->get( 'transmission' ),
        $vehicle->get( 'passengers' ) ? sprintf( _n( '%s passengers', '%s passengers', $vehicle->get( 'passengers' ), 'rentacar-venezia-v2' ), $vehicle->get( 'passengers' ) ) : '',
        $vehicle->get( 'doors' ) ? sprintf( _n( '%s doors', '%s doors', $vehicle->get( 'doors' ), 'rentacar-venezia-v2' ), $vehicle->get( 'doors' ) ) : '',
        $vehicle->get( 'air_conditioning' ) ? __( 'Air conditioning', 'rentacar-venezia-v2' ) : '',
    )
);
?>
<article class="vehicle-card">
    <a class="vehicle-card__image" href="<?php echo esc_url( $vehicle->get( 'permalink' ) ); ?>">
        <?php if ( $vehicle->get( 'featured_image_id' ) ) : ?>
            <?php echo wp_kses_post( wp_get_attachment_image( $vehicle->get( 'featured_image_id' ), 'medium_large', false, array( 'loading' => 'lazy', 'sizes' => '(min-width: 960px) 25vw, (min-width: 640px) 50vw, 100vw' ) ) ); ?>
        <?php else : ?>
            <span class="vehicle-card__image-placeholder"><?php esc_html_e( 'Vehicle image coming soon', 'rentacar-venezia-v2' ); ?></span>
        <?php endif; ?>
    </a>
    <div class="vehicle-card__body">
        <h3><a href="<?php echo esc_url( $vehicle->get( 'permalink' ) ); ?>"><?php echo esc_html( $vehicle->get( 'title' ) ); ?></a></h3>
        <?php if ( $specifications ) : ?><p class="vehicle-card__specs"><?php echo esc_html( implode( ' · ', $specifications ) ); ?></p><?php endif; ?>
        <dl class="vehicle-card__prices" aria-label="<?php esc_attr_e( 'Indicative daily price bands', 'rentacar-venezia-v2' ); ?>">
            <?php foreach ( $valid_bands as $band ) : ?>
                <div><dt><?php echo esc_html( null === $band->to_days ? sprintf( __( '%s+ days', 'rentacar-venezia-v2' ), $band->from_days ) : sprintf( __( '%1$s–%2$s days', 'rentacar-venezia-v2' ), $band->from_days, $band->to_days ) ); ?></dt><dd><?php echo esc_html( sprintf( __( '€%s/day', 'rentacar-venezia-v2' ), number_format_i18n( $band->daily_price, 0 ) ) ); ?></dd></div>
            <?php endforeach; ?>
        </dl>
        <p class="vehicle-card__clarification"><?php esc_html_e( 'Indicative prices. Availability and final price are confirmed by our team.', 'rentacar-venezia-v2' ); ?></p>
        <div class="vehicle-card__actions">
            <button class="button reservation-trigger" type="button" data-reservation-trigger data-vehicle-id="<?php echo esc_attr( $vehicle->get( 'id' ) ); ?>" data-vehicle-title="<?php echo esc_attr( $vehicle->get( 'title' ) ); ?>" data-vehicle-image="<?php echo esc_url( $image_url ); ?>" data-vehicle-specifications="<?php echo esc_attr( implode( ' · ', $specifications ) ); ?>" data-vehicle-price-bands="<?php echo esc_attr( implode( ' · ', $price_labels ) ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Reservation for %s', 'rentacar-venezia-v2' ), $vehicle->get( 'title' ) ) ); ?>"><?php esc_html_e( 'Reservation', 'rentacar-venezia-v2' ); ?></button>
            <a class="text-link" href="<?php echo esc_url( $vehicle->get( 'permalink' ) ); ?>"><?php esc_html_e( 'Details', 'rentacar-venezia-v2' ); ?></a>
        </div>
    </div>
</article>
