<?php
/**
 * Registers REST API endpoints for /wpe/atlas/ga-analytics/ routes.
 *
 * @package AtlasContentModeler
 */

declare(strict_types=1);

namespace WPE\AtlasContentModeler\REST_API\GaAnalytics;

use WP_Error;
use WP_REST_Request;

add_action( 'rest_api_init', __NAMESPACE__ . '\register_rest_routes' );
/**
 * Registers custom routes with the WP REST API.
 */
function register_rest_routes(): void {
	// Route for setting feedback banner transient.
	register_rest_route(
		'wpe',
		'/atlas/ga-analytics',
		[
			'methods'             => 'POST',
			'callback'            => __NAMESPACE__ . '\dispatch_ga_analytics',
			'permission_callback' => static function () {
				return current_user_can( 'manage_options' );
			},
		]
	);
}

/**
 * Handles GA requests from the REST API.
 *
 * @param WP_REST_Request $request The REST API request object.
 *
 * @return WP_Error|\WP_HTTP_Response|\WP_REST_Response
 */
function dispatch_ga_analytics( WP_REST_Request $request ) {
	$params = $request->get_params();
	$body   = [];

	$request = wp_remote_post( 'https://www.google-analytics.com/mp/collect?measurement_id=' . $form_data['measurement_id'] . '&amp;api_secret=' . $form_data['api_secret'], $body );

	if ( ! $request ) {
		return new WP_Error( 'atlas-content-modeler-ga-analytics-error', esc_html__( 'GA was not sent. Reason unknown.', 'atlas-content-modeler' ) );
	}

	return rest_ensure_response(
		[
			'success' => $request,
		]
	);
}
