<?php
/**
 * Functions to handle blueprint import.
 *
 * @package AtlasContentModeler
 */

declare(strict_types=1);

namespace WPE\AtlasContentModeler\Blueprint\Import;

use WP_Error;
use function WPE\AtlasContentModeler\REST_API\Taxonomies\save_taxonomy;
use function WPE\AtlasContentModeler\ContentRegistration\Taxonomies\register as register_taxonomies;

/**
 * Unzips the blueprint zip file.
 *
 * @param string $blueprint_path Path to the blueprint zip file.
 * @return string|WP_Error Unzipped blueprint folder path if successful.
 */
function unzip_blueprint( string $blueprint_path ) {
	if ( ! is_readable( $blueprint_path ) ) {
		return new WP_Error(
			'acm_blueprint_file_not_found',
			__( 'Could not read blueprint file.', 'atlas-content-modeler' )
		);
	}

	WP_Filesystem();
	$upload_folder = wp_upload_dir();
	$target_folder = $upload_folder['path'] . '/' . basename( $blueprint_path, '.zip' );
	$unzipped_file = unzip_file( $blueprint_path, $target_folder );

	if ( $unzipped_file ) {
		return $target_folder;
	}

	return $unzipped_file; // The WP_Error from the failed unzip attempt.
}

/**
 * Reads the acm.json manifest file from the blueprint.
 *
 * @param string $blueprint_folder Directory to find the manifest file.
 * @return array|WP_Error|null Array of manifest data, WP_Error if manifest was
 *                             unreadable, or null if JSON could not be decoded.
 */
