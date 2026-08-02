<?php
/* Template Name: Review request */
defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="main-content" class="site-main site-main--content">
    <article class="rc-container content-page">
        <?php while ( have_posts() ) : the_post(); ?>
            <?php get_template_part( 'template-parts/global/breadcrumbs' ); ?>
            <header class="page-intro content-page__header"><p class="eyebrow"><?php esc_html_e( 'Customer feedback', 'rentacar-venezia-v2' ); ?></p><h1><?php the_title(); ?></h1><p><?php esc_html_e( 'Thank you for renting with us. Choose the airport location used for your rental to share your experience.', 'rentacar-venezia-v2' ); ?></p></header>
            <div class="airport-page__cta"><h2><?php esc_html_e( 'Venice Marco Polo Airport', 'rentacar-venezia-v2' ); ?></h2><a class="button button--secondary" href="<?php echo esc_url( rentacar_venezia_v2_location_review_url( 'venice_marco_polo' ) ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Leave a review', 'rentacar-venezia-v2' ); ?></a></div>
            <div class="airport-page__cta"><h2><?php esc_html_e( 'Treviso Airport', 'rentacar-venezia-v2' ); ?></h2><a class="button button--secondary" href="<?php echo esc_url( rentacar_venezia_v2_location_review_url( 'treviso_airport' ) ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Leave a review', 'rentacar-venezia-v2' ); ?></a></div>
        <?php endwhile; ?>
    </article>
</main>
<?php get_footer();
