<?php
/**
 * Functions that export ACM blueprints.
 *
 * @package AtlasContentModeler
 */

namespace WPE\AtlasContentModeler\Blueprint\Export;

use WP_Error;
use ZipArchive;
use function WPE\AtlasContentModeler\Blueprint\Import\is_acm_media_field_meta;
use \WPE\AtlasContentModeler\ContentConnect\Plugin as ContentConnect;

/**
 * Generates meta data for ACM blueprints.
 *
 * @param array $args Optional overrides for default meta.
 * @return array
 */
function generate_meta( array $args = [] ): array {
	$acm_blueprint_schema_version = '1.0';
	$plugin                       = get_plugin_data( ATLAS_CONTENT_MODELER_FILE );

	$defaults = [
		'version'     => '1.0',
		'name'        => __( 'ACM Blueprint', 'atlas-content-modeler' ),
		'description' => '',
		'min-wp'      => get_bloginfo( 'version' ),
		'min-acm'     => $plugin['Version'],
	];

	$args = wp_parse_args( $args, $defaults );

	return [
		'schema'      => $acm_blueprint_schema_version,
		'version'     => $args['version'],
		'name'        => $args['name'],
		'description' => $args['description'],
		'requires'    => [
			'wordpress' => $args['min-wp'],
			'acm'       => $args['min-acm'],
		],
	];
}

/**
 * Collects posts for the manifest file.
 *
 * @param array $post_types Optional overrides for post types to collect.
 * @return array
 */
function collect_posts( array $post_types = [] ): array {
	if ( empty( $post_types ) ) {
		return [];
	}

	$posts = get_posts(
		[
			'post_status' => 'publish',
			'post_type'   => $post_types,
			'numberposts' => -1, // All posts.
		]
	);

	$posts_keyed_by_id = [];

	foreach ( $posts as $post ) {
		$post = $post->to_array();
		unset( $post['guid'] ); // Strips the URL of the generating site. GUIDs are regenerated on import.
		$posts_keyed_by_id[ $post['ID'] ] = $post;
	}

	return $posts_keyed_by_id;
}

/**
 * Collects post tags for the passed `$posts`.
 *
 * @param array $posts Posts to get tags for.
 * @return array Term arrays keyed by post ID.
 */
function collect_post_tags( array $posts ): array {
	$tag_data = [];

	foreach ( $posts as $post ) {
		$taxonomies = get_post_taxonomies( $post['ID'] );

		foreach ( $taxonomies as $taxonomy ) {
			if ( ! has_term( '', $taxonomy, $post['ID'] ) ) {
				continue;
			}

			$tags = get_the_terms( $post['ID'], $taxonomy );

			if ( ! empty( $tags ) && ! is_wp_error( $tags ) ) {
				$tags_as_arrays = array_map(
					function( $tag ) {
						return $tag->to_array();
					},
					$tags
				);
			}

			if ( ! empty( $tags_as_arrays ) ) {
				$tag_data[ $post['ID'] ] = array_merge(
					$tag_data[ $post['ID'] ] ?? [],
					$tags_as_arrays
				);
			}
		}
	}

	return $tag_data;
}

/**
 * Collects post meta for the provided `$posts`.
 *
 * @param array $posts The posts to collect meta for.
 * @return array
 */
function collect_post_meta( array $posts ): array {
	$all_meta = [];

	foreach ( $posts as $post ) {
		$meta = get_post_meta( $post['ID'], '', true );

		if ( is_array( $meta ) ) {
			unset( $meta['_edit_last'] );
			unset( $meta['_edit_lock'] );
			unset( $meta['_encloseme'] );
			unset( $meta['_pingme'] );

			if ( ! empty( $meta ) ) {
				foreach ( $meta as $key => $value ) {
					$all_meta[ $post['ID'] ][] = [
						'meta_key'   => $key, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
						'meta_value' => is_array( $value ) ? $value[0] : $value, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
					];
				}
			}
		}
	}

	return $all_meta;
}

/**
 * Copies all media referenced in post_meta to the passed `$path`
 * and returns a map of the media ID and new media file path.
 *
 * Post meta that references media includes featured images for posts and
 * ACM media fields, where post meta values are the ID of the media file.
 *
 * @param array  $manifest The ACM manifest file.
 * @param string $path Root path to copy media to (excluding /media/ subfolder).
 * @return array|WP_Error Media ids (keys) to relative file path (values),
 *                        or WP_Error if a media file could not be copied.
 */
