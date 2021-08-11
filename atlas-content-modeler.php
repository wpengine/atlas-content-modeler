<?php
/**
 * Plugin Name: Atlas Content Modeler
 * Plugin URI: https://developers.wpengine.com/
 * Description: Model content for headless WordPress.
 * Author: WP Engine
 * Author URI: https://wpengine.com/
 * Text Domain: atlas-content-modeler
 * Domain Path: /languages
 * Version: 0.4.2
 * Requires at least: 5.2
 * Requires PHP: 7.2
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package AtlasContentModeler
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'ATLAS_CONTENT_MODELER_FILE', __FILE__ );
define( 'ATLAS_CONTENT_MODELER_INCLUDES_DIR', __DIR__ . '/includes/' );
define( 'ATLAS_CONTENT_MODELER_URL', plugin_dir_url( __FILE__ ) );
define( 'ATLAS_CONTENT_MODELER_PATH', plugin_basename( ATLAS_CONTENT_MODELER_FILE ) );
define( 'ATLAS_CONTENT_MODELER_SLUG', dirname( plugin_basename( ATLAS_CONTENT_MODELER_FILE ) ) );

add_action( 'plugins_loaded', 'atlas_content_modeler_loader' );
/**
 * Bootstraps the plugin.
 */
function atlas_content_modeler_loader(): void {
	load_plugin_textdomain( 'atlas_content_modeler', false, __DIR__ . '/languages' );

	$plugin_files = array(
		'publisher/lib/field-functions.php',
		'shared-assets/wp_scripts/shared_assets.php',
		'settings/settings-callbacks.php',
		'content-registration/custom-post-types-registration.php',
		'content-registration/class-wpe-rest-posts-controller.php',
		'rest-api/rest-api-endpoint-registration.php',
		'publisher/class-publisher-form-editing-experience.php',
		'updates/update-functions.php',
		'updates/update-callbacks.php',
	);

	foreach ( $plugin_files as $file ) {
			include_once ATLAS_CONTENT_MODELER_INCLUDES_DIR . $file;
	}

	$form_editing_experience = new \WPE\AtlasContentModeler\FormEditingExperience();
	$form_editing_experience->bootstrap();
}
