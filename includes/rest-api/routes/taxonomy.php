<?php
/**
 * Registers REST API endpoints for /wpe/atlas/taxonomy/ routes.
 *
 * @package AtlasContentModeler
 */

declare(strict_types=1);

namespace WPE\AtlasContentModeler\REST_API\Taxonomy;

use WP_Error;
use WP_REST_Request;
use function WPE\AtlasContentModeler\REST_API\Taxonomies\save_taxonomy;

add_action( 'rest_api_init', __NAMESPACE__ . '\register_rest_routes' );
/**
 * Registers custom routes with the WP REST API.
 */
function register_rest_routes(): void {
	// Route for creating (POST) or updating (PUT) a taxonomy.
	register_rest_route(
		'wpe',
		'/atlas/taxonomy',
		[
			'methods'             => [ 'POST', 'PUT' ],
			'callback'            => __NAMESPACE__ . '\dispatch_update_taxonomy',
			'permission_callback' => static function () {
				return current_user_can( 'manage_options' );
			},
		]
	);

	// Route for deleting a taxonomy.
	register_rest_route(
		'wpe',
		'/atlas/taxonomy/(?P<taxonomy>[\\w-]+)',
		[
			'methods'             => 'DELETE',
			'callback'            => __NAMESPACE__ . '\dispatch_delete_taxonomy',
			'permission_callback' => static function () {
				return current_user_can( 'manage_options' );
			},
		]
	);
}

/**
 * Handles taxonomy POST and PUT requests.
 *
 * @param WP_REST_Request $request The REST API request object.
 *
 * @return WP_Error|\WP_HTTP_Response|\WP_REST_Response
 */
function dispatch_update_taxonomy( WP_REST_Request $request ) {
	$params = $request->get_params();

	unset( $params['_locale'] ); // Sent by wp.apiFetch but not needed.

	$is_update = $request->get_method() === 'PUT';
	$taxonomy  = save_taxonomy( $params, $is_update );

	if ( is_wp_error( $taxonomy ) ) {
		return new WP_Error(
			$taxonomy->get_error_code(),
			$taxonomy->get_error_message(),
			[ 'status' => 400 ]
		);
	}

	return rest_ensure_response(
		[
			'success'  => true,
			'taxonomy' => $taxonomy,
		]
	);
}

/**
 * Handles taxonomy DELETE requests from the REST API.
 *
 * @param WP_REST_Request $request The REST API request object.
 *
 * @return WP_Error|\WP_HTTP_Response|\WP_REST_Response
 */
function dispatch_delete_taxonomy( WP_REST_Request $request ) {
	$slug           = $request->get_param( 'taxonomy' );
	$acm_taxonomies = get_option( 'atlas_content_modeler_taxonomies', array() );

	if ( empty( $acm_taxonomies[ $slug ] ) ) {
		return new WP_Error(
			'acm_invalid_taxonomy',
			esc_html__( 'Invalid ACM taxonomy.', 'atlas-content-modeler' ),
			[ 'status' => 404 ]
		);
	}

	unset( $acm_taxonomies[ $slug ] );

	$updated = update_option( 'atlas_content_modeler_taxonomies', $acm_taxonomies );

	return rest_ensure_response(
		[
			'success' => $updated,
		]
	);
}
