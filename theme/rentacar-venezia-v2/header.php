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
        <a class="site-brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
            <span class="site-brand__main">RENT A CAR</span>
            <span class="site-brand__sub">VENEZIA</span>
        </a>
        <button class="menu-toggle" type="button" aria-expanded="false" aria-controls="primary-navigation" data-menu-toggle>
            <span class="screen-reader-text"><?php esc_html_e( 'Toggle navigation', 'rentacar-venezia-v2' ); ?></span>
            <span aria-hidden="true"></span><span aria-hidden="true"></span><span aria-hidden="true"></span>
        </button>
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
            <?php $languages = rentacar_venezia_v2_language_links(); ?>
            <?php if ( $languages ) : ?>
                <ul class="language-switcher" aria-label="<?php esc_attr_e( 'Language selector', 'rentacar-venezia-v2' ); ?>">
                    <?php foreach ( $languages as $language ) : ?>
                        <li>
                            <a href="<?php echo esc_url( $language['url'] ); ?>" lang="<?php echo esc_attr( $language['language_code'] ); ?>"<?php echo ! empty( $language['active'] ) ? ' aria-current="page"' : ''; ?>><?php echo esc_html( strtoupper( $language['language_code'] ) ); ?></a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
            <?php $whatsapp_url = rentacar_venezia_v2_whatsapp_url(); ?>
            <?php if ( $whatsapp_url ) : ?>
                <a class="button button--whatsapp" href="<?php echo esc_url( $whatsapp_url ); ?>"><?php esc_html_e( 'WhatsApp', 'rentacar-venezia-v2' ); ?></a>
            <?php endif; ?>
        </nav>
    </div>
</header>
