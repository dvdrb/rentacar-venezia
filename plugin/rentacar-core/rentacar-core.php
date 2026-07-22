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
require_once RENTACAR_CORE_PATH . 'src/Vehicles/PricingBand.php';
require_once RENTACAR_CORE_PATH . 'src/Vehicles/PricingBandCollection.php';
require_once RENTACAR_CORE_PATH . 'src/Vehicles/Vehicle.php';
require_once RENTACAR_CORE_PATH . 'src/Vehicles/VehicleGallery.php';
require_once RENTACAR_CORE_PATH . 'src/Vehicles/VehicleMapper.php';
require_once RENTACAR_CORE_PATH . 'src/Vehicles/WpmlVehicleResolver.php';
require_once RENTACAR_CORE_PATH . 'src/Vehicles/VehicleRepository.php';
require_once RENTACAR_CORE_PATH . 'src/Settings/MarketingClaimRegistry.php';
require_once RENTACAR_CORE_PATH . 'src/Pricing/RentalDurationCalculator.php';
require_once RENTACAR_CORE_PATH . 'src/Pricing/Estimate.php';
require_once RENTACAR_CORE_PATH . 'src/Pricing/EstimateService.php';
require_once RENTACAR_CORE_PATH . 'src/Rest/EstimateController.php';

add_action( 'init', array( 'Rentacar_Core_Cars_Post_Type', 'register_when_legacy_absent' ), 9 );
add_action( 'admin_init', array( 'Rentacar_Core_Marketing_Claim_Registry', 'register_setting' ) );
add_action( 'rest_api_init', array( 'Rentacar_Core_Estimate_Controller', 'register_routes' ) );
