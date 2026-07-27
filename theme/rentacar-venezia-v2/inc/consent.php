<?php
defined( 'ABSPATH' ) || exit;

/**
 * The site currently has one optional category: analytics. The cookie is
 * deliberately small and first-party; it records a visitor's choice so the
 * Google Tag Manager plugin can remain entirely absent until that choice.
 */
function rentacar_venezia_v2_cookie_consent_value() {
    $value = isset( $_COOKIE['rentacar_cookie_consent'] ) ? sanitize_key( wp_unslash( $_COOKIE['rentacar_cookie_consent'] ) ) : '';

    return in_array( $value, array( 'necessary', 'analytics' ), true ) ? $value : '';
}

function rentacar_venezia_v2_analytics_consent_granted() {
    return 'analytics' === rentacar_venezia_v2_cookie_consent_value();
}

/** Prevent GTM and its optional child tags from rendering until consent. */
function rentacar_venezia_v2_gate_optional_tracking() {
    if ( rentacar_venezia_v2_analytics_consent_granted() ) {
        return;
    }

    foreach ( array( 'wp_head', 'wp_footer', 'wp_body_open', 'body_open' ) as $hook ) {
        remove_action( $hook, 'gtm4wp_wp_header_begin' );
        remove_action( $hook, 'gtm4wp_wp_header_top' );
        remove_action( $hook, 'gtm4wp_wp_footer' );
        remove_action( $hook, 'gtm4wp_wp_body_open' );
    }
    remove_action( 'wp_enqueue_scripts', 'gtm4wp_enqueue_scripts' );
}
add_action( 'wp', 'rentacar_venezia_v2_gate_optional_tracking', 1 );

function rentacar_venezia_v2_render_cookie_consent() {
    $has_choice = '' !== rentacar_venezia_v2_cookie_consent_value();
    ?>
    <section class="cookie-consent" data-cookie-consent<?php echo $has_choice ? ' hidden' : ''; ?> aria-label="<?php esc_attr_e( 'Cookie choices', 'rentacar-venezia-v2' ); ?>">
        <div class="cookie-consent__inner">
            <div>
                <p class="cookie-consent__eyebrow"><?php esc_html_e( 'Your privacy', 'rentacar-venezia-v2' ); ?></p>
                <h2><?php esc_html_e( 'Choose how this website uses cookies', 'rentacar-venezia-v2' ); ?></h2>
                <p><?php esc_html_e( 'Necessary technologies keep the website working. Analytics is optional and helps us understand visits.', 'rentacar-venezia-v2' ); ?> <a href="<?php echo esc_url( rentacar_venezia_v2_managed_page_url( 'cookie_policy' ) ); ?>"><?php esc_html_e( 'Read the Cookie Policy', 'rentacar-venezia-v2' ); ?></a></p>
            </div>
            <div class="cookie-consent__actions">
                <button class="button button--quiet" type="button" data-cookie-reject><?php esc_html_e( 'Reject', 'rentacar-venezia-v2' ); ?></button>
                <button class="button button--quiet" type="button" data-cookie-preferences><?php esc_html_e( 'Preferences', 'rentacar-venezia-v2' ); ?></button>
                <button class="button" type="button" data-cookie-accept><?php esc_html_e( 'Accept analytics', 'rentacar-venezia-v2' ); ?></button>
            </div>
        </div>
    </section>
    <dialog class="cookie-preferences" data-cookie-preferences-dialog aria-labelledby="cookie-preferences-title">
        <form method="dialog" class="cookie-preferences__panel">
            <header><p class="eyebrow"><?php esc_html_e( 'Cookie preferences', 'rentacar-venezia-v2' ); ?></p><h2 id="cookie-preferences-title"><?php esc_html_e( 'Control optional analytics', 'rentacar-venezia-v2' ); ?></h2></header>
            <p><?php esc_html_e( 'Necessary technologies are always on. Analytics remains off unless you choose to enable it.', 'rentacar-venezia-v2' ); ?></p>
            <label class="cookie-preferences__choice"><input type="checkbox" data-cookie-analytics<?php checked( rentacar_venezia_v2_analytics_consent_granted() ); ?>> <span><strong><?php esc_html_e( 'Analytics', 'rentacar-venezia-v2' ); ?></strong><small><?php esc_html_e( 'Helps us measure visits and improve the site.', 'rentacar-venezia-v2' ); ?></small></span></label>
            <div class="cookie-preferences__actions"><button class="button button--quiet" type="button" data-cookie-close><?php esc_html_e( 'Cancel', 'rentacar-venezia-v2' ); ?></button><button class="button" type="button" data-cookie-save><?php esc_html_e( 'Save preferences', 'rentacar-venezia-v2' ); ?></button></div>
        </form>
    </dialog>
    <?php
}
add_action( 'wp_footer', 'rentacar_venezia_v2_render_cookie_consent', 5 );
