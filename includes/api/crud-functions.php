<?php
/**
 * Functions to create and modify content model entries.
 *
 * @package AtlasContentModeler
 */

declare(strict_types=1);

namespace WPE\AtlasContentModeler\API;

use function WPE\AtlasContentModeler\get_field_from_slug;
use function WPE\AtlasContentModeler\ContentRegistration\get_registered_content_types;

use WPE\AtlasContentModeler\ContentConnect\Plugin as ContentConnect;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Gets the model data for a defined content model.
 *
 * @param string $model The content model slug.
 * @return array
 */
function fetch_model( string $model ): array {
	$models = get_registered_content_types();
	if ( empty( $models[ $model ] ) ) {
		return null;
	}
	return $models[ $model ];
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

	return $relationship->add_relationship( $post->ID, $relationship_id );
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

	return $relationship->replace_relationships( $post->ID, $relationship_ids );
}
