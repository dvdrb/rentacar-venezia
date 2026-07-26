<?php
defined( 'ABSPATH' ) || exit;

final class Rentacar_Core_Null_Language_Resolver implements Rentacar_Core_Language_Resolver_Interface {
    public function current_language() { return substr( determine_locale(), 0, 2 ); }

    public function default_language() { return $this->current_language(); }

    public function post_language( $post_id ) { return null; }

    public function translate_post_id( $post_id, $language = null ) { return (int) $post_id; }

    public function translations( $post_id ) { return array(); }

    public function translated_permalink( $post_id, $language = null ) { return get_permalink( (int) $post_id ); }
}
