<?php
/**
 * Registers REST API endpoints for /wpe/atlas/content-model/ routes.
 *
 * @package AtlasContentModeler
 */

declare(strict_types=1);

namespace WPE\AtlasContentModeler\REST_API\ContentModel;

use WP_Error;
use WP_REST_Request;
use function WPE\AtlasContentModeler\ContentRegistration\get_registered_content_types;
use function WPE\AtlasContentModeler\REST_API\Models\create_model;
use function WPE\AtlasContentModeler\REST_API\Models\update_model;
use function WPE\AtlasContentModeler\REST_API\Models\delete_model;
use function WPE\AtlasContentModeler\REST_API\Fields\cleanup_detached_relationship_fields;
use function WPE\AtlasContentModeler\REST_API\Fields\cleanup_detached_relationship_references;

add_action( 'rest_api_init', __NAMESPACE__ . '\register_rest_routes' );
/**
 * Registers custom routes with the WP REST API.
 */
function register_rest_routes(): void {
	// Route for retrieving a single content type.
	register_rest_route(
		'wpe',
		'/atlas/content-model/([a-z0-9_\-]+)',
		[
			'methods'             => 'GET',
			'callback'            => __NAMESPACE__ . '\dispatch_get_content_model',
			'permission_callback' => static function () {
				return current_user_can( 'manage_options' );
			},
		]
	);

	// Route for creating a single content type.
	register_rest_route(
		'wpe',
		'/atlas/content-model',
		[
			'methods'             => 'POST',
			'callback'            => __NAMESPACE__ . '\dispatch_create_content_model',
			'permission_callback' => static function () {
				return current_user_can( 'manage_options' );
			},
		]
	);

	// Route for updating a single content type.
	register_rest_route(
		'wpe',
		'/atlas/content-model/([a-z0-9_\-]+)',
		[
			'methods'             => 'PATCH',
			'callback'            => __NAMESPACE__ . '\dispatch_update_content_model',
			'permission_callback' => static function () {
				return current_user_can( 'manage_options' );
			},
		]
	);

	// Route for deleting a single content type.
	register_rest_route(
		'wpe',
		'/atlas/content-model/([a-z0-9_\-]+)',
		[
			'methods'             => 'DELETE',
			'callback'            => __NAMESPACE__ . '\dispatch_delete_content_model',
			'permission_callback' => static function () {
				return current_user_can( 'manage_options' );
			},
		]
	);
}

/**
 * Handles model GET requests from the REST API.
 *
 * @param WP_REST_Request $request The REST API request object.
 *
 * @return WP_Error|\WP_HTTP_Response|\WP_REST_Response
 */
function dispatch_get_content_model( WP_REST_Request $request ) {
	$route         = $request->get_route();
	$slug          = substr( strrchr( $route, '/' ), 1 );
	$content_types = get_registered_content_types();

	if ( empty( $content_types[ $slug ] ) ) {
		return rest_ensure_response(
			[
				'success' => false,
				'errors'  => esc_html__( 'The requested content model does not exist.', 'atlas-content-modeler' ),
			]
		);
	}

	return rest_ensure_response(
		[
			'data' => $content_types[ $slug ],
		]
	);
}

/**
 * Handles model POST requests from the REST API.
 *
 * @param WP_REST_Request $request The REST API request object.
 *
 * @return WP_Error|\WP_HTTP_Response|\WP_REST_Response
 */
function dispatch_create_content_model( WP_REST_Request $request ) {
	$params = $request->get_params();

	unset( $params['_locale'] ); // Sent by wp.apiFetch but not needed.

	$model = create_model( $params['slug'], $params );

	if ( is_wp_error( $model ) ) {
		return new WP_Error(
			$model->get_error_code(),
			$model->get_error_message(),
			[ 'status' => 400 ]
		);
	}

	return rest_ensure_response(
		[
			'success' => true,
			'model'   => $model,
		]
	);
}

/**
 * Handles model PATCH requests from the REST API to update an existing model.
 *
 * @param WP_REST_Request $request The REST API request object.
 *
 * @return WP_Error|\WP_HTTP_Response|\WP_REST_Response
 */
function dispatch_update_content_model( WP_REST_Request $request ) {
	$route         = $request->get_route();
	$slug          = substr( strrchr( $route, '/' ), 1 );
	$params        = $request->get_params();
	$content_types = get_registered_content_types();

	if ( empty( $content_types[ $slug ] ) ) {
		return new WP_Error(
			'acm_invalid_content_model_id',
			__( 'Invalid content model ID.', 'atlas-content-modeler' ),
			[ 'status' => 400 ]
		);
	}

	unset( $params['_locale'] ); // Sent by wp.apiFetch but not needed.

	$updated = update_model( $slug, $params );

	if ( is_wp_error( $updated ) ) {
		return new WP_Error(
			$updated->get_error_code(),
			$updated->get_error_message(),
			[ 'status' => 400 ]
		);
	}

	return rest_ensure_response(
		[
			'success' => $updated,
		]
	);
}

/**
 * Handles model DELETE requests from the REST API.
 *
 * @param WP_REST_Request $request The REST API request object.
 *
 * @return WP_Error|\WP_HTTP_Response|\WP_REST_Response
 */
function dispatch_delete_content_model( WP_REST_Request $request ) {
	$route = $request->get_route();
	$slug  = substr( strrchr( $route, '/' ), 1 );

	$model = delete_model( $slug );

	if ( is_wp_error( $model ) ) {
		return $model;
	}

	cleanup_detached_relationship_fields( $slug );
	cleanup_detached_relationship_references( $slug );

	return rest_ensure_response(
		[
			'success' => true,
			'model'   => $model,
		]
	);
}
