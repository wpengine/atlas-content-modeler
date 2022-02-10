<?php
/**
 * Functions that fetch and save blueprint archives.
 *
 * @package AtlasContentModeler
 */

namespace WPE\AtlasContentModeler\Blueprint\Fetch;

use WP_Error;

/**
 * Downloads a blueprint zip file from the specified URL.
 *
 * @param string $url URL to the blueprint zip file.
 *
 * @return string|WP_Error
 */
function get_remote_blueprint( string $url ) {
	if ( ! filter_var( $url, FILTER_VALIDATE_URL ) ) {
		return new WP_Error(
			'acm_blueprint_invalid_url',
			esc_html__( 'Please provide a URL to a blueprint zip file.', 'atlas-content-modeler' ),
			[ 'status' => 400 ]
		);
	}

	$response = wp_remote_get( $url );

	if ( is_wp_error( $response ) ) {
		return new WP_Error(
			'acm_blueprint_http_error',
			$response->get_error_message(),
			[ 'status' => $response->get_error_code() ]
		);
	}

	if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
		return new WP_Error(
			'acm_blueprint_http_error_response_code',
			sprintf(
				/* translators: error message text. */
				esc_html__(
					'Received unexpected error downloading zip. Error: %s',
					'atlas-content-modeler'
				),
				wp_remote_retrieve_response_code( $response ) . ' ' . wp_remote_retrieve_response_message( $response )
			),
			[ 'status' => wp_remote_retrieve_response_code( $response ) ]
		);
	}

	$zip_file = wp_remote_retrieve_body( $response );
	if ( empty( $zip_file ) ) {
		return new WP_Error(
			'acm_blueprint_http_error_empty_body',
			esc_html__( 'Received empty response body.', 'atlas-content-modeler' )
		);
	}

	return $zip_file;
}

/**
 * Saves the provided blueprint zip file to the uploads directory.
 *
 * @param string $blueprint The blueprint zip file.
 * @param string $filename  The name of the file to be saved.
 *
 * @return string|WP_Error Local blueprint zip file destination path on success.
 */
function save_blueprint_to_upload_dir( string $blueprint, string $filename ) {
	global $wp_filesystem;
	if ( empty( $wp_filesystem ) ) {
		require_once ABSPATH . '/wp-admin/includes/file.php';
		\WP_Filesystem();
	}

	$destination = trailingslashit( wp_upload_dir()['path'] ) . $filename;

	/**
	 * Save the blueprint to a temporary location
	 * and check the MIME type before saving to the
	 * final destination in the `wp_upload_dir()` path.
	 */
	$temp_destination = wp_tempnam( $destination );

	$saved = $wp_filesystem->put_contents( $temp_destination, $blueprint );
	if ( ! $saved ) {
		return new WP_Error(
			'acm_blueprint_save_error',
			/* translators: full path to file. */
			sprintf( esc_html__( 'Error saving temporary file to %s', 'atlas-content-modeler' ), $temp_destination )
		);
	}

	if ( mime_content_type( $temp_destination ) !== 'application/zip' ) {
		wp_delete_file( $temp_destination );
		return new WP_Error(
			'acm_blueprint_unsupported_file_type',
			esc_html__( 'Provided file type is not supported. Please provide a link to a valid zip file.', 'atlas-content-modeler' )
		);
	}

	$result = $wp_filesystem->move( $temp_destination, $destination, true );
	if ( ! $result ) {
		wp_delete_file( $temp_destination );
		return new WP_Error(
			'acm_blueprint_save_error',
			/* translators: full path to blueprint file. */
			sprintf( esc_html__( 'Error saving file to %s', 'atlas-content-modeler' ), $destination )
		);
	}

	return $destination;
}
