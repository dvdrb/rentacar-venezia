<footer class="site-footer">
    <div class="rc-container site-footer__grid">
        <section aria-labelledby="footer-brand-title">
            <div id="footer-brand-title" class="site-footer__brand">
                <?php rentacar_venezia_v2_brand_mark( true ); ?>
            </div>
            <p><?php esc_html_e( 'Choose your preferred vehicle. Our team checks availability and confirms the final price personally.', 'rentacar-venezia-v2' ); ?></p>
        </section>
        <nav aria-label="<?php esc_attr_e( 'Explore and rental information', 'rentacar-venezia-v2' ); ?>">
            <?php wp_nav_menu( array( 'theme_location' => has_nav_menu( 'footer' ) ? 'footer' : 'primary', 'container' => false, 'menu_class' => 'footer-navigation', 'fallback_cb' => false ) ); ?>
        </nav>
        <section class="site-footer__contact" aria-labelledby="footer-contact-title">
            <h2 id="footer-contact-title"><?php esc_html_e( 'Contact', 'rentacar-venezia-v2' ); ?></h2>
            <a href="tel:+393445068823">+39 344 506 8823</a>
            <a href="mailto:info@rentacarvenezia.it">info@rentacarvenezia.it</a>
            <p><?php esc_html_e( 'Monday–Friday, 08:00–17:00', 'rentacar-venezia-v2' ); ?></p>
            <?php if ( rentacar_venezia_v2_whatsapp_url() ) : ?><a class="text-link" href="<?php echo esc_url( rentacar_venezia_v2_whatsapp_url() ); ?>"><?php esc_html_e( 'Contact on WhatsApp', 'rentacar-venezia-v2' ); ?></a><?php endif; ?>
        </section>
    </div>
    <div class="site-footer__bottom rc-container"><small>&copy; <?php echo esc_html( wp_date( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?></small><?php if ( get_privacy_policy_url() ) : ?><a href="<?php echo esc_url( get_privacy_policy_url() ); ?>"><?php esc_html_e( 'Privacy Policy', 'rentacar-venezia-v2' ); ?></a><?php endif; ?></div>
</footer>
<?php
$mobile_whatsapp = rentacar_venezia_v2_whatsapp_url();
$mobile_action_label = '';
$mobile_action_url = '';
if ( is_front_page() || is_page_template( 'page-templates/template-airport-location.php' ) ) {
    $mobile_action_label = __( 'Explore the fleet', 'rentacar-venezia-v2' );
    $mobile_action_url   = rentacar_venezia_v2_fleet_url();
} elseif ( is_page_template( 'page-templates/template-fleet.php' ) ) {
    $mobile_action_label = __( 'Filters', 'rentacar-venezia-v2' );
    $mobile_action_url   = '#fleet-filters';
}
?>
<?php if ( $mobile_whatsapp && $mobile_action_url ) : ?>
    <nav class="mobile-action-bar" aria-label="<?php esc_attr_e( 'Quick actions', 'rentacar-venezia-v2' ); ?>">
        <a class="mobile-action-bar__whatsapp" href="<?php echo esc_url( $mobile_whatsapp ); ?>"><?php esc_html_e( 'WhatsApp', 'rentacar-venezia-v2' ); ?></a>
        <a class="mobile-action-bar__primary" href="<?php echo esc_url( $mobile_action_url ); ?>"<?php echo '#fleet-filters' === $mobile_action_url ? ' data-mobile-filter-trigger' : ''; ?>><?php echo esc_html( $mobile_action_label ); ?></a>
    </nav>
<?php endif; ?>
<?php wp_footer(); ?>
</body>
</html>
