<?php
defined( 'ABSPATH' ) || exit;

/** One-time, dry-run-first importer for the established cars post type. */
final class Rentacar_Core_Fleet_Migration {
    const IMAGE_HASH_META = '_rentacar_fleet_migration_source_hash';

    public static function run( $args, $assoc_args ) {
        $csv = isset( $assoc_args['csv'] ) ? (string) $assoc_args['csv'] : '';
        $apply = ! empty( $assoc_args['apply'] );
        $images = isset( $assoc_args['images'] ) ? (string) $assoc_args['images'] : '';

        if ( '' === $csv || ! is_readable( $csv ) ) {
            WP_CLI::error( 'A readable --csv=/absolute/path/to/fleet.csv file is required.' );
        }
        if ( '' !== $images && ! is_dir( $images ) ) {
            WP_CLI::error( 'The --images directory does not exist or is not a directory.' );
        }

        try {
            $rows = self::parse_csv_file( $csv );
        } catch ( RuntimeException $exception ) {
            WP_CLI::error( $exception->getMessage() );
        }

        if ( '' !== $images ) {
            $manifest = self::validate_image_manifest( $rows, $images );
            if ( is_wp_error( $manifest ) ) {
                WP_CLI::error( $manifest->get_error_message() );
            }
        }

        if ( ! post_type_exists( 'cars' ) ) {
            WP_CLI::error( 'The cars post type is not registered. Load the site normally and try again.' );
        }

        if ( $apply ) {
            WP_CLI::warning( 'APPLY MODE: existing vehicle posts and media metadata may be updated. Take a database and uploads backup first.' );
        } else {
            WP_CLI::log( 'DRY RUN: no WordPress posts, metadata, media, translations, or redirects will be changed. Add --apply to write changes.' );
        }

        $summary = array(
            'rows' => count( $rows ), 'matched' => 0, 'updated' => 0, 'unchanged' => 0,
            'partial' => 0, 'skipped' => 0, 'images_imported' => 0, 'images_replaced' => 0,
            'images_unchanged' => 0, 'images_missing' => 0, 'images_invalid' => 0,
            'translation_images_updated' => 0, 'redirects_created' => 0, 'warnings' => 0, 'errors' => 0,
        );
        $had_errors = false;

        foreach ( $rows as $number => $row ) {
            try {
                $result = self::process_row( $row, $number + 2, $images, $apply );
                $result['counts']['warnings'] += count( $result['warnings'] );
                if ( $result['skipped'] ) {
                    $result['counts']['skipped']++;
                }
                self::print_result( $result );
                foreach ( array_keys( $summary ) as $key ) {
                    if ( isset( $result['counts'][ $key ] ) ) {
                        $summary[ $key ] += $result['counts'][ $key ];
                    }
                }
                $had_errors = $had_errors || ! empty( $result['errors'] );
            } catch ( Throwable $exception ) {
                $had_errors = true;
                $summary['errors']++;
                WP_CLI::warning( sprintf( 'CSV row %d failed safely: %s', $number + 2, $exception->getMessage() ) );
            }
        }

        WP_CLI::log( "\nFleet migration complete\n" );
        foreach ( array(
            'Rows' => 'rows', 'Matched' => 'matched', $apply ? 'Updated' : 'Would update' => 'updated',
            'Unchanged' => 'unchanged', 'Partially updated' => 'partial', 'Skipped' => 'skipped',
            $apply ? 'Images imported' : 'Images to import' => 'images_imported', $apply ? 'Images replaced' : 'Images to replace' => 'images_replaced',
            'Images unchanged' => 'images_unchanged', 'Images missing' => 'images_missing', 'Images invalid' => 'images_invalid',
            'Translated featured images updated' => 'translation_images_updated', $apply ? 'Redirects created' : 'Redirects to create' => 'redirects_created',
            'Warnings' => 'warnings', 'Errors' => 'errors',
        ) as $label => $key ) {
            WP_CLI::log( sprintf( '%-20s %d', $label . ':', $summary[ $key ] ) );
        }

        if ( $had_errors ) {
            WP_CLI::halt( 1 );
        }
    }

    public static function parse_csv_file( $path ) {
        $handle = fopen( $path, 'r' );
        if ( false === $handle ) {
            throw new RuntimeException( 'Unable to open the CSV file.' );
        }
        $header = fgetcsv( $handle );
        if ( false === $header ) {
            fclose( $handle );
            throw new RuntimeException( 'The CSV file is empty.' );
        }
        $header = array_map( array( __CLASS__, 'normalize_header' ), $header );
        if ( in_array( '', $header, true ) || count( $header ) !== count( array_unique( $header ) ) ) {
            fclose( $handle );
            throw new RuntimeException( 'CSV headers must be present and unique.' );
        }
        if ( ! in_array( 'post_id', $header, true ) && ! in_array( 'current_slug', $header, true ) ) {
            fclose( $handle );
            throw new RuntimeException( 'CSV requires at least one matching column: post_id or current_slug.' );
        }

        $rows = array();
        while ( false !== ( $values = fgetcsv( $handle ) ) ) {
            if ( array( null ) === $values || array() === $values ) {
                continue;
            }
            $values = array_pad( $values, count( $header ), '' );
            if ( count( $values ) > count( $header ) ) {
                fclose( $handle );
                throw new RuntimeException( 'A CSV row has more values than the header.' );
            }
            $row = array();
            foreach ( $header as $index => $key ) {
                $row[ $key ] = isset( $values[ $index ] ) ? trim( (string) $values[ $index ] ) : '';
            }
            if ( array_filter( $row, static function( $value ) { return '' !== $value; } ) ) {
                $rows[] = $row;
            }
        }
        fclose( $handle );
        return $rows;
    }

    public static function parse_closed_range( $value ) {
        $value = trim( (string) $value );
        if ( ! preg_match( '/^(\d+)\s*-\s*(\d+)$/', $value, $matches ) ) {
            return null;
        }
        $from = (int) $matches[1];
        $to = (int) $matches[2];
        return $from > 0 && $to >= $from ? array( $from, $to ) : null;
    }

    public static function parse_open_range( $value ) {
        $value = trim( (string) $value );
        if ( '' === $value ) {
            return null;
        }
        if ( ! preg_match( '/^(\d+)\s*\+$/', $value, $matches ) ) {
            return false;
        }
        return (int) $matches[1] > 0 ? (int) $matches[1] : false;
    }

