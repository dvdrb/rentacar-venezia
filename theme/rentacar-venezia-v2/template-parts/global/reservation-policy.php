<?php
defined( 'ABSPATH' ) || exit;

$language = function_exists( 'rentacar_venezia_v2_current_language' ) ? rentacar_venezia_v2_current_language() : 'en';
$claims = class_exists( 'Rentacar_Core_Marketing_Claim_Registry' ) ? array(
    'no_credit_card_to_reserve'      => Rentacar_Core_Marketing_Claim_Registry::enabled( 'no_credit_card_to_reserve' ),
    'no_advance_reservation_deposit' => Rentacar_Core_Marketing_Claim_Registry::enabled( 'no_advance_reservation_deposit' ),
    'security_deposit_at_pickup'     => Rentacar_Core_Marketing_Claim_Registry::enabled( 'security_deposit_at_pickup' ),
) : array();

if ( ! $claims['no_credit_card_to_reserve'] || ! $claims['no_advance_reservation_deposit'] || ! $claims['security_deposit_at_pickup'] ) {
    return;
}

$variant = isset( $args['variant'] ) && in_array( $args['variant'], array( 'compact', 'detailed' ), true ) ? $args['variant'] : 'compact';
$summary = Rentacar_Core_Marketing_Claim_Registry::policy_summary( $language );
?>
<section class="reservation-policy reservation-policy--<?php echo esc_attr( $variant ); ?>" aria-label="<?php esc_attr_e( 'Reservation policy', 'rentacar-venezia-v2' ); ?>">
    <?php if ( 'detailed' === $variant ) : ?><h2><?php esc_html_e( 'Reservation policy', 'rentacar-venezia-v2' ); ?></h2><?php endif; ?>
    <p class="reservation-policy__summary"><?php echo esc_html( $summary ); ?></p>
    <ul class="reservation-policy__list">
        <?php foreach ( array_keys( $claims ) as $key ) : $copy = Rentacar_Core_Marketing_Claim_Registry::copy( $key, $language ); ?>
            <li class="reservation-policy__item reservation-policy__item--<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $copy['label'] ); ?></li>
        <?php endforeach; ?>
    </ul>
</section>
