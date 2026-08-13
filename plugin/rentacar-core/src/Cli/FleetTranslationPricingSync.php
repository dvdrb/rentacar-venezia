<?php
defined( 'ABSPATH' ) || exit;

/** Safely synchronizes only validated pricing from a source language to existing vehicle translations. */
final class Rentacar_Core_Fleet_Translation_Pricing_Sync {
    const STARTING_PRICE_META = '_rentacar_starting_price';
    const TARGET_LANGUAGES = array( 'en', 'ro', 'ru' );
    const OCTAVIA_SOURCE_ID = 2942;

    public static function run( $args, $assoc_args ) {
        $source_language = sanitize_key( $assoc_args['source-language'] ?? 'it' );
        $fields = sanitize_key( $assoc_args['fields'] ?? '' );
        $apply = ! empty( $assoc_args['apply'] );

        if ( '' === $source_language ) {
            WP_CLI::error( 'A valid --source-language is required.' );
        }
        if ( 'pricing' === $fields ) {
            self::run_pricing( $source_language, $apply );
            return;
        }
        if ( 'vehicle-content' === $fields ) {
            self::run_octavia_vehicle_content( $source_language, absint( $assoc_args['post-id'] ?? 0 ), $apply );
            return;
        }
        WP_CLI::error( 'This command requires --fields=pricing or --fields=vehicle-content.' );
    }

    /** Existing generic pricing synchronizer. */
    private static function run_pricing( $source_language, $apply ) {
        if ( ! post_type_exists( 'cars' ) || ! function_exists( 'pll_get_post_language' ) || ! function_exists( 'pll_get_post_translations' ) ) {
            WP_CLI::error( 'The cars post type and Polylang post translation APIs must be available.' );
        }

        if ( $apply ) {
            WP_CLI::warning( 'APPLY MODE: only pricing meta and its derived starting price will be synchronized to existing translations. Take a database backup first.' );
        } else {
            WP_CLI::log( 'DRY RUN: no posts, pricing, translations, media, SEO, or language assignments will be changed. Add --apply to write pricing only.' );
        }

        $summary = array( 'sources' => 0, 'translations' => 0, 'updated' => 0, 'unchanged' => 0, 'missing' => 0, 'warnings' => 0, 'errors' => 0 );
        $had_errors = false;
        foreach ( self::source_posts( $source_language ) as $source_id ) {
            $summary['sources']++;
            try {
                $result = self::synchronize_source( $source_id, $source_language, $apply );
                self::print_result( $result, $apply );
                foreach ( array_keys( $summary ) as $key ) {
                    if ( isset( $result['counts'][ $key ] ) ) {
                        $summary[ $key ] += $result['counts'][ $key ];
                    }
                }
                $had_errors = $had_errors || ! empty( $result['errors'] );
            } catch ( Throwable $exception ) {
                $had_errors = true;
                $summary['errors']++;
                WP_CLI::warning( sprintf( 'Source vehicle %d failed safely: %s', $source_id, $exception->getMessage() ) );
            }
        }

        WP_CLI::log( "\nFleet translation pricing sync complete\n" );
        foreach ( array(
            'Italian sources scanned' => 'sources', 'Translations scanned' => 'translations',
            $apply ? 'Translations updated' : 'Translations would update' => 'updated',
            'Translations unchanged' => 'unchanged', 'Missing translations' => 'missing',
            'Warnings' => 'warnings', 'Errors' => 'errors',
        ) as $label => $key ) {
            WP_CLI::log( sprintf( '%-30s %d', $label . ':', $summary[ $key ] ) );
        }

        if ( $had_errors ) {
            WP_CLI::halt( 1 );
        }
    }

