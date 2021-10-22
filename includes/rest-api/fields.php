<?php
/**
 * Field helpers used in REST API callbacks.
 *
 * @package AtlasContentModeler
 */

namespace WPE\AtlasContentModeler\REST_API\Fields;

use function WPE\AtlasContentModeler\ContentRegistration\get_registered_content_types;
use function WPE\AtlasContentModeler\ContentRegistration\update_registered_content_types;
use WPE\AtlasContentModeler\ContentConnect\Plugin as ContentConnect;

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

/**
 * Checks if a duplicate field identifier (slug) exists in the content model.
 *
 * @param string $slug  The current field slug.
 * @param string $id    The current field id.
 * @param string $model_id The id of the content model to check for duplicate slugs.
 * @return bool
 */
function content_model_field_exists( string $slug, string $id, string $model_id ): bool {
	$models = get_registered_content_types();

	foreach ( $models as $current_model => $model ) {
		if ( ! isset( $model['fields'] ) ) {
			continue;
		}

		foreach ( $model['fields'] as $field ) {
			if ( $current_model === $model_id && $field['id'] === $id ) {
				continue;
			}

			if ( $model_id === $current_model &&
			$field['slug'] === $slug ) {
				return true;
			}

			if ( 'relationship' === $field['type'] &&
				$model_id === $field['reference'] &&
				isset( $field['enableReverse'] ) &&
				true === $field['enableReverse'] &&
				isset( $field['reverseSlug'] ) &&
				$slug === $field['reverseSlug']
			) {
				return true;
			}
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

		foreach ( $names as $choice ) {
			if ( $choice['name'] === $current_choice ) {
				return true;
			}
		}
	}
	return false;
}

/**
 * Checks if a duplicate model identifier (slug) exists in the multiple option field.
 *
 * @param array  $slugs  The available field slug names.
 * @param string $current_choice  The currently checked field choice name.
 * @param int    $current_index The content index for the current choice being validated.
 * @return bool
 */
function content_model_multi_option_slug_exists( array $slugs, string $current_choice, int $current_index ): bool {
	if ( ! isset( $slugs ) ) {
		return false;
	}
	if ( $slugs ) {
		if ( $slugs[ $current_index ] ) {
			unset( $slugs[ $current_index ] );
		}

		foreach ( $slugs as $choice ) {
			if ( $choice['slug'] === $current_choice ) {
				return true;
			}
		}
	}
	return false;
}
