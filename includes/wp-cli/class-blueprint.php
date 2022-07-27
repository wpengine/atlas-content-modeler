<?php
/**
 * WP-CLI commands to export and import ACM blueprints.
 *
 * A blueprint is a zip file containing:
 *
 * - An acm.json file that describes models, taxonomies, entries, terms and
 *   other ACM data to restore.
 * - Media files to import for entries with media field data.
 *
 * @package AtlasContentModeler
 */

declare(strict_types=1);

namespace WPE\AtlasContentModeler\WP_CLI;

use function WPE\AtlasContentModeler\Blueprint\Import\{
	check_versions,
	get_manifest,
	import_acm_relationships,
	import_media,
	import_post_meta,
	import_posts,
	import_taxonomies,
	import_terms,
	import_options,
	tag_posts,
	unzip_blueprint
};
use function WPE\AtlasContentModeler\REST_API\Models\create_models;
use function WPE\AtlasContentModeler\Blueprint\Fetch\get_blueprint;
use function WPE\AtlasContentModeler\Blueprint\Fetch\save_blueprint_to_upload_dir;
use function WPE\AtlasContentModeler\ContentRegistration\get_registered_content_types;
use function WPE\AtlasContentModeler\ContentRegistration\Taxonomies\get_acm_taxonomies;
use function WPE\AtlasContentModeler\Blueprint\Export\{
	collect_media,
	collect_post_meta,
	collect_post_tags,
	collect_posts,
	collect_relationships,
	collect_options,
	delete_folder,
	generate_meta,
	get_acm_temp_dir,
	write_manifest,
	zip_blueprint
};

/**
 * Blueprint subcommands for the `wp acm blueprint` WP-CLI command.
 */