    /**
     * Repairs only the historical Ford Focus editorial identity on the Octavia
     * translation group. It intentionally does not synchronize technical data.
     */
    private static function run_octavia_vehicle_content( $source_language, $post_id, $apply ) {
        if ( 'it' !== $source_language || self::OCTAVIA_SOURCE_ID !== $post_id ) {
            WP_CLI::error( '--fields=vehicle-content is intentionally limited to --source-language=it --post-id=2942.' );
        }
        if ( ! post_type_exists( 'cars' ) || ! function_exists( 'pll_get_post_language' ) || ! function_exists( 'pll_get_post_translations' ) ) {
            WP_CLI::error( 'The cars post type and Polylang post translation APIs must be available.' );
        }

        if ( $apply ) {
            WP_CLI::warning( 'APPLY MODE: only the approved Octavia EN/RO/RU post title, slug, editorial content, and Rank Math metadata will be updated. Take a database backup first.' );
        } else {
            WP_CLI::log( 'DRY RUN: no posts, pricing, media, translations, technical vehicle fields, or redirects will be changed. Add --apply to write approved Octavia translation content only.' );
        }

        $result = self::synchronize_octavia_vehicle_content( $apply );
        self::print_vehicle_content_result( $result );
        WP_CLI::log( "\nFleet translation vehicle-content sync complete\n" );
        foreach ( array(
            'Italian sources scanned' => 'sources', 'Translations scanned' => 'translations',
            $apply ? 'Translations updated' : 'Translations would update' => 'updated',
            'Translations unchanged' => 'unchanged', 'Redirects ' . ( $apply ? 'created' : 'to create' ) => 'redirects',
            'Warnings' => 'warnings', 'Errors' => 'errors',
        ) as $label => $key ) {
            WP_CLI::log( sprintf( '%-30s %d', $label . ':', $result['counts'][ $key ] ) );
        }
        if ( $result['errors'] ) {
            WP_CLI::halt( 1 );
        }
    }

    /** Returns the ten persisted pricing keys; tier four starts after tier three implicitly. */
    public static function pricing_meta_keys() {
        return array( 'price_1_days_1', 'price_1_days_2', 'price', 'price_2_days_1', 'price_2_days_2', 'price2', 'price_3_days_1', 'price_3_days_2', 'price3', 'price4' );
    }

    /** Reads and validates the source with the same four-tier validator as the fleet CSV migration. */
    public static function validated_source_pricing( $post_id ) {
        $post_id = absint( $post_id );
        if ( ! $post_id || 'cars' !== get_post_type( $post_id ) ) {
            return new WP_Error( 'invalid_source', 'the source must be an existing cars post' );
        }

        $tier_3_end = (string) get_post_meta( $post_id, 'price_3_days_2', true );
        $row = array(
            'price_tier_1_range' => self::closed_range( $post_id, 'price_1_days_1', 'price_1_days_2' ),
            'price_tier_1_price' => get_post_meta( $post_id, 'price', true ),
            'price_tier_2_range' => self::closed_range( $post_id, 'price_2_days_1', 'price_2_days_2' ),
            'price_tier_2_price' => get_post_meta( $post_id, 'price2', true ),
            'price_tier_3_range' => self::closed_range( $post_id, 'price_3_days_1', 'price_3_days_2' ),
            'price_tier_3_price' => get_post_meta( $post_id, 'price3', true ),
            'price_tier_4_range' => is_numeric( $tier_3_end ) ? ( (int) $tier_3_end + 1 ) . '+' : '',
            'price_tier_4_price' => get_post_meta( $post_id, 'price4', true ),
        );
        $pricing = Rentacar_Core_Fleet_Migration::pricing_meta_from_row( $row, self::minimum_rental_days() );
        if ( is_wp_error( $pricing ) ) {
            return new WP_Error( 'invalid_source_pricing', $pricing->get_error_message() );
        }
        if ( ! is_array( $pricing ) ) {
            return new WP_Error( 'invalid_source_pricing', 'the source does not contain all four pricing tiers' );
        }
        $pricing[ self::STARTING_PRICE_META ] = self::derived_starting_price( $pricing );
        return $pricing;
    }

