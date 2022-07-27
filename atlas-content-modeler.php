<?php
/**
 * Plugin Name: Atlas Content Modeler
 * Plugin URI: https://developers.wpengine.com/
 * Description: Model content for headless WordPress.
 * Author: WP Engine
 * Author URI: https://wpengine.com/
 * Text Domain: atlas-content-modeler
 * Domain Path: /languages
 * Version: 0.19.2
 * Requires at least: 5.7
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
	$composer_autoloader = __DIR__ . '/vendor/autoload.php';

	if ( file_exists( $composer_autoloader ) ) {
		include_once $composer_autoloader;
	}

	load_plugin_textdomain( 'atlas_content_modeler', false, __DIR__ . '/languages' );

	$plugin_files = array(
		'publisher/lib/field-functions.php',
		'shared-assets/wp_scripts/shared_assets.php',
		'settings/settings-callbacks.php',
		'content-registration/custom-post-types-registration.php',
		'content-registration/register-taxonomies.php',
		'content-registration/graphql-mutations.php',
		'content-registration/class-wpe-rest-posts-controller.php',
		'rest-api/init-rest-api.php',
		'publisher/class-publisher-form-editing-experience.php',
		'updates/version-updates.php',
		'content-connect/autoload.php',
		'blueprints/import.php',
		'blueprints/fetch.php',
		'blueprints/export.php',
		'api/crud-functions.php',
		'api/validation-functions.php',
		'api/utility-functions.php',
		'class-validation-exception.php',
		'class-wp-error.php',
	);

	foreach ( $plugin_files as $file ) {
		include_once ATLAS_CONTENT_MODELER_INCLUDES_DIR . $file;
	}

	if ( defined( 'WP_CLI' ) && WP_CLI ) {
		include_once ATLAS_CONTENT_MODELER_INCLUDES_DIR . '/wp-cli/class-blueprint.php';
		\WP_CLI::add_command( 'acm blueprint', 'WPE\AtlasContentModeler\WP_CLI\Blueprint' );
	}

	\WPE\AtlasContentModeler\VersionUpdater\update_plugin();

	new \WPE\AtlasContentModeler\FormEditingExperience();

	// Bootstrap relationships library.
	acm_content_connect_autoloader();
	\WPE\AtlasContentModeler\ContentConnect\Plugin::instance();
}

/**
 * Determines if opt-in usage tracking is enabled.
 *
 * @return bool True if enabled, false if not.
 */
function acm_usage_tracking_enabled(): bool {
	return (bool) get_option( 'atlas_content_modeler_usage_tracking', false );
}
