<?php
defined( 'ABSPATH' ) || exit;

$related = new WP_Query( array( 'post_type' => 'post', 'posts_per_page' => 3, 'post__not_in' => array( get_the_ID() ), 'ignore_sticky_posts' => true ) );
if ( ! $related->have_posts() ) return;
?>
<section class="related-articles" aria-labelledby="related-guides-title"><h2 id="related-guides-title"><?php esc_html_e( 'Related guides', 'rentacar-venezia-v2' ); ?></h2><div class="archive-page__list"><?php while ( $related->have_posts() ) : $related->the_post(); get_template_part( 'template-parts/content/article-card' ); endwhile; ?></div></section>
<?php wp_reset_postdata();