    public static function is_unconfirmed( $value ) {
        return in_array( strtolower( trim( (string) $value ) ), array( 'unconfirmed', 'unknown', 'tbd', 'n/a', 'na' ), true );
    }

    public static function valid_price( $value ) {
        $value = str_replace( ',', '.', trim( (string) $value ) );
        return '' !== $value && is_numeric( $value ) && is_finite( (float) $value ) && (float) $value > 0;
    }

    /** Builds the legacy four-band meta update only when the whole supplied tariff is valid. */
    public static function pricing_meta_from_row( array $row, $minimum_days = 3 ) {
        $ranges = array();
        $prices = array();
        for ( $tier = 1; $tier <= 3; $tier++ ) {
            $range = self::value( $row, 'price_tier_' . $tier . '_range' );
            $price = self::value( $row, 'price_tier_' . $tier . '_price' );
            if ( '' === $range && '' === $price ) {
                return null;
            }
            if ( self::is_unconfirmed( $range ) || self::is_unconfirmed( $price ) ) {
                return new WP_Error( 'unconfirmed_pricing', 'pricing tiers include an unconfirmed value' );
            }
            $parsed = self::parse_closed_range( $range );
            if ( ! $parsed || ! self::valid_price( $price ) ) {
                return new WP_Error( 'invalid_pricing', 'pricing tiers require valid closed day ranges and positive prices' );
            }
            $ranges[] = $parsed;
            $prices[] = self::normalize_price( $price );
        }
        $fourth = self::value( $row, 'price_tier_4_price' );
        if ( self::is_unconfirmed( $fourth ) ) {
            return new WP_Error( 'unconfirmed_pricing', 'tier 4 price is unconfirmed' );
        }
        if ( ! self::valid_price( $fourth ) ) {
            return new WP_Error( 'invalid_pricing', 'tier 4 requires a positive price' );
        }
        if ( $ranges[0][0] !== (int) $minimum_days ) {
            return new WP_Error( 'invalid_pricing', sprintf( 'the first tier must start at the minimum rental duration (%d days)', $minimum_days ) );
        }
        for ( $index = 1; $index < count( $ranges ); $index++ ) {
            if ( $ranges[ $index ][0] !== $ranges[ $index - 1 ][1] + 1 ) {
                return new WP_Error( 'invalid_pricing', 'pricing tiers have a gap or overlap' );
            }
        }
        $tier_4_range = self::value( $row, 'price_tier_4_range' );
        $expected_tier_4_start = $ranges[2][1] + 1;
        if ( '' !== $tier_4_range && self::parse_open_range( $tier_4_range ) !== $expected_tier_4_start ) {
            return new WP_Error( 'invalid_pricing', sprintf( 'tier 4 must be %d+', $expected_tier_4_start ) );
        }

        return array(
            'price_1_days_1' => (string) $ranges[0][0], 'price_1_days_2' => (string) $ranges[0][1], 'price' => $prices[0],
            'price_2_days_1' => (string) $ranges[1][0], 'price_2_days_2' => (string) $ranges[1][1], 'price2' => $prices[1],
            'price_3_days_1' => (string) $ranges[2][0], 'price_3_days_2' => (string) $ranges[2][1], 'price3' => $prices[2],
            'price4' => self::normalize_price( $fourth ),
        );
    }