class Blueprint {
	/**
	 * Imports an ACM blueprint from a PATH or URL.
	 *
	 * ## OPTIONS
	 *
	 * <path>
	 * : The URL or local path of the blueprint zip file, or local path to the
	 * blueprint folder containing the acm.json manifest file. Local paths must
	 * be absolute.
	 *
	 * ## EXAMPLES
	 *
	 *     wp acm blueprint import https://example.com/path/to/blueprint.zip
	 *     wp acm blueprint import /local/path/to/blueprint.zip
	 *     wp acm blueprint import /local/path/to/blueprint-folder/
	 *
	 * @param array $args Options passed to the command.
	 * @param array $assoc_args Optional flags passed to the command.
	 */
	public function import( $args, $assoc_args ) {
		list( $path )      = $args;
		$path_is_directory = pathinfo( $path, PATHINFO_EXTENSION ) === '';

		if ( $path === 'demo' ) {
			\WP_CLI::runcommand(
				'acm blueprint import ' . __DIR__ . '/demo',
				[
					'launch' => false, // Reuse current process.
				]
			);
			return;
		}

		$path_is_directory = pathinfo( $path, PATHINFO_EXTENSION ) === '';

		if ( $path_is_directory ) {
			$blueprint_folder = save_blueprint_to_upload_dir( $path, basename( $path ) );
			if ( is_wp_error( $blueprint_folder ) ) {
				\WP_CLI::error( $blueprint_folder->get_error_message() );
			}
		}

		if ( ! $path_is_directory ) {
			\WP_CLI::log( 'Fetching blueprint.' );
			$zip_file = get_blueprint( $path );
			if ( is_wp_error( $zip_file ) ) {
				\WP_CLI::error( $zip_file->get_error_message() );
			}

			$valid_file = save_blueprint_to_upload_dir( $zip_file, basename( $path ) );
			if ( is_wp_error( $valid_file ) ) {
				\WP_CLI::error( $valid_file->get_error_message() );
			}

			\WP_CLI::log( 'Unzipping.' );
			$blueprint_folder = unzip_blueprint( $valid_file );

			if ( is_wp_error( $blueprint_folder ) ) {
				\WP_CLI::error( $blueprint_folder->get_error_message() );
			}
		}

		\WP_CLI::log( 'Verifying ACM manifest.' );
		$manifest = get_manifest( $blueprint_folder );

		if ( is_wp_error( $manifest ) ) {
			\WP_CLI::error(
				$manifest->get_error_message( 'acm_manifest_error' )
			);
		}

		\WP_CLI::log( 'Checking minimum versions.' );
		$version_test = check_versions( $manifest );

		if ( is_wp_error( $version_test ) ) {
			\WP_CLI::error(
				$version_test->get_error_message( 'acm_version_error' )
			);
		}

		if ( ! empty( $manifest['models'] ?? [] ) ) {
			\WP_CLI::log( 'Importing ACM models and fields.' );

			$model_import = create_models( $manifest['models'] );

			if ( is_wp_error( $model_import ) ) {
				\WP_CLI::error( $model_import->get_error_message() );
			}
		}

		if ( ! empty( $manifest['taxonomies'] ?? [] ) ) {
			\WP_CLI::log( 'Importing ACM taxonomies.' );

			$taxonomy_import = import_taxonomies( $manifest['taxonomies'] );

			if ( is_wp_error( $taxonomy_import ) ) {
				foreach ( $taxonomy_import->get_error_messages() as $message ) {
					\WP_CLI::warning( $message );
				}
			}
		}

		$post_ids_old_new = [];
		if ( ! empty( $manifest['posts'] ?? [] ) ) {
			\WP_CLI::log( 'Importing posts.' );
			$post_ids_old_new = import_posts( $manifest['posts'] );
		}

		$term_ids_old_new = [];
		if ( ! empty( $manifest['post_terms'] ?? [] ) ) {
			\WP_CLI::log( 'Importing terms.' );
			$term_ids_old_new = import_terms( $manifest['post_terms'] );

			if ( is_wp_error( $term_ids_old_new['errors'] ) ) {
				foreach ( $term_ids_old_new['errors']->get_error_messages() as $message ) {
					\WP_CLI::warning( $message );
				}
			}
		}

		if ( ! empty( $manifest['post_terms'] ?? [] ) ) {
			\WP_CLI::log( 'Tagging posts.' );
			$tag_posts = tag_posts(
				$manifest['post_terms'],
				$post_ids_old_new,
				$term_ids_old_new['ids'] ?? []
			);

			if ( is_wp_error( $tag_posts ) ) {
				foreach ( $tag_posts->get_error_messages() as $message ) {
					\WP_CLI::warning( $message );
				}
			}
		}

		$media_ids_old_new = [];
		if ( ! empty( $manifest['media'] ?? [] ) ) {
			\WP_CLI::log( 'Importing media.' );

			$media_ids_old_new = import_media( $manifest['media'], $blueprint_folder );

			if ( is_wp_error( $media_ids_old_new ) ) {
				\WP_CLI::error( $media_ids_old_new->get_error_message() );
			}
		}

		if ( ! empty( $manifest['post_meta'] ?? [] ) ) {
			\WP_CLI::log( 'Importing post meta.' );
			import_post_meta(
				$manifest,
				$post_ids_old_new,
				$media_ids_old_new
			);
		}

		if ( ! empty( $manifest['relationships'] ?? [] ) ) {
			\WP_CLI::log( 'Restoring ACM relationships.' );
			import_acm_relationships(
				$manifest['relationships'],
				$post_ids_old_new
			);
		}

		if ( ! empty( $manifest['wp-options'] ?? [] ) ) {
			\WP_CLI::log( 'Restoring WordPress options.' );
			import_options( $manifest['wp-options'] );
		}

		\WP_CLI::success( 'Import complete.' );
	}

