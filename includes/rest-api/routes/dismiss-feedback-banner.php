<?php
/**
 * Registers REST API endpoints for /wpe/atlas/dismiss-feedback-banner/ routes.
 *
 * @package AtlasContentModeler
 */

declare(strict_types=1);

namespace WPE\AtlasContentModeler\REST_API\DismissFeedbackBanner;

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
		'/atlas/dismiss-feedback-banner',
		[
			'methods'             => 'POST',
			'callback'            => __NAMESPACE__ . '\dispatch_dismiss_feedback_banner',
			'permission_callback' => static function () {
				return current_user_can( 'manage_options' );
			},
		]
	);
}

/**
 * Handles feedback banner dismissal requests from the REST API.
 *
 * @param WP_REST_Request $request The REST API request object.
 *
 * @return WP_Error|\WP_HTTP_Response|\WP_REST_Response
 */
function dispatch_dismiss_feedback_banner( WP_REST_Request $request ) {
	$created = update_user_meta( get_current_user_id(), 'acm_hide_feedback_banner', time() );

	if ( ! $created ) {
		return new WP_Error( 'atlas-content-modeler-feedback-notice-dismiss-error', esc_html__( 'Feedback banner metadata was not set. Reason unknown.', 'atlas-content-modeler' ) );
	}

	return rest_ensure_response(
		[
			'success' => $created,
		]
	);
}
