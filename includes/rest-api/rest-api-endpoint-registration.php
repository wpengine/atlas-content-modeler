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

	// Route for updating a single content type.
	register_rest_route(
		'wpe',
		'/content-model/([A-Za-z0-9])\w+/',
		[
			'methods'             => 'PATCH',
			'callback'            => __NAMESPACE__ . '\dispatch_update_content_model',
			'permission_callback' => static function () {
				return current_user_can( 'manage_options' );
			},
		]
	);

	// Route for creating a content model field (POST) or updating one (PUT).
	register_rest_route(
		'wpe',
		'/content-model-field',
		[
			'methods'             => [ 'POST', 'PUT' ],
			'callback'            => __NAMESPACE__ . '\dispatch_update_content_model_field',
			'permission_callback' => static function () {
				return current_user_can( 'manage_options' );
			},
		]
	);

	// Route for updating the properties of multiple fields in the named model.
	register_rest_route(
		'wpe',
		'/content-model-fields/([A-Za-z])\w+',
		[
			'methods'             => 'PATCH',
			'callback'            => __NAMESPACE__ . '\dispatch_patch_content_model_fields',
			'permission_callback' => static function () {
				return current_user_can( 'manage_options' );
			},
		]
	);

	// Route for deleting a content model field.
	register_rest_route(
		'wpe',
		'/content-model-field/([A-Za-z0-9])\w+/',
		[
			'methods'             => 'DELETE',
			'callback'            => __NAMESPACE__ . '\dispatch_delete_content_model_field',
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
	$model = create_model( $params['slug'], $params );

	if ( is_wp_error( $model ) ) {
		return new WP_Error(
			$model->get_error_code(),
			$model->get_error_message(),
			[ 'status' => 400 ]
		);
	}

	$models = get_registered_content_types();

	return rest_ensure_response(
		[
			'success' => true,
			'model'   => $models[ $params['slug'] ],
		]
	);
}

/**
 * Handles requests from the REST API to create (POST) or update (PUT) a field.
 *
 * @param WP_REST_Request $request The REST API request object.
 *
 * @return WP_Error|\WP_HTTP_Response|\WP_REST_Response
 */
function dispatch_update_content_model_field( WP_REST_Request $request ) {
	$params        = $request->get_params();
	$content_types = get_registered_content_types();

	if ( ! isset( $params['model'] ) || empty( $content_types[ $params['model'] ] ) ) {
		return new WP_Error(
			'wpe_invalid_content_model',
			'The specified content model does not exist.',
			array( 'status' => 400 )
		);
	}

	if (
		content_model_field_exists(
			$params['slug'],
			$params['id'],
			$content_types[ $params['model'] ]
		)
	) {
		return new WP_Error(
			'wpe_duplicate_content_model_field_id',
			'Another field in this model has the same API identifier.',
			array( 'status' => 400 )
		);
	}

	/**
	 * Remove isTitle from all fields if isTitle is set on this field. Only one
	 * field can be used as the entry title.
	 */
	if ( isset( $params['isTitle'] ) && $params['isTitle'] === true ) {
		foreach ( $content_types[ $params['model'] ]['fields'] as $field_id => $field_properties ) {
			unset( $content_types[ $params['model'] ]['fields'][ $field_id ]['isTitle'] );
		}
	}

	$values_to_save = shape_field_args( $params );

	$content_types[ $params['model'] ]['fields'][ $params['id'] ] = $values_to_save;

	$updated = update_registered_content_types( $content_types );

	return rest_ensure_response(
		[
			'success' => $updated,
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
			'wpe_missing_fields_data',
			'Expected a fields key with fields to update.',
			array( 'status' => 400 )
		);
	}

	$content_types = get_registered_content_types();

	if ( empty( $content_types[ $slug ] ) ) {
		return new WP_Error(
			'wpe_invalid_content_model',
			'The specified content model does not exist.',
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

	return rest_ensure_response(
		[
			'success' => $updated,
		]
	);
}

/**
 * Handles model field DELETE requests from the REST API to delete an existing field.
 *
 * @param WP_REST_Request $request The REST API request object.
 *
 * @return WP_Error|\WP_HTTP_Response|\WP_REST_Response
 */
function dispatch_delete_content_model_field( WP_REST_Request $request ) {
	$route         = $request->get_route();
	$field_id      = substr( strrchr( $route, '/' ), 1 );
	$params        = $request->get_params();
	$model         = $params['model'] ?? false;
	$content_types = get_registered_content_types();

	if ( empty( $model ) || empty( $content_types[ $model ] ) ) {
		return new WP_Error(
			'wpe_invalid_content_model',
			'You must specify a valid model.',
			[ 'status' => 400 ]
		);
	}

	if ( ! isset( $content_types[ $model ]['fields'][ $field_id ] ) ) {
		return new WP_Error(
			'wpe_invalid_content_model_field_id',
			'Invalid field ID.',
			[ 'status' => 400 ]
		);
	}

	// Delete child fields (and descendents) of deleted repeater fields.
	if (
		array_key_exists( 'type', $content_types[ $model ]['fields'][ $field_id ] )
		&& 'repeater' === $content_types[ $model ]['fields'][ $field_id ]['type']
	) {
		$child_field_ids = get_children_of_field( (int) $field_id, $content_types[ $model ]['fields'] );

		foreach ( $child_field_ids as $id ) {
			unset( $content_types[ $model ]['fields'][ $id ] );
		}
	}

	unset( $content_types[ $model ]['fields'][ $field_id ] );

	return rest_ensure_response(
		[
			'success' => update_registered_content_types( $content_types ),
		]
	);
}

/**
 * Shapes the field arguments array.
 *
 * @param array $args The field arguments.
 *
 * @return array
 */
function shape_field_args( array $args ): array {
	$defaults = [
		'show_in_rest'    => true,
		'show_in_graphql' => true,
	];

	$merged = array_merge( $defaults, $args );

	unset( $merged['_locale'] ); // Sent by wp.apiFetch but not needed.
	unset( $merged['model'] ); // The field is stored in the fields property of its model.

	return $merged;
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
			'wpe_invalid_content_model_id',
			'Invalid content model ID.',
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
	if ( empty( $post_type_slug ) ) {
		return new WP_Error(
			'wpe_content_model_invalid_id',
			'Please provide a valid API Identifier.',
			[ 'status' => 400 ]
		);
	}

	$existing_content_types = get_post_types();

	$content_types = get_registered_content_types();

	if ( ! empty( $content_types[ $post_type_slug ] ) || array_key_exists( $post_type_slug, $existing_content_types ) ) {
		return new WP_Error(
			'wpe_content_model_already_exists',
			'A content model with this API Identifier already exists.',
			[ 'status' => 400 ]
		);
	}

	if ( empty( $args['singular'] ) || empty( $args['plural'] ) ) {
		return new WP_Error(
			'wpe_content_model_invalid_labels',
			'Please provide singular and plural labels when creating a content model.',
			[ 'status' => 400 ]
		);
	}

	// @todo validate field types
	try {
		$args = wp_parse_args( $args, generate_custom_post_type_args( $args ) );
	} catch ( \InvalidArgumentException $exception ) {
		return new WP_Error(
			[
				'invalid-args',
				$exception->getMessage(),
				[ 'status' => 400 ],
			]
		);
	}

	$content_types[ $post_type_slug ] = $args;

	$created = update_registered_content_types( $content_types );

	if ( ! $created ) {
		return new WP_Error( 'model-not-created', esc_html__( 'Model not created. Reason unknown.', 'wpe-content-model' ) );
	}

	return true;
}

/**
 * Updates the specified content model.
 *
 * @param string $post_type_slug The post type slug.
 * @param array  $args Model arguments.
 *
 * @return bool|WP_Error
 */
function update_model( string $post_type_slug, array $args ) {
	if ( empty( $post_type_slug ) ) {
		return new WP_Error(
			'wpe_invalid_content_model_id',
			'Invalid content model ID.'
		);
	}

	if ( empty( $args['singular'] ) || empty( $args['plural'] ) ) {
		return new WP_Error(
			'wpe_invalid_content_model_arguments',
			'Please provide singular and plural labels when creating a content model.'
		);
	}

	$content_types = get_registered_content_types();
	if ( empty( $content_types[ $post_type_slug ] ) ) {
		return new WP_Error(
			'wpe_invalid_content_model_id',
			'Invalid content model ID.'
		);
	}

	$args['name']          = $args['plural'];
	$args['singular_name'] = $args['singular'];

	$new_args = wp_parse_args( $args, $content_types[ $post_type_slug ] );

	$content_types[ $post_type_slug ] = $new_args;

	$updated = update_registered_content_types( $content_types );

	if ( ! $updated ) {
		return new WP_Error(
			'model-not-updated',
			'Model not updated. Reason unknown.'
		);
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

/**
 * Checks if a duplicate field identifier (slug) exists in the content model.
 *
 * @param string $slug  The current field slug.
 * @param string $id    The current field id.
 * @param array  $model The content model to check for duplicate slugs.
 * @return bool
 */
function content_model_field_exists( string $slug, string $id, array $model ): bool {
	if ( ! isset( $model['fields'] ) ) {
		return false;
	}

	foreach ( $model['fields'] as $field ) {
		// Exclude the field being edited from slug collision checks.
		if ( $field['id'] === $id ) {
			continue;
		}
		if ( $field['slug'] === $slug ) {
			return true;
		}
	}

	return false;
}

/**
 * Gets children (and their descendents) of the field with `$id` so that these
 * can be deleted when a parent field is removed.
 *
 * @param int   $id     The parent id to look for children.
 * @param array $fields Fields to search.
 * @return array The ids of children and descendents.
 */
function get_children_of_field( int $id, array $fields ): array {
	$children = [];

	foreach ( $fields as $field_id => $field ) {
		if (
			array_key_exists( 'parent', $field )
			&& (int) $field['parent'] === $id
		) {
			$children[] = (int) $field_id;
			$children   = array_merge( $children, get_children_of_field( $field_id, $fields ) );
		}
	}

	return $children;
}
