<?php
/* Template Name: Airport location */
defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="main-content" class="site-main site-main--content">
    <article class="rc-container content-page">
        <?php while ( have_posts() ) : the_post(); $location_key = (string) get_post_meta( get_the_ID(), '_rentacar_location_key', true ); $location_theme_image = rentacar_venezia_v2_location_theme_image( $location_key ); $location = rentacar_venezia_v2_pickup_locations()[ $location_key ] ?? array(); $airport_code = 'treviso_airport' === $location_key ? 'TSF' : 'VCE'; ?>
            <?php get_template_part( 'template-parts/global/breadcrumbs' ); ?>
            <header class="page-intro content-page__header"><p class="eyebrow"><?php esc_html_e( 'Airport pickup', 'rentacar-venezia-v2' ); ?></p><h1><?php the_title(); ?></h1></header>
            <?php if ( has_post_thumbnail() ) : ?>
                <figure class="content-page__featured-image"><?php the_post_thumbnail( 'large', array( 'loading' => 'eager', 'fetchpriority' => 'high', 'alt' => '' ) ); ?></figure>
            <?php elseif ( $location_theme_image ) : ?>
                <figure class="content-page__featured-image content-page__featured-image--location"><img src="<?php echo esc_url( $location_theme_image['url'] ); ?>" width="<?php echo esc_attr( $location_theme_image['width'] ); ?>" height="<?php echo esc_attr( $location_theme_image['height'] ); ?>" alt="" fetchpriority="high" decoding="async"></figure>
            <?php endif; ?>
            <div class="content-page__body"><?php the_content(); ?></div>
            <section class="airport-page__process" aria-labelledby="airport-process-title">
                <p class="eyebrow"><?php esc_html_e( 'Airport pickup', 'rentacar-venezia-v2' ); ?></p>
                <h2 id="airport-process-title"><?php echo esc_html( sprintf( __( 'Pickup at %s in three simple steps', 'rentacar-venezia-v2' ), $location['label'] ?? get_the_title() ) ); ?></h2>
                <ol><li><strong><?php esc_html_e( 'Choose your car', 'rentacar-venezia-v2' ); ?></strong><span><?php esc_html_e( 'Browse the current fleet and select the vehicle that fits your trip.', 'rentacar-venezia-v2' ); ?></span></li><li><strong><?php esc_html_e( 'Send your trip details', 'rentacar-venezia-v2' ); ?></strong><span><?php esc_html_e( 'Choose your dates and select this airport as the pickup location.', 'rentacar-venezia-v2' ); ?></span></li><li><strong><?php esc_html_e( 'Receive the final details', 'rentacar-venezia-v2' ); ?></strong><span><?php esc_html_e( 'Our team confirms availability, the final price and the practical pickup arrangement.', 'rentacar-venezia-v2' ); ?></span></li></ol>
            </section>
            <section class="airport-page__practical" aria-labelledby="airport-practical-title">
                <h2 id="airport-practical-title"><?php esc_html_e( 'Practical information', 'rentacar-venezia-v2' ); ?></h2>
                <dl><div><dt><?php esc_html_e( 'Airport code', 'rentacar-venezia-v2' ); ?></dt><dd><?php echo esc_html( $airport_code ); ?></dd></div><div><dt><?php esc_html_e( 'After-hours pickup', 'rentacar-venezia-v2' ); ?></dt><dd><?php esc_html_e( '07:30–19:30 included; 19:30–22:30 €25; 22:30–05:30 €50; 05:30–07:30 €25.', 'rentacar-venezia-v2' ); ?></dd></div><div><dt><?php esc_html_e( 'Different-airport return', 'rentacar-venezia-v2' ); ?></dt><dd><?php esc_html_e( 'A €25 charge applies when pickup and return airports differ.', 'rentacar-venezia-v2' ); ?></dd></div></dl>
            </section>
            <section class="airport-page__faq" aria-labelledby="airport-faq-title">
                <h2 id="airport-faq-title"><?php esc_html_e( 'Airport pickup questions', 'rentacar-venezia-v2' ); ?></h2>
                <details><summary><?php esc_html_e( 'Is there an airport desk?', 'rentacar-venezia-v2' ); ?></summary><p><?php esc_html_e( 'Pickup arrangements are confirmed directly with you before collection.', 'rentacar-venezia-v2' ); ?></p></details>
                <details><summary><?php esc_html_e( 'Can I return to the other airport?', 'rentacar-venezia-v2' ); ?></summary><p><?php esc_html_e( 'Yes. Select a different return airport in the reservation form; the applicable €25 charge is shown separately in the estimate.', 'rentacar-venezia-v2' ); ?></p></details>
            </section>
            <section class="airport-page__cta"><h2><?php esc_html_e( 'Choose the car for your journey', 'rentacar-venezia-v2' ); ?></h2><a class="button" href="<?php echo esc_url( add_query_arg( 'pickup_location', $location['value'] ?? '', rentacar_venezia_v2_fleet_url() ) ); ?>"><?php esc_html_e( 'Explore the fleet', 'rentacar-venezia-v2' ); ?></a></section>
        <?php endwhile; ?>
    </article>
</main>
<?php get_footer();
