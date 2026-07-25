<?php
/**
 * WPML-powered header language selector.
 *
 * Country flags and page-equivalent links are supplied directly by WPML. The
 * component is intentionally absent if WPML is unavailable or only one
 * language is enabled.
 */

defined( 'ABSPATH' ) || exit;

$languages = rentacar_venezia_v2_language_links();
if ( ! $languages ) {
    return;
}

$current_language = null;
foreach ( $languages as $language ) {
    if ( ! empty( $language['active'] ) ) {
        $current_language = $language;
        break;
    }
}

if ( ! is_array( $current_language ) || empty( $current_language['language_code'] ) ) {
    return;
}

$menu_id = wp_unique_id( 'language-switcher-menu-' );
$current_code = strtoupper( (string) $current_language['language_code'] );
?>
<div class="language-switcher" data-language-switcher>
    <button
        type="button"
        class="language-switcher__trigger"
        aria-expanded="false"
        aria-controls="<?php echo esc_attr( $menu_id ); ?>"
        aria-label="<?php esc_attr_e( 'Choose language', 'rentacar-venezia-v2' ); ?>"
        data-language-trigger
    >
        <?php if ( ! empty( $current_language['country_flag_url'] ) ) : ?>
            <img class="language-switcher__flag" src="<?php echo esc_url( $current_language['country_flag_url'] ); ?>" alt="" width="22" height="15" loading="eager" decoding="async">
        <?php else : ?>
            <span class="language-switcher__flag-fallback" aria-hidden="true"><?php echo esc_html( $current_code ); ?></span>
        <?php endif; ?>
        <span class="language-switcher__code"><?php echo esc_html( $current_code ); ?></span>
        <svg class="language-switcher__chevron" viewBox="0 0 16 16" aria-hidden="true"><path d="m4 6 4 4 4-4"></path></svg>
    </button>

    <div id="<?php echo esc_attr( $menu_id ); ?>" class="language-switcher__menu" hidden data-language-menu>
        <ul class="language-switcher__list">
            <?php foreach ( $languages as $language ) : ?>
                <?php
                if ( empty( $language['language_code'] ) || empty( $language['url'] ) ) {
                    continue;
                }

                $language_code = (string) $language['language_code'];
                $language_name = ! empty( $language['native_name'] )
                    ? (string) $language['native_name']
                    : ( ! empty( $language['translated_name'] ) ? (string) $language['translated_name'] : strtoupper( $language_code ) );
                $is_active = ! empty( $language['active'] );
                ?>
                <li class="language-switcher__item">
                    <a class="language-switcher__link" href="<?php echo esc_url( $language['url'] ); ?>" lang="<?php echo esc_attr( $language_code ); ?>" hreflang="<?php echo esc_attr( $language_code ); ?>"<?php echo $is_active ? ' aria-current="page"' : ''; ?>>
                        <?php if ( ! empty( $language['country_flag_url'] ) ) : ?>
                            <img class="language-switcher__flag" src="<?php echo esc_url( $language['country_flag_url'] ); ?>" alt="" width="22" height="15" loading="eager" decoding="async">
                        <?php else : ?>
                            <span class="language-switcher__flag-fallback" aria-hidden="true"><?php echo esc_html( strtoupper( $language_code ) ); ?></span>
                        <?php endif; ?>
                        <span class="language-switcher__name"><?php echo esc_html( $language_name ); ?></span>
                        <?php if ( $is_active ) : ?>
                            <svg class="language-switcher__check" viewBox="0 0 16 16" aria-hidden="true"><path d="m3 8 3.1 3L13 4.5"></path></svg>
                        <?php endif; ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>
