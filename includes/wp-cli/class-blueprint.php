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
	 */
	public function export() {
		\WP_CLI::log( 'Collecting ACM data…' );
		\WP_CLI::log( 'Collecting entries…' );
		\WP_CLI::log( 'Collecting media…' );
		\WP_CLI::log( 'Generating zip…' );
		\WP_CLI::log( 'Done! Blueprint saved to path/to/file.zip.' );
	}
}
