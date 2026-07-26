<?php
/* Template Name: Airport location */
defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="main-content" class="site-main site-main--content">
    <article class="rc-container content-page">
        <?php while ( have_posts() ) : the_post(); $location_key = (string) get_post_meta( get_the_ID(), '_rentacar_location_key', true ); $location_theme_image = rentacar_venezia_v2_location_theme_image( $location_key ); ?>
            <?php get_template_part( 'template-parts/global/breadcrumbs' ); ?>
            <header class="page-intro content-page__header"><p class="eyebrow"><?php esc_html_e( 'Airport pickup', 'rentacar-venezia-v2' ); ?></p><h1><?php the_title(); ?></h1></header>
            <?php if ( has_post_thumbnail() ) : ?>
                <figure class="content-page__featured-image"><?php the_post_thumbnail( 'large', array( 'loading' => 'eager', 'fetchpriority' => 'high', 'alt' => '' ) ); ?></figure>
            <?php elseif ( $location_theme_image ) : ?>
                <figure class="content-page__featured-image content-page__featured-image--location"><img src="<?php echo esc_url( $location_theme_image['url'] ); ?>" width="<?php echo esc_attr( $location_theme_image['width'] ); ?>" height="<?php echo esc_attr( $location_theme_image['height'] ); ?>" alt="" fetchpriority="high" decoding="async"></figure>
            <?php endif; ?>
            <div class="content-page__body"><?php the_content(); ?></div>
        <?php endwhile; ?>
    </article>
</main>
<?php get_footer();
