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
use function WPE\AtlasContentModeler\ContentRegistration\get_registered_content_types;
use function WPE\AtlasContentModeler\ContentRegistration\update_registered_content_types;
use function WPE\AtlasContentModeler\REST_API\Fields\shape_field_args;
use function WPE\AtlasContentModeler\REST_API\Fields\content_model_field_exists;
use function WPE\AtlasContentModeler\REST_API\Fields\content_model_multi_option_exists;
use function WPE\AtlasContentModeler\REST_API\Fields\content_model_multi_option_slug_exists;

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
			'wpe_invalid_content_model',
			__( 'The specified content model does not exist.', 'atlas-content-modeler' ),
			array( 'status' => 400 )
		);
	}

	if ( isset( $params['type'] ) && $params['type'] === 'relationship' ) {
		if ( empty( $params['reference'] ) || empty( $params['cardinality'] ) ) {
			return new WP_Error(
				'atlas_content_modeler_missing_field_argument',
				__( 'The relationship field requires a reference and cardinality argument.', 'atlas-content-modeler' ),
				array( 'status' => 400 )
			);
		}

		if ( empty( $content_types[ $params['reference'] ] ) ) {
			return new WP_Error(
				'atlas_content_modeler_invalid_related_content_model',
				__( 'The related content model no longer exists.', 'atlas-content-modeler' ),
				array( 'status' => 400 )
			);
		}
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
	// aaCheck if a slug is defined for each choice on save.
	if ( isset( $params['type'] ) && $params['type'] === 'multipleChoice' && $params['choices'] ) {
		$options_index = -1;
		$problem_index = [];
		foreach ( $params['choices'] as $choice ) {
			++$options_index;
			if ( $choice['slug'] === '' ) {
				$problem_index[] = $options_index;
			}
		}
		if ( $problem_index ) {
			$problem_error_slug_blank = new WP_Error(
				'wpe_option_slug_undefined',
				'Multiple Choice Field update failed, please set a slug for your choice before saving.',
				array( 'status' => 400 )
			);
			$problem_error_slug_blank->add( 'problem_index', $problem_index );
			return $problem_error_slug_blank;
		}
	}

	// Check if a choice name is a duplicate.
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
				'Another choice in this field has the same name.',
				array( 'status' => 400 )
			);
			$problem_duplicate_name->add( 'problem_name_index', $problem_name_index );
			return $problem_duplicate_name;
		}
	}

	// Check if a slug name is a duplicate.
	if ( isset( $params['type'] ) && $params['type'] === 'multipleChoice' && $params['choices'] ) {
		$options_slug_index        = -1;
		$problem_option_slug_index = [];
		foreach ( $params['choices'] as $choice ) {
			++$options_slug_index;
			if ( content_model_multi_option_slug_exists( $params['choices'], $choice['slug'], $options_slug_index ) ) {
				$problem_option_slug_index[] = $options_slug_index;
			}
		}
		if ( $problem_option_slug_index ) {
			$problem_duplicate_slug = new WP_Error(
				'wpe_option_slug_duplicate',
				'Another choice in this field has the same API identifier.',
				array( 'status' => 400 )
			);
			$problem_duplicate_slug->add( 'problem_option_slug_index', $problem_option_slug_index );
			return $problem_duplicate_slug;
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

	// Checks the field slug on the current model.
	if (
		content_model_field_exists(
			$params['slug'],
			$params['id'],
			$params['model']
		)
	) {
		return new WP_Error(
			'wpe_duplicate_content_model_field_id',
			__( 'Another field in this model has the same API identifier.', 'atlas-content-modeler' ),
			array( 'status' => 400 )
		);
	}

	// Checks the reverse slug on the reference model.
	if (
		isset( $params['type'] ) &&
		$params['type'] === 'relationship' &&
		isset( $params['enableReverse'] ) &&
		true === $params['enableReverse'] &&
		content_model_field_exists(
			$params['reverseSlug'],
			$params['id'],
			$params['reference']
		)
	) {
			return new WP_Error(
				'wpe_duplicate_field_reverse_slug',
				sprintf(
					/* translators: %s: reference id of the referenced to field */
					__( 'Another field in the model %s model has the same API identifier.', 'atlas-content-modeler' ),
					$params['reference']
				),
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
