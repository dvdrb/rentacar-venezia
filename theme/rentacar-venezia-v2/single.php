<?php
defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="main-content" class="site-main"><article class="rc-container content-page"><?php while ( have_posts() ) : the_post(); ?><header class="page-intro"><p class="eyebrow"><?php echo esc_html( get_post_type_object( get_post_type() )->labels->singular_name ); ?></p><h1><?php the_title(); ?></h1></header><div class="content-page__body"><?php the_content(); ?></div><?php endwhile; ?></article></main>
<?php get_footer(); ?>
