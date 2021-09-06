<?php
/**
 * Relationship field helpers used in REST API callbacks.
 *
 * @package AtlasContentModeler
 */

namespace WPE\AtlasContentModeler\REST_API\Relationships;

use function WPE\AtlasContentModeler\ContentRegistration\get_registered_content_types;
use function WPE\AtlasContentModeler\ContentRegistration\update_registered_content_types;
use WPE\AtlasContentModeler\ContentConnect\Plugin as ContentConnect;

/**
 * Deletes relationship fields that refer to the `$slug` model from all models.
 *
 * @param string $slug The reference model to delete relationship fields for.
 */
function cleanup_detached_relationship_fields( string $slug ) {
	$models = get_registered_content_types();

	foreach ( $models as $model_id => $model ) {
		foreach ( $model['fields'] as $field_id => $field ) {
			if ( $field['type'] === 'relationship' && $field['reference'] === $slug ) {
				unset( $models[ $model_id ]['fields'][ $field_id ] );
			}
		}
	}

	update_registered_content_types( $models );
}

/**
 * Deletes rows in the post-to-post table whose id1 value refers to a post that
 * has the `$slug` post type.
 *
 * This depends on model entries for `$slug` existing in the database. At the
 * moment we do not delete entries when a model is deleted. If that changes,
 * this cleanup function must run before entries are removed from the database.
 *
 * TODO: When we implement backreferences, clean those here too.
 *
 * TODO: This could be moved to the acm-content-connect package when a hook
 * exists for deleting ACM models.
 *
 * @param string $slug The reference model to delete relationship entries for.
 */
function cleanup_detached_relationship_references( string $slug ) {
	global $wpdb;

	$table        = ContentConnect::instance()->get_table( 'p2p' );
	$post_to_post = $table->get_table_name();

	/**
	 * PHPCS is disabled to prevent warnings about the unescaped `$post_to_post`
	 * table name, which is derived from an unfilterable string literal in
	 * WPE\AtlasContentModeler\ContentConnect\Tables\PostToPost\get_table_name.
	 *
	 * Needed feature to fix this: https://core.trac.wordpress.org/ticket/52506.
	 *
	 * phpcs:disable
	 */
	$wpdb->query(
		$wpdb->prepare(
			"
			DELETE `{$post_to_post}`
			FROM `{$post_to_post}`
			INNER JOIN {$wpdb->posts} ON `{$post_to_post}`.id1 = {$wpdb->posts}.ID
			WHERE {$wpdb->posts}.post_type = %s;
			",
			$slug
		)
	);
	// phpcs:enable
}
