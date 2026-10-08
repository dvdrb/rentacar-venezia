<?php
defined( 'ABSPATH' ) || exit;

/** Creates new Polylang vehicle families once; subsequent runs inspect them. */
final class Rentacar_Core_Fleet_Provision {
    const KEY_META = '_rentacar_vehicle_provisioning_key';
    const IMAGE_KEY_META = '_rentacar_vehicle_provisioning_image_key';
    const LANGUAGES = array( 'it', 'en', 'ro', 'ru' );

    /** WordPress rechecks global slug uniqueness on ordinary editor saves. */
    public static function preserve_provisioned_slug( $override, $slug, $post_id, $status, $type, $parent ) {
        if ( 'cars' !== $type || ! $post_id || $override ) return $override;
        $key = get_post_meta( $post_id, self::KEY_META, true );
        if ( $key && $slug === $key && Rentacar_Core_Fleet_Migration::slug_is_available_in_vehicle_language( $post_id, $slug ) ) return $slug;
        return $override;
    }

    public static function run( $args, $assoc_args ) {
        $apply = isset( $assoc_args['apply'] );
        $publish = isset( $assoc_args['publish'] );
        if ( $apply && $publish ) WP_CLI::error( '--apply and --publish are separate operations.' );
        $path = $assoc_args['manifest'] ?? '';
        $images = $assoc_args['images'] ?? '';
        if ( ! is_string( $path ) || ! is_file( $path ) || ! is_readable( $path ) || ! self::absolute_path( $path ) ) WP_CLI::error( 'A readable absolute --manifest path is required.' );
        if ( ! is_string( $images ) || ! is_dir( $images ) || ! self::absolute_path( $images ) ) WP_CLI::error( 'An absolute --images directory is required.' );
        if ( ! post_type_exists( 'cars' ) || ! function_exists( 'pll_set_post_language' ) || ! function_exists( 'pll_save_post_translations' ) || ! function_exists( 'pll_get_post_translations' ) || ! function_exists( 'pll_default_language' ) ) WP_CLI::error( 'The cars post type and Polylang APIs are required.' );

        $manifest = self::parse_manifest( file_get_contents( $path ) );
        if ( is_wp_error( $manifest ) ) WP_CLI::error( $manifest->get_error_message() );
        $default = pll_default_language( 'slug' );
        WP_CLI::log( 'Polylang default language: ' . $default );
        if ( ! in_array( $default, self::LANGUAGES, true ) ) WP_CLI::error( 'The default language must be one of it, en, ro, ru.' );

        $plans = array(); $image_hashes = array();
        foreach ( $manifest['vehicles'] as $vehicle ) {
            $plan = self::plan_family( $vehicle, $images );
            if ( is_wp_error( $plan ) ) WP_CLI::error( $vehicle['key'] . ': ' . $plan->get_error_message() );
            if ( isset( $image_hashes[ $plan['image']['hash'] ] ) ) WP_CLI::error( 'Two families have identical image content: ' . $image_hashes[ $plan['image']['hash'] ] . ' and ' . $vehicle['key'] );
            $image_hashes[ $plan['image']['hash'] ] = $vehicle['key'];
            if ( 'EXISTING' === $plan['state'] ) {
                $check = self::publication_check( $plan );
                if ( is_wp_error( $check ) ) WP_CLI::error( $vehicle['key'] . ': ' . $check->get_error_message() );
            }
            $plans[] = $plan;
            WP_CLI::log( sprintf( '[%s] %s — %s; image %s (%s)', $plan['state'], $vehicle['key'], implode( ', ', array_map( static function( $language ) use ( $plan ) { return strtoupper( $language ) . ':' . ( $plan['posts'][ $language ] ?: 'CREATE DRAFT' ); }, self::LANGUAGES ) ), $vehicle['image'], $plan['image']['hash'] ) );
            WP_CLI::log( sprintf( '  technical: %s; %s; %s; %d passengers; %d doors; air conditioning unset', $vehicle['powertrain'], $vehicle['gearbox'], $vehicle['engine'], $vehicle['passengers'], $vehicle['doors'] ) );
            foreach ( self::LANGUAGES as $language ) {
                $copy = $vehicle['locales'][ $language ];
                WP_CLI::log( sprintf( '  %s /%s/ — %s', strtoupper( $language ), $vehicle['slug'], $vehicle['title'] ) );
                WP_CLI::log( '    content: ' . $copy['content'] );
                WP_CLI::log( '    SEO title: ' . $copy['seo_title'] );
                WP_CLI::log( '    SEO description: ' . $copy['seo_description'] );
            }
            WP_CLI::log( sprintf( '  pricing: 3–5 €%s; 6–14 €%s; 15–29 €%s; 30+ €%s', $vehicle['pricing']['price_tier_1_price'], $vehicle['pricing']['price_tier_2_price'], $vehicle['pricing']['price_tier_3_price'], $vehicle['pricing']['price_tier_4_price'] ) );
        }
        if ( ! $apply && ! $publish ) {
            WP_CLI::success( 'DRY RUN: ' . count( $plans ) . ' families, ' . ( count( $plans ) * 4 ) . ' locale records planned; no writes.' );
            return;
        }
        if ( $publish ) {
            foreach ( $plans as $plan ) {
                $check = self::publication_check( $plan );
                if ( is_wp_error( $check ) ) WP_CLI::error( $plan['vehicle']['key'] . ': ' . $check->get_error_message() );
            }
            foreach ( $plans as $plan ) foreach ( $plan['posts'] as $id ) {
                if ( 'publish' !== get_post_status( $id ) ) Rentacar_Core_Fleet_Migration::update_vehicle_post_without_translation_sync( $id, array( 'ID' => $id, 'post_status' => 'publish', 'post_name' => $plan['vehicle']['slug'] ) );
            }
            WP_CLI::success( 'Published only the validated provisioned records: ' . implode( ', ', array_merge( ...array_map( static function( $plan ) { return array_values( $plan['posts'] ); }, $plans ) ) ) );
            return;
        }
        foreach ( $plans as $plan ) {
            if ( 'EXISTING' === $plan['state'] ) continue;
            $ids = self::create_family( $plan );
            if ( is_wp_error( $ids ) ) WP_CLI::error( $plan['vehicle']['key'] . ': ' . $ids->get_error_message() );
            WP_CLI::log( '[CREATED DRAFT] ' . $plan['vehicle']['key'] . ' ' . wp_json_encode( $ids ) );
        }
        WP_CLI::success( 'Apply complete. Newly created posts remain drafts; existing records were not changed.' );
    }

