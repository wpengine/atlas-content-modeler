<?php
/**
 * Settings related callbacks.
 *
 * @package WPE_Content_Model
 */

declare(strict_types=1);

namespace WPE\ContentModel\Settings;

use function WPE\ContentModel\ContentRegistration\get_registered_content_types;

add_action( 'admin_menu', __NAMESPACE__ . '\register_admin_menu_page' );
/**
 * Registers the wp-admin menu page.
 */
function register_admin_menu_page(): void {
	$icon = require __DIR__ . '/views/admin-menu-icon.php';
	add_menu_page(
		esc_html__( 'Content Model', 'wpe-content-model' ),
		esc_html__( 'Content Model', 'wpe-content-model' ),
		'manage_options',
		'wpe-content-model',
		__NAMESPACE__ . '\render_admin_menu_page',
		$icon
	);
}

/**
 * Renders the wp-admin menu page.
 */
function render_admin_menu_page() {
	include_once __DIR__ . '/views/admin-menu-page.php';
}

add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\enqueue_settings_assets' );
/**
 * Registers and enqueues admin scripts and styles.
 *
 * @param string $hook The current admin page.
 */
function enqueue_settings_assets( $hook ) {
	$plugin = get_plugin_data( WPE_CONTENT_MODEL_FILE );

	wp_register_script(
		'wpe-content-model-app',
		WPE_CONTENT_MODEL_URL . 'includes/settings/dist/index.js',
		[ 'wp-api', 'wp-api-fetch', 'wp-a11y', 'react' ],
		$plugin['Version'],
		true
	);

	wp_localize_script(
		'wpe-content-model-app',
		'wpeContentModel',
		array(
			'initialState' => get_registered_content_types(),
		)
	);

	wp_register_style(
		'wpe-content-model-fonts',
		'https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,400;0,600;0,700;1,400&display=swap',
		[],
		$plugin['Version']
	);

	wp_register_style(
		'wpe-content-model-app-styles',
		WPE_CONTENT_MODEL_URL . 'includes/settings/dist/index.css',
		[ 'wpe-content-model-fonts' ],
		$plugin['Version']
	);

	if ( 'toplevel_page_wpe-content-model' === $hook ) {
		wp_enqueue_script( 'wpe-content-model-app' );
		wp_enqueue_style( 'wpe-content-model-app-styles' );
	}
}
