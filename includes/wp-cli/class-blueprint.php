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

use function WPE\AtlasContentModeler\Blueprint\Import\unzip_blueprint;
use function WPE\AtlasContentModeler\Blueprint\Import\get_manifest;
use function WPE\AtlasContentModeler\Blueprint\Import\check_versions;
use function WPE\AtlasContentModeler\Blueprint\Import\import_taxonomies;
use function WPE\AtlasContentModeler\Blueprint\Import\import_posts;
use function WPE\AtlasContentModeler\Blueprint\Import\import_terms;
use function WPE\AtlasContentModeler\Blueprint\Import\tag_posts;
use function WPE\AtlasContentModeler\Blueprint\Import\import_media;
use function WPE\AtlasContentModeler\Blueprint\Import\import_post_meta;
use function WPE\AtlasContentModeler\Blueprint\Import\import_acm_relationships;
use function WPE\AtlasContentModeler\REST_API\Models\create_models;

/**
 * Blueprint subcommands for the `wp acm blueprint` WP-CLI command.
 */
class Blueprint {
	/**
	 * Imports an ACM blueprint from a URL.
	 *
	 * ## OPTIONS
	 *
	 * <url>
	 * : The URL of the blueprint zip file to fetch.
	 *
	 * ## EXAMPLES
	 *
	 *     wp acm blueprint import https://example.com/path/to/blueprint.zip
	 *
	 * @param array $args Options passed to the command.
	 */
	public function import( $args ) {
		list( $url ) = $args;
		// \WP_CLI::log( 'Fetching zip.' );
		// TODO: Fetch zip from $url and save to wp_upload_dir().

		\WP_CLI::log( 'Unzipping blueprint.' );
		// TODO: Replace this with the zip path from the previous step.
		$upload_dir       = wp_upload_dir();
		$zip_file         = $upload_dir['path'] . '/acm-rabbits.zip';
		$blueprint_folder = unzip_blueprint( $zip_file );

		if ( is_wp_error( $blueprint_folder ) ) {
			\WP_CLI::error( $blueprint_folder->get_error_message() );
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
				\WP_CLI::error( $taxonomy_import->get_error_message() );
			}
		}

		$post_ids_old_new = [];
		if ( ! empty( $manifest['posts'] ?? [] ) ) {
			\WP_CLI::log( 'Importing posts.' );
			$post_ids_old_new = import_posts( $manifest['posts'] );
		}

		$term_ids_old_new = [];
		if ( ! empty( $manifest['terms'] ?? [] ) ) {
			\WP_CLI::log( 'Importing terms.' );
			$term_ids_old_new = import_terms( $manifest['terms'] );

			if ( is_wp_error( $term_ids_old_new ) ) {
				\WP_CLI::error( $term_ids_old_new->get_error_message() );
			}
		}

		if ( ! empty( $manifest['post_terms'] ?? [] ) ) {
			\WP_CLI::log( 'Tagging posts.' );
			$tag_posts = tag_posts( $manifest['post_terms'], $post_ids_old_new, $term_ids_old_new );

			if ( is_wp_error( $tag_posts ) ) {
				\WP_CLI::error( $tag_posts->get_error_message() );
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

		\WP_CLI::log( 'Done!' );
	}

	/**
	 * Exports an ACM blueprint using the current state of the site.
	 */
	public function export() {
		\WP_CLI::log( 'Collecting ACM data…' );
		\WP_CLI::log( 'Collecting entries…' );
		\WP_CLI::log( 'Collecting media…' );
		\WP_CLI::log( 'Generating zip…' );
		\WP_CLI::log( 'Done! Blueprint saved to path/to/file.zip.' );
	}
}
