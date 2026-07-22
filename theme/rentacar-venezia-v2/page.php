<?php
defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="main-content" class="site-main">
    <article class="rc-container content-page">
        <?php while ( have_posts() ) : the_post(); ?>
            <header class="page-intro"><p class="eyebrow"><?php esc_html_e( 'Rent a Car Venezia', 'rentacar-venezia-v2' ); ?></p><h1><?php the_title(); ?></h1></header>
            <div class="content-page__body"><?php the_content(); ?></div>
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
