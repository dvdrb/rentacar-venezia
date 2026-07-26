<?php
defined( 'ABSPATH' ) || exit;

final class Rentacar_Core_Language_Resolver_Factory {
    public static function create() {
        if ( function_exists( 'pll_current_language' ) ) {
            return new Rentacar_Core_Polylang_Language_Resolver();
        }

        if ( has_filter( 'wpml_current_language' ) ) {
            return new Rentacar_Core_Wpml_Language_Resolver();
        }

        return new Rentacar_Core_Null_Language_Resolver();
    }
}
