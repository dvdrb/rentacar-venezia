<?php
/**
 * @package Movers Lite
 */
?>
<div class="blog-post-repeat">
    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
        <?php if ( is_search() || !is_single() ) : // Only display Excerpts for Search ?>
            <div class="blog-post-thumb"><a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>"><?php the_post_thumbnail('large'); ?></a></div><!-- post-thumb -->
        <?php else : ?>
            <div class="blog-post-thumb"><a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>"><?php the_post_thumbnail('large'); ?></a></div><!-- post-thumb -->
        <?php endif; ?>
        <div class="post-content">
            <h2 class="entry-title"><a href="<?php the_permalink(); ?>" rel="bookmark"><?php the_title(); ?></a></h2>
            <?php if ( is_search() || !is_single() ) : // Only display Excerpts for Search ?>
                <div class="entry-summary">
                    <?php the_excerpt(); ?>
                    <p class="read-more"><a href="<?php the_permalink(); ?>" title="<?php the_title(); ?>" ><?php esc_attr_e('Read More','movers-lite'); ?></a></p>
                </div><!-- .entry-summary -->
            <?php else : ?>
                <div class="entry-content">
                    <?php the_content( __( 'Continue reading <span class="meta-nav">&rarr;</span>', 'movers-lite' ) ); ?>
                </div><!-- .entry-content --><div class="clear"></div>
        </div>
            <?php
                    wp_link_pages( array(
                        'before' => '<div class="page-links">' . __( 'Pages:', 'movers-lite' ),
                        'after'  => '</div>',
                    ) );
                ?>
        <?php endif; ?>
    </article><!-- #post-## -->
</div><!-- blog-post-repeat -->