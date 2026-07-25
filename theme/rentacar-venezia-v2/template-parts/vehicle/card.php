<?php
defined( 'ABSPATH' ) || exit;

$vehicle = $args['vehicle'] ?? null;
if ( ! $vehicle instanceof Rentacar_Core_Vehicle ) {
    return;
}

$image_id = rentacar_venezia_v2_vehicle_image_id( $vehicle );
$image_url = $image_id ? wp_get_attachment_image_url( $image_id, 'medium_large' ) : '';
$title = rentacar_venezia_v2_vehicle_title( $vehicle );
$specifications = rentacar_venezia_v2_vehicle_specs( $vehicle );
$bands = rentacar_venezia_v2_vehicle_bands( $vehicle );
$starting_price = rentacar_venezia_v2_vehicle_starting_price( $vehicle );
$image_presentation_class = rentacar_venezia_v2_vehicle_image_presentation_class( $vehicle );
$price_labels = array();
foreach ( $bands as $band ) {
    $price_labels[] = rentacar_venezia_v2_price_range_label( $band ) . ' ' . rentacar_venezia_v2_price_label( $band );
}
?>
<article class="vehicle-card">
    <a class="vehicle-card__image<?php echo $image_presentation_class ? ' ' . esc_attr( $image_presentation_class ) : ''; ?>" href="<?php echo esc_url( $vehicle->get( 'permalink' ) ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'View details for %s', 'rentacar-venezia-v2' ), $title ) ); ?>">
        <?php if ( $image_id ) : ?>
            <?php echo wp_kses_post( wp_get_attachment_image( $image_id, 'medium_large', false, array( 'loading' => 'lazy', 'sizes' => '(min-width: 960px) 31vw, (min-width: 640px) 48vw, 100vw', 'alt' => rentacar_venezia_v2_vehicle_image_alt( $vehicle, $image_id, true ) ) ) ); ?>
        <?php else : ?>
            <span class="vehicle-card__image-placeholder"><?php esc_html_e( 'Vehicle image unavailable', 'rentacar-venezia-v2' ); ?></span>
        <?php endif; ?>
    </a>
    <div class="vehicle-card__body">
        <h3><a href="<?php echo esc_url( $vehicle->get( 'permalink' ) ); ?>"><?php echo esc_html( $title ); ?></a></h3>
        <?php if ( $specifications ) : ?><p class="vehicle-card__specs"><?php echo esc_html( implode( ' · ', $specifications ) ); ?></p><?php endif; ?>
        <?php if ( null !== $starting_price ) : ?>
            <p class="vehicle-card__starting-price"><span><?php esc_html_e( 'Starting from', 'rentacar-venezia-v2' ); ?></span><strong><?php echo esc_html( sprintf( __( '€%s/day', 'rentacar-venezia-v2' ), number_format_i18n( $starting_price, 0 ) ) ); ?></strong></p>
        <?php endif; ?>
        <p class="vehicle-card__rate-label"><?php esc_html_e( 'Indicative daily rates', 'rentacar-venezia-v2' ); ?></p>
        <?php if ( $bands ) : ?>
            <dl class="vehicle-card__prices" aria-label="<?php esc_attr_e( 'Indicative daily price bands', 'rentacar-venezia-v2' ); ?>">
                <?php foreach ( $bands as $band ) : ?><div><dt><?php echo esc_html( rentacar_venezia_v2_price_range_label( $band ) ); ?></dt><dd><?php echo esc_html( rentacar_venezia_v2_price_label( $band ) ); ?></dd></div><?php endforeach; ?>
            </dl>
        <?php else : ?>
            <p class="vehicle-card__price-pending"><?php esc_html_e( 'Price to be confirmed', 'rentacar-venezia-v2' ); ?></p>
        <?php endif; ?>
        <div class="vehicle-card__actions">
            <button class="button reservation-trigger" type="button" data-reservation-trigger data-vehicle-id="<?php echo esc_attr( $vehicle->get( 'id' ) ); ?>" data-vehicle-title="<?php echo esc_attr( $title ); ?>" data-vehicle-image="<?php echo esc_url( $image_url ); ?>" data-vehicle-specifications="<?php echo esc_attr( implode( ' · ', $specifications ) ); ?>" data-vehicle-price-bands="<?php echo esc_attr( implode( ' · ', $price_labels ) ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Reservation for %s', 'rentacar-venezia-v2' ), $title ) ); ?>"><?php esc_html_e( 'Reservation', 'rentacar-venezia-v2' ); ?></button>
            <a class="text-link" href="<?php echo esc_url( $vehicle->get( 'permalink' ) ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'View details for %s', 'rentacar-venezia-v2' ), $title ) ); ?>"><?php esc_html_e( 'View details', 'rentacar-venezia-v2' ); ?></a>
        </div>
    </div>
</article>