    /** Compares only the ten persisted tier fields. The derived price follows a tier correction. */
    public static function pricing_changes( $target_id, array $source_pricing ) {
        $changes = array();
        foreach ( self::pricing_meta_keys() as $key ) {
            $old = (string) get_post_meta( $target_id, $key, true );
            $new = (string) $source_pricing[ $key ];
            if ( $old !== $new ) {
                $changes[ $key ] = array( 'old' => $old, 'new' => $new );
            }
        }
        return $changes;
    }

    /** Returns the exact persisted pricing values without inspecting unrelated vehicle fields. */
    public static function pricing_values( $post_id ) {
        $values = array();
        foreach ( array_merge( self::pricing_meta_keys(), array( self::STARTING_PRICE_META ) ) as $key ) {
            $values[ $key ] = (string) get_post_meta( $post_id, $key, true );
        }
        return $values;
    }

    /** Applies only price-tier meta, refreshes the derived price, and verifies every persisted value. */
    public static function apply_pricing( $target_id, array $source_pricing ) {
        foreach ( self::pricing_meta_keys() as $key ) {
            update_post_meta( $target_id, $key, $source_pricing[ $key ] );
        }
        if ( class_exists( 'Rentacar_Core_Vehicle_Maintenance' ) ) {
            $result = Rentacar_Core_Vehicle_Maintenance::update_starting_price( $target_id );
            if ( 'valid' !== $result['status'] ) {
                return new WP_Error( 'starting_price_invalid', 'the translated pricing did not produce a valid derived starting price' );
            }
        } else {
            update_post_meta( $target_id, self::STARTING_PRICE_META, $source_pricing[ self::STARTING_PRICE_META ] );
        }

        if ( self::pricing_changes( $target_id, $source_pricing ) || (string) get_post_meta( $target_id, self::STARTING_PRICE_META, true ) !== (string) $source_pricing[ self::STARTING_PRICE_META ] ) {
            return new WP_Error( 'pricing_verification_failed', 'saved pricing does not match the validated Italian source' );
        }
        return true;
    }

    /** Processes one existing source translation map without changing Polylang data. */
    public static function synchronize_source( $source_id, $source_language, $apply ) {
        $source = get_post( $source_id );
        $result = array(
            'source' => $source, 'source_language' => $source_language, 'targets' => array(), 'errors' => array(),
            'counts' => array( 'translations' => 0, 'updated' => 0, 'unchanged' => 0, 'missing' => 0, 'warnings' => 0, 'errors' => 0 ),
        );
        $pricing = self::validated_source_pricing( $source_id );
        if ( is_wp_error( $pricing ) ) {
            $result['errors'][] = sprintf( '%s (post %d) — source pricing is invalid: %s.', $source ? $source->post_title : 'Source vehicle', $source_id, $pricing->get_error_message() );
            $result['counts']['errors']++;
            return $result;
        }

        $translations = (array) pll_get_post_translations( $source_id );
        foreach ( self::target_languages( $source_language ) as $target_language ) {
            $target_id = absint( $translations[ $target_language ] ?? 0 );
            if ( ! $target_id ) {
                $result['counts']['missing']++;
                continue;
            }
            $target = get_post( $target_id );
            if ( ! $target || 'cars' !== $target->post_type || $target_language !== pll_get_post_language( $target_id, 'slug' ) ) {
                $result['errors'][] = sprintf( '%s — %s translation post %d is not a valid cars translation.', $source->post_title, strtoupper( $target_language ), $target_id );
                $result['counts']['errors']++;
                continue;
            }

            $result['counts']['translations']++;
            $old_pricing = self::pricing_values( $target_id );
            $changes = self::pricing_changes( $target_id, $pricing );
            if ( $changes && $old_pricing[ self::STARTING_PRICE_META ] !== $pricing[ self::STARTING_PRICE_META ] ) {
                $changes[ self::STARTING_PRICE_META ] = array( 'old' => $old_pricing[ self::STARTING_PRICE_META ], 'new' => $pricing[ self::STARTING_PRICE_META ] );
            }
            $target_result = array( 'language' => $target_language, 'post' => $target, 'changes' => $changes, 'old_pricing' => $old_pricing, 'new_pricing' => $pricing, 'status' => $changes ? 'UPDATE' : 'UNCHANGED', 'error' => null );
            if ( $changes && $apply ) {
                $saved = self::apply_pricing( $target_id, $pricing );
                if ( is_wp_error( $saved ) ) {
                    $target_result['status'] = 'ERROR';
                    $target_result['error'] = $saved->get_error_message();
                    $result['errors'][] = sprintf( '%s — %s post %d: %s.', $source->post_title, strtoupper( $target_language ), $target_id, $target_result['error'] );
                    $result['counts']['errors']++;
                }
            }
            if ( 'UPDATE' === $target_result['status'] ) {
                $result['counts']['updated']++;
            } elseif ( 'UNCHANGED' === $target_result['status'] ) {
                $result['counts']['unchanged']++;
            }
            $result['targets'][] = $target_result;
        }
        return $result;
    }

