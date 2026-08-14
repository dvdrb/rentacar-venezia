<?php
/* Template Name: Pickup locations hub */
defined( 'ABSPATH' ) || exit;
get_header();
$copy = rentacar_venezia_v2_landing_copy( 'pickup_locations' );
?>
<main id="main-content" class="site-main landing-page"><div class="rc-container">
<?php get_template_part( 'template-parts/global/breadcrumbs' ); ?>
<header class="landing-hero"><p class="eyebrow"><?php echo esc_html( $copy['eyebrow'] ); ?></p><h1><?php echo esc_html( get_the_title() ); ?></h1><p><?php echo esc_html( $copy['intro'] ); ?></p><a class="button" href="<?php echo esc_url( rentacar_venezia_v2_fleet_url() ); ?>"><?php esc_html_e( 'View available cars', 'rentacar-venezia-v2' ); ?></a></header>
<section class="landing-location-grid" aria-label="<?php esc_attr_e( 'Pickup locations', 'rentacar-venezia-v2' ); ?>"><?php foreach ( rentacar_venezia_v2_pickup_locations() as $key => $location ) : $url = rentacar_venezia_v2_location_page_url( $key ); if ( ! $url ) continue; ?><article><p class="eyebrow"><?php echo esc_html( rentacar_venezia_v2_landing_location_type_label( $location['type'] ?? '' ) ); ?></p><h2><?php echo esc_html( $location['label'] ); ?></h2><p><?php echo esc_html( rentacar_venezia_v2_landing_copy( $key )['intro'] ); ?></p><a class="text-link" href="<?php echo esc_url( $url ); ?>"><?php esc_html_e( 'Learn about pickup', 'rentacar-venezia-v2' ); ?></a></article><?php endforeach; ?></section>
</div></main><?php get_footer();
