<?php
/**
 * @package Movers Lite
 * Setup the WordPress core custom header feature.
 *
 * @uses movers_lite_header_style()

 */
function movers_lite_custom_header_setup() {
	add_theme_support( 'custom-header', apply_filters( 'movers_lite_custom_header_args', array(
		'default-text-color'     => 'ffffff',
		'width'                  => 1600,
		'height'                 => 400,
		'wp-head-callback'       => 'movers_lite_header_style',
	) ) );
}
add_action( 'after_setup_theme', 'movers_lite_custom_header_setup' );

if ( ! function_exists( 'movers_lite_header_style' ) ) :
/**
 * Styles the header image and text displayed on the blog
 *
 * @see movers_lite_custom_header_setup().
 */
function movers_lite_header_style() {
	$header_text_color = get_header_textcolor();
	?>

	<?php
	// If the header text option is untouched, let's bail.
	if ( display_header_text() ) {
		return;
	}

	// If the header text has been hidden.
	?>
    <style type="text/css">
		.logo {
			margin: 0 auto 0 0;
		}

		.logo h1,
		.logo p{
			clip: rect(1px, 1px, 1px, 1px);
			position: absolute;
		}
    </style>
	
    <?php
	
}
endif; // movers_lite_header_style