<?php
/**
 * Temporary local-only, read-only WordPress runtime audit.
 * Removed immediately after successful execution.
 */

add_action( 'init', static function (): void {
    if ( empty( $_GET['rentacar_local_audit'] ) ) {
        return;
    }

    $token = '__LOCAL_AUDIT_TOKEN__';
    $remote = $_SERVER['REMOTE_ADDR'] ?? '';
    $host = strtolower( preg_replace( '/:\d+$/', '', $_SERVER['HTTP_HOST'] ?? '' ) );
    $home_host = strtolower( (string) wp_parse_url( home_url( '/' ), PHP_URL_HOST ) );

    if (
        ! in_array( $remote, array( '127.0.0.1', '::1' ), true ) ||
        ( strpos( $host, '.local' ) === false && strpos( $host, 'rentacar-venezia-local' ) === false ) ||
        ( strpos( $home_host, '.local' ) === false && strpos( $home_host, 'rentacar-venezia-local' ) === false ) ||
        ! hash_equals( $token, (string) ( $_GET['token'] ?? '' ) )
    ) {
        status_header( 404 );
        exit;
    }

    $safe_scalar = static function ( $value ) {
        if ( is_null( $value ) || is_bool( $value ) || is_int( $value ) || is_float( $value ) ) {
            return $value;
        }
        return is_string( $value ) ? wp_strip_all_tags( $value ) : null;
    };

    $safe_value = static function ( $value ) use ( &$safe_value, $safe_scalar ) {
        if ( is_array( $value ) ) {
            $out = array();
            foreach ( $value as $key => $child ) {
                if ( is_scalar( $key ) ) {
                    $out[ (string) $key ] = $safe_value( $child );
                }
            }
            return $out;
        }
        return $safe_scalar( $value );
    };

    $language_for_post = static function ( int $post_id ): ?string {
        $language = apply_filters( 'wpml_element_language_code', null, array(
            'element_id'   => $post_id,
            'element_type' => 'post_' . get_post_type( $post_id ),
        ) );
        return is_string( $language ) ? $language : null;
    };

    $post_summary = static function ( int $post_id ) use ( $language_for_post ): ?array {
        $post = get_post( $post_id );
        if ( ! $post ) {
            return null;
        }
        return array(
            'id'       => (int) $post->ID,
            'type'     => $post->post_type,
            'title'    => wp_strip_all_tags( get_the_title( $post ) ),
            'slug'     => $post->post_name,
            'status'   => $post->post_status,
            'language' => $language_for_post( (int) $post->ID ),
            'url'      => get_permalink( $post ) ?: null,
        );
    };

    $field_summary = static function ( array $field, ?string $parent = null ) use ( &$field_summary, $safe_value ): array {
        $allowed = array( 'key', 'name', 'label', 'type', 'required', 'default_value', 'return_format', 'conditional_logic', 'choices', 'layout', 'min', 'max' );
        $out = array( 'parent' => $parent );
        foreach ( $allowed as $key ) {
            if ( array_key_exists( $key, $field ) ) {
                $out[ $key ] = $safe_value( $field[ $key ] );
            }
        }
        $out['required'] = ! empty( $field['required'] );
        $children = array();
        foreach ( array( 'sub_fields', 'layouts' ) as $child_key ) {
            if ( empty( $field[ $child_key ] ) || ! is_array( $field[ $child_key ] ) ) {
                continue;
            }
            if ( 'layouts' === $child_key ) {
                foreach ( $field['layouts'] as $layout ) {
                    $layout_out = array( 'name' => $layout['name'] ?? null, 'label' => $layout['label'] ?? null, 'sub_fields' => array() );
                    foreach ( (array) ( $layout['sub_fields'] ?? array() ) as $child ) {
                        $layout_out['sub_fields'][] = $field_summary( $child, $field['key'] ?? $parent );
                    }
                    $children['layouts'][] = $layout_out;
                }
            } else {
                foreach ( $field['sub_fields'] as $child ) {
                    $children['sub_fields'][] = $field_summary( $child, $field['key'] ?? $parent );
                }
            }
        }
        if ( $children ) {
            $out['children'] = $children;
        }
        return $out;
    };

    require_once ABSPATH . 'wp-admin/includes/plugin.php';
    $plugins = array();
    foreach ( get_plugins() as $file => $plugin ) {
        $plugins[] = array(
            'file'    => $file,
            'name'    => wp_strip_all_tags( (string) $plugin['Name'] ),
            'version' => (string) $plugin['Version'],
            'active'  => is_plugin_active( $file ),
        );
    }

    $themes = array();
    foreach ( wp_get_themes() as $stylesheet => $theme ) {
        $themes[] = array(
            'stylesheet' => $stylesheet,
            'name'       => $theme->get( 'Name' ),
            'version'    => $theme->get( 'Version' ),
            'template'   => $theme->get_template(),
            'root'       => $theme->get_theme_root(),
        );
    }

    $post_types = array();
    foreach ( get_post_types( array(), 'objects' ) as $name => $type ) {
        if ( ! $type->public && ! $type->show_ui ) {
            continue;
        }
        $post_types[ $name ] = array(
            'label'        => $type->label,
            'public'       => (bool) $type->public,
            'show_ui'      => (bool) $type->show_ui,
            'show_in_rest' => (bool) $type->show_in_rest,
            'rewrite'      => is_array( $type->rewrite ) ? ( $type->rewrite['slug'] ?? null ) : $type->rewrite,
            'has_archive'  => $type->has_archive,
            'supports'     => get_all_post_type_supports( $name ),
            'count'        => (int) ( wp_count_posts( $name )->publish ?? 0 ),
            'taxonomies'   => get_object_taxonomies( $name ),
        );
    }

    $vehicle_taxonomies = array();
    foreach ( get_object_taxonomies( 'cars', 'objects' ) as $name => $taxonomy ) {
        $terms = get_terms( array( 'taxonomy' => $name, 'hide_empty' => false ) );
        $vehicle_taxonomies[ $name ] = array(
            'label'        => $taxonomy->label,
            'hierarchical' => (bool) $taxonomy->hierarchical,
            'public'       => (bool) $taxonomy->public,
            'rewrite'      => is_array( $taxonomy->rewrite ) ? ( $taxonomy->rewrite['slug'] ?? null ) : $taxonomy->rewrite,
            'object_types' => $taxonomy->object_type,
            'terms'        => array_map( static function ( $term ) {
                return array( 'id' => (int) $term->term_id, 'name' => $term->name, 'slug' => $term->slug, 'count' => (int) $term->count );
            }, is_wp_error( $terms ) ? array() : $terms ),
        );
    }

    $acf_groups = array();
    if ( function_exists( 'acf_get_field_groups' ) && function_exists( 'acf_get_fields' ) ) {
        foreach ( acf_get_field_groups() as $group ) {
            $fields = array();
            foreach ( (array) acf_get_fields( $group ) as $field ) {
                $fields[] = $field_summary( $field );
            }
            $acf_groups[] = array(
                'key'        => $group['key'] ?? null,
                'title'      => $group['title'] ?? null,
                'active'     => ! empty( $group['active'] ),
                'location'   => $safe_value( $group['location'] ?? array() ),
                'menu_order' => $group['menu_order'] ?? null,
                'position'   => $group['position'] ?? null,
                'style'      => $group['style'] ?? null,
                'origin'     => ! empty( $group['local'] ) ? 'PHP/local JSON' : 'database or runtime',
                'fields'     => $fields,
            );
        }
    }

    $wpml_languages = apply_filters( 'wpml_active_languages', null, array( 'skip_missing' => 0, 'orderby' => 'code' ) );
    $wpml_settings = get_option( 'icl_sitepress_settings', array() );
    $wpml = array(
        'available'        => defined( 'ICL_SITEPRESS_VERSION' ),
        'default_language' => apply_filters( 'wpml_default_language', null ),
        'languages'        => array(),
        'url_mode'         => is_array( $wpml_settings ) ? ( $wpml_settings['language_negotiation_type'] ?? null ) : null,
        'browser_redirect' => is_array( $wpml_settings ) ? ! empty( $wpml_settings['automatic_redirect'] ) : null,
        'post_types'       => is_array( $wpml_settings ) ? $safe_value( $wpml_settings['custom_posts_sync_option'] ?? array() ) : array(),
        'taxonomies'       => is_array( $wpml_settings ) ? $safe_value( $wpml_settings['taxonomies_sync_option'] ?? array() ) : array(),
        'custom_fields'    => is_array( $wpml_settings ) ? $safe_value( $wpml_settings['custom_fields_translation'] ?? array() ) : array(),
    );
    foreach ( is_array( $wpml_languages ) ? $wpml_languages : array() as $code => $language ) {
        $wpml['languages'][] = array( 'code' => $code, 'native_name' => $language['native_name'] ?? null, 'default_locale' => $language['default_locale'] ?? null, 'url' => $language['url'] ?? null, 'active' => ! empty( $language['active'] ) );
    }

    $locations = get_nav_menu_locations();
    $menus = array();
    foreach ( get_registered_nav_menus() as $location => $label ) {
        $menu_id = isset( $locations[ $location ] ) ? (int) $locations[ $location ] : 0;
        $items = $menu_id ? wp_get_nav_menu_items( $menu_id ) : array();
        $menus[] = array(
            'location' => $location,
            'label'    => $label,
            'menu_id'  => $menu_id ?: null,
            'items'    => array_map( static function ( $item ) use ( $language_for_post ) {
                return array( 'id' => (int) $item->ID, 'title' => wp_strip_all_tags( $item->title ), 'url' => $item->url, 'parent' => (int) $item->menu_item_parent, 'language' => $language_for_post( (int) $item->ID ) );
            }, is_array( $items ) ? $items : array() ),
        );
    }

    global $wp_registered_sidebars;
    $sidebars = array();
    $sidebar_widgets = wp_get_sidebars_widgets();
    foreach ( (array) $wp_registered_sidebars as $id => $sidebar ) {
        $sidebars[] = array( 'id' => $id, 'name' => $sidebar['name'], 'widgets' => array_values( $sidebar_widgets[ $id ] ?? array() ) );
    }

    $forms = array();
    foreach ( array( 'wpcf7_contact_form', 'wpforms' ) as $form_type ) {
        foreach ( get_posts( array( 'post_type' => $form_type, 'post_status' => 'any', 'posts_per_page' => -1, 'fields' => 'ids' ) ) as $form_id ) {
            $content = (string) get_post_field( 'post_content', $form_id );
            $forms[] = array(
                'id'                    => (int) $form_id,
                'type'                  => $form_type,
                'title'                 => wp_strip_all_tags( get_the_title( $form_id ) ),
                'shortcode'             => 'wpcf7_contact_form' === $form_type ? '[contact-form-7 id="' . (int) $form_id . '"]' : '[wpforms id="' . (int) $form_id . '"]',
                'known_business_recipient_present' => false !== strpos( strtolower( $content ), 'info@rentacarvenezia.it' ),
            );
        }
    }

    $hard_coded_ids = array( 6, 20, 23, 122, 135 );
    $report = array(
        'generated_at' => gmdate( 'c' ),
        'method'       => 'temporary localhost-only MU-plugin read-only runtime audit',
        'site'         => array(
            'home'                => home_url( '/' ),
            'siteurl'             => site_url( '/' ),
            'permalink_structure' => get_option( 'permalink_structure' ),
            'category_base'       => get_option( 'category_base' ),
            'tag_base'            => get_option( 'tag_base' ),
            'show_on_front'       => get_option( 'show_on_front' ),
            'page_on_front'       => (int) get_option( 'page_on_front' ),
            'page_for_posts'      => (int) get_option( 'page_for_posts' ),
            'timezone_string'     => get_option( 'timezone_string' ),
            'gmt_offset'          => get_option( 'gmt_offset' ),
        ),
        'active_theme' => array(
            'template'   => get_template(),
            'stylesheet' => get_stylesheet(),
            'name'       => wp_get_theme()->get( 'Name' ),
            'version'    => wp_get_theme()->get( 'Version' ),
            'parent'     => wp_get_theme()->parent() ? wp_get_theme()->parent()->get_stylesheet() : null,
            'root'       => get_theme_root(),
        ),
        'themes'              => $themes,
        'plugins'             => $plugins,
        'mu_plugins'          => get_mu_plugins(),
        'post_types'          => $post_types,
        'vehicle_taxonomies'  => $vehicle_taxonomies,
        'vehicle_examples'    => array_map( $post_summary, get_posts( array( 'post_type' => 'cars', 'post_status' => 'publish', 'posts_per_page' => 5, 'fields' => 'ids' ) ) ),
        'acf_groups'          => $acf_groups,
        'wpml'                => $wpml,
        'pages'               => array( 'front' => $post_summary( (int) get_option( 'page_on_front' ) ), 'posts' => $post_summary( (int) get_option( 'page_for_posts' ) ) ),
        'hard_coded_ids'      => array_map( $post_summary, $hard_coded_ids ),
        'menus'               => $menus,
        'sidebars'            => $sidebars,
        'forms'               => $forms,
    );

    $destination = '/Users/dvdrb/Projects/rentacar-venezia/docs/generated/local-wordpress-audit.json';
    $json = wp_json_encode( $report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
    if ( false === $json || false === file_put_contents( $destination, $json . PHP_EOL, LOCK_EX ) ) {
        status_header( 500 );
        echo 'audit-write-failed';
        exit;
    }

    header( 'Content-Type: application/json; charset=utf-8' );
    echo wp_json_encode( array( 'status' => 'ok' ) );
    exit;
}, PHP_INT_MAX );
