<?php
/**
 * Functions that fetch and save blueprint archives.
 *
 * @package AtlasContentModeler
 */

namespace WPE\AtlasContentModeler\Blueprint\Fetch;

/**
 * Downloads a blueprint zip file from the specified URL.
 *
 * @param string $url URL to the blueprint zip file.
 *
 * @return string|void
 */
function get_blueprint_from_url( string $url ) {
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
function save_blueprint_to_upload_dir( string $blueprint, string $destination ): bool {
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
