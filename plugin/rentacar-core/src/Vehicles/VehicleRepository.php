<?php
defined( 'ABSPATH' ) || exit;

/**
 * Read-only access to the existing cars post type and its established ACF data.
 */
final class Rentacar_Core_Vehicle_Repository {
    private $mapper;

    public function __construct( Rentacar_Core_Vehicle_Mapper $mapper = null ) {
        $this->mapper = $mapper ? $mapper : new Rentacar_Core_Vehicle_Mapper();
    }

    public function find( $vehicle_id ) {
        $post = get_post( (int) $vehicle_id );

        if ( ! $post instanceof WP_Post || 'cars' !== $post->post_type ) {
            return null;
        }

        return $this->mapper->map( $post );
    }

    public function find_by_slug( $slug, $language = null ) {
        $query = new WP_Query(
            array(
                'post_type'              => 'cars',
                'post_status'            => 'publish',
                'name'                   => sanitize_title( $slug ),
                'posts_per_page'         => 1,
                'no_found_rows'          => true,
                'ignore_sticky_posts'    => true,
                'suppress_filters'       => empty( $language ),
                'lang'                   => $language,
            )
        );

        if ( empty( $query->posts ) ) {
            return null;
        }

        return $this->mapper->map( $query->posts[0] );
    }

    public function query( array $arguments = array() ) {
        $defaults = array(
            'post_type'           => 'cars',
            'post_status'         => 'publish',
            'posts_per_page'      => -1,
            'orderby'             => 'menu_order title',
            'order'               => 'ASC',
            'ignore_sticky_posts' => true,
        );
        $query = new WP_Query( wp_parse_args( $arguments, $defaults ) );
        $vehicles = array();

        foreach ( $query->posts as $post ) {
            $vehicles[] = $this->mapper->map( $post );
        }

        return $vehicles;
    }
}
