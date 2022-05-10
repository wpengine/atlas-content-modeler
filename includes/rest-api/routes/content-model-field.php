<?php
/**
 * Registers REST API endpoints for /wpe/atlas/content-model-field/ routes.
 *
 * @package AtlasContentModeler
 */

declare(strict_types=1);

namespace WPE\AtlasContentModeler\REST_API\ContentModelField;

use WP_Error;
use WP_REST_Request;
use function WPE\AtlasContentModeler\ContentRegistration\camelcase;
use function WPE\AtlasContentModeler\ContentRegistration\get_registered_content_types;
use function WPE\AtlasContentModeler\ContentRegistration\update_registered_content_types;
use function WPE\AtlasContentModeler\REST_API\Fields\shape_field_args;
use function WPE\AtlasContentModeler\REST_API\Fields\content_model_field_exists;
use function WPE\AtlasContentModeler\REST_API\Fields\content_model_multi_option_exists;
use function WPE\AtlasContentModeler\REST_API\Fields\content_model_multi_option_slug_exists;
use function WPE\AtlasContentModeler\REST_API\Fields\content_model_reverse_slug_exists;
use function WPE\AtlasContentModeler\REST_API\GraphQL\is_allowed_field_id;

add_action( 'rest_api_init', __NAMESPACE__ . '\register_rest_routes' );
/**
 * Registers custom routes with the WP REST API.
 */
