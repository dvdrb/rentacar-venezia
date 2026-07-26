<?php
defined( 'ABSPATH' ) || exit;

/** Reports technical-field differences without changing posts. */
final class Rentacar_Core_Vehicle_Translation_Audit {
    private $policy;

    public function __construct( Rentacar_Core_Vehicle_Field_Policy $policy = null ) {
        $this->policy = $policy ? $policy : new Rentacar_Core_Vehicle_Field_Policy();
    }

    public function compare( $source_id, $target_id ) {
        $source = $this->policy->snapshot( $source_id );
        $target = $this->policy->snapshot( $target_id );
        $differences = array();

        foreach ( $source as $key => $value ) {
            if ( wp_json_encode( $value ) !== wp_json_encode( $target[ $key ] ?? null ) ) {
                $differences[ $key ] = array( 'source' => $value, 'target' => $target[ $key ] ?? null );
            }
        }

        return $differences;
    }
}
