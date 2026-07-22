<?php
defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="main-content" class="site-main"><section class="rc-container empty-state"><p class="eyebrow">404</p><h1><?php esc_html_e( 'This page is not on our route.', 'rentacar-venezia-v2' ); ?></h1><p><?php esc_html_e( 'Explore the fleet or return to the homepage to start your trip.', 'rentacar-venezia-v2' ); ?></p><a class="button" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Back to home', 'rentacar-venezia-v2' ); ?></a></section></main>
<?php get_footer(); ?>
