<?php
defined( 'ABSPATH' ) || exit;

get_header();
$archive_title = is_home() ? single_post_title( '', false ) : get_the_archive_title();
if ( '' === trim( wp_strip_all_tags( $archive_title ) ) ) {
    $archive_title = __( 'Latest updates', 'rentacar-venezia-v2' );
}
?>
<main id="main-content" class="site-main site-main--archive">
    <div class="rc-container archive-page">
        <?php get_template_part( 'template-parts/global/breadcrumbs' ); ?>
        <header class="page-intro">
            <p class="eyebrow"><?php esc_html_e( 'Rent a Car Venezia', 'rentacar-venezia-v2' ); ?></p>
            <h1><?php echo wp_kses_post( $archive_title ); ?></h1>
        </header>
        <?php if ( have_posts() ) : ?>
            <div class="archive-page__list">
                <?php while ( have_posts() ) : the_post(); ?>
                    <article <?php post_class( 'archive-entry' ); ?>>
                        <?php if ( has_post_thumbnail() ) : ?><a class="archive-entry__image" href="<?php the_permalink(); ?>"><?php the_post_thumbnail( 'medium_large', array( 'loading' => 'lazy', 'alt' => '' ) ); ?></a><?php endif; ?>
                        <div><h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2><div class="archive-entry__excerpt"><?php the_excerpt(); ?></div><a class="text-link" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Read more', 'rentacar-venezia-v2' ); ?></a></div>
                    </article>
                <?php endwhile; ?>
            </div>
            <?php the_posts_pagination( array( 'mid_size' => 1, 'prev_text' => __( 'Previous', 'rentacar-venezia-v2' ), 'next_text' => __( 'Next', 'rentacar-venezia-v2' ) ) ); ?>
        <?php else : ?>
            <section class="empty-state"><h2><?php esc_html_e( 'Nothing has been published here yet.', 'rentacar-venezia-v2' ); ?></h2><a class="button" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Back to home', 'rentacar-venezia-v2' ); ?></a></section>
        <?php endif; ?>
    </div>
</main>
<?php get_footer(); ?>