function collect_media( array $manifest, string $path ) {
	$media_ids_to_paths = [];
	$media_copied       = [];
	$post_meta          = $manifest['post_meta'] ?? [];

	if ( ! empty( $post_meta ) ) {
		delete_folder( $path . '/media/' );
	}

	foreach ( $post_meta as $post_id => $metas ) {
		foreach ( $metas as $meta ) {
			$is_thumbnail_meta       = $meta['meta_key'] === '_thumbnail_id';
			$is_acm_media_field_meta = is_acm_media_field_meta(
				$manifest,
				$post_id,
				$meta['meta_key']
			);

			if ( ! $is_thumbnail_meta && ! $is_acm_media_field_meta ) {
				continue;
			}

			$image_path = wp_get_original_image_path( $meta['meta_value'] );

			if ( ! $image_path ) {
				continue;
			}

			$media_already_copied = array_key_exists(
				$image_path,
				$media_copied
			);

			if ( $media_already_copied ) {
				/**
				 * Different media IDs can point to the same media file path.
				 * This check ensures we don't duplicate the same media file.
				 * We just refer to the original copy from subsequent IDs that
				 * point to the same file.
				 */
				$media_ids_to_paths[ $meta['meta_value'] ] = $media_copied[ $image_path ]; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
				continue;
			}

			$copy_media = copy_media( $image_path, $path, $meta['meta_value'] );

			if ( ! $copy_media['success'] ) {
				return new WP_Error(
					'acm_media_write_error',
					/* translators: full path to file. */
					sprintf( esc_html__( 'Error saving temporary file to %s', 'atlas-content-modeler' ), $copy_media['path'] )
				);
			}

			$media_relative_path                       = $copy_media['path'];
			$media_copied[ $image_path ]               = $media_relative_path;
			$media_ids_to_paths[ $meta['meta_value'] ] = $media_relative_path; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
		}
	}

	return $media_ids_to_paths;
}

/**
 * Copy media at the `$image_path` to the `$destination_folder`.
 *
 * Used to collect media in a temporary location before it is compressed with
 * the ACM manifest.
 *
 * @param string $image_path Path to the original image.
 * @param string $destination_folder Path to the blueprint folder.
 * @param string $media_id The media ID associated with the media.
 * @return array With 'success' (bool) and 'path' containing the
 *               location of the copied file.
 */
function copy_media( string $image_path, string $destination_folder, string $media_id ): array {
	global $wp_filesystem;

	if ( empty( $wp_filesystem ) ) {
		require_once ABSPATH . '/wp-admin/includes/file.php';
		\WP_Filesystem();
	}

	$destination_root = trailingslashit( $destination_folder );

	/**
	 * Append `$media_id` so that two different files both named 'example.jpg'
	 * in different WP upload subfolders are written to different destinations.
	 *
	 * If we copied two files from `uploads/1999/01/example.jpg` and
	 * `uploads/2022/02/example.jpg` to `media/example.jpg` without the
	 * unique subfolder, one file would overwrite the other.
	 */
	$media_folder = "{$destination_root}media/{$media_id}/";

	if ( ! $wp_filesystem->exists( $media_folder ) ) {
		wp_mkdir_p( $media_folder );
	}

	$copy_succeeded = $wp_filesystem->copy(
		$image_path,
		$media_folder . basename( $image_path ),
		true
	);

	return [
		'success' => $copy_succeeded,
		'path'    => str_replace(
			$destination_root,
			'',
			$media_folder . basename( $image_path )
		),
	];
}

/**
 * Deletes the passed `$path`.
 *
 * @param string $path Path to remove.
 * @return bool True on success, false on failure.
 */
function delete_folder( string $path ) {
	global $wp_filesystem;

	if ( empty( $wp_filesystem ) ) {
		require_once ABSPATH . '/wp-admin/includes/file.php';
		\WP_Filesystem();
	}

	return $wp_filesystem->rmdir( $path, true );
}

/**
 * Collects relevant ACM relationships from the wp_acm_post_to_post table.
 *
 * Relationships link two or more posts via an ACM relationship field.
 *
 * A “relevant” relationship is one that links two post IDs from the passed
 * `$posts` array. We don't collect other relationships because we don't need
 * to restore any between posts we are not importing.
 *
 * @param array $posts Posts to collect relationships for, keyed by post ID.
 * @return array
 */
function collect_relationships( array $posts ): array {
	global $wpdb;

	$relevant_relationships = [];

	$table         = ContentConnect::instance()->get_table( 'p2p' );
	$post_to_post  = $table->get_table_name();
	$relationships = $wpdb->get_results( "SELECT * FROM {$post_to_post};", ARRAY_A ); // phpcs:ignore

	foreach ( $relationships as $relationship ) {
		if (
			array_key_exists( $relationship['id1'], $posts )
			&& array_key_exists( $relationship['id2'], $posts )
		) {
			$relevant_relationships[] = $relationship;
		}
	}

	return $relevant_relationships;
}

/**
 * Collects passed `$options` from the wp_options table.
 *
 * @param array $options List of options to collect.
 * @return array WordPress option values keyed by option name.
 */
