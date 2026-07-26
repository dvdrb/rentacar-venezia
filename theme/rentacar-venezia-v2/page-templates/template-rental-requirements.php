<?php
/* Template Name: Rental requirements */
defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="main-content" class="site-main site-main--content">
    <article class="rc-container content-page information-page">
        <?php while ( have_posts() ) : the_post(); ?>
            <?php get_template_part( 'template-parts/global/breadcrumbs' ); ?>
            <header class="page-intro content-page__header"><p class="eyebrow"><?php esc_html_e( 'Rental information', 'rentacar-venezia-v2' ); ?></p><h1><?php the_title(); ?></h1></header>
            <div class="requirements-grid"><section><h2><?php esc_html_e( 'Driver eligibility', 'rentacar-venezia-v2' ); ?></h2><p><?php esc_html_e( 'Drivers must be at least 23 and have held a licence for at least three years.', 'rentacar-venezia-v2' ); ?></p></section><section><h2><?php esc_html_e( 'Required documents', 'rentacar-venezia-v2' ); ?></h2><p><?php esc_html_e( 'Bring your original driving licence and passport or identity document. Your licence must be legally valid in Italy.', 'rentacar-venezia-v2' ); ?></p></section><section><h2><?php esc_html_e( 'Payment and deposit', 'rentacar-venezia-v2' ); ?></h2><p><?php esc_html_e( 'No payment is needed to submit a request. Payment and the separate security deposit are due at pickup: €350 up to five seats and €500 for seven to nine seats.', 'rentacar-venezia-v2' ); ?></p></section><section><h2><?php esc_html_e( 'Mileage and fuel', 'rentacar-venezia-v2' ); ?></h2><p><?php esc_html_e( 'Each rental day includes 150 km. Additional distance is €0.10/km. Return the vehicle with the same fuel level.', 'rentacar-venezia-v2' ); ?></p></section><section><h2><?php esc_html_e( 'Insurance', 'rentacar-venezia-v2' ); ?></h2><p><?php esc_html_e( 'Coverage, exclusions and any remaining responsibility are confirmed in the rental contract.', 'rentacar-venezia-v2' ); ?></p></section><section><h2><?php esc_html_e( 'Airport pickup', 'rentacar-venezia-v2' ); ?></h2><p><?php esc_html_e( 'Venice Marco Polo Airport and Treviso Airport are the available public pickup locations.', 'rentacar-venezia-v2' ); ?></p></section></div>
            <?php if ( '1' !== get_post_meta( get_the_ID(), '_rc_provisioned_content', true ) && trim( get_the_content() ) ) : ?><div class="content-page__body"><?php the_content(); ?></div><?php endif; ?>
        <?php endwhile; ?>
    </article>
</main>
<?php get_footer();
