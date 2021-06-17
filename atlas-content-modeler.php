<?php
/**
 * Plugin Name: Atlas Content Modeler
 * Plugin URI: https://developers.wpengine.com/
 * Description: Model content for headless WordPress.
 * Author: WP Engine
 * Author URI: https://wpengine.com/
 * Text Domain: atlas-content-modeler
 * Domain Path: /languages
 * Version: 0.3.0
 *
 * @package AtlasContentModeler
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'ATLAS_CONTENT_MODELER_FILE', __FILE__ );
define( 'ATLAS_CONTENT_MODELER_DIR', __DIR__ );
define( 'ATLAS_CONTENT_MODELER_URL', plugin_dir_url( __FILE__ ) );
define( 'ATLAS_CONTENT_MODELER_PATH', plugin_basename( ATLAS_CONTENT_MODELER_FILE ) );
define( 'ATLAS_CONTENT_MODELER_SLUG', dirname( plugin_basename( ATLAS_CONTENT_MODELER_FILE ) ) );

add_action( 'plugins_loaded', 'atlas_content_modeler_loader' );
/**
 * Bootstraps the plugin.
 */
function atlas_content_modeler_loader(): void {
	load_plugin_textdomain( 'atlas_content_modeler', false, __DIR__ . '/languages' );

	require_once __DIR__ . '/includes/publisher/lib/field-functions.php';
	require_once __DIR__ . '/includes/shared-assets/wp_scripts/shared_assets.php';
	require_once __DIR__ . '/includes/settings/settings-callbacks.php';
	require_once __DIR__ . '/includes/content-registration/custom-post-types-registration.php';
	require_once __DIR__ . '/includes/rest-api/rest-api-endpoint-registration.php';
	require_once __DIR__ . '/includes/publisher/class-publisher-form-editing-experience.php';
	require_once ATLAS_CONTENT_MODELER_DIR . '/includes/updates/update-functions.php';
	require_once ATLAS_CONTENT_MODELER_DIR . '/includes/updates/update-callbacks.php';

	$form_editing_experience = new \WPE\AtlasContentModeler\FormEditingExperience();
	$form_editing_experience->bootstrap();
}
