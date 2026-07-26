<?php
defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="main-content" class="site-main">
    <section class="section archive-page">
        <div class="rc-container">
            <?php get_template_part( 'template-parts/global/breadcrumbs' ); ?>
            <header class="page-intro"><p class="eyebrow"><?php esc_html_e( 'Local information', 'rentacar-venezia-v2' ); ?></p><h1><?php esc_html_e( 'Guides for Venice and Treviso', 'rentacar-venezia-v2' ); ?></h1><p><?php esc_html_e( 'Practical information for airport pickup, driving and preparing your rental request.', 'rentacar-venezia-v2' ); ?></p></header>
            <?php if ( have_posts() ) : ?><div class="archive-page__list"><?php while ( have_posts() ) : the_post(); get_template_part( 'template-parts/content/article-card' ); endwhile; ?></div><?php the_posts_pagination(); ?><?php else : ?><section class="empty-state"><h2><?php esc_html_e( 'Guides will appear here when they are ready to publish.', 'rentacar-venezia-v2' ); ?></h2><p><?php esc_html_e( 'In the meantime, our team can help you choose the right airport pickup and vehicle.', 'rentacar-venezia-v2' ); ?></p></section><?php endif; ?>
            <p class="fleet-final-cta"><a class="button" data-guide-cta href="<?php echo esc_url( rentacar_venezia_v2_fleet_url() ); ?>"><?php esc_html_e( 'Explore the fleet', 'rentacar-venezia-v2' ); ?></a></p>
        </div>
    </section>
</main>
<?php get_footer();
