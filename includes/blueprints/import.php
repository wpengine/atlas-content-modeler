<?php
/**
 * Functions to handle blueprint import.
 *
 * @package AtlasContentModeler
 */

declare(strict_types=1);

namespace WPE\AtlasContentModeler\Blueprint\Import;

use WP_Error;
use \WPE\AtlasContentModeler\ContentConnect\Plugin as ContentConnect;
use function WPE\AtlasContentModeler\REST_API\Taxonomies\save_taxonomy;
use function WPE\AtlasContentModeler\ContentRegistration\Taxonomies\register as register_taxonomies;
use function WPE\AtlasContentModeler\get_field_type_from_slug;

/**
 * Unzips the blueprint zip file.
 *
 * @param string $blueprint_path Path to the blueprint zip file.
 * @return string|WP_Error Unzipped blueprint folder path if successful.
 */
function unzip_blueprint( string $blueprint_path ) {
	global $wp_filesystem;

	if ( ! is_readable( $blueprint_path ) ) {
		return new WP_Error(
			'acm_blueprint_file_not_found',
			sprintf(
				// translators: path to zip file.
				__( 'Could not read blueprint file at %s.', 'atlas-content-modeler' ),
				$blueprint_path
			)
		);
	}

	if ( empty( $wp_filesystem ) ) {
		require_once ABSPATH . 'wp-admin/includes/file.php';
		\WP_Filesystem();
	}

	$upload_folder = wp_upload_dir();
	$target_folder = $upload_folder['path'];
	$unzipped_file = unzip_file( $blueprint_path, $target_folder );

	if ( ! is_wp_error( $unzipped_file ) ) {
		// Assume the zip folder name is the same as the zip file name by default.
		$unzipped_folder = $target_folder . '/' . basename( $blueprint_path, '.zip' );

		// Try to determine the actual root folder name if possible, in case the zip file was renamed.
		if ( class_exists( 'ZipArchive' ) ) {
			$zip  = new \ZipArchive();
			$open = $zip->open( $blueprint_path );
			if ( $open === true ) {
				$file_name       = $zip->getNameIndex( 0 );
				$zip_root_folder = dirname( $file_name ) === '.' ? $file_name : dirname( $file_name );
				$unzipped_folder = $target_folder . '/' . $zip_root_folder;
				$zip->close();
			}
		}

		return $unzipped_folder;
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
				'acm.json is missing the required meta.requires.wordpress property.', // phpcs:ignore WordPress.WP.CapitalPDangit.Misspelled
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
 * @return WP_Error|void Gives a WP_Error in the case of collisions with an
 *                       existing taxonomy or other taxonomy import issues.
 */
function import_taxonomies( array $taxonomies ) {
	$import_errors = false;
	$errors        = new WP_Error(
		'acm_taxonomy_import_error',
		__( 'Errors encountered during taxonomy import.', 'atlas-content-modeler' )
	);

	foreach ( $taxonomies as $taxonomy ) {
		$saved = save_taxonomy( $taxonomy, false );

		if ( is_wp_error( $saved ) ) {
			$import_errors = true;
			$errors->add(
				$saved->get_error_code(),
				$saved->get_error_message()
			);
		}
	}

	/**
	 * ACM already registers taxonomies on the init hook, but that occurs at the
	 * beginning of the WP-CLI process/WP hook sequence. We re-register new
	 * taxonomies here so they are available to later import steps
	 * (import_terms) within the same PHP process.
	 */
	register_taxonomies();

	if ( $import_errors ) {
		return $errors;
	}
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
 * @param array $post_terms Posts keyed by post ID, each with an array of terms to import related to that post.
 * @return array
 */
function import_terms( array $post_terms ) {
	/**
	 * Stores term IDs that changed during import.
	 */
	$term_ids_old_new = [];

	$seen = [];

	$import_errors = false;
	$errors        = new WP_Error(
		'acm_term_import_error',
		__( 'Errors encountered during term import.', 'atlas-content-modeler' )
	);

	foreach ( $post_terms as $terms ) {
		foreach ( $terms as $term ) {
			if (
				empty( $term['term_id'] )
				|| empty( $term['name'] )
				|| empty( $term['taxonomy'] )
			) {
				continue;
			}

			// Prevents import of terms that have already been imported from another post.
			if ( in_array( $term['term_id'], $seen, true ) ) {
				continue;
			}

			$term_info = [
				'slug'        => $term['slug'] ?? '',
				'description' => $term['description'] ?? '',
			];

			// Continue if the term already exists.
			$term_already_exists = term_exists( $term['name'], $term['taxonomy'], $term['parent'] ?? null );

			if ( $term_already_exists ) {
				continue;
			}

			$inserted_term = wp_insert_term( $term['name'], $term['taxonomy'], $term_info );

			if ( is_wp_error( $inserted_term ) ) {
				$import_errors = true;
				$errors->add(
					$inserted_term->get_error_code(),
					$inserted_term->get_error_message()
				);
			} else {
				$seen[] = $term['term_id'];
			}

			if (
				! is_wp_error( $inserted_term )
				&& $term['term_id'] !== $inserted_term['term_id']
			) {
				$term_ids_old_new[ $term['term_id'] ] = $inserted_term['term_id'];
			}
		}
	}

	return [
		'ids'    => $term_ids_old_new,
		'errors' => $import_errors ? $errors : false,
	];
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
function tag_posts( array $post_terms, array $post_ids_old_new, array $term_ids_old_new ) {
	$import_errors = false;
	$errors        = new WP_Error(
		'acm_tag_import_error',
		__( 'Errors encountered during post tagging.', 'atlas-content-modeler' )
	);

	foreach ( $post_terms as $post_id => $terms ) {
		foreach ( $terms as $term ) {
			$new_post_id = $post_ids_old_new[ $post_id ] ?? $post_id;
			$new_term_id = $term_ids_old_new[ $term['term_id'] ] ?? $term['term_id'];
			$result      = wp_set_post_terms( $new_post_id, [ (int) $new_term_id ], $term['taxonomy'], true );

			if ( is_wp_error( $result ) ) {
				$import_errors = true;
				$errors->add(
					$result->get_error_code(),
					$result->get_error_message()
				);
			}
		}
	}

	if ( $import_errors ) {
		return $errors;
	}

	return true;
}

/**
 * Imports media files.
 *
 * @param array  $media Paths to media files keyed by original media ID, relative
 *                      to the folder they were unzipped in.
 * @param string $blueprint_folder Path to the blueprint folder.
 * @return array|WP_Error Map of old and new media IDs on success.
 */
function import_media( array $media, string $blueprint_folder ) {
	/**
	 * Stores media IDs that changed during import.
	 */
	$media_ids_old_new = [];

	foreach ( $media as $original_media_id => $relative_file_path ) {
		$full_file_path = $blueprint_folder . '/' . $relative_file_path;

		if ( ! is_readable( $full_file_path ) ) {
			return new WP_Error(
				'acm_media_error',
				sprintf(
					// translators: the full path to the media file.
					__( 'Could not read media file at %s.', 'atlas-content-modeler' ),
					$full_file_path
				)
			);
		}

		$file_info = wp_check_filetype( $full_file_path );

		$attachment = [
			'post_title'     => sanitize_title( basename( $full_file_path, '.' . $file_info['ext'] ) ),
			'post_mime_type' => $file_info['type'],
		];

		$new_media_id = wp_insert_attachment( $attachment, $full_file_path, 0, true );

		if ( is_wp_error( $new_media_id ) ) {
			return $new_media_id;
		}

		// Generates thumbnails for image files if needed.
		require_once ABSPATH . 'wp-admin/includes/image.php';
		$attachment_data = wp_generate_attachment_metadata( $new_media_id, $full_file_path );
		wp_update_attachment_metadata( $new_media_id, $attachment_data );

		/**
		 * Stores changed media IDs so the correct media reference can be
		 * inserted into post_meta in a later import step.
		 */
		if ( $new_media_id !== $original_media_id ) {
			$media_ids_old_new[ $original_media_id ] = $new_media_id;
		}
	}

	return $media_ids_old_new;
}

/**
 * Imports post meta.
 *
 * @param array $manifest The full blueprint manifest.
 * @param array $post_ids_old_new A map of original post IDs from the manifest
 *                                and their new ID when imported.
 * @param array $media_ids_old_new A map of original media IDs from the manifest
 *                                  and their new ID when imported.
 * @return void Does not report errors during post meta update.
 */
function import_post_meta( array $manifest, array $post_ids_old_new, array $media_ids_old_new ) {
	$post_meta = $manifest['post_meta'] ?? [];

	foreach ( $post_meta as $original_post_id => $metas ) {
		foreach ( $metas as $meta ) {
			$is_thumbnail_meta       = $meta['meta_key'] === '_thumbnail_id';
			$is_acm_media_field_meta = is_acm_media_field_meta(
				$manifest,
				$original_post_id,
				$meta['meta_key']
			);

			/**
			 * Thumbnails and media fields must use the new media ID for media
			 * imported in the previous step for their meta_key. This replaces
			 * the original media ID that appears in the manifest from the site
			 * the blueprint was generated on.
			 */
			if ( $is_thumbnail_meta || $is_acm_media_field_meta ) {
				$meta['meta_value'] = // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
					$media_ids_old_new[ $meta['meta_value'] ]
					?? $meta['meta_value'];
			}

			/**
			 * Update meta for the newly imported post ID, not the original post
			 * ID stored in the manifest.
			 */
			$new_post_id =
				$post_ids_old_new[ $original_post_id ]
				?? $original_post_id;

			update_post_meta(
				(int) $new_post_id,
				$meta['meta_key'],
				$meta['meta_value']
			);
		}
	}
}

/**
 * Determines if the passed $field_slug is an ACM media field.
 *
 * Meta values for ACM media fields contain the original media ID that may need
 * to be adjusted to use the newly imported media ID before meta is imported.
 *
 * @param array  $manifest The full blueprint manifest.
 * @param int    $post_id Post ID to determine post type.
 * @param string $field_slug Field slug to determine field type.
 * @return bool
 */
function is_acm_media_field_meta( array $manifest, int $post_id, string $field_slug ): bool {
	$post_type        = $manifest['posts'][ $post_id ]['post_type'] ?? '';
	$acm_post_types   = array_keys( $manifest['models'] ?? [] );
	$is_acm_post_type = in_array( $post_type, $acm_post_types, true );

	if ( ! $is_acm_post_type ) {
		return false;
	}

	return 'media' === get_field_type_from_slug(
		$field_slug,
		$manifest['models'] ?? [],
		$post_type
	);
}

/**
 * Repopulates the acm_post_to_post table that stores relationships between
 * posts for ACM relationship fields.
 *
 * @param array $relationships Relationships as stored in their raw form in the
 *                             wp_acm_post_to_post table.
 * @param array $post_ids_old_new A map of original post IDs from the manifest
 *                                and their new ID when imported.
 * @return void
 */
function import_acm_relationships( array $relationships, array $post_ids_old_new ) {
	global $wpdb;

	$table        = ContentConnect::instance()->get_table( 'p2p' );
	$post_to_post = $table->get_table_name();

	foreach ( $relationships as $relationship ) {
		// phpcs:disable
		$wpdb->query(
			$wpdb->prepare(
				"
				INSERT INTO {$post_to_post} (`id1`, `id2`, `name`, `order`)
				VALUES (%s, %s, %s, %s);
				",
				$post_ids_old_new[ $relationship['id1'] ] ?? $relationship['id1'],
				$post_ids_old_new[ $relationship['id2'] ] ?? $relationship['id2'],
				$relationship['name'],
				$relationship['order']
			)
		);
		// phpcs:enable
	}
}

/**
 * Import WordPress options to the wp_options table.
 *
 * @param array $options WordPress options to import.
 */
function import_options( array $options ): void {
	foreach ( $options as $name => $value ) {
		update_option( $name, $value );
	}
}
