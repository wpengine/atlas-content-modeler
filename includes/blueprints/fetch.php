<?php
/**
 * Functions that fetch and save blueprint archives.
 *
 * @package AtlasContentModeler
 */

namespace WPE\AtlasContentModeler\Blueprint\Fetch;

use WP_Error;

/**
 * Gets blueprint from either local or remote path.
 *
 * @param string $path The path to the file.
 * @return string|WP_Error
 */
function get_blueprint( string $path ) {
	if ( filter_var( $path, FILTER_VALIDATE_URL ) ) {
		return get_remote_blueprint( $path );
	}

	return get_local_blueprint( $path );
}

/**
 * Downloads a blueprint zip file from the specified local path.
 *
 * @param string $path The path to the zip file.
 * @return string|WP_Error
 */
function get_local_blueprint( string $path ) {
	if ( ! is_readable( $path ) ) {
		return new WP_Error(
			'acm_blueprint_file_not_readable',
			esc_html__( 'File or directory not found or readable.', 'atlas-content-modeler' )
		);
	}

	return file_get_contents( $path ); // phpcs:ignore
}

/**
 * Downloads a blueprint zip file from the specified remote URL.
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

	$response = wp_remote_get( $url, [ 'timeout' => 15 ] );

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
 * @param string $blueprint The blueprint zip file or path to a local blueprint directory.
 * @param string $filename  The name of the file or directory to be saved.
 *
 * @return string|WP_Error Local blueprint zip file or directory destination path on success.
 */
function save_blueprint_to_upload_dir( string $blueprint, string $filename ) {
	global $wp_filesystem;
	if ( empty( $wp_filesystem ) ) {
		require_once ABSPATH . '/wp-admin/includes/file.php';
		\WP_Filesystem();
	}

	$destination      = trailingslashit( wp_upload_dir()['path'] ) . $filename;
	$is_absolute_path = $blueprint[0] === '/' ?? false;

	/**
	 * Copy blueprints given as a path to a local directory to the WordPress
	 * upload directory. Ensures media is accessible to WordPress, and will
	 * stay accessible if the original blueprint path is moved or removed.
	 */
	if (
		$is_absolute_path &&
		pathinfo( $blueprint, PATHINFO_EXTENSION ) === ''
	) {
		if ( ! $wp_filesystem->is_dir( $blueprint ) ) {
			return new WP_Error(
				'acm_blueprint_save_error',
				/* translators: path to blueprint */
				sprintf( esc_html__( 'Could not read directory at %s', 'atlas-content-modeler' ), $blueprint )
			);
		}

		$wp_filesystem->mkdir( $destination );

		$copied = copy_dir( $blueprint, $destination );

		if ( ! $copied ) {
			return new WP_Error(
				'acm_blueprint_save_error',
				/* translators: %1$s: path to blueprint, %2$s: path to attempted copy destination. */
				sprintf( esc_html__( 'Error copying directory from %1$s to %2$s', 'atlas-content-modeler' ), $blueprint, $destination )
			);
		}

		return $destination;
	}

	/**
	 * Assume now that the blueprint is a zip file and not a folder. Save it
	 * to a temporary location and check the MIME type before moving it to the
	 * final destination in the WordPress upload directory.
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