    private static function process_row( array $row, $line, $images, $apply ) {
        $result = self::empty_result();
        $post = self::find_vehicle( $row );
        $label = self::value( $row, 'title' ) ?: self::value( $row, 'current_slug' ) ?: 'CSV row ' . $line;
        if ( is_wp_error( $post ) ) {
            $result['skipped'] = true;
            $result['warnings'][] = $post->get_error_message();
            return $result;
        }
        $result['counts']['matched']++;
        $label = $post->post_title;
        $result['label'] = sprintf( 'Post %d — %s', $post->ID, $label );
        $language = self::value( $row, 'language' );
        if ( '' !== $language && function_exists( 'pll_get_post_language' ) && $language !== pll_get_post_language( $post->ID, 'slug' ) ) {
            $result['skipped'] = true;
            $result['warnings'][] = sprintf( '%s — CSV language does not match the existing post language.', $label );
            return $result;
        }

        $changes = array();
        $post_update = array( 'ID' => $post->ID );
        $title = self::value( $row, 'title' );
        if ( self::is_unconfirmed( $title ) ) {
            $result['warnings'][] = sprintf( '%s — title not updated because CSV value is unconfirmed.', $label );
        } elseif ( self::has_generic_title_attribute( $title ) ) {
            $result['warnings'][] = sprintf( '%s — title not updated because generic fuel or transmission text belongs in structured fields.', $label );
        } else {
            self::consider_text( $changes, $post->post_title, $title, 'title', $post_update, 'post_title', $label );
        }
        $requested_slug = self::value( $row, 'new_slug' );
        if ( '' !== $requested_slug ) {
            if ( self::is_unconfirmed( $requested_slug ) ) {
                $result['warnings'][] = sprintf( '%s — slug not updated because CSV value is unconfirmed.', $label );
            } elseif ( ! self::rank_math_redirects_available() ) {
                $result['warnings'][] = sprintf( '%s — slug not updated because the Rank Math Redirections module is unavailable.', $label );
            } else {
                $slug = sanitize_title( $requested_slug );
                if ( '' === $slug ) {
                    $result['warnings'][] = sprintf( '%s — slug is invalid and was skipped.', $label );
                } elseif ( ! self::slug_is_available_in_vehicle_language( $post->ID, $slug ) ) {
                    $result['warnings'][] = sprintf( '%s — slug "%s" is already in use by a different vehicle in the same language and was skipped.', $label, $slug );
                } elseif ( $slug !== $post->post_name ) {
                    $changes['slug'] = array( 'old' => $post->post_name, 'new' => $slug );
                    $post_update['post_name'] = $slug;
                }
            }
        }

        $meta = self::vehicle_meta_from_row( $row, $label, $result['warnings'] );
        foreach ( $meta as $key => $value ) {
            $current = get_post_meta( $post->ID, $key, true );
            if ( (string) $current !== (string) $value ) {
                $changes[ self::display_key( $key ) ] = array( 'old' => $current, 'new' => $value, 'meta_key' => $key );
            }
        }

        $pricing = self::pricing_meta_from_row( $row, self::minimum_rental_days() );
        if ( is_wp_error( $pricing ) ) {
            $result['warnings'][] = sprintf( '%s — pricing not updated: %s.', $label, $pricing->get_error_message() );
        } elseif ( is_array( $pricing ) ) {
            foreach ( $pricing as $key => $value ) {
                $current = get_post_meta( $post->ID, $key, true );
                if ( (string) $current !== (string) $value ) {
                    $changes[ self::display_key( $key ) ] = array( 'old' => $current, 'new' => $value, 'meta_key' => $key );
                }
            }
            self::check_requested_starting_price( $row, $pricing, $label, $result['warnings'] );
        } elseif ( '' !== self::value( $row, 'starting_price' ) ) {
            $result['warnings'][] = sprintf( '%s — starting_price is derived from all four price tiers and cannot be updated alone.', $label );
        }

        self::consider_seo( $post->ID, $row, $changes, $label, $result['warnings'] );
        $image = self::image_plan( $post->ID, $row, $images, $label, $result['warnings'] );
        $result['image'] = $image;
        if ( $image && in_array( $image['status'], array( 'IMPORT', 'REPLACE' ), true ) ) {
            $changes['featured_image'] = array( 'old' => get_post_thumbnail_id( $post->ID ), 'new' => $image['description'], 'image' => $image );
        }

        $image_requires_apply = $image && in_array( $image['status'], array( 'IMPORT', 'REPLACE' ), true );
        if ( ! $apply || ! $image_requires_apply ) {
            self::count_image_plan( $image, $result );
        }

        $result['changes'] = $changes;
        if ( ! $changes ) {
            $result['status'] = 'UNCHANGED';
            $result['counts']['unchanged']++;
            return $result;
        }
        $result['status'] = 'UPDATE';
        if ( ! $apply ) {
            if ( isset( $changes['slug'] ) ) {
                $redirect = self::preview_rank_math_redirect( $changes['slug']['old'], $changes['slug']['new'] );
                if ( is_wp_error( $redirect ) ) {
                    $result['warnings'][] = sprintf( '%s — old URL redirect needs review: %s.', $label, $redirect->get_error_message() );
                } elseif ( $redirect ) {
                    $result['counts']['redirects_created']++;
                    $result['redirect'] = $redirect;
                }
            }
            $result['counts']['updated']++;
            return $result;
        }

        $old_permalink = isset( $changes['slug'] ) ? get_permalink( $post->ID ) : '';
        if ( count( $post_update ) > 1 ) {
            self::update_vehicle_post_without_translation_sync( $post->ID, $post_update );
        }
        foreach ( $changes as $change ) {
            if ( isset( $change['meta_key'] ) ) {
                update_post_meta( $post->ID, $change['meta_key'], $change['new'] );
            }
        }
        if ( $image ) {
            $attachment_id = self::apply_image( $post->ID, $image );
            if ( is_wp_error( $attachment_id ) ) {
                $result['warnings'][] = sprintf( '%s — image not updated: %s.', $label, $attachment_id->get_error_message() );
                unset( $result['changes']['featured_image'] );
            } else {
                $image['attachment_id'] = $attachment_id;
                self::count_image_plan( $image, $result );
                $translation_sync = self::sync_featured_image_translations( $post->ID, $attachment_id, $image['translation_targets'] ?? array() );
                if ( is_wp_error( $translation_sync ) ) {
                    $result['warnings'][] = sprintf( '%s — translated featured images were not fully synchronized: %s.', $label, $translation_sync->get_error_message() );
                } else {
                    $result['counts']['translation_images_updated'] += $translation_sync;
                }
            }
        }
        if ( class_exists( 'Rentacar_Core_Vehicle_Maintenance' ) && is_array( $pricing ) ) {
            Rentacar_Core_Vehicle_Maintenance::update_starting_price( $post->ID );
        }
        if ( isset( $changes['slug'] ) ) {
            $redirect = self::ensure_rank_math_redirect_for_permalinks( $old_permalink, get_permalink( $post->ID ), $post->ID );
            if ( is_wp_error( $redirect ) ) {
                $result['warnings'][] = sprintf( '%s — old URL redirect needs review: %s.', $label, $redirect->get_error_message() );
            } elseif ( 'created' === $redirect ) {
                $result['counts']['redirects_created']++;
                $result['redirect'] = self::redirect_preview( $old_permalink, get_permalink( $post->ID ) );
            }
        }

        $result['counts']['updated']++;
        if ( $result['warnings'] ) {
            $result['status'] = 'PARTIAL';
            $result['counts']['updated']--;
            $result['counts']['partial']++;
        }
        return $result;
    }

    private static function find_vehicle( array $row ) {
        $id = absint( self::value( $row, 'post_id' ) );
        if ( $id ) {
            $post = get_post( $id );
            if ( ! $post || 'cars' !== $post->post_type ) {
                return new WP_Error( 'invalid_vehicle', sprintf( 'Post %d was not found as a cars vehicle; row skipped.', $id ) );
            }
            return $post;
        }
        $slug = self::value( $row, 'current_slug' );
        if ( '' === $slug ) {
            return new WP_Error( 'missing_match', 'Neither post_id nor current_slug was supplied; row skipped.' );
        }
        $args = array( 'post_type' => 'cars', 'post_status' => 'any', 'name' => sanitize_title( $slug ), 'posts_per_page' => 2, 'suppress_filters' => false );
        if ( '' !== self::value( $row, 'language' ) ) {
            $args['lang'] = self::value( $row, 'language' );
        } else {
            $args['suppress_filters'] = true;
        }
        $matches = get_posts( $args );
        if ( 1 !== count( $matches ) ) {
            return new WP_Error( 'ambiguous_vehicle', sprintf( 'Slug "%s" matched %d vehicle posts; supply post_id or language and skip the row.', $slug, count( $matches ) ) );
        }
        return $matches[0];
    }

