<?php
defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="main-content" class="site-main site-main--content">
    <article class="rc-container content-page<?php echo 'cookie_policy' === get_post_meta( get_queried_object_id(), '_rc_provisioning_key', true ) ? ' legal-page' : ''; ?>">
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
            <?php if ( 'guides' === get_post_meta( get_the_ID(), '_rc_provisioning_key', true ) ) : ?>
                <?php
                $guides = new WP_Query(
                    array(
                        'post_type'           => 'post',
                        'post_status'         => 'publish',
                        'posts_per_page'      => 12,
                        'ignore_sticky_posts' => true,
                        'meta_key'            => '_rc_seo_indexable',
                        'meta_value'          => '1',
                    )
                );
                ?>
                <?php if ( $guides->have_posts() ) : ?>
                    <section class="archive-page__list" aria-label="<?php esc_attr_e( 'Guides for Venice and Treviso', 'rentacar-venezia-v2' ); ?>">
                        <?php while ( $guides->have_posts() ) : $guides->the_post(); ?>
                            <?php get_template_part( 'template-parts/content/article-card' ); ?>
                        <?php endwhile; ?>
                    </section>
                    <?php wp_reset_postdata(); ?>
                <?php else : ?>
                    <section class="empty-state"><h2><?php esc_html_e( 'Guides will appear here when they are ready to publish.', 'rentacar-venezia-v2' ); ?></h2><p><?php esc_html_e( 'In the meantime, our team can help you choose the right airport pickup and vehicle.', 'rentacar-venezia-v2' ); ?></p></section>
                <?php endif; ?>
            <?php endif; ?>
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
