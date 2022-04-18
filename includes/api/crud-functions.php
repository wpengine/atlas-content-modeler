<?php
/**
 * Functions to create and modify content model entries.
 *
 * @package AtlasContentModeler
 */

declare(strict_types=1);

namespace WPE\AtlasContentModeler\API;

use function WPE\AtlasContentModeler\get_field_from_slug;
use function WPE\AtlasContentModeler\sanitize_fields;
use function WPE\AtlasContentModeler\ContentRegistration\get_registered_content_types;
use function WPE\AtlasContentModeler\API\Utility\get_data_for_fields;
use function WPE\AtlasContentModeler\get_entry_title_field;
use function WPE\AtlasContentModeler\API\validation\validate_model_field_data;

use WPE\AtlasContentModeler\ContentConnect\Plugin as ContentConnect;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Insert a content model entry.
 *
 * @uses wp_insert_post
 *
 * @param string $model_slug Content model slug.
 * @param array  $field_data Content model field data.
 * @param array  $post_data Post data.
 * @param bool   $skip_validation True to skip model field validation. Default false.
 *
 * @return int|WP_Error The newly created content model entry id or WP_Error.
 */
function insert_model_entry( string $model_slug, array $field_data, array $post_data = [], bool $skip_validation = false ) {
	$model_schema = fetch_model( $model_slug );
	if ( empty( $model_schema ) ) {
		return new \WP_Error( 'model_schema_not_found', "The content model {$model_slug} was not found" );
	}

	$post_data = array_merge(
		$post_data,
		[
			'post_type'  => $model_slug,
			'meta_input' => sanitize_fields( $model_schema, $field_data ),
		]
	);

	if ( ! $skip_validation ) {
		$valid = validate_model_field_data( $model_schema, $post_data['meta_input'] );

		if ( is_wp_error( $valid ) && $valid->has_errors() ) {
			return $valid;
		}
	}

	$entry_title_field = get_entry_title_field( $model_schema['fields'] );
	if ( ! empty( $entry_title_field ) && ! empty( $post_data['meta_input'][ $entry_title_field['slug'] ] ) ) {
		$post_data['post_title'] = $post_data['meta_input'][ $entry_title_field['slug'] ];
	}

	return wp_insert_post( $post_data, true );
}

/**
 * Update a content model entry.
 *
 * @param int          $post_id         The id of the post being updated.
 * @param array        $field_data      The field data being saved.
 * @param array        $post_data       The post data object.
 * @param bool|boolean $skip_validation Option to skip validation.
 *
 * @return int|WP_Error The updated content model entry id or WP_Error.
 */
function update_model_entry( int $post_id, array $field_data, array $post_data = [], bool $skip_validation = false ) {
	$post_data = get_post( $post_id );

	if ( ! $post_data ) {
		return new \WP_Error( 'model_entry_not_found', "The post ID {$post_id} was not found" );
	}
	$post_data = array_merge( (array) $post_data, [ 'ID' => $post_id ] );

	return insert_model_entry( $post_data['post_type'], $field_data, $post_data, $skip_validation );
}

/**
 * Add a relationship to a given post.
 *
 * Relationship must already be defined between models.
 *
 * @param int    $post_id The post or content entry id.
 * @param string $relationship_field_slug The content model field slug.
 * @param int    $relationship_id The post id to associate.
 *
 * @return bool|WP_Error False or WP_Error if relation could not be made, else true.
 */
function add_relationship( int $post_id, string $relationship_field_slug, int $relationship_id ) {
	$relationship = get_relationship( $post_id, $relationship_field_slug );
	if ( is_wp_error( $relationship ) ) {
		return $relationship;
	}

	return $relationship->add_relationship( $post_id, $relationship_id );
}

/**
 * Replace relationships between posts.
 *
 * Relationship must already be defined between models.
 *
 * @param int    $post_id The post or content entry id.
 * @param string $relationship_field_slug The content model field slug.
 * @param array  $relationship_ids Array of post or content entry ids.
 *
 * @return bool|WP_Error False or WP_Error if relation could not be made, else true.
 */
function replace_relationship( int $post_id, string $relationship_field_slug, array $relationship_ids ) {
	$relationship = get_relationship( $post_id, $relationship_field_slug );
	if ( is_wp_error( $relationship ) ) {
		return $relationship;
	}

	return $relationship->replace_relationships( $post_id, $relationship_ids );
}

/**
 * Get a relation object for a content model.
 *
 * @param int    $post_id The post or content entry id.
 * @param string $relationship_field_slug The content model field slug.
 *
 * @return WPE\AtlasContentModeler\ContentConnect\Relationships\PostToPost|WP_Error A relation object or WP_Error.
 */
function get_relationship( int $post_id, string $relationship_field_slug ) {
	$post = get_post( $post_id );
	if ( empty( $post ) ) {
		return new \WP_Error( 'invalid_post_object', 'The post object was invalid' );
	}

	$field = get_field_from_slug( $relationship_field_slug, get_option( 'atlas_content_modeler_post_types' ), $post->post_type );
	if ( empty( $field ) ) {
		return new \WP_Error( 'field_not_found', 'Content model field not found' );
	}

	$registry     = ContentConnect::instance()->get_registry();
	$relationship = $registry->get_post_to_post_relationship( $post->post_type, $field['reference'], $field['id'] );
	if ( ! $relationship ) {
		return new \WP_Error( 'content_relationship_not_found', 'Content model relationship not found' );
	}

	return $relationship;
}

/**
 * Fetch a content model field.
 *
 * @param string $model The model field slug.
 * @param string $field_slug The field slug.
 *
 * @return array|null The model field as associative array or null.
 */
function fetch_model_field( string $model, string $field_slug ): ?array {
	$model_schema = fetch_model( $model );
	$field_schema = null;

	if ( empty( $model_schema['fields'] ) ) {
		return $field_schema;
	}

	foreach ( $model_schema['fields'] as $field ) {
		if ( $field_slug !== $field['slug'] ) {
			continue;
		}

		$field_schema = $field;
		break;
	}

	return $field_schema;
}

/**
 * Gets the model data for a defined content model.
 *
 * @param string $model The content model slug.
 *
 * @return array|null The content model schema or null if not found.
 */
function fetch_model( string $model ): ?array {
	$models = get_registered_content_types();
	if ( empty( $models[ $model ] ) ) {
		return null;
	}

	return $models[ $model ];
}
