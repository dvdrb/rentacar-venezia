<?php
defined( 'ABSPATH' ) || exit;

/** Small, mail-client-safe HTML presentation for reservation notifications. */
final class Rentacar_Core_Reservation_Email_Template {
    public static function headers( array $headers = array() ) {
        $headers[] = 'Content-Type: text/html; charset=UTF-8';

        return $headers;
    }

    public static function render( $heading, $intro, $reference, array $sections, $footer = '' ) {
        $content = '';
        foreach ( $sections as $section ) {
            if ( empty( $section['rows'] ) || ! is_array( $section['rows'] ) ) {
                continue;
            }

            $rows = '';
            foreach ( $section['rows'] as $label => $value ) {
                $rows .= '<tr><td style="padding:10px 0;border-bottom:1px solid #e4e9f0;color:#46566c;font-size:14px;vertical-align:top;width:42%;">' . self::escape( $label ) . '</td><td style="padding:10px 0;border-bottom:1px solid #e4e9f0;color:#14213d;font-size:14px;font-weight:600;vertical-align:top;">' . self::value( $value ) . '</td></tr>';
            }
            $content .= '<section style="margin:26px 0 0;"><h2 style="margin:0 0 8px;color:#071b3a;font-size:18px;line-height:1.3;">' . self::escape( $section['title'] ) . '</h2><table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0">' . $rows . '</table></section>';
        }

        return '<!doctype html><html lang="en"><body style="margin:0;padding:0;background:#f3f6f8;color:#14213d;font-family:Arial,sans-serif;"><table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background:#f3f6f8;"><tr><td style="padding:32px 16px;"><table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:680px;margin:0 auto;background:#ffffff;border:1px solid #d7dee8;border-radius:12px;overflow:hidden;"><tr><td style="padding:28px 32px;background:#071b3a;color:#ffffff;"><p style="margin:0 0 8px;color:#ffc928;font-size:12px;font-weight:bold;letter-spacing:1px;text-transform:uppercase;">Rent a Car Venezia</p><h1 style="margin:0;color:#ffffff;font-size:26px;line-height:1.2;">' . self::escape( $heading ) . '</h1></td></tr><tr><td style="padding:30px 32px;"><p style="margin:0;color:#46566c;font-size:16px;line-height:1.6;">' . self::value( $intro ) . '</p><p style="margin:22px 0 0;padding:12px 14px;border-left:4px solid #ffc928;background:#fff8df;color:#071b3a;font-size:14px;"><strong>Reference:</strong> ' . self::escape( $reference ) . '</p>' . $content . '</td></tr><tr><td style="padding:20px 32px;background:#f7f8fa;color:#46566c;font-size:12px;line-height:1.55;">' . self::value( $footer ?: 'Rent a Car Venezia · +39 344 506 8823 · info@rentacarvenezia.it' ) . '</td></tr></table></td></tr></table></body></html>';
    }

    private static function escape( $value ) {
        return htmlspecialchars( (string) $value, ENT_QUOTES, 'UTF-8' );
    }

    private static function value( $value ) {
        return nl2br( self::escape( $value ) );
    }
}
