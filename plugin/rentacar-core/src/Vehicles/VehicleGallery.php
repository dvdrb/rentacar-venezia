<?php
defined( 'ABSPATH' ) || exit;

final class Rentacar_Core_Vehicle_Gallery {
    private $featured_image_id;
    private $image_ids;

    public function __construct( $featured_image_id, array $image_ids = array() ) {
        $this->featured_image_id = max( 0, (int) $featured_image_id );
        $this->image_ids = array_values( array_unique( array_filter( array_map( 'intval', $image_ids ) ) ) );
    }

    public function featured_image_id() {
        return $this->featured_image_id;
    }

    public function image_ids() {
        return $this->image_ids;
    }

    public function all_image_ids() {
        $ids = $this->image_ids;

        if ( $this->featured_image_id ) {
            array_unshift( $ids, $this->featured_image_id );
        }

        return array_values( array_unique( $ids ) );
    }
}
