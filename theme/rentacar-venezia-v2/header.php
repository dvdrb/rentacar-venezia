<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo( 'charset' ); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link" href="#main-content"><?php esc_html_e( 'Skip to content', 'rentacar-venezia-v2' ); ?></a>
<header class="site-header" data-site-header>
    <div class="site-header__inner rc-container">
        <div class="site-branding">
            <?php rentacar_venezia_v2_brand_mark(); ?>
        </div>
        <nav id="primary-navigation" class="primary-navigation" aria-label="<?php esc_attr_e( 'Primary navigation', 'rentacar-venezia-v2' ); ?>" data-primary-navigation>
            <?php
            wp_nav_menu(
                array(
                    'theme_location' => 'primary',
                    'container'      => false,
                    'menu_class'     => 'primary-navigation__list',
                    'fallback_cb'    => 'wp_page_menu',
                )
            );
            ?>
        </nav>
        <div class="site-header__actions">
            <?php get_template_part( 'template-parts/global/language-switcher' ); ?>
            <?php $whatsapp_url = rentacar_venezia_v2_whatsapp_url(); ?>
            <?php if ( $whatsapp_url ) : ?>
                <a class="button button--whatsapp" href="<?php echo esc_url( $whatsapp_url ); ?>"><?php esc_html_e( 'WhatsApp', 'rentacar-venezia-v2' ); ?></a>
            <?php endif; ?>
            <button class="menu-toggle" type="button" aria-expanded="false" aria-controls="primary-navigation" data-menu-toggle>
                <span class="screen-reader-text"><?php esc_html_e( 'Toggle navigation', 'rentacar-venezia-v2' ); ?></span>
                <span aria-hidden="true"></span><span aria-hidden="true"></span><span aria-hidden="true"></span>
            </button>
        </div>
    </div>
</header>
