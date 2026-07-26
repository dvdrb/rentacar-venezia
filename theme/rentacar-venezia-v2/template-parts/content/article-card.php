<?php
defined( 'ABSPATH' ) || exit;
?>
<article <?php post_class( 'archive-entry' ); ?>>
    <?php if ( has_post_thumbnail() ) : ?><a class="archive-entry__image" href="<?php the_permalink(); ?>"><?php the_post_thumbnail( 'medium_large', array( 'loading' => 'lazy', 'decoding' => 'async' ) ); ?></a><?php endif; ?>
    <div><p class="eyebrow"><?php echo esc_html( get_the_date() ); ?></p><h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2><p class="archive-entry__excerpt"><?php echo esc_html( get_the_excerpt() ); ?></p><a class="text-link" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Read more', 'rentacar-venezia-v2' ); ?></a></div>
</article>
