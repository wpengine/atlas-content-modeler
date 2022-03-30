<?php
/**
 * Functions to create and modify content model entries.
 *
 * @package AtlasContentModeler
 */

declare(strict_types=1);

namespace WPE\AtlasContentModeler\API;

use function WPE\AtlasContentModeler\get_field_from_slug;
use WPE\AtlasContentModeler\ContentConnect\Plugin as ContentConnect;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Create a relationship between posts.
 *
 * Relationship must already be defined between models.
 *
 * @param int    $post_id The post or content entry id.
 * @param string $relation_field_slug The content model field slug.
 * @param array  $relation_ids Array of post or content entry ids.
 *
 * @return bool|WP_Error False or WP_Error if relation could not be made, else true.
 */
function insert_relationship( int $post_id, string $relation_field_slug, array $relation_ids ) {
	$post = get_post( $post_id );
	if ( empty( $post ) ) {
		return new \WP_Error();
	}

	$field = get_field_from_slug( $relation_field_slug, get_option( 'atlas_content_modeler_post_types' ), $post->post_type );
	if ( empty( $field ) ) {
		return new \WP_Error();
	}

	$registry     = ContentConnect::instance()->get_registry();
	$relationship = $registry->get_post_to_post_relationship( $post->post_type, $field['reference'], $field['id'] );
	if ( ! $relationship ) {
		return new \WP_Error();
	}

	return $relationship->replace_relationships( $post->ID, $relation_ids );
}
