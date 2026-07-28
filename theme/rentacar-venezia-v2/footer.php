<footer class="site-footer">
    <div class="rc-container site-footer__grid">
        <section aria-labelledby="footer-brand-title">
            <div id="footer-brand-title" class="site-footer__brand">
                <?php rentacar_venezia_v2_brand_mark( 'footer' ); ?>
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
            <p><?php esc_html_e( 'Monday–Friday, 24/24', 'rentacar-venezia-v2' ); ?></p>
            <p><?php esc_html_e( 'Saturday–Sunday, 07:00–23:00', 'rentacar-venezia-v2' ); ?></p>
            <nav class="site-footer__social" aria-label="<?php esc_attr_e( 'Social links', 'rentacar-venezia-v2' ); ?>">
                <a class="site-footer__social-link" href="https://www.instagram.com/rentacar_veniceairport/" target="_blank" rel="noopener noreferrer" aria-label="Instagram"><svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.7" r=".9" class="site-footer__social-fill"/></svg><span class="screen-reader-text">Instagram</span></a>
                <a class="site-footer__social-link" href="https://www.facebook.com/people/Rent-A-Car-Venezia-no-credit-card/61585973730435/#" target="_blank" rel="noopener noreferrer" aria-label="Facebook"><svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M14 21v-8h2.8l.4-3H14V8.1c0-.9.3-1.6 1.7-1.6H17V3.8c-.3 0-1.2-.1-2.2-.1-2.2 0-3.7 1.3-3.7 3.8V10H8.5v3h2.6v8H14Z" class="site-footer__social-fill"/></svg><span class="screen-reader-text">Facebook</span></a>
                <?php if ( rentacar_venezia_v2_whatsapp_url() ) : ?><a class="site-footer__social-link" href="<?php echo esc_url( rentacar_venezia_v2_whatsapp_url() ); ?>" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp"><svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M20.5 11.6a8.4 8.4 0 0 1-12.4 7.4L3.5 20.5 5 15.9A8.5 8.5 0 1 1 20.5 11.6Z"/><path d="M8.7 7.7c.2-.5.4-.5.7-.5h.5c.2 0 .4.1.5.4l.7 1.6c.1.3.1.5-.1.7l-.5.6c.5.9 1.2 1.6 2.1 2.1l.6-.5c.2-.2.4-.2.7-.1l1.6.7c.3.1.4.3.4.5v.5c0 .3 0 .5-.5.7-.4.2-1.1.4-2 .1-1-.3-2.4-1.2-3.5-2.3-1.1-1.1-2-2.5-2.3-3.5-.3-.9-.1-1.6.1-2Z"/></svg><span class="screen-reader-text">WhatsApp</span></a><?php endif; ?>
                <?php if ( rentacar_venezia_v2_telegram_url() ) : ?><a class="site-footer__social-link" href="<?php echo esc_url( rentacar_venezia_v2_telegram_url() ); ?>" target="_blank" rel="noopener noreferrer" aria-label="Telegram"><svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="m21 4-3 16-6-5-3.1 2.9.4-4.2L17 6.4 7.5 12l-3.5-1.2L21 4Z"/><path d="m9.3 13.7 2.7 1.3"/></svg><span class="screen-reader-text">Telegram</span></a><?php endif; ?>
            </nav>
        </section>
    </div>
    <div class="site-footer__bottom rc-container">
        <small>&copy; <?php echo esc_html( wp_date( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?></small>
        <nav class="site-footer__legal" aria-label="<?php esc_attr_e( 'Legal information', 'rentacar-venezia-v2' ); ?>">
            <?php $terms_url = rentacar_venezia_v2_managed_page_url( 'terms' ); if ( $terms_url ) : ?><a href="<?php echo esc_url( $terms_url ); ?>"><?php esc_html_e( 'Terms and Conditions', 'rentacar-venezia-v2' ); ?></a><?php endif; ?>
            <?php $privacy_url = rentacar_venezia_v2_localized_privacy_policy_url(); if ( $privacy_url ) : ?><a href="<?php echo esc_url( $privacy_url ); ?>"><?php esc_html_e( 'Privacy Policy', 'rentacar-venezia-v2' ); ?></a><?php endif; ?>
        </nav>
    </div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
