<?php
defined( 'ABSPATH' ) || exit;

/**
 * Authoritative phone-number normalizer backed by the same bundled
 * libphonenumber-js metadata used by the theme.
 */
final class Rentacar_Core_Phone_Number_Service {
    private static $metadata = null;

    public function normalize( $country, $number, $submitted_calling_code = '' ) {
        $country = strtoupper( preg_replace( '/[^A-Za-z]/', '', (string) $country ) );
        $metadata = self::metadata();

        if ( 2 !== strlen( $country ) || empty( $metadata['countries'][ $country ] ) ) {
            return $this->failure( 'phone_country_required', 'phone_country' );
        }

        $country_data = $metadata['countries'][ $country ];
        $calling_code = (string) $country_data[0];
        $claimed_calling_code = preg_replace( '/\D/', '', (string) $submitted_calling_code );
        if ( '' !== $claimed_calling_code && $calling_code !== $claimed_calling_code ) {
            return $this->failure( 'phone_calling_code_mismatch', 'phone_country' );
        }

        $number = trim( (string) $number );
        if ( '' === $number ) {
            return $this->failure( 'phone_number_required', 'phone' );
        }
        if ( ! preg_match( '/^\+?[0-9\s().-]+$/', $number ) || false !== strpos( substr( $number, 1 ), '+' ) ) {
            return $this->failure( 'phone_invalid', 'phone' );
        }

        $is_international = '+' === substr( $number, 0, 1 );
        $national = preg_replace( '/\D/', '', $number );
        if ( $is_international ) {
            if ( 0 !== strpos( $national, $calling_code ) ) {
                return $this->failure( 'phone_calling_code_mismatch', 'phone_country' );
            }
            $national = substr( $national, strlen( $calling_code ) );
        }
        if ( '' === $national ) {
            return $this->failure( 'phone_number_required', 'phone' );
        }

        $national = $this->normalize_national_prefix( $national, $country_data );
        if ( ! $this->is_valid_national_number( $national, $country_data ) || strlen( $calling_code . $national ) > 15 ) {
            return $this->failure( 'phone_invalid', 'phone' );
        }

        $e164 = '+' . $calling_code . $national;
        return array(
            'valid'              => true,
            'phone'              => $e164,
            'phone_country'      => $country,
            'phone_calling_code' => '+' . $calling_code,
            'phone_national'     => $national,
            'phone_e164'         => $e164,
            'phone_display'      => $this->format_international( $calling_code, $national, $country_data ),
        );
    }

    public static function country_options( $locale = 'en' ) {
        $locale = in_array( $locale, array( 'en', 'it', 'ro', 'ru' ), true ) ? $locale : 'en';
        $metadata = self::metadata();
        $options = array();
        foreach ( $metadata['countries'] as $country => $country_data ) {
            $options[] = array(
                'country'      => $country,
                'calling_code' => '+' . $country_data[0],
                'name'         => $metadata['country_names'][ $country ][ $locale ] ?? ( $metadata['country_names'][ $country ]['en'] ?? $country ),
                'search'       => implode( ' ', (array) ( $metadata['country_names'][ $country ] ?? array() ) ),
                'flag'         => self::flag( $country ),
            );
        }
        usort( $options, function( $left, $right ) { return strcasecmp( $left['name'], $right['name'] ); } );
        return $options;
    }

    public static function error_message( $code ) {
        $messages = array(
            'phone_country_required'       => __( 'Please select a country.', 'rentacar-core' ),
            'phone_number_required'        => __( 'Please enter a phone number.', 'rentacar-core' ),
            'phone_calling_code_mismatch'  => __( 'The country calling code does not match the selected country.', 'rentacar-core' ),
            'phone_invalid'                => __( 'Please enter a valid phone number.', 'rentacar-core' ),
        );
        return $messages[ $code ] ?? $messages['phone_invalid'];
    }

    private static function metadata() {
        if ( null !== self::$metadata ) {
            return self::$metadata;
        }
        $path = defined( 'RENTACAR_CORE_PATH' ) ? RENTACAR_CORE_PATH . 'data/phone-metadata.json' : dirname( __DIR__, 2 ) . '/data/phone-metadata.json';
        $metadata = json_decode( file_get_contents( $path ), true );
        self::$metadata = is_array( $metadata ) ? $metadata : array( 'countries' => array(), 'country_names' => array() );
        return self::$metadata;
    }

    private function normalize_national_prefix( $national, array $country_data ) {
        if ( $this->is_valid_national_number( $national, $country_data ) ) {
            return $national;
        }
        $prefix = isset( $country_data[7] ) && is_string( $country_data[7] ) ? $country_data[7] : ( isset( $country_data[5] ) && is_string( $country_data[5] ) ? $country_data[5] : '' );
        if ( '' === $prefix || ! $this->matches( $prefix, $national, false ) ) {
            return $national;
        }
        $transform = isset( $country_data[8] ) && is_string( $country_data[8] ) ? $country_data[8] : '';
        $candidate = preg_replace( $this->regex( '^(?:' . $prefix . ')' ), $transform, $national, 1 );
        return $this->is_valid_national_number( $candidate, $country_data ) ? $candidate : $national;
    }

    private function is_valid_national_number( $national, array $country_data ) {
        if ( ! ctype_digit( $national ) || empty( $country_data[2] ) ) {
            return false;
        }
        $possible_lengths = isset( $country_data[3] ) && is_array( $country_data[3] ) ? $country_data[3] : array();
        return ( ! $possible_lengths || in_array( strlen( $national ), $possible_lengths, true ) ) && $this->matches( $country_data[2], $national );
    }

    private function format_international( $calling_code, $national, array $country_data ) {
        $formatted = $national;
        foreach ( (array) ( $country_data[4] ?? array() ) as $format ) {
            if ( ! is_array( $format ) || empty( $format[0] ) || empty( $format[1] ) ) {
                continue;
            }
            $leading = isset( $format[2][0] ) ? $format[2][0] : '';
            if ( $leading && ! $this->matches( $leading, $national, false ) ) {
                continue;
            }
            if ( $this->matches( $format[0], $national ) ) {
                $formatted = preg_replace( $this->regex( '^(?:' . $format[0] . ')$' ), $format[1], $national );
                break;
            }
        }
        return '+' . $calling_code . ' ' . $formatted;
    }

    private function matches( $pattern, $value, $anchored = true ) {
        $pattern = $anchored ? '^(?:' . $pattern . ')$' : '^(?:' . $pattern . ')';
        return 1 === preg_match( $this->regex( $pattern ), $value );
    }

    private function regex( $pattern ) {
        return '~' . str_replace( '~', '\\~', $pattern ) . '~D';
    }

    private function failure( $code, $field ) {
        return array( 'valid' => false, 'code' => $code, 'field' => $field );
    }

    private static function flag( $country ) {
        $flag = '';
        foreach ( str_split( $country ) as $letter ) {
            $flag .= html_entity_decode( '&#' . ( ord( $letter ) + 127397 ) . ';', ENT_NOQUOTES, 'UTF-8' );
        }
        return $flag;
    }
}
