<footer class="site-footer">
    <div class="rc-container site-footer__grid">
        <section aria-labelledby="footer-brand-title">
            <p id="footer-brand-title" class="site-brand site-brand--inverse"><span class="site-brand__main">RENT A CAR</span><span class="site-brand__sub">VENEZIA</span></p>
            <p><?php esc_html_e( 'Choose your preferred vehicle. Our team checks availability and confirms the final price personally.', 'rentacar-venezia-v2' ); ?></p>
        </section>
        <nav aria-label="<?php esc_attr_e( 'Footer navigation', 'rentacar-venezia-v2' ); ?>">
            <?php wp_nav_menu( array( 'theme_location' => 'footer', 'container' => false, 'menu_class' => 'footer-navigation', 'fallback_cb' => false ) ); ?>
        </nav>
    </div>
    <div class="site-footer__bottom rc-container"><small>&copy; <?php echo esc_html( wp_date( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?></small></div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
