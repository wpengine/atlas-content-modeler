<?php
/**
 * Registers custom REST API endpoints for content modeling.
 *
 * @package AtlasContentModeler
 */

declare(strict_types=1);

namespace WPE\AtlasContentModeler\REST_API;

use WP_Error;
use WP_REST_Request;
use function WPE\AtlasContentModeler\ContentRegistration\generate_custom_post_type_args;
use function WPE\AtlasContentModeler\ContentRegistration\get_registered_content_types;
use function WPE\AtlasContentModeler\ContentRegistration\update_registered_content_types;

add_action( 'rest_api_init', __NAMESPACE__ . '\register_rest_routes' );

/**
 * Registers custom routes with the WP REST API.
 */
function register_rest_routes(): void {
	// Route for retrieving a single content type.
	register_rest_route(
		'wpe',
		'/atlas/content-model/([A-Za-z])\w+/',
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
		'/atlas/content-model/([A-Za-z0-9])\w+/',
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
		'/atlas/content-model-field',
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
		'/atlas/content-model-fields/([A-Za-z])\w+',
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
		'/atlas/content-model-field/([A-Za-z0-9])\w+/',
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
		'/atlas/content-model/([A-Za-z])\w+/',
		[
			'methods'             => 'DELETE',
			'callback'            => __NAMESPACE__ . '\dispatch_delete_model',
			'permission_callback' => static function () {
				return current_user_can( 'manage_options' );
			},
		]
	);

	// Route for setting feedback banner transient.
	register_rest_route(
		'wpe',
		'/atlas/dismiss-feedback-banner',
		[
			'methods'             => 'POST',
			'callback'            => __NAMESPACE__ . '\dispatch_dismiss_feedback_banner',
			'permission_callback' => static function () {
				return current_user_can( 'manage_options' );
			},
		]
	);

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
		'/atlas/taxonomy/([A-Za-z])\w+/',
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
			__( 'The specified content model does not exist.', 'atlas-content-modeler' ),
			array( 'status' => 400 )
		);
	}

	if ( isset( $params['type'] ) && $params['type'] === 'multipleChoice' && ! $params['choices'] ) {
		return new WP_Error(
			'wpe_invalid_multi_options',
			'Multiple Choice update failed. Options need to be created before updating a Multiple Choice field.',
			array( 'status' => 400 )
		);
	}
	if ( isset( $params['type'] ) && $params['type'] === 'multipleChoice' && $params['choices'] ) {
		$options_index = -1;
		$problem_index = [];
		foreach ( $params['choices'] as $choice ) {
			++$options_index;
			if ( $choice['name'] === '' ) {
				$problem_index[] = $options_index;
			}
		}
		if ( $problem_index ) {
			$problem_error_name_blank = new WP_Error(
				'wpe_option_name_undefined',
				'Multiple Choice Field update failed, please set a name for your choice before saving.',
				array( 'status' => 400 )
			);
			$problem_error_name_blank->add( 'problem_index', $problem_index );
			return $problem_error_name_blank;
		}
	}

	if ( isset( $params['type'] ) && $params['type'] === 'multipleChoice' && $params['choices'] ) {
		$options_name_index = -1;
		$problem_name_index = [];
		foreach ( $params['choices'] as $choice ) {
			++$options_name_index;
			if ( content_model_multi_option_exists( $params['choices'], $choice['name'], $options_name_index ) ) {
				$problem_name_index[] = $options_name_index;
			}
		}
		if ( $problem_name_index ) {
			$problem_duplicate_name = new WP_Error(
				'wpe_duplicate_content_model_multi_option_id',
				'Another option in this field has the same API identifier.',
				array( 'status' => 400 )
			);
			$problem_duplicate_name->add( 'problem_name_index', $problem_name_index );
			return $problem_duplicate_name;
		}
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
			__( 'Another field in this model has the same API identifier.', 'atlas-content-modeler' ),
			array( 'status' => 400 )
		);
	}

	/**
	 * Remove isTitle from all fields if isTitle is set on this field. Only one
	 * field can be used as the entry title.
	 */
	if ( isset( $params['isTitle'] ) && $params['isTitle'] === true && ! empty( $content_types[ $params['model'] ]['fields'] ) ) {
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
			__( 'Expected a fields key with fields to update.', 'atlas-content-modeler' ),
			array( 'status' => 400 )
		);
	}

	$content_types = get_registered_content_types();

	if ( empty( $content_types[ $slug ] ) ) {
		return new WP_Error(
			'wpe_invalid_content_model',
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
			__( 'You must specify a valid model.', 'atlas-content-modeler' ),
			[ 'status' => 400 ]
		);
	}

	if ( ! isset( $content_types[ $model ]['fields'][ $field_id ] ) ) {
		return new WP_Error(
			'wpe_invalid_content_model_field_id',
			__( 'Invalid field ID.', 'atlas-content-modeler' ),
			[ 'status' => 400 ]
		);
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
function dispatch_delete_model( WP_REST_Request $request ) {
	$route         = $request->get_route();
	$slug          = substr( strrchr( $route, '/' ), 1 );
	$content_types = get_registered_content_types();

	if ( empty( $content_types[ $slug ] ) ) {
		return rest_ensure_response(
			[
				'success' => false,
				'errors'  => esc_html__( 'The specified content model does not exist.', 'atlas-content-modeler' ),
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
 * Handles feedback banner dismissal requests from the REST API.
 *
 * @param WP_REST_Request $request The REST API request object.
 *
 * @return WP_Error|\WP_HTTP_Response|\WP_REST_Response
 */
function dispatch_dismiss_feedback_banner( WP_REST_Request $request ) {
	$created = update_user_meta( get_current_user_id(), 'acm_hide_feedback_banner', time() );

	if ( ! $created ) {
		return new WP_Error( 'atlas-content-modeler-feedback-notice-dismiss-error', esc_html__( 'Feedback banner metadata was not set. Reason unknown.', 'atlas-content-modeler' ) );
	}

	return rest_ensure_response(
		[
			'success' => $created,
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
			'atlas_content_modeler_invalid_id',
			__( 'Please provide a valid API Identifier.', 'atlas-content-modeler' ),
			[ 'status' => 400 ]
		);
	}

	$existing_content_types = get_post_types();

	$content_types = get_registered_content_types();

	if ( ! empty( $content_types[ $post_type_slug ] ) || array_key_exists( $post_type_slug, $existing_content_types ) ) {
		return new WP_Error(
			'atlas_content_modeler_already_exists',
			__( 'A content model with this API Identifier already exists.', 'atlas-content-modeler' ),
			[ 'status' => 400 ]
		);
	}

	if ( empty( $args['singular'] ) || empty( $args['plural'] ) ) {
		return new WP_Error(
			'atlas_content_modeler_invalid_labels',
			__( 'Please provide singular and plural labels when creating a content model.', 'atlas-content-modeler' ),
			[ 'status' => 400 ]
		);
	}

	// @todo maybe remove these defaults, or change them to false for opt-in exposure.
	// should these only be saved to the model when non-default?
	$defaults = [
		'show_in_rest'    => true,
		'show_in_graphql' => true,
	];

	$args = wp_parse_args( $args, $defaults );

	if ( empty( $args['fields'] ) ) {
		$args['fields'] = [];
	}

	$content_types[ $post_type_slug ] = $args;

	$created = update_registered_content_types( $content_types );

	if ( ! $created ) {
		return new WP_Error( 'model-not-created', esc_html__( 'Model not created. Reason unknown.', 'atlas-content-modeler' ) );
	}

	return true;
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
			__( 'Invalid content model ID.', 'atlas-content-modeler' )
		);
	}

	if ( empty( $args['singular'] ) || empty( $args['plural'] ) ) {
		return new WP_Error(
			'wpe_invalid_content_model_arguments',
			__( 'Please provide singular and plural labels when creating a content model.', 'atlas-content-modeler' )
		);
	}

	$content_types = get_registered_content_types();
	if ( empty( $content_types[ $post_type_slug ] ) ) {
		return new WP_Error(
			'wpe_invalid_content_model_id',
			__( 'Invalid content model ID.', 'atlas-content-modeler' )
		);
	}

	$new_args = wp_parse_args( $args, $content_types[ $post_type_slug ] );

	$content_types[ $post_type_slug ] = $new_args;

	$updated = update_registered_content_types( $content_types );

	if ( ! $updated ) {
		return new WP_Error(
			'model-not-updated',
			__( 'Model not updated. Reason unknown.', 'atlas-content-modeler' )
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
		return new WP_Error( 'model-not-deleted', esc_html__( 'Please provide a post-type-slug.', 'atlas-content-modeler' ) );
	}

	$content_types = get_registered_content_types();

	if ( empty( $content_types[ $post_type_slug ] ) ) {
		return new WP_Error( 'model-not-deleted', esc_html__( 'Content type does not exist.', 'atlas-content-modeler' ) );
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
 * Checks if a duplicate model identifier (name) exists in the multiple option field.
 *
 * @param array  $names  The available field choice names.
 * @param string $current_choice  The currently checked field choice name.
 * @param int    $current_index The content index for the current choice being validated.
 * @return bool
 */
function content_model_multi_option_exists( array $names, string $current_choice, int $current_index ): bool {
	if ( ! isset( $names ) ) {
		return false;
	}
	if ( $names ) {
		if ( $names[ $current_index ] ) {
			unset( $names[ $current_index ] );
		}

			$problem_options_list = [];

		foreach ( $names as $choice ) {
			if ( $choice['name'] === $current_choice ) {
				return true;
			}
		}
	}
	return false;
}

/**
 * Saves a taxonomy.
 *
 * @param array $params Parameters passed from the taxonomy form.
 * @param bool  $is_update True if `$params` came from a PUT request.
 * @return array|WP_Error
 * @since 0.6.0
 */
function save_taxonomy( array $params, bool $is_update ) {
	if ( empty( $params['slug'] ) ) {
		return new WP_Error(
			'atlas_content_modeler_invalid_id',
			esc_html__( 'Please provide a valid API Identifier.', 'atlas-content-modeler' ),
			[ 'status' => 400 ]
		);
	}

	$acm_taxonomies     = get_option( 'atlas_content_modeler_taxonomies', array() );
	$wp_taxonomies      = get_taxonomies();
	$non_acm_taxonomies = array_diff( array_keys( $wp_taxonomies ), array_keys( $acm_taxonomies ) );

	// Prevents creation of a taxonomy if one with the same slug exists that was not created in ACM.
	if ( in_array( $params['slug'], $non_acm_taxonomies, true ) ) {
		return new WP_Error(
			'atlas_content_modeler_taxonomy_exists',
			esc_html__( 'A taxonomy with this API Identifier already exists.', 'atlas-content-modeler' ),
			[ 'status' => 400 ]
		);
	}

	// Allows updates of existing ACM taxonomies, but prevents creation of ACM taxonomies with identical slugs.
	if ( ! $is_update && array_key_exists( $params['slug'], $acm_taxonomies ) ) {
		return new WP_Error(
			'atlas_content_modeler_taxonomy_exists',
			esc_html__( 'A taxonomy with this API Identifier already exists.', 'atlas-content-modeler' ),
			[ 'status' => 400 ]
		);
	}

	if ( empty( $params['singular'] ) || empty( $params['plural'] ) ) {
		return new WP_Error(
			'atlas_content_modeler_invalid_labels',
			esc_html__( 'Please provide singular and plural labels when creating a taxonomy.', 'atlas-content-modeler' ),
			[ 'status' => 400 ]
		);
	}

	$defaults = [
		'types'           => [],
		'show_in_rest'    => true,
		'show_in_graphql' => true,
		'hierarchical'    => false,
		'api_visibility'  => 'private',
	];

	$taxonomy                            = wp_parse_args( $params, $defaults );
	$acm_taxonomies[ $taxonomy['slug'] ] = $taxonomy;
	$created                             = update_option( 'atlas_content_modeler_taxonomies', $acm_taxonomies );

	if ( ! $created ) {
		return new WP_Error(
			'atlas_content_modeler_taxonomy_not_created',
			esc_html__( 'Taxonomy not created. Reason unknown.', 'atlas-content-modeler' )
		);
	}

	return $taxonomy;
}

/**
 * Handles taxonomy DELETE requests from the REST API.
 *
 * @param WP_REST_Request $request The REST API request object.
 *
 * @return WP_Error|\WP_HTTP_Response|\WP_REST_Response
 */
function dispatch_delete_taxonomy( WP_REST_Request $request ) {
	$route          = $request->get_route();
	$slug           = substr( strrchr( $route, '/' ), 1 );
	$acm_taxonomies = get_option( 'atlas_content_modeler_taxonomies', array() );

	if ( empty( $acm_taxonomies[ $slug ] ) ) {
		return rest_ensure_response(
			[
				'success' => false,
				'errors'  => esc_html__( 'The specified taxonomy does not exist.', 'atlas-content-modeler' ),
			]
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
