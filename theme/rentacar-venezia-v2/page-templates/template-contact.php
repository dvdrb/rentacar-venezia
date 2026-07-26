<?php
/* Template Name: Contact */
defined( 'ABSPATH' ) || exit;

$whatsapp_url = rentacar_venezia_v2_whatsapp_url();
$contact_status = isset( $_GET['contact_status'] ) ? sanitize_key( wp_unslash( $_GET['contact_status'] ) ) : '';
$privacy_policy_url = function_exists( 'get_privacy_policy_url' ) ? get_privacy_policy_url() : '';

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
                <section class="contact-form" aria-labelledby="contact-form-title">
                    <h2 id="contact-form-title"><?php esc_html_e( 'Send a general question', 'rentacar-venezia-v2' ); ?></h2>
                    <p><?php esc_html_e( 'For a vehicle reservation, please use the reservation request from the fleet. Use this form for other questions.', 'rentacar-venezia-v2' ); ?></p>
                    <?php if ( 'sent' === $contact_status ) : ?>
                        <p class="contact-form__status contact-form__status--success" role="status"><?php esc_html_e( 'Thank you. Your message has been sent.', 'rentacar-venezia-v2' ); ?></p>
                    <?php elseif ( $contact_status ) : ?>
                        <p class="contact-form__status contact-form__status--error" role="alert"><?php esc_html_e( 'We could not send your message. Please review the form or contact us by phone or WhatsApp.', 'rentacar-venezia-v2' ); ?></p>
                    <?php endif; ?>
                    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
                        <input type="hidden" name="action" value="rentacar_submit_contact">
                        <?php wp_nonce_field( 'rentacar_submit_contact', 'rentacar_contact_nonce' ); ?>
                        <p class="honeypot" aria-hidden="true"><label><?php esc_html_e( 'Website', 'rentacar-venezia-v2' ); ?><input name="website" tabindex="-1" autocomplete="off"></label></p>
                        <div class="contact-form__two"><label><?php esc_html_e( 'Full name', 'rentacar-venezia-v2' ); ?><input name="name" autocomplete="name" required></label><label><?php esc_html_e( 'Phone or WhatsApp', 'rentacar-venezia-v2' ); ?><input name="phone" autocomplete="tel" required></label></div>
                        <label><?php esc_html_e( 'Email', 'rentacar-venezia-v2' ); ?><input name="email" type="email" autocomplete="email" required></label>
                        <label><?php esc_html_e( 'Topic', 'rentacar-venezia-v2' ); ?><select name="topic" required><option value=""><?php esc_html_e( 'Choose a topic', 'rentacar-venezia-v2' ); ?></option><option value="general"><?php esc_html_e( 'General question', 'rentacar-venezia-v2' ); ?></option><option value="airport"><?php esc_html_e( 'Airport pickup', 'rentacar-venezia-v2' ); ?></option><option value="existing_request"><?php esc_html_e( 'Existing request', 'rentacar-venezia-v2' ); ?></option></select></label>
                        <label><?php esc_html_e( 'Message', 'rentacar-venezia-v2' ); ?><textarea name="message" rows="5" required></textarea></label>
                        <label class="check-label"><input name="privacy" type="checkbox" value="1" required><span><?php esc_html_e( 'I agree that my details will be used only to respond to this question.', 'rentacar-venezia-v2' ); ?><?php if ( $privacy_policy_url ) : ?> <a href="<?php echo esc_url( $privacy_policy_url ); ?>"><?php esc_html_e( 'Privacy Policy', 'rentacar-venezia-v2' ); ?></a><?php endif; ?></span></label>
                        <button class="button" type="submit"><?php esc_html_e( 'Send message', 'rentacar-venezia-v2' ); ?></button>
                    </form>
                </section>
                <?php wp_link_pages( array( 'before' => '<nav class="post-pagination" aria-label="' . esc_attr__( 'Page navigation', 'rentacar-venezia-v2' ) . '">', 'after' => '</nav>' ) ); ?>
            </article>
        </div>
    <?php endwhile; ?>
</main>
<?php get_footer();
