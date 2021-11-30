<?php
/**
 * Model helpers used in REST API callbacks.
 *
 * @package AtlasContentModeler
 */

declare(strict_types=1);

namespace WPE\AtlasContentModeler\REST_API\Models;

use WP_Error;
use function WPE\AtlasContentModeler\ContentRegistration\get_registered_content_types;
use function WPE\AtlasContentModeler\ContentRegistration\update_registered_content_types;
use function WPE\AtlasContentModeler\ContentRegistration\Taxonomies\get_acm_taxonomies;

/**
 * Creates a custom content model.
 *
 * @param string $post_type_slug The post type slug.
 * @param array  $args Model arguments.
 *
 * @return array|WP_Error The newly created model on success. WP_Error on failure.
 */
function create_model( string $post_type_slug, array $args ) {
	$args = get_model_args( $post_type_slug, $args );

	if ( is_wp_error( $args ) ) {
		return $args;
	}

	$existing_content_types = get_post_types();
	$content_types          = get_registered_content_types();

	if ( ! empty( $content_types[ $args['slug'] ] ) || array_key_exists( $args['slug'], $existing_content_types ) ) {
		return new WP_Error(
			'acm_model_exists',
			__( 'A content model with this Model ID already exists.', 'atlas-content-modeler' ),
			[ 'status' => 400 ]
		);
	}

	$content_types[ $args['slug'] ] = $args;
	$created                        = update_registered_content_types( $content_types );

	if ( ! $created ) {
		return new WP_Error( 'model-not-created', esc_html__( 'Model not created. Reason unknown.', 'atlas-content-modeler' ) );
	}

	return $content_types[ $args['slug'] ];
}

/**
 * Creates multiple models via a single batch update.
 *
 * Performs no update if any of the passed models exist or have invalid properties.
 *
 * @param array $models The models to create.
 *
 * @return array|WP_Error The newly created models on success or WP_Error.
 */
function create_models( array $models ) {
	$existing_content_types = get_post_types();
	$content_types          = get_registered_content_types();

	foreach ( $models as $model ) {
		if ( ! is_array( $model ) ) {
			continue;
		}

		$args = get_model_args( $model['slug'] ?? '', $model );

		if ( is_wp_error( $args ) ) {
			return $args;
		}

		if ( ! empty( $content_types[ $args['slug'] ] ) || array_key_exists( $args['slug'], $existing_content_types ) ) {
			return new WP_Error(
				'acm_model_exists',
				// translators: The name of the model.
				sprintf( __( 'A model with slug ‘%s’ already exists.', 'atlas-content-modeler' ), $args['slug'] ),
				[ 'status' => 400 ]
			);
		}

		$content_types[ $args['slug'] ] = $args;
	}

	$updated = update_registered_content_types( $content_types );

	if ( ! $updated ) {
		return new WP_Error( 'models-not-updated', esc_html__( 'Models not updated. Reason unknown.', 'atlas-content-modeler' ) );
	}

	return $content_types;
}

/**
 * Validates existing model properties and adds missing defaults.
 *
 * @param string $post_type_slug The post type slug.
 * @param array  $args Model arguments.
 *
 * @return array|WP_Error The model arguments on success. WP_Error if needed arguments are missing or invalid.
 */
function get_model_args( string $post_type_slug, array $args ) {
	$post_type_slug = sanitize_key( $post_type_slug );

	if ( empty( $post_type_slug ) || strlen( $post_type_slug ) > 20 ) {
		return new WP_Error(
			'acm_invalid_model_id',
			__( 'Please provide a valid Model ID.', 'atlas-content-modeler' ),
			[ 'status' => 400 ]
		);
	}

	if ( empty( $args['singular'] ) || empty( $args['plural'] ) ) {
		return new WP_Error(
			'acm_invalid_labels',
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

	$args['slug'] = $post_type_slug;

	return $args;
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
			'acm_invalid_content_model_id',
			__( 'Invalid content model ID.', 'atlas-content-modeler' )
		);
	}

	if ( empty( $args['singular'] ) || empty( $args['plural'] ) ) {
		return new WP_Error(
			'acm_invalid_content_model_arguments',
			__( 'Please provide singular and plural labels when creating a content model.', 'atlas-content-modeler' )
		);
	}

	$content_types = get_registered_content_types();
	if ( empty( $content_types[ $post_type_slug ] ) ) {
		return new WP_Error(
			'acm_invalid_content_model_id',
			__( 'Invalid content model ID.', 'atlas-content-modeler' )
		);
	}

	$new_args = wp_parse_args( $args, $content_types[ $post_type_slug ] );

	// Updating the slug is unsupported.
	$new_args['slug'] = $post_type_slug;

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
 * @param string $post_type_slug The Model ID.
 *
 * @return bool|WP_Error WP_Error on failures, otherwise true.
 */
function delete_model( string $post_type_slug ) {
	$content_types = get_registered_content_types();

	if ( empty( $post_type_slug ) || empty( $content_types[ $post_type_slug ] ) ) {
		return new WP_Error(
			'acm_invalid_model_id',
			__( 'Please provide a valid Model ID.', 'atlas-content-modeler' ),
			[ 'status' => 400 ]
		);
	}

	$taxonomies          = get_acm_taxonomies();
	$has_taxonomy_update = false;

	foreach ( $taxonomies as $tax_slug => $taxonomy ) {
		if ( ! isset( $taxonomy['types'] ) || ! is_array( $taxonomy['types'] ) ) {
			continue;
		}

		$type_index = array_search( $post_type_slug, $taxonomy['types'], true );
		if ( $type_index !== false ) {
			$has_taxonomy_update = true;
			unset( $taxonomy['types'][ $type_index ] );
			$taxonomies[ $tax_slug ]['types'] = array_values( $taxonomy['types'] );
		}
	}

	if ( $has_taxonomy_update ) {
		$updated = update_option( 'atlas_content_modeler_taxonomies', $taxonomies );

		if ( ! $updated ) {
			return new WP_Error(
				'acm_taxonomies_not_updated',
				__( 'Model deletion aborted. Failed to remove model from associated taxonomies.', 'atlas-content-modeler' )
			);
		}
	}

	$model = $content_types[ $post_type_slug ];
	unset( $content_types[ $post_type_slug ] );

	$updated = update_registered_content_types( $content_types );

	if ( ! $updated ) {
		return new WP_Error(
			'acm_model_not_deleted',
			__( 'Model not deleted. Reason unknown.', 'atlas-content-modeler' )
		);
	}

	return $model;
}