function register_rest_routes(): void {
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
			'acm_invalid_content_model',
			__( 'The specified content model does not exist.', 'atlas-content-modeler' ),
			array( 'status' => 400 )
		);
	}

	$graphql_type = ucfirst( camelcase( $content_types[ $params['model'] ]['singular'] ) );

	// Prevents use of reserved field slugs during new field creation.
	if (
		$request->get_method() === 'POST' &&
		isset( $params['slug'] ) &&
		! is_allowed_field_id( $params, $graphql_type, true )
	) {
		return new WP_Error(
			'acm_reserved_field_slug',
			__( 'Identifier in use or reserved.', 'atlas-content-modeler' ),
			array( 'status' => 400 )
		);
	}

	if ( isset( $params['type'] ) && $params['type'] === 'relationship' ) {
		if ( empty( $params['reference'] ) || empty( $params['cardinality'] ) ) {
			return new WP_Error(
				'acm_missing_field_argument',
				__( 'The relationship field requires a reference and cardinality argument.', 'atlas-content-modeler' ),
				array( 'status' => 400 )
			);
		}

		if ( empty( $content_types[ $params['reference'] ] ) ) {
			return new WP_Error(
				'acm_invalid_related_content_model',
				__( 'The related content model no longer exists.', 'atlas-content-modeler' ),
				array( 'status' => 400 )
			);
		}
	}

	if ( isset( $params['type'] ) && $params['type'] === 'multipleChoice' && ! $params['choices'] ) {
		return new WP_Error(
			'acm_invalid_multi_options',
			__( 'Multiple Choice update failed. Choices need to be created before updating a Multiple Choice field.', 'atlas-content-modeler' ),
			array( 'status' => 400 )
		);
	}
	if ( isset( $params['type'] ) && $params['type'] === 'multipleChoice' && $params['choices'] ) {
		$problem_index = [];
		foreach ( $params['choices'] as $index => $choice ) {
			if ( $choice['name'] === '' ) {
				$problem_index[] = $index;
			}
		}
		if ( ! empty( $problem_index ) ) {
			return new WP_Error(
				'acm_option_name_undefined',
				__( 'Multiple Choice Field update failed, please set a name for your choice before saving.', 'atlas-content-modeler' ),
				array(
					'status'       => 400,
					'problemIndex' => $problem_index,
				)
			);
		}
	}
	// Check if a slug is defined for each choice on save.
	if ( isset( $params['type'] ) && $params['type'] === 'multipleChoice' && $params['choices'] ) {
		$problem_index = [];
		foreach ( $params['choices'] as $index => $choice ) {
			if ( $choice['slug'] === '' ) {
				$problem_index[] = $index;
			}
		}

		// Check if a choice slug is blank.
		if ( ! empty( $problem_index ) ) {
			return new WP_Error(
				'acm_option_slug_undefined',
				__( 'Multiple Choice Field update failed, please set a slug for your choice before saving.', 'atlas-content-modeler' ),
				array(
					'status'       => 400,
					'problemIndex' => $problem_index,
				)
			);
		}
	}

	// Check if a choice name is a duplicate.
	if ( isset( $params['type'] ) && $params['type'] === 'multipleChoice' && $params['choices'] ) {
		$problem_name_index = [];
		foreach ( $params['choices'] as $index => $choice ) {
			if ( content_model_multi_option_exists( $params['choices'], $choice['name'], $index ) ) {
				$problem_name_index[] = $index;
			}
		}
		if ( $problem_name_index ) {
			return new WP_Error(
				'acm_duplicate_content_model_multi_option_id',
				__( 'Another choice in this field has the same name.', 'atlas-content-modeler' ),
				array(
					'status'     => 400,
					'duplicates' => $problem_name_index,
				)
			);
		}
	}

	// Check if a choice API identifier is a duplicate.
	if ( isset( $params['type'] ) && $params['type'] === 'multipleChoice' && $params['choices'] ) {
		$problem_option_slugs = [];
		foreach ( $params['choices'] as $index => $choice ) {
			if ( content_model_multi_option_slug_exists( $params['choices'], $choice['slug'], $index ) ) {
				$problem_option_slugs[] = $index;
			}
		}
		if ( $problem_option_slugs ) {
			return new WP_Error(
				'acm_option_slug_duplicate',
				__( 'Another choice in this field has the same API identifier.', 'atlas-content-modeler' ),
				array(
					'status'     => 400,
					'duplicates' => $problem_option_slugs,
				)
			);
		}
	}

	if ( isset( $params['type'] ) && $params['type'] === 'multipleChoice' && $params['choices'] ) {
		$problem_name_index = [];
		foreach ( $params['choices'] as $index => $choice ) {
			if ( content_model_multi_option_exists( $params['choices'], $choice['name'], $index ) ) {
				$problem_name_index[] = $index;
			}
		}
		if ( $problem_name_index ) {
			return new WP_Error(
				'acm_duplicate_content_model_multi_option_id',
				__( 'Another option in this field has the same API identifier.', 'atlas-content-modeler' ),
				array(
					'status'     => 400,
					'duplicates' => $problem_name_index,
				)
			);
		}
	}

	// Checks the field slug on the current model.
	if (
		content_model_field_exists(
			$params['slug'],
			$params['id'],
			$params['model']
		)
	) {
		return new WP_Error(
			'acm_duplicate_content_model_field_id',
			__( 'Another field in this model has the same API identifier.', 'atlas-content-modeler' ),
			array( 'status' => 400 )
		);
	}

	// Checks the reverse slug does not appear in the reference model's field slugs.
	if (
		( $params['type'] ?? '' ) === 'relationship' &&
		( $params['enableReverse'] ?? false ) === true &&
		content_model_field_exists(
			$params['reverseSlug'],
			$params['id'],
			$params['reference']
		)
	) {
		return new WP_Error(
			'acm_reverse_slug_conflict',
			sprintf(
				/* translators: %s: reference id of the referenced to field */
				__( 'A field in the %s model has the same identifier.', 'atlas-content-modeler' ),
				$params['reference']
			),
			array( 'status' => 400 )
		);
	}

	// Checks the reverse slug is not used by other relationship fields in the same model.
	if (
		( $params['type'] ?? '' ) === 'relationship' &&
		( $params['enableReverse'] ?? false ) === true &&
		content_model_reverse_slug_exists( $params )
	) {
		return new WP_Error(
			'acm_reverse_slug_in_use',
			__( 'A relationship field in this model has the same identifier.', 'atlas-content-modeler' ),
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
			'acm_invalid_content_model',
			__( 'You must specify a valid model.', 'atlas-content-modeler' ),
			[ 'status' => 400 ]
		);
	}

	if ( ! isset( $content_types[ $model ]['fields'][ $field_id ] ) ) {
		return new WP_Error(
			'acm_invalid_content_model_field_id',
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
