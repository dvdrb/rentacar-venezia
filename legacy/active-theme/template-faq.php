<?php
/**
Template name: FAQ page

 */
get_header(); ?>
    <div class="main-container">
        <div class="content-area">
            <div class="middle-align content_sidebar">
                <div class="site-main" id="sitemain">
                    <?php while ( have_posts() ) : the_post(); ?>

                        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                            <header class="entry-header">
                                <h1 class="entry-title"><?php the_title(); ?></h1>
                            </header><!-- .entry-header -->

                            <div class="entry-content">
                                <?php the_content(); ?>
                            </div><!-- .entry-content --><div class="clear"></div>
                        </article><!-- #post-## -->


                            <?php

//                            // check if the repeater field has rows of data
//                            if( have_rows('faq') ): ?>
<!--                                <div class="faq_section"> --><?//
//                                // loop through the rows of data
//                                while ( have_rows('faq') ) : the_row();
//
//                                    // display a sub field value
//                                    the_sub_field('question');
//                                    the_sub_field('answer');
//                                endwhile; ?><!-- </div>--><?//
//
//                            else :
//
//                                // no rows found
//
//                            endif;

                            ?>

                    <?php endwhile; // end of the loop. ?>
                </div>
                <?php get_sidebar(); ?>
                <div class="clear"></div>
            </div>
        </div>
    </div>
<?php get_footer(); ?>