<?php
defined( 'ABSPATH' ) || exit;
?>
<p class="article-meta"><time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time><?php if ( get_the_modified_date( 'U' ) !== get_the_date( 'U' ) ) : ?> · <?php esc_html_e( 'Updated', 'rentacar-venezia-v2' ); ?> <time datetime="<?php echo esc_attr( get_the_modified_date( DATE_W3C ) ); ?>"><?php echo esc_html( get_the_modified_date() ); ?></time><?php endif; ?></p>