    /** Returns the approved localized editorial identity for the Octavia translation repair. */
    public static function octavia_vehicle_content_profiles() {
        return array(
            'en' => array(
                'title' => 'Škoda Octavia 1.0 e-TEC',
                'slug' => 'skoda-octavia-1-0-etec',
                'content' => '<p>The Škoda Octavia 1.0 e-TEC is a hybrid rental car with a DSG dual-clutch gearbox and five seats. It is a comfortable choice for travel in Venice, Treviso, and the surrounding area. Daily prices start from €80; availability and the final price are confirmed by our team.</p>',
                'seo_title' => 'Škoda Octavia 1.0 e-TEC rental in Venice and Treviso | G&D',
                'seo_description' => 'Rent a Škoda Octavia 1.0 e-TEC in Venice or Treviso. Hybrid, DSG dual-clutch gearbox, 5 seats. From €80/day. Request availability from G&D Rent A Car.',
            ),
            'ro' => array(
                'title' => 'Škoda Octavia 1.0 e-TEC',
                'slug' => 'skoda-octavia-1-0-etec',
                'content' => '<p>Škoda Octavia 1.0 e-TEC este o mașină hibridă de închiriat, cu cutie DSG cu dublu ambreiaj și 5 locuri. Este o alegere confortabilă pentru călătorii în Veneția, Treviso și împrejurimi. Tarifele zilnice pornesc de la 80 €; disponibilitatea și prețul final sunt confirmate de echipa noastră.</p>',
                'seo_title' => 'Închiriere Škoda Octavia 1.0 e-TEC în Veneția și Treviso | G&D',
                'seo_description' => 'Închiriază o Škoda Octavia 1.0 e-TEC în Veneția sau Treviso. Hibridă, cutie DSG cu dublu ambreiaj, 5 locuri. De la 80 €/zi. Cere disponibilitate la G&D Rent A Car.',
            ),
            'ru' => array(
                'title' => 'Škoda Octavia 1.0 e-TEC',
                'slug' => 'skoda-octavia-1-0-etec',
                'content' => '<p>Škoda Octavia 1.0 e-TEC — гибридный автомобиль в аренду с коробкой DSG с двойным сцеплением и 5 местами. Это комфортный вариант для поездок по Венеции, Тревизо и окрестностям. Стоимость аренды — от 80 € в день; наличие и окончательную цену подтверждает наша команда.</p>',
                'seo_title' => 'Аренда Škoda Octavia 1.0 e-TEC в Венеции и Тревизо | G&D',
                'seo_description' => 'Арендуйте Škoda Octavia 1.0 e-TEC в Венеции или Тревизо. Гибрид, коробка DSG с двойным сцеплением, 5 мест. От 80 €/день. Запросите наличие в G&D Rent A Car.',
            ),
        );
    }