    public static function vehicle_meta_from_row( array $row, $label, array &$warnings ) {
        $meta = array();
        $fields = array(
            'transmission' => array( 'gearbox', array( 'manual' => 'Manual', 'automatic' => 'Automatic', 'auto' => 'Automatic', 'direct-shift gearbox' => 'Direct-shift gearbox', 'smg' => 'SMG' ) ),
            'seats' => array( 'max_passagers', 'integer' ),
            'doors' => array( 'doors', 'integer' ),
            'air_conditioning' => array( 'air_conditioning', 'boolean' ),
            'fuel' => array( '_rentacar_powertrain', array( 'petrol' => 'petrol', 'gasoline' => 'petrol', 'benzina' => 'petrol', 'diesel' => 'diesel', 'hybrid' => 'hybrid', 'ibrida' => 'hybrid', 'plug-in hybrid' => 'plug_in_hybrid', 'plug in hybrid' => 'plug_in_hybrid', 'phev' => 'plug_in_hybrid', 'electric' => 'electric', 'elettrica' => 'electric', 'other' => 'other' ) ),
            'engine' => array( '_rentacar_engine', 'text' ),
        );
        foreach ( $fields as $column => $definition ) {
            $value = self::value( $row, $column );
            if ( 'fuel' === $column && '' !== self::value( $row, 'powertrain' ) ) {
                $value = self::value( $row, 'powertrain' );
            }
            if ( '' === $value ) {
                continue;
            }
            if ( self::is_unconfirmed( $value ) ) {
                $warnings[] = sprintf( '%s — %s not updated because CSV value is unconfirmed.', $label, $column );
                continue;
            }
            if ( 'integer' === $definition[1] ) {
                if ( ! ctype_digit( $value ) || (int) $value < 1 || (int) $value > 99 ) {
                    $warnings[] = sprintf( '%s — %s must be a whole number from 1 to 99.', $label, $column );
                } else {
                    $meta[ $definition[0] ] = (string) (int) $value;
                }
                continue;
            }
            if ( 'boolean' === $definition[1] ) {
                $boolean = self::boolean_value( $value );
                if ( null === $boolean ) {
                    $warnings[] = sprintf( '%s — air_conditioning must be yes/no or 1/0.', $label );
                } else {
                    $meta[ $definition[0] ] = $boolean ? '1' : '0';
                }
                continue;
            }
            if ( 'text' === $definition[1] ) {
                $value = sanitize_text_field( $value );
                if ( '' !== $value ) {
                    $meta[ $definition[0] ] = $value;
                }
                continue;
            }
            $lookup = array_change_key_case( $definition[1], CASE_LOWER );
            $normalized = strtolower( $value );
            if ( ! isset( $lookup[ $normalized ] ) ) {
                $warnings[] = sprintf( '%s — %s value "%s" is unsupported.', $label, $column, $value );
            } else {
                $meta[ $definition[0] ] = $lookup[ $normalized ];
            }
        }
        return $meta;
    }

    /** Allows matching slugs in other Polylang languages but not within the same language. */
    public static function slug_is_available_in_vehicle_language( $post_id, $slug ) {
        $post_id = absint( $post_id );
        $slug = sanitize_title( $slug );
        if ( ! $post_id || '' === $slug ) {
            return false;
        }
        $language = function_exists( 'pll_get_post_language' ) ? pll_get_post_language( $post_id, 'slug' ) : false;
        $matches = get_posts( array(
            'post_type' => 'cars', 'post_status' => 'any', 'name' => $slug,
            'posts_per_page' => -1, 'suppress_filters' => true,
        ) );
        foreach ( $matches as $match ) {
            if ( $post_id === (int) $match->ID ) {
                continue;
            }
            if ( ! $language || ! function_exists( 'pll_get_post_language' ) || $language === pll_get_post_language( $match->ID, 'slug' ) ) {
                return false;
            }
        }
        return true;
    }

    private static function consider_text( array &$changes, $current, $value, $name, array &$post_update, $post_key, $label ) {
        if ( '' === $value || self::is_unconfirmed( $value ) ) {
            return;
        }
        $value = sanitize_text_field( $value );
        if ( '' !== $value && (string) $current !== $value ) {
            $changes[ $name ] = array( 'old' => $current, 'new' => $value );
            $post_update[ $post_key ] = $value;
        }
    }

    private static function consider_seo( $post_id, array $row, array &$changes, $label, array &$warnings ) {
        if ( ! defined( 'RANK_MATH_VERSION' ) && ! class_exists( '\RankMath\Helper' ) ) {
            return;
        }
        foreach ( array( 'seo_title' => 'rank_math_title', 'seo_description' => 'rank_math_description' ) as $column => $key ) {
            $value = self::value( $row, $column );
            if ( '' === $value ) {
                continue;
            }
            if ( self::is_unconfirmed( $value ) ) {
                $warnings[] = sprintf( '%s — %s not updated because CSV value is unconfirmed.', $label, $column );
                continue;
            }
            $value = 'seo_description' === $column ? sanitize_textarea_field( $value ) : sanitize_text_field( $value );
            $current = get_post_meta( $post_id, $key, true );
            if ( (string) $current !== $value ) {
                $changes[ $column ] = array( 'old' => $current, 'new' => $value, 'meta_key' => $key );
            }
        }
    }

    /** Validates exact CSV filename mappings before any image import is considered. */
    public static function validate_image_manifest( array $rows, $directory ) {
        $filenames = array();
        $hashes = array();
        foreach ( $rows as $index => $row ) {
            $file = self::value( $row, 'image_file' );
            if ( '' === $file || self::is_unconfirmed( $file ) ) {
                return new WP_Error( 'image_manifest_missing_file', sprintf( 'CSV row %d has no confirmed image_file.', $index + 2 ) );
            }
            $filename = wp_basename( $file );
            if ( $filename !== $file ) {
                return new WP_Error( 'image_manifest_filename', sprintf( 'CSV row %d image_file must be a filename without directories.', $index + 2 ) );
            }
            if ( isset( $filenames[ $filename ] ) ) {
                return new WP_Error( 'image_manifest_duplicate_filename', sprintf( 'CSV image_file %s is assigned to more than one vehicle (rows %d and %d).', $filename, $filenames[ $filename ], $index + 2 ) );
            }
            $filenames[ $filename ] = $index + 2;
            $inspection = self::inspect_image_source( $directory, $filename );
            if ( 'VALID' === $inspection['status'] ) {
                if ( isset( $hashes[ $inspection['hash'] ] ) ) {
                    return new WP_Error( 'image_manifest_duplicate_content', sprintf( 'CSV image files %s and %s have identical content; assign a distinct approved image to each vehicle.', $hashes[ $inspection['hash'] ], $filename ) );
                }
                $hashes[ $inspection['hash'] ] = $filename;
            }
        }
        return true;
    }