	/**
	 * Exports an ACM blueprint using the current state of the site.
	 *
	 * [--name]
	 * : Optional blueprint name. Used in the manifest and zip file name.
	 * Defaults to “ACM Blueprint” resulting in acm-blueprint.zip.
	 *
	 * [--description]
	 * : Optional description of the blueprint.
	 *
	 * [--min-wp]
	 * : Minimum WordPress version. Defaults to current WordPress version.
	 *
	 * [--min-acm]
	 * : Minimum Atlas Content Modeler plugin version. Defaults to current
	 * ACM version.
	 *
	 * [--version]
	 * : Optional blueprint version. Defaults to 1.0.
	 *
	 * [--post-types]
	 * : Post types to collect posts for, separated by commas. Defaults to post,
	 * page and all registered ACM post types.
	 *
	 * [--wp-options]
	 * : Named wp_options keys to export, separated by commas. Empty by default.
	 *
	 * [--open]
	 * : Open the folder containing the generated zip on success (macOS only,
	 * requires that `shell_exec()` has not been disabled).
	 *
	 * @param array $args Options passed to the command, keyed by integer.
	 * @param array $assoc_args Options keyed by string.
	 */
	public function export( $args, $assoc_args ) {
		$meta_overrides = [];

		\WP_CLI::log( 'Collecting ACM data.' );
		foreach ( [ 'name', 'description', 'min-wp', 'min-acm', 'version' ] as $key ) {
			if ( ( $assoc_args[ $key ] ?? false ) ) {
				$meta_overrides[ $key ] = $assoc_args[ $key ];
			}
		}

		$meta     = generate_meta( $meta_overrides );
		$manifest = [ 'meta' => $meta ];
		$temp_dir = get_acm_temp_dir( $manifest );

		if ( is_wp_error( $temp_dir ) ) {
			\WP_CLI::error( $temp_dir->get_error_message() );
		}

		delete_folder( $temp_dir ); // Cleans up previous exports.

		\WP_CLI::log( 'Collecting ACM models.' );
		$manifest['models'] = get_registered_content_types();

		\WP_CLI::log( 'Collecting ACM taxonomies.' );
		$manifest['taxonomies'] = get_acm_taxonomies();

		\WP_CLI::log( 'Collecting posts.' );
		$post_types = array_merge(
			array_keys( get_registered_content_types() ),
			[ 'post', 'page' ]
		);
		if ( ! empty( $assoc_args['post-types'] ) ) {
			$post_types = array_map(
				'trim',
				explode( ',', $assoc_args['post-types'] )
			);
		}
		$manifest['posts'] = collect_posts( $post_types );

		if ( ! empty( $manifest['posts'] ?? [] ) ) {
			\WP_CLI::log( 'Collecting post tags.' );
			$manifest['post_terms'] = collect_post_tags(
				$manifest['posts'] ?? []
			);
		}

		\WP_CLI::log( 'Collecting post meta.' );
		$manifest['post_meta'] = collect_post_meta(
			$manifest['posts'] ?? []
		);

		if ( ! empty( $manifest['post_meta'] ?? [] ) ) {
			\WP_CLI::log( 'Collecting media.' );
			$manifest['media'] = collect_media(
				$manifest,
				$temp_dir
			);
		}

		\WP_CLI::log( 'Collecting ACM relationships' );
		$manifest['relationships'] = collect_relationships(
			$manifest['posts'] ?? []
		);

		if ( ! empty( $assoc_args['wp-options'] ) ) {
			$wp_options             = array_map(
				'trim',
				explode( ',', $assoc_args['wp-options'] )
			);
			$manifest['wp-options'] = collect_options( $wp_options );
		}

		\WP_CLI::log( 'Writing acm.json manifest.' );
		$write_manifest = write_manifest( $manifest, $temp_dir );

		if ( is_wp_error( $write_manifest ) ) {
			\WP_CLI::error( $write_manifest->get_error_message() );
		}

		\WP_CLI::log( 'Generating zip.' );
		$path_to_zip = zip_blueprint(
			$temp_dir,
			sanitize_title_with_dashes( $manifest['meta']['name'] )
		);

		if ( is_wp_error( $path_to_zip ) ) {
			\WP_CLI::error( $path_to_zip->get_error_message() );
		}

		if (
			PHP_OS === 'Darwin'
			&& ( $assoc_args['open'] ?? false )
			&& function_exists( 'shell_exec' )
		) {
			\WP_CLI::log( 'Opening blueprint temp folder.' );
			shell_exec( "open {$temp_dir}" ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.system_calls_shell_exec
		}

		\WP_CLI::success( sprintf( 'Blueprint saved to %s.', $path_to_zip ) );
	}
}