    /** Plans or applies the one approved Octavia EN/RO/RU editorial content repair. */
    public static function synchronize_octavia_vehicle_content( $apply ) {
        $source = get_post( self::OCTAVIA_SOURCE_ID );
        $result = array(
            'source' => $source, 'targets' => array(), 'errors' => array(),
            'counts' => array( 'sources' => 1, 'translations' => 0, 'updated' => 0, 'unchanged' => 0, 'redirects' => 0, 'warnings' => 0, 'errors' => 0 ),
        );
        if ( ! $source || 'cars' !== $source->post_type || 'it' !== pll_get_post_language( $source->ID, 'slug' ) || 'Škoda Octavia 1.0 e-TEC' !== $source->post_title ) {
            return self::vehicle_content_error( $result, 'Italian post 2942 is not the expected Škoda Octavia source vehicle.' );
        }
        $translations = (array) pll_get_post_translations( $source->ID );
        $expected = array( 'it' => 2942, 'en' => 4383, 'ro' => 4384, 'ru' => 4385 );
        if ( $expected !== $translations ) {
            return self::vehicle_content_error( $result, 'The Octavia Polylang translation map does not match the approved IT/EN/RO/RU family.' );
        }

        foreach ( self::octavia_vehicle_content_profiles() as $language => $profile ) {
            $target_id = absint( $translations[ $language ] );
            $target = get_post( $target_id );
            if ( ! $target || 'cars' !== $target->post_type || $language !== pll_get_post_language( $target_id, 'slug' ) ) {
                self::vehicle_content_error( $result, sprintf( '%s translation post %d is not a valid cars translation.', strtoupper( $language ), $target_id ) );
                continue;
            }
            $result['counts']['translations']++;
            $target_result = self::octavia_target_result( $target, $language, $profile );
            if ( is_wp_error( $target_result ) ) {
                self::vehicle_content_error( $result, sprintf( '%s post %d: %s.', strtoupper( $language ), $target_id, $target_result->get_error_message() ) );
                continue;
            }
            $redirect_recovery = self::octavia_redirect_is_missing( $target, $profile );
            if ( $redirect_recovery && ! isset( $target_result['changes']['slug'] ) ) {
                $target_result['redirect_source_permalink'] = self::translated_permalink_with_slug( get_permalink( $target_id ), 'rent-a-car-ford-focus' );
            }
            if ( ( $target_result['changes'] || $redirect_recovery ) && $apply ) {
                $applied = self::apply_octavia_vehicle_content( $target, $target_result );
                if ( is_wp_error( $applied ) ) {
                    $target_result['status'] = 'ERROR';
                    $target_result['error'] = $applied->get_error_message();
                    self::vehicle_content_error( $result, sprintf( '%s post %d: %s.', strtoupper( $language ), $target_id, $target_result['error'] ) );
                } else {
                    $target_result['redirect'] = $applied['redirect'];
                    if ( $target_result['redirect'] ) {
                        $result['counts']['redirects']++;
                    }
                }
            } elseif ( ( isset( $target_result['changes']['slug'] ) || $redirect_recovery ) ) {
                $old_redirect_permalink = $target_result['redirect_source_permalink'] ?? $target_result['old_permalink'];
                $redirect = Rentacar_Core_Fleet_Migration::preview_rank_math_redirect_for_permalinks( $old_redirect_permalink, $target_result['new_permalink'] );
                if ( is_wp_error( $redirect ) ) {
                    $target_result['warnings'][] = $redirect->get_error_message();
                    $result['counts']['warnings']++;
                } elseif ( $redirect ) {
                    $target_result['redirect'] = $redirect;
                    $result['counts']['redirects']++;
                }
            }
            if ( 'ERROR' !== $target_result['status'] ) {
                $result['counts'][ ( $target_result['changes'] || $target_result['redirect'] ) ? 'updated' : 'unchanged' ]++;
            }
            $result['targets'][] = $target_result;
        }
        return $result;
    }