    /** Inspects an image without importing or touching WordPress media. */
    public static function inspect_image_source( $directory, $filename ) {
        if ( '' === $filename || wp_basename( $filename ) !== $filename ) {
            return array( 'status' => 'INVALID', 'reason' => 'image_file must be a filename without directories' );
        }
        $path = trailingslashit( $directory ) . $filename;
        if ( ! is_file( $path ) || ! is_readable( $path ) ) {
            return array( 'status' => 'MISSING', 'path' => $path, 'filename' => $filename, 'reason' => 'file is not readable' );
        }
        $size = filesize( $path );
        if ( false === $size || $size < 1 ) {
            return array( 'status' => 'INVALID', 'path' => $path, 'filename' => $filename, 'reason' => 'file is empty or unreadable' );
        }
        $dimensions = @getimagesize( $path );
        $mime = function_exists( 'wp_get_image_mime' ) ? wp_get_image_mime( $path ) : ( $dimensions['mime'] ?? '' );
        $extension = strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) );
        $allowed = array( 'webp' => 'image/webp', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg', 'png' => 'image/png' );
        if ( ! is_array( $dimensions ) || empty( $dimensions[0] ) || empty( $dimensions[1] ) || ! isset( $allowed[ $extension ] ) || $allowed[ $extension ] !== $mime ) {
            return array( 'status' => 'INVALID', 'path' => $path, 'filename' => $filename, 'reason' => 'file must be a readable WebP, JPEG, or PNG whose extension matches its real image MIME type' );
        }
        $hash = hash_file( 'sha256', $path );
        if ( ! $hash ) {
            return array( 'status' => 'INVALID', 'path' => $path, 'filename' => $filename, 'reason' => 'could not calculate SHA-256 checksum' );
        }
        return array( 'status' => 'VALID', 'path' => $path, 'filename' => $filename, 'hash' => $hash, 'mime' => $mime, 'width' => (int) $dimensions[0], 'height' => (int) $dimensions[1], 'size' => (int) $size );
    }

    public static function image_plan( $post_id, array $row, $directory, $label, array &$warnings ) {
        $file = self::value( $row, 'image_file' );
        if ( '' === $file ) {
            return null;
        }
        if ( self::is_unconfirmed( $file ) || '' === $directory ) {
            $warnings[] = sprintf( '%s — image not updated because no confirmed --images file is available.', $label );
            return null;
        }
        $inspection = self::inspect_image_source( $directory, $file );
        $current_attachment = (int) get_post_thumbnail_id( $post_id );
        $current_file = $current_attachment && function_exists( 'get_attached_file' ) ? wp_basename( (string) get_attached_file( $current_attachment ) ) : '';
        if ( 'VALID' !== $inspection['status'] ) {
            $warnings[] = sprintf( '%s — image %s %s; existing image preserved.', $label, $file, strtolower( $inspection['reason'] ) );
            return array_merge( $inspection, array( 'status' => $inspection['status'], 'current_attachment' => $current_attachment, 'current_file' => $current_file, 'alt' => self::value( $row, 'image_alt' ), 'description' => $inspection['reason'], 'translation_targets' => array() ) );
        }
        $hash = $inspection['hash'];
        $existing = get_posts( array( 'post_type' => 'attachment', 'post_status' => 'inherit', 'posts_per_page' => 1, 'meta_key' => self::IMAGE_HASH_META, 'meta_value' => $hash, 'fields' => 'ids', 'suppress_filters' => true ) );
        $attachment_id = $existing ? (int) $existing[0] : 0;
        $alt = self::value( $row, 'image_alt' );
        if ( $attachment_id && $attachment_id === $current_attachment ) {
            if ( '' === $alt || self::is_unconfirmed( $alt ) || $alt === get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) ) {
                return array_merge( $inspection, array( 'status' => 'UNCHANGED', 'attachment_id' => $attachment_id, 'current_attachment' => $current_attachment, 'current_file' => $current_file, 'alt' => $alt, 'description' => 'attachment #' . $attachment_id . ' already has this source hash', 'translation_targets' => array() ) );
            }
            return array_merge( $inspection, array( 'status' => 'REPLACE', 'attachment_id' => $attachment_id, 'current_attachment' => $current_attachment, 'current_file' => $current_file, 'alt' => $alt, 'description' => 'update alt text for attachment #' . $attachment_id, 'translation_targets' => array() ) );
        }
        $translation_targets = self::translation_image_targets( $post_id, $current_attachment, $warnings, $label );
        return array_merge( $inspection, array( 'status' => $current_attachment ? 'REPLACE' : 'IMPORT', 'attachment_id' => $attachment_id, 'current_attachment' => $current_attachment, 'current_file' => $current_file, 'alt' => $alt, 'attachment_title' => self::value( $row, 'title' ), 'description' => $attachment_id ? 'set existing attachment #' . $attachment_id . ' as featured image' : 'import ' . $inspection['filename'] . ' and set it as featured image', 'translation_targets' => $translation_targets ) );
    }

    private static function apply_image( $post_id, array $image ) {
        $attachment_id = (int) $image['attachment_id'];
        if ( ! $attachment_id ) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/media.php';
            require_once ABSPATH . 'wp-admin/includes/image.php';
            $filetype = wp_check_filetype_and_ext( $image['path'], $image['filename'] );
            if ( empty( $filetype['type'] ) || 0 !== strpos( $filetype['type'], 'image/' ) ) {
                return new WP_Error( 'invalid_image', 'file is not a supported image type' );
            }
            $temporary = wp_tempnam( $image['filename'] );
            if ( ! $temporary || ! copy( $image['path'], $temporary ) ) {
                return new WP_Error( 'image_copy_failed', 'could not prepare a safe temporary copy for import' );
            }
            $attachment_id = media_handle_sideload( array( 'name' => $image['filename'], 'tmp_name' => $temporary, 'error' => 0, 'size' => filesize( $temporary ) ), $post_id );
            if ( is_wp_error( $attachment_id ) ) {
                return $attachment_id;
            }
            update_post_meta( $attachment_id, self::IMAGE_HASH_META, $image['hash'] );
            if ( '' !== $image['attachment_title'] ) {
                wp_update_post( array( 'ID' => $attachment_id, 'post_title' => sanitize_text_field( $image['attachment_title'] ) ) );
            }
            if ( ! wp_get_attachment_metadata( $attachment_id ) ) {
                return new WP_Error( 'image_metadata', 'WordPress did not generate attachment metadata' );
            }
        }
        if ( '' !== $image['alt'] && ! self::is_unconfirmed( $image['alt'] ) ) {
            update_post_meta( $attachment_id, '_wp_attachment_image_alt', sanitize_text_field( $image['alt'] ) );
        }
        if ( $attachment_id === (int) get_post_thumbnail_id( $post_id ) || set_post_thumbnail( $post_id, $attachment_id ) ) {
            return $attachment_id;
        }
        return new WP_Error( 'featured_image', 'could not set the attachment as the featured image' );
    }

    /** Uses the existing Polylang relation map and syncs only same-image featured references. */
    public static function translation_image_targets( $post_id, $current_attachment, array &$warnings, $label ) {
        if ( ! function_exists( 'pll_get_post_translations' ) ) {
            return array();
        }
        $targets = array();
        foreach ( (array) pll_get_post_translations( $post_id ) as $language => $translation_id ) {
            $translation_id = absint( $translation_id );
            if ( ! $translation_id || $translation_id === (int) $post_id ) {
                continue;
            }
            if ( 'cars' !== get_post_type( $translation_id ) || ( function_exists( 'pll_get_post_language' ) && $language !== pll_get_post_language( $translation_id, 'slug' ) ) ) {
                $warnings[] = sprintf( '%s — %s translation featured image was not synchronized because its Polylang target is invalid.', $label, strtoupper( $language ) );
                continue;
            }
            if ( (int) get_post_thumbnail_id( $translation_id ) !== (int) $current_attachment ) {
                $warnings[] = sprintf( '%s — %s translation retains its distinct featured image.', $label, strtoupper( $language ) );
                continue;
            }
            $targets[] = $translation_id;
        }
        return $targets;
    }

    /** Updates only existing translated featured-image references after source image success. */
    public static function sync_featured_image_translations( $source_id, $attachment_id, array $targets ) {
        $updated = 0;
        foreach ( $targets as $target_id ) {
            if ( (int) get_post_thumbnail_id( $target_id ) === (int) $attachment_id ) {
                continue;
            }
            if ( ! set_post_thumbnail( $target_id, $attachment_id ) || (int) get_post_thumbnail_id( $target_id ) !== (int) $attachment_id ) {
                return new WP_Error( 'translation_featured_image', sprintf( 'could not verify translated featured image for post %d', $target_id ) );
            }
            $updated++;
        }
        return $updated;
    }

    /** Returns redirect paths for dry-run output without writing Rank Math data. */
    public static function redirect_preview( $old_permalink, $new_permalink ) {
        return array(
            'from' => wp_parse_url( $old_permalink, PHP_URL_PATH ),
            'to' => wp_parse_url( $new_permalink, PHP_URL_PATH ),
        );
    }

    /** Previews a Rank Math redirect between two already-resolved vehicle permalinks. */
    public static function preview_rank_math_redirect_for_permalinks( $old_permalink, $new_permalink ) {
        $plan = self::redirect_plan_for_permalinks( $old_permalink, $new_permalink );
        if ( is_wp_error( $plan ) || ! $plan ) {
            return $plan;
        }
        $action = self::rank_math_redirect_action( $plan );
        return is_wp_error( $action ) ? $action : ( 'create' === $action ? self::redirect_preview_from_plan( $plan ) : null );
    }

    /** Creates a verified Rank Math redirect between two already-resolved vehicle permalinks. */
    public static function ensure_rank_math_redirect_for_permalinks( $old_permalink, $new_permalink, $post_id ) {
        $plan = self::redirect_plan_for_permalinks( $old_permalink, $new_permalink );
        if ( is_wp_error( $plan ) || ! $plan ) {
            return $plan;
        }
        $action = self::rank_math_redirect_action( $plan, $post_id );
        if ( is_wp_error( $action ) || 'unchanged' === $action ) {
            return $action;
        }
        $redirect = \RankMath\Redirections\Redirection::from( array(
            'sources' => array( array( 'pattern' => $plan['source'], 'comparison' => 'exact' ) ),
            'url_to' => $plan['destination'], 'header_code' => '301', 'status' => 'active',
        ) );
        return $redirect->is_infinite_loop() || ! $redirect->save()
            ? new WP_Error( 'redirect_create_failed', 'Rank Math rejected the 301 redirect' )
            : 'created';
    }

    private static function preview_rank_math_redirect( $old_slug, $new_slug ) {
        $plan = self::fleet_redirect_plan( $old_slug, $new_slug );
        if ( is_wp_error( $plan ) || ! $plan ) {
            return $plan;
        }
        $action = self::rank_math_redirect_action( $plan );
        return is_wp_error( $action ) ? $action : ( 'create' === $action ? self::redirect_preview_from_plan( $plan ) : null );
    }

    private static function ensure_rank_math_redirect( $old_slug, $new_slug, $post_id ) {
        $plan = self::fleet_redirect_plan( $old_slug, $new_slug );
        if ( is_wp_error( $plan ) || ! $plan ) {
            return $plan;
        }
        $action = self::rank_math_redirect_action( $plan, $post_id );
        if ( is_wp_error( $action ) || 'unchanged' === $action ) {
            return $action;
        }
        $redirect = \RankMath\Redirections\Redirection::from( array(
            'sources' => array( array( 'pattern' => $plan['source'], 'comparison' => 'exact' ) ),
            'url_to' => $plan['destination'], 'header_code' => '301', 'status' => 'active',
        ) );
        return $redirect->is_infinite_loop() || ! $redirect->save()
            ? new WP_Error( 'redirect_create_failed', 'Rank Math rejected the 301 redirect' )
            : 'created';
    }

    /** Builds a language-aware redirect plan from real WordPress permalinks. */
    private static function redirect_plan_for_permalinks( $old_permalink, $new_permalink ) {
        $old_path = trim( (string) wp_parse_url( $old_permalink, PHP_URL_PATH ), '/' );
        $new_parts = wp_parse_url( $new_permalink );
        $new_path = trim( (string) wp_parse_url( $new_permalink, PHP_URL_PATH ), '/' );
        if ( '' === $old_path || '' === $new_path || ! is_array( $new_parts ) || empty( $new_parts['scheme'] ) || empty( $new_parts['host'] ) ) {
            return new WP_Error( 'redirect_permalink_invalid', 'WordPress did not provide valid old and new vehicle permalinks.' );
        }
        if ( $old_path === $new_path ) {
            return null;
        }
        $new_slug = sanitize_title( basename( $new_path ) );
        if ( '' === $new_slug ) {
            return new WP_Error( 'redirect_slug_invalid', 'the translated vehicle slug is invalid.' );
        }
        return array( 'source' => $old_path, 'destination' => $new_permalink, 'new_slug' => $new_slug );
    }

    private static function rank_math_redirect_action( array $plan, $post_id = 0 ) {
        if ( ! self::rank_math_redirects_available() ) {
            return new WP_Error( 'redirect_unavailable', 'Rank Math Redirections is unavailable' );
        }
        if ( $post_id && ! self::redirect_destination_resolves_to_vehicle( $plan['destination'], $post_id, $plan['new_slug'] ) ) {
            return new WP_Error( 'redirect_destination_invalid', 'the redirect destination does not resolve to the expected vehicle' );
        }
        $existing = \RankMath\Redirections\DB::match_redirections( $plan['source'] );
        if ( $existing ) {
            return self::rank_math_redirect_matches_plan( $existing, $plan )
                ? 'unchanged'
                : new WP_Error( 'redirect_conflict', 'an existing Rank Math redirect already owns the old URL' );
        }
        return 'create';
    }

    /** Builds a stable Rank Math redirect plan without relying on request globals. */
    public static function fleet_redirect_plan( $old_slug, $new_slug ) {
        $old_slug = sanitize_title( $old_slug );
        $new_slug = sanitize_title( $new_slug );
        if ( '' === $old_slug || '' === $new_slug ) {
            return new WP_Error( 'redirect_slug_invalid', 'the old and new vehicle slugs must be valid' );
        }
        if ( $old_slug === $new_slug ) {
            return null;
        }
        $destination = self::redirect_destination_for_slug( $new_slug );
        if ( is_wp_error( $destination ) ) {
            return $destination;
        }
        return array( 'source' => 'cars/' . $old_slug, 'destination' => $destination, 'new_slug' => $new_slug );
    }

    /** Uses WordPress URL configuration rather than HTTP_HOST or a request permalink. */
    public static function redirect_destination_for_slug( $slug ) {
        $slug = sanitize_title( $slug );
        if ( '' === $slug ) {
            return new WP_Error( 'redirect_slug_invalid', 'the new vehicle slug must be valid' );
        }
        $destination = home_url( '/cars/' . $slug . '/' );
        $parts = wp_parse_url( $destination );
        if ( ! is_array( $parts ) || empty( $parts['scheme'] ) || empty( $parts['host'] ) || 'cars' === strtolower( $parts['host'] ) || preg_match( '#^https?:/(?!/)#i', $destination ) ) {
            return new WP_Error( 'redirect_destination_invalid', 'WordPress did not produce a valid redirect destination URL' );
        }
        return $destination;
    }

    public static function rank_math_redirect_matches_plan( array $redirect, array $plan ) {
        return '301' === (string) ( $redirect['header_code'] ?? '' )
            && 'active' === (string) ( $redirect['status'] ?? '' )
            && untrailingslashit( (string) ( $redirect['url_to'] ?? '' ) ) === untrailingslashit( $plan['destination'] );
    }

    private static function redirect_destination_resolves_to_vehicle( $destination, $post_id, $slug ) {
        $post = get_post( $post_id );
        if ( ! $post || 'cars' !== $post->post_type || $slug !== $post->post_name ) {
            return false;
        }
        // url_to_postid() does not resolve every Polylang language-prefixed URL.
        // Compare the target's actual localized permalink instead of falling back
        // to a global route lookup that can reject a valid translation.
        return untrailingslashit( (string) get_permalink( $post_id ) ) === untrailingslashit( (string) $destination );
    }

    private static function redirect_preview_from_plan( array $plan ) {
        return self::redirect_preview( home_url( '/' . $plan['source'] . '/' ), $plan['destination'] );
    }

    private static function rank_math_redirects_available() {
        return class_exists( '\RankMath\Redirections\Redirection' ) && class_exists( '\RankMath\Redirections\DB' ) && class_exists( '\RankMath\Helper' ) && \RankMath\Helper::is_module_active( 'redirections' );
    }

    /** Updates one vehicle while temporarily disabling only existing technical-field synchronization hooks. */
    public static function update_vehicle_post_without_translation_sync( $post_id, array $post_update ) {
        $hooked = class_exists( 'Rentacar_Core_Vehicle_Field_Synchronizer' );
        $maintenance_hooked = class_exists( 'Rentacar_Core_Vehicle_Maintenance' );
        if ( $hooked ) {
            remove_action( 'save_post_cars', array( 'Rentacar_Core_Vehicle_Field_Synchronizer', 'synchronize_from_default_translation' ), 40 );
        }
        if ( $maintenance_hooked ) {
            remove_action( 'save_post_cars', array( 'Rentacar_Core_Vehicle_Maintenance', 'update_starting_price' ), 30 );
        }
        try {
            self::update_post_with_migration_slug_override( $post_id, $post_update );
        } finally {
            if ( $maintenance_hooked ) {
                add_action( 'save_post_cars', array( 'Rentacar_Core_Vehicle_Maintenance', 'update_starting_price' ), 30, 1 );
            }
            if ( $hooked ) {
                add_action( 'save_post_cars', array( 'Rentacar_Core_Vehicle_Field_Synchronizer', 'synchronize_from_default_translation' ), 40, 1 );
            }
        }
    }

    /**
     * Lets Polylang translations share an exact vehicle slug for this one
     * update only. WordPress's normal global uniqueness policy remains active
     * for every other post, slug, post type, and request.
     */
    public static function update_post_with_migration_slug_override( $post_id, array $post_update ) {
        $post_id = absint( $post_id );
        $requested_slug = isset( $post_update['post_name'] ) ? sanitize_title( $post_update['post_name'] ) : '';
        $filter = null;

        if ( $requested_slug && 'cars' === get_post_type( $post_id ) && self::slug_is_available_in_vehicle_language( $post_id, $requested_slug ) ) {
            $filter = static function( $override_slug, $slug, $candidate_post_id, $post_status, $post_type, $post_parent ) use ( $post_id, $requested_slug ) {
                if ( $post_id === (int) $candidate_post_id && 'cars' === $post_type && $requested_slug === $slug ) {
                    return $requested_slug;
                }
                return $override_slug;
            };
            add_filter( 'pre_wp_unique_post_slug', $filter, 10, 6 );
        }

        try {
            $updated = wp_update_post( $post_update, true );
        } finally {
            if ( $filter ) {
                remove_filter( 'pre_wp_unique_post_slug', $filter, 10 );
            }
        }

        if ( is_wp_error( $updated ) || ! $updated ) {
            throw new RuntimeException( is_wp_error( $updated ) ? $updated->get_error_message() : 'wp_update_post failed' );
        }
        if ( $requested_slug && $requested_slug !== get_post_field( 'post_name', $post_id ) ) {
            throw new RuntimeException( 'WordPress generated a suffixed slug; the requested slug was not accepted.' );
        }
    }

    private static function check_requested_starting_price( array $row, array $pricing, $label, array &$warnings ) {
        $requested = self::value( $row, 'starting_price' );
        if ( '' === $requested || self::is_unconfirmed( $requested ) ) {
            return;
        }
        if ( ! self::valid_price( $requested ) ) {
            $warnings[] = sprintf( '%s — starting_price must be a positive number; it is derived and was not written.', $label );
            return;
        }
        $minimum = min( array_map( 'floatval', array( $pricing['price'], $pricing['price2'], $pricing['price3'], $pricing['price4'] ) ) );
        if ( (float) self::normalize_price( $requested ) !== $minimum ) {
            $warnings[] = sprintf( '%s — starting_price (%s) does not match the derived minimum (%s).', $label, $requested, $minimum );
        }
    }

    private static function print_result( array $result ) {
        foreach ( $result['warnings'] as $warning ) {
            WP_CLI::warning( $warning );
        }
        if ( $result['skipped'] ) {
            WP_CLI::log( '[SKIPPED] ' . ( $result['label'] ?: 'Unmatched CSV row' ) );
            return;
        }
        if ( $result['image'] ) {
            self::print_image_result( $result['label'], $result['image'] );
            if ( ! $result['changes'] ) {
                return;
            }
        }
        WP_CLI::log( sprintf( "\n[%s] %s", $result['status'], $result['label'] ) );
        foreach ( $result['changes'] as $field => $change ) {
            WP_CLI::log( sprintf( "%s:\n  old: %s\n  new: %s", $field, self::printable( $change['old'] ), self::printable( $change['new'] ) ) );
        }
        if ( $result['redirect'] ) {
            WP_CLI::log( sprintf( "301 redirect:\n  %s\n  -> %s", $result['redirect']['from'], $result['redirect']['to'] ) );
        }
    }

    private static function print_image_result( $label, array $image ) {
        WP_CLI::log( sprintf( "\n[%s] %s", $image['status'], $label ) );
        if ( ! empty( $image['current_attachment'] ) ) {
            WP_CLI::log( sprintf( '  current attachment: %d', $image['current_attachment'] ) );
        }
        if ( ! empty( $image['current_file'] ) ) {
            WP_CLI::log( sprintf( '  current file: %s', $image['current_file'] ) );
        }
        WP_CLI::log( sprintf( '  source file: %s', $image['filename'] ?? '(not available)' ) );
        if ( ! empty( $image['hash'] ) ) {
            WP_CLI::log( sprintf( '  hash: %s', $image['hash'] ) );
        }
        if ( 'VALID' === ( $image['status'] ?? '' ) || in_array( $image['status'], array( 'IMPORT', 'REPLACE', 'UNCHANGED' ), true ) ) {
            WP_CLI::log( sprintf( '  source: %dx%d, %s, %d bytes', $image['width'], $image['height'], $image['mime'], $image['size'] ) );
        }
        WP_CLI::log( sprintf( '  action: %s', $image['description'] ) );
        if ( ! empty( $image['translation_targets'] ) ) {
            WP_CLI::log( sprintf( '  translated featured images: %s', implode( ', ', array_map( 'absint', $image['translation_targets'] ) ) ) );
        }
    }

    private static function empty_result() {
        return array( 'label' => '', 'status' => '', 'changes' => array(), 'warnings' => array(), 'errors' => array(), 'redirect' => null, 'image' => null, 'skipped' => false, 'counts' => array( 'matched' => 0, 'updated' => 0, 'unchanged' => 0, 'partial' => 0, 'skipped' => 0, 'images_imported' => 0, 'images_replaced' => 0, 'images_unchanged' => 0, 'images_missing' => 0, 'images_invalid' => 0, 'translation_images_updated' => 0, 'redirects_created' => 0, 'warnings' => 0, 'errors' => 0 ) );
    }

    private static function count_image_plan( $image, array &$result ) {
        if ( ! $image ) {
            return;
        }
        $key = array(
            'IMPORT' => 'images_imported', 'REPLACE' => 'images_replaced', 'UNCHANGED' => 'images_unchanged',
            'MISSING' => 'images_missing', 'INVALID' => 'images_invalid',
        )[ $image['status'] ] ?? null;
        if ( $key ) {
            $result['counts'][ $key ]++;
        }
    }

    private static function value( array $row, $key ) {
        $aliases = array( 'seats' => array( 'seats', 'max_passagers' ), 'fuel' => array( 'fuel', 'powertrain' ) );
        foreach ( $aliases[ $key ] ?? array( $key ) as $candidate ) {
            if ( isset( $row[ $candidate ] ) ) {
                return trim( (string) $row[ $candidate ] );
            }
        }
        return '';
    }

    private static function normalize_header( $header ) { return strtolower( preg_replace( '/^\xEF\xBB\xBF/', '', trim( (string) $header ) ) ); }
    private static function normalize_price( $price ) { return rtrim( rtrim( number_format( (float) str_replace( ',', '.', $price ), 2, '.', '' ), '0' ), '.' ); }
    private static function boolean_value( $value ) { $value = strtolower( trim( (string) $value ) ); return in_array( $value, array( '1', 'yes', 'true', 'si', 'sì' ), true ) ? true : ( in_array( $value, array( '0', 'no', 'false' ), true ) ? false : null ); }
    private static function has_generic_title_attribute( $title ) { return (bool) preg_match( '/\|\s*(diesel|benzina|petrol|gasoline|hybrid|ibrida|electric|elettrica|automatic|manual)\b/i', (string) $title ); }
    private static function minimum_rental_days() { return class_exists( 'Rentacar_Core_Rental_Policy' ) ? Rentacar_Core_Rental_Policy::minimum_rental_days() : 3; }
    private static function display_key( $key ) { return str_replace( array( '_rentacar_', '_', 'price1', 'price2', 'price3', 'price4' ), array( '', ' ', 'price tier 1', 'price tier 2', 'price tier 3', 'price tier 4' ), $key ); }
    private static function printable( $value ) { return '' === (string) $value ? '(empty)' : ( is_scalar( $value ) ? (string) $value : wp_json_encode( $value ) ); }
}
