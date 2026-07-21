<?php
/**
 * Plugin Name: Rentacar Core
 * Description: Site-specific vehicle and availability-request domain services.
 * Version: 0.1.0
 * Text Domain: rentacar-core
 * Requires PHP: 7.4
 */

defined( 'ABSPATH' ) || exit;

define( 'RENTACAR_CORE_PATH', plugin_dir_path( __FILE__ ) );

require_once RENTACAR_CORE_PATH . 'src/PostTypes/CarsPostType.php';

add_action( 'init', array( 'Rentacar_Core_Cars_Post_Type', 'register_when_legacy_absent' ), 9 );
