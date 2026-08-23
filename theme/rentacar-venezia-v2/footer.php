<footer class="site-footer">
    <?php $business = rentacar_venezia_v2_business_data(); ?>
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
            <p><?php echo esc_html( $business['street_address'] . ', ' . $business['locality'] ); ?></p>
            <a href="tel:<?php echo esc_attr( $business['phone'] ); ?>"><?php echo esc_html( $business['phone_display'] ); ?></a>
            <a href="mailto:<?php echo esc_attr( $business['email'] ); ?>"><?php echo esc_html( $business['email'] ); ?></a>
            <p><?php echo esc_html( $business['weekday_hours'] ); ?></p>
            <?php if ( '' !== $business['weekend_hours'] ) : ?><p><?php echo esc_html( $business['weekend_hours'] ); ?></p><?php endif; ?>
            <nav class="site-footer__social" aria-label="<?php esc_attr_e( 'Social links', 'rentacar-venezia-v2' ); ?>">
                <a class="site-footer__social-link site-footer__social-link--instagram" href="https://www.instagram.com/rentacar_veniceairport/" target="_blank" rel="noopener noreferrer" aria-label="Instagram"><svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.7" r=".9" class="site-footer__social-fill"/></svg><span class="screen-reader-text">Instagram</span></a>
                <a class="site-footer__social-link site-footer__social-link--facebook" href="https://www.facebook.com/people/Rent-A-Car-Venezia-no-credit-card/61585973730435/#" target="_blank" rel="noopener noreferrer" aria-label="Facebook"><svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M14 21v-8h2.8l.4-3H14V8.1c0-.9.3-1.6 1.7-1.6H17V3.8c-.3 0-1.2-.1-2.2-.1-2.2 0-3.7 1.3-3.7 3.8V10H8.5v3h2.6v8H14Z" class="site-footer__social-fill"/></svg><span class="screen-reader-text">Facebook</span></a>
                <?php if ( rentacar_venezia_v2_whatsapp_url() ) : ?><a class="site-footer__social-link site-footer__social-link--whatsapp" href="<?php echo esc_url( rentacar_venezia_v2_whatsapp_url() ); ?>" target="_blank" rel="noopener noreferrer" aria-label="WhatsApp"><svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z" class="site-footer__social-fill"/></svg><span class="screen-reader-text">WhatsApp</span></a><?php endif; ?>
                <?php if ( rentacar_venezia_v2_telegram_url() ) : ?><a class="site-footer__social-link site-footer__social-link--telegram" href="<?php echo esc_url( rentacar_venezia_v2_telegram_url() ); ?>" target="_blank" rel="noopener noreferrer" aria-label="Telegram"><svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="m21 4-3 16-6-5-3.1 2.9.4-4.2L17 6.4 7.5 12l-3.5-1.2L21 4Z" class="site-footer__social-fill"/><path d="m9.3 13.7 2.7 1.3"/></svg><span class="screen-reader-text">Telegram</span></a><?php endif; ?>
            </nav>
        </section>
    </div>
    <div class="site-footer__bottom rc-container">
        <small>&copy; <?php echo esc_html( wp_date( 'Y' ) ); ?> <?php echo esc_html( $business['public_name'] ); ?></small>
        <nav class="site-footer__legal" aria-label="<?php esc_attr_e( 'Legal information', 'rentacar-venezia-v2' ); ?>">
            <?php $terms_url = rentacar_venezia_v2_managed_page_url( 'terms' ); if ( $terms_url ) : ?><a href="<?php echo esc_url( $terms_url ); ?>"><?php esc_html_e( 'Terms and Conditions', 'rentacar-venezia-v2' ); ?></a><?php endif; ?>
            <?php $privacy_url = rentacar_venezia_v2_localized_privacy_policy_url(); if ( $privacy_url ) : ?><a href="<?php echo esc_url( $privacy_url ); ?>"><?php esc_html_e( 'Privacy Policy', 'rentacar-venezia-v2' ); ?></a><?php endif; ?>
        </nav>
    </div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
