<?php
defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="main-content" class="site-main site-main--content">
    <article class="rc-container content-page">
        <?php while ( have_posts() ) : the_post(); ?>
            <?php get_template_part( 'template-parts/global/breadcrumbs' ); ?>
            <header class="page-intro content-page__header">
                <p class="eyebrow"><?php esc_html_e( 'Rent a Car Venezia', 'rentacar-venezia-v2' ); ?></p>
                <h1><?php the_title(); ?></h1>
            </header>
            <?php if ( has_post_thumbnail() ) : ?>
                <figure class="content-page__featured-image"><?php the_post_thumbnail( 'large', array( 'loading' => 'eager', 'fetchpriority' => 'high', 'alt' => '' ) ); ?></figure>
            <?php endif; ?>
            <div class="content-page__body">
                <?php the_content(); ?>
                <?php wp_link_pages( array( 'before' => '<nav class="post-pagination" aria-label="' . esc_attr__( 'Page navigation', 'rentacar-venezia-v2' ) . '">', 'after' => '</nav>' ) ); ?>
            </div>
            <?php if ( function_exists( 'have_rows' ) && have_rows( 'faq' ) ) : ?>
                <section class="faq-list" aria-label="<?php esc_attr_e( 'Frequently asked questions', 'rentacar-venezia-v2' ); ?>">
                    <?php while ( have_rows( 'faq' ) ) : the_row(); ?>
                        <details><summary><?php echo esc_html( get_sub_field( 'question' ) ); ?></summary><div><?php echo wp_kses_post( get_sub_field( 'answer' ) ); ?></div></details>
                    <?php endwhile; ?>
                </section>
            <?php endif; ?>
        <?php endwhile; ?>
    </article>
</main>
<?php get_footer(); ?>
