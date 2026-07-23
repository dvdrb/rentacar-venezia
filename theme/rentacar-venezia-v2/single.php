<?php
defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="main-content" class="site-main">
    <article class="rc-container content-page">
        <?php while ( have_posts() ) : the_post(); ?>
            <nav class="breadcrumbs" aria-label="<?php esc_attr_e( 'Breadcrumbs', 'rentacar-venezia-v2' ); ?>">
                <?php foreach ( rentacar_venezia_v2_breadcrumb_items() as $index => $item ) : ?>
                    <?php if ( $item['url'] ) : ?><a href="<?php echo esc_url( $item['url'] ); ?>"><?php echo esc_html( $item['label'] ); ?></a><?php else : ?><span aria-current="page"><?php echo esc_html( $item['label'] ); ?></span><?php endif; ?>
                    <?php if ( $index < count( rentacar_venezia_v2_breadcrumb_items() ) - 1 ) : ?><span aria-hidden="true">/</span><?php endif; ?>
                <?php endforeach; ?>
            </nav>
            <header class="page-intro">
                <p class="eyebrow"><?php echo esc_html( get_post_type_object( get_post_type() )->labels->singular_name ); ?></p>
                <h1><?php the_title(); ?></h1>
            </header>
            <?php if ( has_post_thumbnail() ) : ?>
                <figure class="content-page__featured-image"><?php the_post_thumbnail( 'large', array( 'loading' => 'eager', 'fetchpriority' => 'high', 'alt' => '' ) ); ?></figure>
            <?php endif; ?>
            <div class="content-page__body">
                <?php the_content(); ?>
                <?php wp_link_pages( array( 'before' => '<nav class="post-pagination" aria-label="' . esc_attr__( 'Page navigation', 'rentacar-venezia-v2' ) . '">', 'after' => '</nav>' ) ); ?>
            </div>
        <?php endwhile; ?>
    </article>
</main>
<?php get_footer(); ?>
