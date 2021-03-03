<?php
/**
 * Registers custom REST API endpoints for content modeling.
 *
 * @package WPE_Content_Model
 */

declare(strict_types=1);

namespace WPE\ContentModel\REST_API;

use WP_Error;
use WP_REST_Request;
use function WPE\ContentModel\ContentRegistration\generate_custom_post_type_args;
use function WPE\ContentModel\ContentRegistration\get_registered_content_types;
use function WPE\ContentModel\ContentRegistration\update_registered_content_types;

add_action( 'rest_api_init', __NAMESPACE__ . '\register_rest_routes' );
/**
 * Registers custom routes with the WP REST API.
 */
function register_rest_routes(): void {
	// @todo Route for updating a single content type.

	// Route for retrieving a single content type.
	register_rest_route(
		'wpe',
		'/content-model/([A-Za-z])\w+/',
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
		'/content-model',
		[
			'methods'             => 'POST',
			'callback'            => __NAMESPACE__ . '\dispatch_create_content_model',
			'permission_callback' => static function () {
				return current_user_can( 'manage_options' );
			},
		]
	);

	// Route for deleting a single content type.
	register_rest_route(
		'wpe',
		'/content-model/([A-Za-z])\w+/',
		[
			'methods'             => 'DELETE',
			'callback'            => __NAMESPACE__ . '\dispatch_delete_model',
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
				'errors'  => esc_html__( 'The requested content model does not exist.', 'wpe-content-model' ),
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

	// @todo simplify create_model() signature to single array?
	$model = create_model( $params['postTypeSlug'], $params );

	if ( is_wp_error( $model ) ) {
		return rest_ensure_response(
			[
				'success' => false,
				'errors'  => $model->get_all_error_data(),
			]
		);
	}

	return rest_ensure_response( [ 'success' => true ] );
}

/**
 * Handles model DELETE requests from the REST API.
 *
 * @param WP_REST_Request $request The REST API request object.
 *
 * @return WP_Error|\WP_HTTP_Response|\WP_REST_Response
 */
function dispatch_delete_model( WP_REST_Request $request ) {
	$route         = $request->get_route();
	$slug          = substr( strrchr( $route, '/' ), 1 );
	$content_types = get_registered_content_types();

	if ( empty( $content_types[ $slug ] ) ) {
		return rest_ensure_response(
			[
				'success' => false,
				'errors'  => esc_html__( 'The specified content model does not exist.', 'wpe-content-model' ),
			]
		);
	}

	unset( $content_types[ $slug ] );

	$updated = update_registered_content_types( $content_types );

	return rest_ensure_response(
		[
			'success' => $updated,
		]
	);
}

/**
 * Creates a custom content model.
 *
 * @param string $post_type_slug The post type slug.
 * @param array  $args Model arguments.
 *
 * @return WP_Error|bool
 */
function create_model( string $post_type_slug, array $args ) {
	$errors = [];

	if ( empty( $post_type_slug ) ) {
		$errors[] = [
			'field'   => 'postTypeSlug',
			'message' => esc_html__( 'Please provide a postTypeSlug.', 'wpe-content-model' ),
		];
	}

	if ( empty( $args['singular'] ) || empty( $args['plural'] ) ) {
		$errors[] = [
			'field'   => 'labels',
			'message' => esc_html__( 'Please provide singular and plural labels when creating a content model.', 'wpe-content-model' ),
		];
	}

	$content_types = get_registered_content_types();
	if ( ! empty( $content_types[ $post_type_slug ] ) ) {
		$errors[] = [
			'code'    => 'already-exists',
			'message' => esc_html__( 'Content model already exists. Please update the existing one or delete it and recreate.', 'wpe-content-model' ),
		];
	}

	// @todo validate field types
	try {
		$args = wp_parse_args( $args, generate_custom_post_type_args( $args ) );
	} catch ( \InvalidArgumentException $exception ) {
		$errors[] = [
			'code'    => 'invalid-args',
			'message' => $exception->getMessage(),
		];
	}

	if ( ! empty( $errors ) ) {
		return new WP_Error( 'model-not-created', esc_html__( 'Model not created.', 'wpe-content-model' ), [ 'errors' => $errors ] );
	}

	$content_types[ $post_type_slug ] = $args;

	$created = update_registered_content_types( $content_types );

	if ( ! $created ) {
		return new WP_Error( 'model-not-created', esc_html__( 'Model not created. Reason unknown.', 'wpe-content-model' ) );
	}

	return true;
}

/**
 * Deletes the specified model from the database.
 *
 * @param string $post_type_slug The post type slug.
 *
 * @return bool|WP_Error WP_Error if invalid parameters passed, otherwise true/false.
 */
function delete_model( string $post_type_slug ) {
	if ( empty( $post_type_slug ) ) {
		return new WP_Error( 'model-not-deleted', esc_html__( 'Please provide a post-type-slug.', 'wpe-content-model' ) );
	}

	$content_types = get_registered_content_types();

	if ( empty( $content_types[ $post_type_slug ] ) ) {
		return new WP_Error( 'model-not-deleted', esc_html__( 'Content type does not exist.', 'wpe-content-model' ) );
	}

	unset( $content_types[ $post_type_slug ] );
	return update_registered_content_types( $content_types );
}