    /** Builds one translation's diff without reading or changing technical, media, or pricing data. */
    private static function octavia_target_result( $target, $language, array $profile ) {
        $changes = array();
        $post_update = array( 'ID' => $target->ID );
        foreach ( array( 'title' => 'post_title', 'content' => 'post_content' ) as $field => $post_key ) {
            $old = (string) $target->$post_key;
            $new = $profile[ $field ];
            if ( $old !== $new ) {
                $changes[ $field ] = array( 'old' => $old, 'new' => $new );
                $post_update[ $post_key ] = $new;
            }
        }
        foreach ( array( 'seo_title' => 'rank_math_title', 'seo_description' => 'rank_math_description' ) as $field => $meta_key ) {
            $old = (string) get_post_meta( $target->ID, $meta_key, true );
            if ( $old !== $profile[ $field ] ) {
                $changes[ $field ] = array( 'old' => $old, 'new' => $profile[ $field ], 'meta_key' => $meta_key );
            }
        }
        $old_permalink = get_permalink( $target->ID );
        $new_permalink = $old_permalink;
        $slug = sanitize_title( $profile['slug'] );
        if ( $slug !== $target->post_name ) {
            if ( ! Rentacar_Core_Fleet_Migration::slug_is_available_in_vehicle_language( $target->ID, $slug ) ) {
                return new WP_Error( 'slug_collision', sprintf( 'requested slug "%s" is already in use by another vehicle in %s.', $slug, strtoupper( $language ) ) );
            }
            $changes['slug'] = array( 'old' => $target->post_name, 'new' => $slug );
            $post_update['post_name'] = $slug;
            $new_permalink = self::translated_permalink_with_slug( $old_permalink, $slug );
            if ( is_wp_error( $new_permalink ) ) {
                return $new_permalink;
            }
        }
        return array(
            'language' => $language, 'post' => $target, 'changes' => $changes, 'post_update' => $post_update,
            'old_permalink' => $old_permalink, 'new_permalink' => $new_permalink, 'redirect' => null,
            'warnings' => array(), 'status' => $changes ? 'UPDATE' : 'UNCHANGED', 'error' => null,
        );
    }

    /** Writes only planned editorial post fields and Rank Math metadata, then verifies all persisted values. */
    private static function apply_octavia_vehicle_content( $target, array $target_result ) {
        $changes = $target_result['changes'];
        if ( count( $target_result['post_update'] ) > 1 ) {
            try {
                Rentacar_Core_Fleet_Migration::update_vehicle_post_without_translation_sync( $target->ID, $target_result['post_update'] );
            } catch ( Throwable $exception ) {
                return new WP_Error( 'post_update_failed', $exception->getMessage() );
            }
        }
        foreach ( $changes as $change ) {
            if ( isset( $change['meta_key'] ) ) {
                update_post_meta( $target->ID, $change['meta_key'], $change['new'] );
            }
        }
        $saved = get_post( $target->ID );
        foreach ( array( 'title' => 'post_title', 'content' => 'post_content' ) as $field => $post_key ) {
            if ( isset( $changes[ $field ] ) && (string) $saved->$post_key !== (string) $changes[ $field ]['new'] ) {
                return new WP_Error( 'post_verification_failed', $field . ' was not saved exactly as planned' );
            }
        }
        foreach ( $changes as $field => $change ) {
            if ( isset( $change['meta_key'] ) && (string) get_post_meta( $target->ID, $change['meta_key'], true ) !== (string) $change['new'] ) {
                return new WP_Error( 'seo_verification_failed', $field . ' was not saved exactly as planned' );
            }
        }
        $redirect = null;
        if ( isset( $changes['slug'] ) || self::octavia_redirect_is_missing( $target, array( 'slug' => $saved->post_name ) ) ) {
            $old_redirect_permalink = $target_result['redirect_source_permalink'] ?? $target_result['old_permalink'];
            $redirect = Rentacar_Core_Fleet_Migration::ensure_rank_math_redirect_for_permalinks( $old_redirect_permalink, get_permalink( $target->ID ), $target->ID );
            if ( is_wp_error( $redirect ) ) {
                return $redirect;
            }
            if ( 'unchanged' === $redirect ) {
                $redirect = null;
            } else {
                $redirect = Rentacar_Core_Fleet_Migration::redirect_preview( $old_redirect_permalink, get_permalink( $target->ID ) );
            }
        }
        return array( 'redirect' => $redirect );
    }