function get_manifest( string $blueprint_folder ) {
	$manifest_path = $blueprint_folder . '/acm.json';

	if ( ! is_readable( $manifest_path ) ) {
		return new WP_Error(
			'acm_manifest_error',
			__(
				'Could not read an acm.json file in the blueprint folder.',
				'atlas-content-modeler'
			)
		);
	}

	$manifest = file_get_contents( $manifest_path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

	return json_decode( $manifest, true );
}

/**
 * Checks that the current site environment meets the minimum versions specified
 * in the ACM manifest.
 *
 * @param array $manifest ACM manifest data.
 * @return true|WP_Error True if all is well.
 */
function check_versions( array $manifest ) {
	if ( ! isset( $manifest['meta']['requires']['acm'] ) ) {
		return new WP_Error(
			'acm_version_error',
			__(
				'acm.json is missing the required meta.requires.acm property.',
				'atlas-content-modeler'
			)
		);
	}

	if ( ! isset( $manifest['meta']['requires']['wordpress'] ) ) {
		return new WP_Error(
			'acm_version_error',
			__(
				'acm.json is missing the required meta.requires.WordPress property.',
				'atlas-content-modeler'
			)
		);
	}

	$plugin = get_plugin_data( ATLAS_CONTENT_MODELER_FILE );

	$exceeds_minimum_acm_version = version_compare(
		$plugin['Version'],
		$manifest['meta']['requires']['acm'],
		'>='
	);

	if ( ! $exceeds_minimum_acm_version ) {
		return new WP_Error(
			'acm_version_error',
			sprintf(
				// translators: 1. Required ACM version number. 2. Current ACM version number.
				__( 'acm.json requires an ACM version of %1$s but the current ACM version is %2$s.', 'atlas-content-modeler' ),
				$manifest['meta']['requires']['acm'],
				$plugin['Version']
			)
		);
	}

	$exceeds_minimum_wp_version = version_compare(
		get_bloginfo( 'version' ),
		$manifest['meta']['requires']['wordpress'],
		'>='
	);

	if ( ! $exceeds_minimum_wp_version ) {
		return new WP_Error(
			'acm_version_error',
			sprintf(
				// translators: 1. Required WP version number. 2. Current WP version number.
				__( 'acm.json requires a WordPress version of %1$s but the current WordPress version is %2$s.', 'atlas-content-modeler' ),
				$manifest['meta']['requires']['wordpress'],
				get_bloginfo( 'version' )
			)
		);
	}

	return true;
}

/**
 * Imports ACM taxonomies.
 *
 * @param array $taxonomies Taxonomies in their original stored format.
 * @return WP_Error|void Gives a WP_Error in the case of collision with an
 *                       existing taxonomy.
 */
function import_taxonomies( array $taxonomies ) {
	foreach ( $taxonomies as $taxonomy ) {
		$saved = save_taxonomy( $taxonomy, false );

		if ( is_wp_error( $saved ) ) {
			return $saved;
		}
	}

	/**
	 * ACM already registers taxonomies on the init hook, but that occurs at the
	 * beginning of the WP-CLI process/WP hook sequence. We re-register new
	 * taxonomies here so they are available to later import steps
	 * (import_terms) within the same PHP process.
	 */
	register_taxonomies();
}

/**
 * Imports WordPress posts.
 *
 * @param array $posts WordPress post data to import.
 * @return array A map of original post IDs to new post IDs to be used to
 *               correctly assign post meta in subsequent import steps.
 */
function import_posts( array $posts ) {
	/**
	 * Stores any post IDs that change during import.
	 */
	$post_ids_old_new = [];

	foreach ( $posts as $post ) {
		/**
		 * Store and then remove the 'ID' property. `wp_insert_post()` treats
		 * posts with an 'ID' as an update but we want to create a new post.
		 */
		$old_id = $post['ID'];
		unset( $post['ID'] );

		/**
		 * Tries to re-use the same post ID. WordPress will only reuse it if
		 * there is no existing post with that ID. This reduces how many posts
		 * we have to remap IDs for in post meta and ACM relationships.
		 */
		$post['import_id'] = $old_id;

		/**
		 * Removes old GUIDs. WordPress creates new ones based on the post's
		 * new ID and permalink.
		 */
		unset( $post['guid'] );

		$new_id = wp_insert_post( $post );

		/**
		 * Updates $post_ids_old_new for any post IDs that had to change
		 * during import. A scenario where this is required:
		 * - A post in the manifest has an ID of 10.
		 * - We asked WP to try to use the ID of 10 by setting import_id.
		 * - But there is already a post on the target site with an ID of 10.
		 * - WP creates the new post but gives it an ID of 50.
		 * - We add the 10 => 50 relationship to our $post_ids_old_new array.
		 * - We return $post_ids_old_new from this function.
		 * - Post meta, terms and relationships in the manifest file that
		 *   reference a post ID of 10 can use the correct ID of 50 in later
		 *   import steps.
		 */
		if ( $new_id !== $old_id ) {
			$post_ids_old_new[ $old_id ] = $new_id;
		}
	}

	return $post_ids_old_new;
}

/**
 * Imports terms.
 *
 * @param array $terms Terms to import.
 * @return array|WP_Error
 */
function import_terms( array $terms ) {
	/**
	 * Stores term IDs that changed during import.
	 */
	$term_ids_old_new = [];

	foreach ( $terms as $term ) {
		$term_info = [
			'slug'        => $term['slug'],
			'description' => $term['description'],
		];

		$inserted_term = wp_insert_term( $term['name'], $term['taxonomy'], $term_info );

		if ( is_wp_error( $inserted_term ) ) {
			return $inserted_term;
		}

		if ( $term['term_id'] !== $inserted_term['term_id'] ) {
			$term_ids_old_new[ $term['term_id'] ] = $inserted_term['term_id'];
		}
	}

	return $term_ids_old_new;
}


/**
 * Sets terms on posts.
 *
 * @param array $post_terms Post term data.
 * @param array $post_ids_old_new A map of original post IDs from the manifest
 *                                and their new ID when imported.
 * @param array $term_ids_old_new A map of original term IDs from the manifest
 *                                and their new ID when imported.
 * @return true|WP_Error True on success, WP_Error if setting any term failed.
 */
function tag_posts( $post_terms, $post_ids_old_new, $term_ids_old_new ) {
	foreach ( $post_terms as $post_id => $terms ) {
		foreach ( $terms as $term ) {
			$new_post_id = $post_ids_old_new[ $post_id ] ?? $post_id;
			$new_term_id = $term_ids_old_new[ $term['term_id'] ] ?? $term['term_id'];
			$result      = wp_set_post_terms( $new_post_id, [ (int) $new_term_id ], $term['taxonomy'], true );

			if ( is_wp_error( $result ) ) {
				return $result;
			}
		}
	}

	return true;
}
