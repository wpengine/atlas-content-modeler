<?php
/**
 * Plugin Name: Atlas Content Modeler
 * Plugin URI: https://developers.wpengine.com/
 * Description: Model content for headless WordPress.
 * Author: WP Engine
 * Author URI: https://wpengine.com/
 * Text Domain: wpe-content-model
 * Domain Path: /languages
 * Version: 0.2.0
 *
 * @package WPE_Content_Model
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WPE_CONTENT_MODEL_FILE', __FILE__ );
define( 'WPE_CONTENT_MODEL_DIR', __DIR__ );
define( 'WPE_CONTENT_MODEL_URL', plugin_dir_url( __FILE__ ) );
define( 'WPE_CONTENT_MODEL_PATH', plugin_basename( WPE_CONTENT_MODEL_FILE ) );
define( 'WPE_CONTENT_MODEL_SLUG', dirname( plugin_basename( WPE_CONTENT_MODEL_FILE ) ) );

add_action( 'plugins_loaded', 'wpe_content_model_loader' );
/**
 * Bootstraps the plugin.
 */
function wpe_content_model_loader(): void {
	require_once __DIR__ . '/includes/publisher/lib/field-functions.php';
	require_once __DIR__ . '/includes/settings/settings-callbacks.php';
	require_once __DIR__ . '/includes/content-registration/custom-post-types-registration.php';
	require_once __DIR__ . '/includes/rest-api/rest-api-endpoint-registration.php';
	require_once __DIR__ . '/includes/publisher/class-publisher-form-editing-experience.php';
	require_once WPE_CONTENT_MODEL_DIR . '/includes/updates/update-functions.php';
	require_once WPE_CONTENT_MODEL_DIR . '/includes/updates/update-callbacks.php';

	$form_editing_experience = new \WPE\AtlasContentModeler\FormEditingExperience();
	$form_editing_experience->bootstrap();
}