    /** Identifies the recoverable state where content saved but the safe redirect did not. */
    private static function octavia_redirect_is_missing( $target, array $profile ) {
        $expected_slug = sanitize_title( $profile['slug'] ?? '' );
        if ( '' === $expected_slug || $expected_slug !== $target->post_name || ! class_exists( '\RankMath\Redirections\DB' ) ) {
            return false;
        }
        $old_source = self::translated_permalink_with_slug( get_permalink( $target->ID ), 'rent-a-car-ford-focus' );
        if ( is_wp_error( $old_source ) ) {
            return false;
        }
        $source = trim( (string) wp_parse_url( $old_source, PHP_URL_PATH ), '/' );
        return ! \RankMath\Redirections\DB::match_redirections( $source );
    }

    /** Replaces only the final slug segment of an existing localized vehicle permalink. */
    private static function translated_permalink_with_slug( $permalink, $slug ) {
        $parts = wp_parse_url( $permalink );
        if ( ! is_array( $parts ) || empty( $parts['scheme'] ) || empty( $parts['host'] ) || empty( $parts['path'] ) ) {
            return new WP_Error( 'permalink_invalid', 'WordPress did not provide a valid translated vehicle permalink.' );
        }
        $segments = explode( '/', trim( $parts['path'], '/' ) );
        array_pop( $segments );
        $segments[] = $slug;
        return $parts['scheme'] . '://' . $parts['host'] . '/' . implode( '/', $segments ) . '/';
    }

    private static function vehicle_content_error( array &$result, $message ) {
        $result['errors'][] = $message;
        $result['counts']['errors']++;
        return $result;
    }

    private static function source_posts( $language ) {
        $sources = array();
        foreach ( get_posts( array( 'post_type' => 'cars', 'post_status' => 'publish', 'posts_per_page' => -1, 'fields' => 'ids', 'suppress_filters' => true ) ) as $post_id ) {
            if ( $language === pll_get_post_language( $post_id, 'slug' ) ) {
                $sources[] = absint( $post_id );
            }
        }
        sort( $sources, SORT_NUMERIC );
        return $sources;
    }

    private static function target_languages( $source_language ) {
        return array_values( array_diff( self::TARGET_LANGUAGES, array( $source_language ) ) );
    }

    private static function closed_range( $post_id, $from_key, $to_key ) {
        return get_post_meta( $post_id, $from_key, true ) . '-' . get_post_meta( $post_id, $to_key, true );
    }

    private static function derived_starting_price( array $pricing ) {
        $price = min( array_map( 'floatval', array( $pricing['price'], $pricing['price2'], $pricing['price3'], $pricing['price4'] ) ) );
        return rtrim( rtrim( number_format( $price, 2, '.', '' ), '0' ), '.' );
    }

    private static function minimum_rental_days() {
        return class_exists( 'Rentacar_Core_Rental_Policy' ) ? Rentacar_Core_Rental_Policy::minimum_rental_days() : 3;
    }

    private static function print_result( array $result, $apply ) {
        foreach ( $result['targets'] as $target ) {
            if ( ! $target['changes'] && 'ERROR' !== $target['status'] ) {
                continue;
            }
            WP_CLI::log( sprintf( '[%s] %s', $target['status'], $result['source']->post_title ) );
            WP_CLI::log( sprintf( '  source: %s post %d', strtoupper( $result['source_language'] ), $result['source']->ID ) );
            WP_CLI::log( sprintf( '  target: %s post %d', strtoupper( $target['language'] ), $target['post']->ID ) );
            if ( 'ERROR' === $target['status'] ) {
                WP_CLI::warning( '  ' . $target['error'] );
                continue;
            }
            WP_CLI::log( "\npricing:" );
            foreach ( self::tier_diffs( $target['changes'], $target['old_pricing'], $target['new_pricing'] ) as $tier => $values ) {
                WP_CLI::log( sprintf( '  tier %d:', $tier ) );
                WP_CLI::log( sprintf( '    old: %s', $values['old'] ) );
                WP_CLI::log( sprintf( '    new: %s', $values['new'] ) );
            }
            if ( isset( $target['changes'][ self::STARTING_PRICE_META ] ) ) {
                WP_CLI::log( sprintf( '  starting price: %s -> %s', $target['changes'][ self::STARTING_PRICE_META ]['old'], $target['changes'][ self::STARTING_PRICE_META ]['new'] ) );
            }
            WP_CLI::log( '' );
        }
        foreach ( $result['errors'] as $error ) {
            WP_CLI::warning( $error );
        }
    }

