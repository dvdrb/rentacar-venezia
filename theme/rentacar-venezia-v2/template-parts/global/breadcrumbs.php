<?php
defined( 'ABSPATH' ) || exit;

$items = rentacar_venezia_v2_breadcrumb_items( $args['post_id'] ?? 0 );
if ( count( $items ) < 2 ) {
    return;
}
?>
<nav class="breadcrumbs" aria-label="<?php esc_attr_e( 'Breadcrumbs', 'rentacar-venezia-v2' ); ?>">
    <ol class="breadcrumbs__list">
        <?php foreach ( $items as $index => $item ) : ?>
            <li class="breadcrumbs__item">
                <?php if ( ! empty( $item['url'] ) ) : ?>
                    <a href="<?php echo esc_url( $item['url'] ); ?>"><?php echo esc_html( $item['label'] ); ?></a>
                <?php else : ?>
                    <span aria-current="page"><?php echo esc_html( $item['label'] ); ?></span>
                <?php endif; ?>
                <?php if ( $index < count( $items ) - 1 ) : ?><span class="breadcrumbs__separator" aria-hidden="true">/</span><?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ol>
</nav>
