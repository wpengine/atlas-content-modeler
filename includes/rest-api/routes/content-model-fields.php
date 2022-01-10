<?php
/**
 * Registers REST API endpoints for /wpe/atlas/content-model-fields/ routes.
 *
 * @package AtlasContentModeler
 */

declare(strict_types=1);

namespace WPE\AtlasContentModeler\REST_API\ContentModelFields;

use WP_Error;
use WP_REST_Request;
use function WPE\AtlasContentModeler\ContentRegistration\get_registered_content_types;
use function WPE\AtlasContentModeler\ContentRegistration\update_registered_content_types;

add_action( 'rest_api_init', __NAMESPACE__ . '\register_rest_routes' );
/**
 * Registers custom routes with the WP REST API.
 */
function register_rest_routes(): void {
	// Route for updating the properties of multiple fields in the named model.
	register_rest_route(
		'wpe',
		'/atlas/content-model-fields/([a-z0-9_\-]+)',
		[
			'methods'             => 'PATCH',
			'callback'            => __NAMESPACE__ . '\dispatch_patch_content_model_fields',
			'permission_callback' => static function () {
				return current_user_can( 'manage_options' );
			},
		]
	);
}

/**
 * Handles field PATCH requests from the REST API.
 *
 * Expects a 'fields' key in passed params containing a keyed array of
 * field ids, each with an array of field properties to update:
 * [ 'fields' => [ '1616069966137' => [ 'position' => '10000' ] ] ]
 *
 * @param WP_REST_Request $request The REST API request object.
 *
 * @return WP_Error|\WP_HTTP_Response|\WP_REST_Response
 */
function dispatch_patch_content_model_fields( WP_REST_Request $request ) {
	$route  = $request->get_route();
	$slug   = substr( strrchr( $route, '/' ), 1 );
	$params = $request->get_params();

	if ( empty( $params['fields'] ) ) {
		return new WP_Error(
			'acm_missing_fields_data',
			__( 'Expected a fields key with fields to update.', 'atlas-content-modeler' ),
			array( 'status' => 400 )
		);
	}

	$content_types = get_registered_content_types();

	if ( empty( $content_types[ $slug ] ) ) {
		return new WP_Error(
			'acm_invalid_content_model',
			__( 'The specified content model does not exist.', 'atlas-content-modeler' ),
			array( 'status' => 400 )
		);
	}

	// Updates only the field properties passed in the request.
	foreach ( $params['fields'] as $field_id => $field_properties ) {
		foreach ( $field_properties as $property => $value ) {
			if ( empty( $content_types[ $slug ]['fields'][ $field_id ] ) ) {
				continue;
			}

			$content_types[ $slug ]['fields'][ $field_id ][ $property ] = $value;
		}
	}

	$updated = update_registered_content_types( $content_types );

	// @todo: Update relationship names

	return rest_ensure_response(
		[
			'success' => $updated,
		]
	);
}
