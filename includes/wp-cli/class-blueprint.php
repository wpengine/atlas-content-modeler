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

use function WPE\AtlasContentModeler\Blueprint\Fetch\get_remote_blueprint;
use function WPE\AtlasContentModeler\Blueprint\Fetch\save_blueprint_to_upload_dir;
use function WPE\AtlasContentModeler\Blueprint\Export\{
	generate_meta,
	get_acm_temp_dir,
	write_manifest
};

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
		\WP_CLI::log( 'Fetching zip.' );

		$zip_file = get_remote_blueprint( $url );
		if ( is_wp_error( $zip_file ) ) {
			\WP_CLI::error( $zip_file->get_error_message() );
		}

		$valid_file = save_blueprint_to_upload_dir( $zip_file, basename( $url ) );
		if ( is_wp_error( $valid_file ) ) {
			\WP_CLI::error( $valid_file->get_error_message() );
		}

		\WP_CLI::log( 'Unzipping.' );
		\WP_CLI::log( 'Verifying ACM manifest.' );
		\WP_CLI::log( 'Importing ACM models and fields.' );
		\WP_CLI::log( 'Importing ACM taxonomies and terms.' );
		\WP_CLI::log( 'Importing posts.' );
		\WP_CLI::log( 'Importing media.' );
		\WP_CLI::log( 'Importing post meta.' );
		\WP_CLI::log( 'Importing post terms.' );
		\WP_CLI::log( 'Restoring ACM relationships.' );
		\WP_CLI::log( 'Done!' );
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

		$meta = generate_meta( $meta_overrides );

		\WP_CLI::log( 'Collecting entries.' );
		\WP_CLI::log( 'Collecting media.' );

		$manifest = [
			'meta' => $meta,
		];

		\WP_CLI::log( 'Writing acm.json manifest.' );
		$temp_dir = get_acm_temp_dir( $manifest );
		write_manifest( $manifest, $temp_dir );

		\WP_CLI::log( 'Generating zip.' );

		\WP_CLI::success( 'Blueprint saved to path/to/file.zip.' );
	}
}
