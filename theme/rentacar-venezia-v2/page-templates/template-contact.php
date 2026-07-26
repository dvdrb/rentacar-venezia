<?php
/* Template Name: Contact */
defined( 'ABSPATH' ) || exit;

$whatsapp_url = rentacar_venezia_v2_whatsapp_url();

get_header();
?>
<main id="main-content" class="site-main contact-page">
    <?php while ( have_posts() ) : the_post(); ?>
        <section class="contact-page__hero">
            <div class="rc-container contact-page__hero-inner">
                <?php get_template_part( 'template-parts/global/breadcrumbs' ); ?>
                <div>
                    <p class="contact-page__eyebrow"><?php esc_html_e( 'Local support', 'rentacar-venezia-v2' ); ?></p>
                    <h1><?php the_title(); ?></h1>
                    <p class="contact-page__intro"><?php esc_html_e( 'Tell us your preferred vehicle, travel dates and airport. We will reply personally with availability, the final price and rental conditions.', 'rentacar-venezia-v2' ); ?></p>
                    <div class="contact-page__actions">
                        <a class="button" href="<?php echo esc_url( rentacar_venezia_v2_fleet_url() ); ?>"><?php esc_html_e( 'View all cars', 'rentacar-venezia-v2' ); ?></a>
                        <?php if ( $whatsapp_url ) : ?><a class="button button--secondary" href="<?php echo esc_url( $whatsapp_url ); ?>"><?php esc_html_e( 'Contact us on WhatsApp', 'rentacar-venezia-v2' ); ?></a><?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
        <div class="rc-container contact-page__layout">
            <aside class="contact-page__guide" aria-labelledby="contact-guide-title">
                <h2 id="contact-guide-title"><?php esc_html_e( 'How it works', 'rentacar-venezia-v2' ); ?></h2>
                <ol>
                    <li><strong><?php esc_html_e( 'Choose a vehicle', 'rentacar-venezia-v2' ); ?></strong><?php esc_html_e( 'Browse the fleet and select the car that suits your trip.', 'rentacar-venezia-v2' ); ?></li>
                    <li><strong><?php esc_html_e( 'Send the reservation request', 'rentacar-venezia-v2' ); ?></strong><?php esc_html_e( 'Share your dates and airport details in one short request.', 'rentacar-venezia-v2' ); ?></li>
                    <li><strong><?php esc_html_e( 'Personal confirmation', 'rentacar-venezia-v2' ); ?></strong><?php esc_html_e( 'We confirm availability, the final price and rental conditions personally.', 'rentacar-venezia-v2' ); ?></li>
                </ol>
            </aside>
            <article class="contact-page__content content-page__body">
                <?php if ( has_post_thumbnail() ) : ?><figure class="content-page__featured-image"><?php the_post_thumbnail( 'large', array( 'loading' => 'eager', 'fetchpriority' => 'high', 'alt' => '' ) ); ?></figure><?php endif; ?>
                <?php the_content(); ?>
                <?php wp_link_pages( array( 'before' => '<nav class="post-pagination" aria-label="' . esc_attr__( 'Page navigation', 'rentacar-venezia-v2' ) . '">', 'after' => '</nav>' ) ); ?>
            </article>
        </div>
    <?php endwhile; ?>
</main>
<?php get_footer();
