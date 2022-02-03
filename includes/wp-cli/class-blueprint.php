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

		if ( empty( $url ) || ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
			\WP_CLI::error( 'Please provide a valid URL to a blueprint zip file.' );
		}

		$zip_file = $this->get_blueprint_from_url( $url );
		if ( empty( $zip_file ) ) {
			return;
		}

		$destination = trailingslashit( wp_upload_dir()['path'] ) . basename( $url );
		$valid_file  = $this->save_blueprint_to_upload_dir( $zip_file, $destination );
		if ( ! $valid_file ) {
			return;
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
	 */
	public function export() {
		\WP_CLI::log( 'Collecting ACM data…' );
		\WP_CLI::log( 'Collecting entries…' );
		\WP_CLI::log( 'Collecting media…' );
		\WP_CLI::log( 'Generating zip…' );
		\WP_CLI::log( 'Done! Blueprint saved to path/to/file.zip.' );
	}

	/**
	 * Downloads a blueprint zip file from the specified URL.
	 *
	 * @param string $url URL to the blueprint zip file.
	 *
	 * @return string|void
	 */
	private function get_blueprint_from_url( string $url ) {
		$response = wp_remote_get( $url );
		if ( is_wp_error( $response ) ) {
			\WP_CLI::error( $response->get_error_message() );
			return;
		}

		if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
			\WP_CLI::error( 'Received unexpected error downloading zip.' );
			return;
		}

		$zip_file = wp_remote_retrieve_body( $response );
		if ( empty( $zip_file ) ) {
			\WP_CLI::error( 'Error retrieving zip file from response body.' );
			return;
		}

		return $zip_file;
	}

	/**
	 * Saves the provided blueprint zip file to the uploads directory.
	 *
	 * @param string $blueprint The blueprint zip file.
	 * @param string $destination The full destination path, including file name.
	 *
	 * @return bool
	 */
	private function save_blueprint_to_upload_dir( string $blueprint, string $destination ): bool {
		global $wp_filesystem;
		if ( empty( $wp_filesystem ) ) {
			require_once ABSPATH . '/wp-admin/includes/file.php';
			\WP_Filesystem();
		}

		/**
		 * Save the blueprint to a temporary location
		 * and check the MIME type before saving to the
		 * final destination in the `upload_dir()` path.
		 */
		$temp_destination = wp_tempnam( $destination );

		$saved = $wp_filesystem->put_contents( $temp_destination, $blueprint );
		if ( ! $saved ) {
			\WP_CLI::error( sprintf( 'Error saving temporary file to %s', $temp_destination ) );
			return false;
		}

		if ( mime_content_type( $temp_destination ) !== 'application/zip' ) {
			\WP_CLI::error( 'Provided file type is not supported. Please provide a link to a zip file.' );
			wp_delete_file( $temp_destination );
			return false;
		}

		$result = $wp_filesystem->move( $temp_destination, $destination, true );
		if ( ! $result ) {
			\WP_CLI::error( sprintf( 'Error saving file to %s', $destination ) );
			wp_delete_file( $temp_destination );
		}

		return $result;
	}
}
