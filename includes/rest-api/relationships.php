<?php
/**
 * Relationship field helpers used in REST API callbacks.
 *
 * @package AtlasContentModeler
 */

namespace WPE\AtlasContentModeler\REST_API\Relationships;

use function WPE\AtlasContentModeler\ContentRegistration\get_registered_content_types;
use function WPE\AtlasContentModeler\ContentRegistration\update_registered_content_types;

/**
 * Deletes relationship fields that refer to the `$slug` model from all models.
 *
 * @param string $slug The reference model to delete relationship fields for.
 */
function cleanup_detached_relationship_fields( $slug ) {
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
 * Deletes references to entries of the `$slug` model from the references table.
 *
 * @param string $slug The reference model to delete relationship entries for.
 */
function cleanup_detached_relationship_references( $slug ) {
	// Find and remove relevant references.
}
