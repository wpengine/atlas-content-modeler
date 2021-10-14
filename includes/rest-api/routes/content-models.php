<?php
/**
 * Registers REST API endpoints for /wpe/atlas/content-models/ routes.
 *
 * @package AtlasContentModeler
 */

declare(strict_types=1);

namespace WPE\AtlasContentModeler\REST_API\ContentModels;

use WP_Error;
use WP_REST_Request;
use function WPE\AtlasContentModeler\REST_API\Models\create_models;
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

	// Route for creating multiple models.
	register_rest_route(
		'wpe',
		'/atlas/content-models/',
		[
			'methods'             => 'PUT',
			'callback'            => __NAMESPACE__ . '\dispatch_put_content_models',
			'permission_callback' => static function () {
				return current_user_can( 'manage_options' );
			},
		]
	);
}

/**
 * Handles `/wpe/atlas/content-models/` PUT requests.
 *
 * @param WP_REST_Request $request The REST API request object.
 *
 * @return WP_Error|\WP_HTTP_Response|\WP_REST_Response
 */
function dispatch_put_content_models( WP_REST_Request $request ) {
	$params = $request->get_params();
	unset( $params['_locale'] ); // Sent by wp.apiFetch but not needed.
	$result = create_models( $params );

	if ( is_wp_error( $result ) ) {
		return $result;
	}

	return rest_ensure_response(
		[
			'success' => true,
		]
	);
}
