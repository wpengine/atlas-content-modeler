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
use function WPE\AtlasContentModeler\get_entry_title_field;
use function WPE\AtlasContentModeler\get_attributes_for_field_type;
use function WPE\AtlasContentModeler\API\array_extract_by_keys;
use function WPE\AtlasContentModeler\API\validation\validate_model_field_data;
use function WPE\AtlasContentModeler\ContentRegistration\get_registered_content_types;

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
 *
 * @return int|WP_Error The newly created content model entry id or WP_Error.
 */
function insert_model_entry( string $model_slug, array $field_data, array $post_data = [] ) {
	$model_schema = get_model( $model_slug );
	if ( empty( $model_schema ) ) {
		return new \WP_Error( 'model_schema_not_found', "The content model {$model_slug} was not found" );
	}

	$post_data = array_merge(
		$post_data,
		[
			'post_type'  => $model_slug,
			'meta_input' => trim_space( $field_data ),
		]
	);

	// Validate model field data only. Ignores non-model field values.
	$valid = validate_model_field_data( $model_schema, $post_data['meta_input'] );
	if ( is_wp_error( $valid ) ) {
		return $valid;
	}

	// Sanitize meta data before insert.
	$post_data['meta_input'] = sanitize_fields( $model_schema, $post_data['meta_input'] );

	// Ensure post_title is set if field is title field.
	$entry_title_field = get_entry_title_field( $model_schema['fields'] );
	if ( ! empty( $entry_title_field ) && ! empty( $post_data['meta_input'][ $entry_title_field['slug'] ] ) ) {
		$post_data['post_title'] = $post_data['meta_input'][ $entry_title_field['slug'] ];
	}

	// Get relationship fields and data.
	$relation_field_keys = get_attributes_for_field_type( 'slug', 'relationship', $model_slug );
	$relation_data       = array_extract_by_keys( $post_data['meta_input'], $relation_field_keys );

	// Remove relationship field data from meta.
	$post_data['meta_input'] = array_remove_by_keys( $post_data['meta_input'], $relation_field_keys );

	// Insert content model entry excluding relationships.
	$post_id = wp_insert_post( $post_data, true );

	// Append the relationships.
	foreach ( $relation_data as $field_name => $relationship_ids ) {
		// Due to sanitize_field() converting to comma delimted string.
		if ( is_string( $relationship_ids ) ) {
			$relationship_ids = explode( ',', $relationship_ids );
		}

		foreach ( $relationship_ids as $relationship_id ) {
			add_relationship( $post_id, $field_name, (int) $relationship_id );
		}
	}

	return $post_id;
}

/**
 * Update a content model entry.
 *
 * @param int   $post_id The id of the post being updated.
 * @param array $field_data The field data being saved.
 * @param array $post_data The post data object.
 *
 * @return int|WP_Error The updated content model entry id or WP_Error.
 */
function update_model_entry( int $post_id, array $field_data, array $post_data = [] ) {
	$wp_post = get_post( $post_id );
	if ( ! $wp_post ) {
		return new \WP_Error( 'model_entry_not_found', "The post ID {$post_id} was not found" );
	}

	$post_data = array_merge( $post_data, [ 'ID' => $post_id ] );

	return insert_model_entry( $wp_post->post_type, $field_data, $post_data );
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
function get_field( string $model, string $field_slug ): ?array {
	$model_schema = get_model( $model );
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
function get_model( string $model ): ?array {
	$models = get_registered_content_types();
	if ( empty( $models[ $model ] ) ) {
		return null;
	}

	return $models[ $model ];
}
