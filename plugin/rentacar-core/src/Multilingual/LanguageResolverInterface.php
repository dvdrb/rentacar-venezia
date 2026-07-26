<?php
defined( 'ABSPATH' ) || exit;

interface Rentacar_Core_Language_Resolver_Interface {
    public function current_language();

    public function default_language();

    public function post_language( $post_id );

    public function translate_post_id( $post_id, $language = null );

    public function translations( $post_id );

    public function translated_permalink( $post_id, $language = null );
}
