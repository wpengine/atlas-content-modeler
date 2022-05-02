<?php
/**
 * REST related functions.
 *
 * @package AtlasContentModeler
 */

declare(strict_types=1);

namespace WPE\AtlasContentModeler\REST_API;

/**
 * Format a response for ACM response format.
 *
 * @param  bool  $success Response status.
 * @param  array $data    The response data.
 *
 * @return array The response data as an array.
 */
function format_response_data( bool $success, array $data ): array {
	return \compact( 'success', 'data' );
}

/**
 * Create a WP_REST_Response that uses ACM response data formatting.
 *
 * @param bool  $success Whether the response was successful or not.
 * @param array $data    The data for the response.
 * @param int   $status  Optional http status code. Default 200.
 * @param array $headers Optional http headers. Default empty array.
 *
 * @return \WP_REST_Response A WP_REST_Response object.
 */
function create_rest_response( bool $success, array $data, int $status = 200, array $headers = [] ): \WP_REST_Response {
	$data = format_response_data( $success, $data );

	return new \WP_REST_Response( $data, $status, $headers );
}
