<?php
/* Template Name: How it works */
defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="main-content" class="site-main site-main--content">
    <article class="rc-container content-page information-page">
        <?php while ( have_posts() ) : the_post(); ?>
            <?php get_template_part( 'template-parts/global/breadcrumbs' ); ?>
            <header class="page-intro content-page__header"><p class="eyebrow"><?php esc_html_e( 'How it works', 'rentacar-venezia-v2' ); ?></p><h1><?php the_title(); ?></h1><p><?php esc_html_e( 'A clear route from your travel dates to collection.', 'rentacar-venezia-v2' ); ?></p></header>
            <ol class="information-steps"><li><strong><?php esc_html_e( 'Enter your trip details.', 'rentacar-venezia-v2' ); ?></strong></li><li><strong><?php esc_html_e( 'Choose your car.', 'rentacar-venezia-v2' ); ?></strong></li><li><strong><?php esc_html_e( 'Complete the short reservation form.', 'rentacar-venezia-v2' ); ?></strong></li><li><strong><?php esc_html_e( 'Receive the final details.', 'rentacar-venezia-v2' ); ?></strong></li><li><strong><?php esc_html_e( 'Collect your vehicle.', 'rentacar-venezia-v2' ); ?></strong></li></ol>
            <p class="information-page__notice"><?php esc_html_e( 'Submitting this request does not immediately confirm the reservation. We will check availability and contact you.', 'rentacar-venezia-v2' ); ?></p>
            <?php if ( '1' !== get_post_meta( get_the_ID(), '_rc_provisioned_content', true ) && trim( get_the_content() ) ) : ?><div class="content-page__body"><?php the_content(); ?></div><?php endif; ?>
            <p><a class="button" href="<?php echo esc_url( rentacar_venezia_v2_fleet_url() ); ?>"><?php esc_html_e( 'Explore the fleet', 'rentacar-venezia-v2' ); ?></a></p>
        <?php endwhile; ?>
    </article>
</main>
<?php get_footer();
