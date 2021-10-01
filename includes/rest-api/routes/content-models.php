<?php
/**
 * Registers REST API endpoints for /wpe/atlas/content-models/ routes.
 *
 * @package AtlasContentModeler
 */

declare(strict_types=1);

namespace WPE\AtlasContentModeler\REST_API\ContentModels;

use function WPE\AtlasContentModeler\ContentRegistration\get_registered_content_types;

add_action( 'rest_api_init', __NAMESPACE__ . '\register_rest_routes' );
/**
 * Registers custom routes with the WP REST API.
 */
function register_rest_routes(): void {
	// Route for retrieving all models.
	register_rest_route(
		'wpe',
		'/atlas/content-models/',
		[
			'methods'             => 'GET',
			'callback'            => static function () {
				return get_registered_content_types();
			},
			'permission_callback' => static function () {
				return current_user_can( 'manage_options' );
			},
		]
	);
}
