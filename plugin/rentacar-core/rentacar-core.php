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
require_once RENTACAR_CORE_PATH . 'src/Multilingual/LanguageResolverInterface.php';
require_once RENTACAR_CORE_PATH . 'src/Multilingual/PolylangLanguageResolver.php';
require_once RENTACAR_CORE_PATH . 'src/Multilingual/WpmlLanguageResolver.php';
require_once RENTACAR_CORE_PATH . 'src/Multilingual/NullLanguageResolver.php';
require_once RENTACAR_CORE_PATH . 'src/Multilingual/LanguageResolverFactory.php';
require_once RENTACAR_CORE_PATH . 'src/Multilingual/VehicleFieldPolicy.php';
require_once RENTACAR_CORE_PATH . 'src/Multilingual/VehicleFieldSynchronizer.php';
require_once RENTACAR_CORE_PATH . 'src/Multilingual/VehicleTranslationAudit.php';
require_once RENTACAR_CORE_PATH . 'src/Vehicles/VehicleMapper.php';
require_once RENTACAR_CORE_PATH . 'src/Vehicles/VehicleRepository.php';
require_once RENTACAR_CORE_PATH . 'src/Settings/MarketingClaimRegistry.php';
require_once RENTACAR_CORE_PATH . 'src/Settings/ReservationExtras.php';
require_once RENTACAR_CORE_PATH . 'src/Settings/ReservationExtraSettings.php';
require_once RENTACAR_CORE_PATH . 'src/Settings/RentalPolicy.php';
require_once RENTACAR_CORE_PATH . 'src/Pricing/RentalDurationCalculator.php';
require_once RENTACAR_CORE_PATH . 'src/Pricing/Estimate.php';
require_once RENTACAR_CORE_PATH . 'src/Pricing/EstimateService.php';
require_once RENTACAR_CORE_PATH . 'src/Rest/EstimateController.php';
require_once RENTACAR_CORE_PATH . 'src/Enquiries/ReservationReference.php';
require_once RENTACAR_CORE_PATH . 'src/Enquiries/ReservationRequest.php';
require_once RENTACAR_CORE_PATH . 'src/Enquiries/ReservationEmailTemplate.php';
require_once RENTACAR_CORE_PATH . 'src/Enquiries/ReservationValidator.php';
require_once RENTACAR_CORE_PATH . 'src/Enquiries/BusinessNotification.php';
require_once RENTACAR_CORE_PATH . 'src/Enquiries/CustomerAcknowledgement.php';
require_once RENTACAR_CORE_PATH . 'src/Enquiries/ReservationRateLimiter.php';
require_once RENTACAR_CORE_PATH . 'src/Enquiries/ReservationController.php';
require_once RENTACAR_CORE_PATH . 'src/Enquiries/ContactController.php';

add_action( 'init', array( 'Rentacar_Core_Cars_Post_Type', 'register_when_legacy_absent' ), 9 );
add_filter( 'pll_get_post_types', function( $post_types, $is_settings = false ) { $post_types['cars'] = 'cars'; return $post_types; }, 10, 2 );
add_filter( 'posts_clauses', array( 'Rentacar_Core_Cars_Post_Type', 'constrain_main_query_to_language' ), 20, 2 );
add_action( 'admin_init', array( 'Rentacar_Core_Marketing_Claim_Registry', 'register_setting' ) );
add_action( 'admin_init', array( 'Rentacar_Core_Reservation_Extra_Settings', 'register_setting' ) );
add_action( 'admin_menu', array( 'Rentacar_Core_Reservation_Extra_Settings', 'register_page' ) );
add_action( 'rest_api_init', array( 'Rentacar_Core_Estimate_Controller', 'register_routes' ) );
add_action( 'admin_post_nopriv_rentacar_submit_reservation', array( 'Rentacar_Core_Reservation_Controller', 'handle' ) );
add_action( 'admin_post_rentacar_submit_reservation', array( 'Rentacar_Core_Reservation_Controller', 'handle' ) );
add_action( 'admin_post_nopriv_rentacar_submit_contact', array( 'Rentacar_Core_Contact_Controller', 'handle' ) );
add_action( 'admin_post_rentacar_submit_contact', array( 'Rentacar_Core_Contact_Controller', 'handle' ) );
add_action( 'admin_notices', array( 'Rentacar_Core_Reservation_Controller', 'admin_notice' ) );
add_filter( 'rentacar_core_reservation_recipient', function( $recipient ) {
    $configured_recipient = (string) get_option( Rentacar_Core_Reservation_Extra_Settings::RECIPIENT_OPTION, '' );

    return is_email( $configured_recipient ) ? $configured_recipient : $recipient;
} );