    public static function parse_manifest( $json ) {
        $data = json_decode( (string) $json, true );
        if ( ! is_array( $data ) || array_keys( $data ) !== array( 'vehicles' ) || ! is_array( $data['vehicles'] ) || ! $data['vehicles'] ) return new WP_Error( 'manifest', 'Manifest must contain only a nonempty vehicles array.' );
        $keys = array(); $images = array(); $hashes = array();
        foreach ( $data['vehicles'] as $vehicle ) {
            if ( ! is_array( $vehicle ) || ! self::exact_keys( $vehicle, array( 'key', 'title', 'slug', 'image', 'engine', 'powertrain', 'gearbox', 'passengers', 'doors', 'pricing', 'locales' ) ) ) return new WP_Error( 'manifest', 'Each vehicle must contain exactly the documented fields.' );
            if ( ! is_string( $vehicle['key'] ) || ! preg_match( '/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $vehicle['key'] ) || $vehicle['slug'] !== $vehicle['key'] || ! is_string( $vehicle['title'] ) || '' === trim( $vehicle['title'] ) || trim( $vehicle['title'] ) !== wp_strip_all_tags( $vehicle['title'] ) || false !== strpos( $vehicle['title'], '|' ) ) return new WP_Error( 'manifest', 'Invalid vehicle identity or title.' );
            if ( isset( $keys[ $vehicle['key'] ] ) ) return new WP_Error( 'manifest', 'Duplicate provisioning key.' );
            $keys[ $vehicle['key'] ] = true;
            if ( ! is_string( $vehicle['image'] ) || ! preg_match( '/^[a-z0-9-]+\.webp$/', $vehicle['image'] ) || isset( $images[ $vehicle['image'] ] ) ) return new WP_Error( 'manifest', 'Images must be unique plain WebP filenames.' );
            $images[ $vehicle['image'] ] = true;
            if ( ! in_array( $vehicle['powertrain'], Rentacar_Core_Vehicle_Maintenance::POWERTRAINS, true ) || ! in_array( $vehicle['gearbox'], array( 'Manual', 'Automatic', 'Direct-shift gearbox', 'SMG' ), true ) || ! is_int( $vehicle['passengers'] ) || $vehicle['passengers'] < 1 || $vehicle['passengers'] > 99 || ! is_int( $vehicle['doors'] ) || $vehicle['doors'] < 1 || $vehicle['doors'] > 99 || ! is_string( $vehicle['engine'] ) || trim( $vehicle['engine'] ) !== wp_strip_all_tags( $vehicle['engine'] ) ) return new WP_Error( 'manifest', 'Unsupported technical fields.' );
            if ( ! is_array( $vehicle['pricing'] ) || ! self::exact_keys( $vehicle['pricing'], array( 'price_tier_1_range', 'price_tier_1_price', 'price_tier_2_range', 'price_tier_2_price', 'price_tier_3_range', 'price_tier_3_price', 'price_tier_4_range', 'price_tier_4_price' ) ) ) return new WP_Error( 'manifest', 'Invalid pricing schema.' );
            $pricing = Rentacar_Core_Fleet_Migration::pricing_meta_from_row( $vehicle['pricing'], Rentacar_Core_Rental_Policy::minimum_rental_days() );
            if ( is_wp_error( $pricing ) || ! is_array( $pricing ) ) return new WP_Error( 'manifest', 'Pricing must contain four valid continuous ranges.' );
            if ( ! is_array( $vehicle['locales'] ) || ! self::exact_keys( $vehicle['locales'], self::LANGUAGES ) ) return new WP_Error( 'manifest', 'Exactly IT, EN, RO, and RU locale content is required.' );
            foreach ( self::LANGUAGES as $language ) {
                $copy = $vehicle['locales'][ $language ];
                if ( ! is_array( $copy ) || ! self::exact_keys( $copy, array( 'content', 'seo_title', 'seo_description' ) ) ) return new WP_Error( 'manifest', 'Invalid locale content schema.' );
                foreach ( $copy as $value ) if ( ! is_string( $value ) || '' === trim( $value ) || trim( $value ) !== wp_strip_all_tags( $value ) ) return new WP_Error( 'manifest', 'Locale content and SEO must be nonempty plain text.' );
            }
        }
        return $data;
    }

    private static function exact_keys( array $values, array $keys ) {
        $actual = array_keys( $values ); sort( $actual ); sort( $keys ); return $actual === $keys;
    }
    private static function absolute_path( $path ) { return '/' === substr( $path, 0, 1 ); }

    public static function plan_family( array $vehicle, $images ) {
        $image = Rentacar_Core_Fleet_Migration::inspect_image_source( $images, $vehicle['image'] );
        if ( 'VALID' !== $image['status'] || 'image/webp' !== $image['mime'] ) return new WP_Error( 'image', 'Missing or invalid WebP image: ' . ( $image['reason'] ?? $vehicle['image'] ) );
        $posts = array_fill_keys( self::LANGUAGES, 0 );
        $matches = get_posts( array( 'post_type' => 'cars', 'post_status' => 'any', 'posts_per_page' => -1, 'fields' => 'ids', 'suppress_filters' => true, 'meta_key' => self::KEY_META, 'meta_value' => $vehicle['key'] ) );
        foreach ( $matches as $id ) {
            $language = pll_get_post_language( $id, 'slug' );
            if ( ! isset( $posts[ $language ] ) || $posts[ $language ] ) return new WP_Error( 'identity', 'Duplicate or unknown-language provisioned record.' );
            $posts[ $language ] = (int) $id;
        }
        $existing = count( array_filter( $posts ) );
        if ( $existing && 4 !== $existing ) return new WP_Error( 'partial', 'Partial family found; inspect and repair it manually before rerunning.' );
        if ( ! $existing ) {
            $same_slug = get_posts( array( 'post_type' => 'cars', 'post_status' => 'any', 'posts_per_page' => -1, 'fields' => 'ids', 'suppress_filters' => true, 'name' => $vehicle['slug'] ) );
            foreach ( $same_slug as $id ) if ( in_array( pll_get_post_language( $id, 'slug' ), self::LANGUAGES, true ) ) return new WP_Error( 'slug', 'Requested slug already exists in a target language.' );
        } else {
            $family = array_map( 'intval', (array) pll_get_post_translations( $posts['it'] ) );
            foreach ( self::LANGUAGES as $language ) {
                $id = $posts[ $language ];
                if ( ( $family[ $language ] ?? 0 ) !== $id || get_post_field( 'post_name', $id ) !== $vehicle['slug'] || get_post_type( $id ) !== 'cars' || get_post_meta( $id, self::KEY_META, true ) !== $vehicle['key'] || ! Rentacar_Core_Fleet_Migration::slug_is_available_in_vehicle_language( $id, $vehicle['slug'] ) ) return new WP_Error( 'identity', 'Existing Polylang relation, identity, or exact slug is invalid.' );
            }
        }
        return array( 'state' => $existing ? 'EXISTING' : 'CREATE', 'vehicle' => $vehicle, 'posts' => $posts, 'image' => $image );
    }

    private static function create_family( array $plan ) {
        $vehicle = $plan['vehicle'];
        $attachment = self::import_image( $plan['image'], $vehicle );
        if ( is_wp_error( $attachment ) ) return $attachment;
        $pricing = Rentacar_Core_Fleet_Migration::pricing_meta_from_row( $vehicle['pricing'], Rentacar_Core_Rental_Policy::minimum_rental_days() );
        $ids = array();
        foreach ( self::LANGUAGES as $language ) {
            $copy = $vehicle['locales'][ $language ];
            $id = wp_insert_post( array( 'post_type' => 'cars', 'post_status' => 'draft', 'post_title' => $vehicle['title'], 'post_content' => $copy['content'], 'post_name' => $vehicle['slug'] ), true );
            if ( is_wp_error( $id ) || ! $id ) return new WP_Error( 'insert', 'Post creation failed for ' . $language );
            update_post_meta( $id, self::KEY_META, $vehicle['key'] );
            pll_set_post_language( $id, $language );
            if ( $language !== pll_get_post_language( $id, 'slug' ) ) return new WP_Error( 'language', 'Polylang language did not persist.' );
            if ( ! Rentacar_Core_Fleet_Migration::slug_is_available_in_vehicle_language( $id, $vehicle['slug'] ) ) return new WP_Error( 'slug', 'Exact slug is occupied in ' . $language );
            Rentacar_Core_Fleet_Migration::update_vehicle_post_without_translation_sync( $id, array( 'ID' => $id, 'post_name' => $vehicle['slug'] ) );
            update_post_meta( $id, 'gearbox', $vehicle['gearbox'] );
            update_post_meta( $id, 'max_passagers', $vehicle['passengers'] );
            update_post_meta( $id, 'doors', $vehicle['doors'] );
            update_post_meta( $id, Rentacar_Core_Vehicle_Maintenance::POWERTRAIN_META, $vehicle['powertrain'] );
            update_post_meta( $id, '_rentacar_engine', $vehicle['engine'] );
            foreach ( $pricing as $key => $value ) update_post_meta( $id, $key, $value );
            update_post_meta( $id, 'rank_math_title', $copy['seo_title'] );
            update_post_meta( $id, 'rank_math_description', $copy['seo_description'] );
            if ( ! set_post_thumbnail( $id, $attachment ) ) return new WP_Error( 'image', 'Featured image could not be set.' );
            $result = Rentacar_Core_Vehicle_Maintenance::update_starting_price( $id );
            if ( 'valid' !== $result['status'] ) return new WP_Error( 'pricing', 'Derived starting price is invalid.' );
            $ids[ $language ] = (int) $id;
        }
        pll_save_post_translations( $ids );
        $check = self::publication_check( array( 'vehicle' => $vehicle, 'posts' => $ids, 'image' => $plan['image'] ) );
        return is_wp_error( $check ) ? $check : $ids;
    }

    private static function import_image( array $image, array $vehicle ) {
        $existing = get_posts( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'posts_per_page' => -1, 'fields' => 'ids', 'suppress_filters' => true, 'meta_key' => Rentacar_Core_Fleet_Migration::IMAGE_HASH_META, 'meta_value' => $image['hash'] ) );
        if ( $existing ) {
            if ( 1 !== count( $existing ) || get_post_meta( $existing[0], self::IMAGE_KEY_META, true ) !== $vehicle['key'] ) return new WP_Error( 'image', 'Image hash belongs to another attachment or family.' );
            return (int) $existing[0];
        }
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';
        $filetype = wp_check_filetype_and_ext( $image['path'], $vehicle['image'] );
        if ( ( $filetype['type'] ?? '' ) !== 'image/webp' ) return new WP_Error( 'image', 'WordPress rejected WebP MIME.' );
        $temporary = wp_tempnam( $vehicle['image'] );
        if ( ! $temporary || ! copy( $image['path'], $temporary ) ) return new WP_Error( 'image', 'Could not copy image for media import.' );
        $id = media_handle_sideload( array( 'name' => $vehicle['image'], 'tmp_name' => $temporary, 'error' => 0, 'size' => filesize( $temporary ) ), 0, $vehicle['title'] );
        if ( is_wp_error( $id ) ) return $id;
        update_post_meta( $id, Rentacar_Core_Fleet_Migration::IMAGE_HASH_META, $image['hash'] );
        update_post_meta( $id, self::IMAGE_KEY_META, $vehicle['key'] );
        update_post_meta( $id, '_wp_attachment_image_alt', $vehicle['title'] );
        if ( ! wp_get_attachment_metadata( $id ) ) return new WP_Error( 'image', 'Attachment metadata was not generated.' );
        return (int) $id;
    }

    public static function publication_check( array $plan ) {
        $posts = $plan['posts']; $vehicle = $plan['vehicle'];
        if ( count( array_filter( $posts ) ) !== 4 ) return new WP_Error( 'incomplete', 'All four translations are required.' );
        $family = array_map( 'intval', (array) pll_get_post_translations( $posts['it'] ) );
        $attachment = 0;
        foreach ( self::LANGUAGES as $language ) {
            $id = $posts[ $language ];
            $post = get_post( $id );
            if ( ! $post || 'cars' !== $post->post_type || $family[ $language ] !== $id || pll_get_post_language( $id, 'slug' ) !== $language || get_post_meta( $id, self::KEY_META, true ) !== $vehicle['key'] || $post->post_name !== $vehicle['slug'] || ! Rentacar_Core_Fleet_Migration::slug_is_available_in_vehicle_language( $id, $vehicle['slug'] ) ) return new WP_Error( 'identity', 'Invalid relation, ownership, or slug for ' . $language );
            $image_id = (int) get_post_thumbnail_id( $id );
            if ( ! $image_id || ! wp_attachment_is_image( $image_id ) || ( $attachment && $attachment !== $image_id ) ) return new WP_Error( 'image', 'Missing or inconsistent featured image.' );
            $attachment = $image_id;
            if ( get_post_meta( $image_id, self::IMAGE_KEY_META, true ) !== $vehicle['key'] || get_post_meta( $image_id, Rentacar_Core_Fleet_Migration::IMAGE_HASH_META, true ) !== $plan['image']['hash'] ) return new WP_Error( 'image', 'Featured image identity is invalid.' );
            if ( ! in_array( get_post_meta( $id, Rentacar_Core_Vehicle_Maintenance::POWERTRAIN_META, true ), Rentacar_Core_Vehicle_Maintenance::POWERTRAINS, true ) ) return new WP_Error( 'powertrain', 'Invalid powertrain.' );
            $price = Rentacar_Core_Fleet_Translation_Pricing_Sync::validated_source_pricing( $id );
            if ( is_wp_error( $price ) || 'valid' !== Rentacar_Core_Vehicle_Maintenance::starting_price_result( $id )['status'] || (string) $price[ Rentacar_Core_Vehicle_Maintenance::STARTING_PRICE_META ] !== (string) get_post_meta( $id, Rentacar_Core_Vehicle_Maintenance::STARTING_PRICE_META, true ) ) return new WP_Error( 'pricing', 'Invalid pricing or derived starting price.' );
            if ( '' === trim( $post->post_content ) || '' === trim( (string) get_post_meta( $id, 'rank_math_title', true ) ) || '' === trim( (string) get_post_meta( $id, 'rank_math_description', true ) ) ) return new WP_Error( 'editorial', 'Missing localized content or Rank Math metadata.' );
        }
        return true;
    }
}
