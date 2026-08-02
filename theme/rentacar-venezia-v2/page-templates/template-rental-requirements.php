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
            <div class="requirements-grid"><section><h2><?php esc_html_e( 'Driver eligibility', 'rentacar-venezia-v2' ); ?></h2><p><?php esc_html_e( 'Driver eligibility is checked by our team before confirmation of the request.', 'rentacar-venezia-v2' ); ?></p></section><section><h2><?php esc_html_e( 'Required documents', 'rentacar-venezia-v2' ); ?></h2><p><?php esc_html_e( 'The documents required for your request are confirmed by our team before collection.', 'rentacar-venezia-v2' ); ?></p></section><section><h2><?php esc_html_e( 'Payment and deposit', 'rentacar-venezia-v2' ); ?></h2><p><?php echo esc_html( rentacar_venezia_v2_payment_deposit_policy_label() ); ?></p></section><section><h2><?php esc_html_e( 'Mileage and fuel', 'rentacar-venezia-v2' ); ?></h2><p><?php echo esc_html( rentacar_venezia_v2_mileage_policy_label() ); ?></p></section><section><h2><?php esc_html_e( 'Insurance', 'rentacar-venezia-v2' ); ?></h2><p><?php esc_html_e( 'Coverage, exclusions and any remaining responsibility are confirmed in the rental contract.', 'rentacar-venezia-v2' ); ?></p></section><section><h2><?php esc_html_e( 'Airport pickup', 'rentacar-venezia-v2' ); ?></h2><p><?php esc_html_e( 'Venice Marco Polo Airport and Treviso Airport are the available public pickup locations.', 'rentacar-venezia-v2' ); ?></p></section></div>
            <?php if ( '1' !== get_post_meta( get_the_ID(), '_rc_provisioned_content', true ) && trim( get_the_content() ) ) : ?><div class="content-page__body"><?php the_content(); ?></div><?php endif; ?>
        <?php endwhile; ?>
    </article>
</main>
<?php get_footer();