    /** Prints the compact per-language diff for the fixed Octavia editorial repair. */
    private static function print_vehicle_content_result( array $result ) {
        foreach ( $result['targets'] as $target ) {
            if ( ! $target['changes'] && ! $target['redirect'] && 'ERROR' !== $target['status'] ) {
                continue;
            }
            WP_CLI::log( sprintf( '[%s] %s post %d', $target['status'], strtoupper( $target['language'] ), $target['post']->ID ) );
            if ( 'ERROR' === $target['status'] ) {
                WP_CLI::warning( '  ' . $target['error'] );
                continue;
            }
            foreach ( array( 'title', 'slug', 'seo_title', 'seo_description' ) as $field ) {
                if ( ! isset( $target['changes'][ $field ] ) ) {
                    continue;
                }
                WP_CLI::log( $field . ':' );
                WP_CLI::log( '  old: ' . $target['changes'][ $field ]['old'] );
                WP_CLI::log( '  new: ' . $target['changes'][ $field ]['new'] );
            }
            if ( isset( $target['changes']['content'] ) ) {
                WP_CLI::log( 'content:' );
                WP_CLI::log( '  old: Ford Focus references removed' );
                WP_CLI::log( '  new: localized Škoda Octavia content applied' );
            }
            if ( $target['redirect'] ) {
                WP_CLI::log( sprintf( "301 redirect:\n  %s\n  -> %s", $target['redirect']['from'], $target['redirect']['to'] ) );
            }
            foreach ( $target['warnings'] as $warning ) {
                WP_CLI::warning( '  ' . $warning );
            }
            WP_CLI::log( '' );
        }
        foreach ( $result['errors'] as $error ) {
            WP_CLI::warning( $error );
        }
    }

    private static function tier_diffs( array $changes, array $old_pricing, array $new_pricing ) {
        $tiers = array();
        $keys = array(
            1 => array( 'price_1_days_1', 'price_1_days_2', 'price' ),
            2 => array( 'price_2_days_1', 'price_2_days_2', 'price2' ),
            3 => array( 'price_3_days_1', 'price_3_days_2', 'price3' ),
            4 => array( 'price_3_days_2', 'price4' ),
        );
        foreach ( $keys as $tier => $tier_keys ) {
            if ( ! array_intersect( $tier_keys, array_keys( $changes ) ) ) {
                continue;
            }
            $old = self::tier_label( $tier, $old_pricing );
            $new = self::tier_label( $tier, $new_pricing );
            $tiers[ $tier ] = array( 'old' => $old, 'new' => $new );
        }
        return $tiers;
    }

    private static function tier_label( $tier, array $pricing ) {
        if ( 1 === $tier ) return $pricing['price_1_days_1'] . '-' . $pricing['price_1_days_2'] . ' €' . $pricing['price'];
        if ( 2 === $tier ) return $pricing['price_2_days_1'] . '-' . $pricing['price_2_days_2'] . ' €' . $pricing['price2'];
        if ( 3 === $tier ) return $pricing['price_3_days_1'] . '-' . $pricing['price_3_days_2'] . ' €' . $pricing['price3'];
        return ( (int) $pricing['price_3_days_2'] + 1 ) . '+ €' . $pricing['price4'];
    }
}