function collect_options( array $options ): array {
	$option_values = [];

	foreach ( $options as $option ) {
		$option_values[ $option ] = get_option( $option );
	}

	return $option_values;
}

/**
 * Writes the acm.json manifest file to the given `$path`.
 *
 * @param array  $manifest ACM manifest data.
 * @param string $path Where to write the manifest file.
 * @return string|WP_Error The path to manifest or an error if the file could
 *                         not be written.
 */
function write_manifest( array $manifest, string $path ) {
	global $wp_filesystem;

	if ( empty( $wp_filesystem ) ) {
		require_once ABSPATH . '/wp-admin/includes/file.php';
		\WP_Filesystem();
	}

	$path = trailingslashit( $path );

	if ( ! $wp_filesystem->exists( $path ) ) {
		wp_mkdir_p( $path );
	}

	$write_path = $path . 'acm.json';
	$saved      = $wp_filesystem->put_contents( $write_path, wp_json_encode( $manifest, JSON_PRETTY_PRINT ) );

	if ( ! $saved ) {
		return new WP_Error(
			'acm_manifest_write_error',
			/* translators: full path to file. */
			sprintf( esc_html__( 'Error saving temporary file to %s', 'atlas-content-modeler' ), $write_path )
		);
	}

	return $write_path;
}

/**
 * Compress the blueprint files into a zip file at `$path` named `$zip_name`.
 *
 * @param string $path The location of blueprint files to zip. Also used as the
 *                     folder where the zip file will be created.
 * @param string $zip_name The name of the zip file, excluding '.zip'.
 * @return string|WP_Error Path to zip file or error if zip creation failed.
 */
function zip_blueprint( string $path, string $zip_name ) {
	global $wp_filesystem;

	if ( ! class_exists( 'ZipArchive' ) ) {
		return new WP_Error(
			'acm_zip_error',
			esc_html__( 'Unable to create blueprint zip file. ZipArchive not available.', 'atlas-content-modeler' )
		);
	}

	if ( empty( $wp_filesystem ) ) {
		require_once ABSPATH . '/wp-admin/includes/file.php';
		\WP_Filesystem();
	}

	$zip      = new ZipArchive();
	$path     = trailingslashit( $path );
	$zip_path = $path . $zip_name . '.zip';

	$create_zip = $zip->open( $zip_path, ZipArchive::CREATE );

	if ( ! $create_zip ) {
		return new WP_Error(
			'acm_zip_error',
			esc_html__( 'Unable to create blueprint zip file.', 'atlas-content-modeler' )
		);
	}

	$add_manifest = $zip->addFile( $path . '/acm.json', $zip_name . '/acm.json' );

	if ( ! $add_manifest ) {
		return new WP_Error(
			'acm_zip_error',
			esc_html__( 'Could not add manifest to zip file.', 'atlas-content-modeler' )
		);
	}

	if ( $wp_filesystem->exists( $path . '/media/' ) ) {
		$add_media = $zip->addGlob(
			$path . '/media/**/*',
			0,
			[
				'add_path'    => trailingslashit( $zip_name ),
				'remove_path' => $path,
			]
		);

		if ( ! $add_media ) {
			return new WP_Error(
				'acm_zip_error',
				esc_html__( 'Could not add media to zip file.', 'atlas-content-modeler' )
			);
		}
	}

	$save_zip = $zip->close();

	if ( ! $save_zip ) {
		return new WP_Error(
			'acm_zip_error',
			esc_html__( 'Could not save blueprint zip file.', 'atlas-content-modeler' )
		);
	}

	return $zip_path;
}

/**
 * Gives the path to a directory where blueprint files can be temporarily
 * written before they are compressed for download.
 *
 * @param array $manifest The full ACM manifest file, used to determine the
 *                        name of the directory.
 * @return string|WP_Error The temporary directory path or an error if the
 *                         manifest name is missing.
 */
function get_acm_temp_dir( $manifest ) {
	$manifest_name = trim( $manifest['meta']['name'] ?? '' );
	if ( empty( $manifest_name ) ) {
		return new WP_Error(
			'acm_manifest_name_missing',
			esc_html__( 'The manifest has a missing or empty meta.name property.', 'atlas-content-modeler' )
		);
	}

	$temp_dir    = get_temp_dir();
	$folder_name = sanitize_title_with_dashes( $manifest_name );

	if ( empty( $folder_name ) ) {
		return new WP_Error(
			'acm_manifest_name_bad',
			esc_html__(
				'The manifest meta.name resulted in an empty folder name. Check that it contains at least one alphanumeric ASCII character.',
				'atlas-content-modeler'
			)
		);
	}

	return "{$temp_dir}acm/{$folder_name}/";
}
