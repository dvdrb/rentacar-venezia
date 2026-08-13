<?php
/** Focused checks for the safe editor-owned Contact content migration. */
define( 'ABSPATH', __DIR__ . '/' );

function esc_attr( $value ) { return $value; }
function esc_html( $value ) { return $value; }
function wp_strip_all_tags( $value ) { return strip_tags( $value ); }

require_once dirname( __DIR__, 2 ) . '/theme/rentacar-venezia-v2/inc/contact-content.php';

function contact_content_assert( $condition, $message ) {
    if ( ! $condition ) {
        fwrite( STDERR, "FAIL: {$message}\n" );
        exit( 1 );
    }
}

$business = array(
    'phone' => '+393445068823',
    'phone_display' => '+39 344 506 8823',
    'email' => 'info@rentacarvenezia.it',
    'weekday_hours' => 'Monday–Friday, 24/24',
    'weekend_hours' => 'Saturday–Sunday, 07:00–23:00',
);
$legacy = '<h2>Contact Rent a Car Venezia</h2><p>Phone: <a href="mailto:info@rentacar-venezia-local.local">info@rentacar-venezia-local.local</a></p><h3>Office hours</h3><ul><li>Monday–Friday: 08:00–17:00</li></ul><p>Our office address is Venice Marco Polo Airport.</p><p>You can send an online request at any time.</p><p>Keep this unrelated editor paragraph.</p>';
$result = rentacar_venezia_v2_contact_content_migrate( $legacy, 'en', $business );

contact_content_assert( $result['changed'], 'Legacy Contact content is changed in migration mode.' );
contact_content_assert( false === strpos( $result['content'], 'rentacar-venezia-local.local' ), 'Localhost email is removed from editor-owned Contact content.' );
contact_content_assert( false === strpos( $result['content'], 'Rent a Car Venezia' ), 'Legacy public business name is removed from editor-owned Contact content.' );
contact_content_assert( false === strpos( $result['content'], '08:00–17:00' ), 'Legacy office hours are removed from editor-owned Contact content.' );
contact_content_assert( false === strpos( $result['content'], 'Marco Polo Airport' ), 'Airport office-address claim is removed from editor-owned Contact content.' );
contact_content_assert( false !== strpos( $result['content'], 'Contact G&amp;D Rent A Car' ) && false !== strpos( $result['content'], 'info@rentacarvenezia.it' ), 'Canonical public identity and email are inserted.' );
contact_content_assert( false !== strpos( $result['content'], 'Keep this unrelated editor paragraph.' ), 'Unrelated editor content is preserved.' );
contact_content_assert( ! rentacar_venezia_v2_contact_content_migrate( $result['content'], 'en', $business )['changed'], 'Migration is idempotent.' );
contact_content_assert( false !== strpos( rentacar_venezia_v2_contact_content_summary( $result['content'] ), 'G&amp;D Rent A Car' ), 'Migration reports a readable after-content summary.' );

echo "Contact content checks passed.\n";
