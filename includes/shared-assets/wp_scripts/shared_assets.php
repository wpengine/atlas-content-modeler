<?php
/**
 * Shared enqueued scripts and styles
 *
 * @package AtlasContentModeler
 */

declare(strict_types=1);

namespace WPE\AtlasContentModeler\Shared;

add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\enqueue_shared_assets' );
/**
 * Loads shared scripts and styles between content modeler apps
 */
function enqueue_shared_assets() {
	$plugin = get_plugin_data( ATLAS_CONTENT_MODELER_FILE );

	wp_register_script(
		'atlas-content-modeler-feedback-banner',
		ATLAS_CONTENT_MODELER_URL . 'includes/shared-assets/js/feedback-banner.js',
		[ 'wp-api-fetch', 'wp-i18n' ],
		$plugin['Version'],
		true
	);

	wp_enqueue_script(
		'atlas-content-modeler-gtag',
		'https://www.googletagmanager.com/gtag/js?id=G-S056CLLZ34',
		[],
		$plugin['Version'],
		false
	);

	wp_register_script(
		'atlas-content-modeler-google-analytics',
		ATLAS_CONTENT_MODELER_URL . 'includes/shared-assets/js/ga-analytics.js',
		[ 'atlas-content-modeler-gtag' ],
		$plugin['Version'],
		true
	);

	wp_set_script_translations( 'atlas-content-modeler-feedback-banner', 'atlas-content-modeler' );
}
