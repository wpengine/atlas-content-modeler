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

	wp_set_script_translations( 'atlas-content-modeler-feedback-banner', 'atlas-content-modeler' );
}
