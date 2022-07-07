<?php
/**
 * Registers REST API end-points for /wpe/atlas/validate-field/ routes
 *
 * @package AtlasContentModeler
 */

declare(strict_types=1);

namespace WPE\AtlasContentModeler\REST_API\ValidateField;

use WP_Error;
use WP_REST_Request;

add_action( 'rest_api_init', __NAMESPACE__ . '\register_rest_routes' );

/**
 * Registers custom routes with the WP REST API.
 */
function register_rest_routes(): void {
	register_rest_route(
		'wpe',
		'/atlas/validate-unique-email',
		[
			'methods'             => 'GET',
			'callback'            => __NAMESPACE__ . '\dispatch_get_validate_unique_email',
			'permission_callback' => static function () {
				return current_user_can( 'manage_options' );
			},
		]
	);
}

/**
 * Handles requests from the REST API to GET if an email is unqiue
 *
 * @param WP_REST_Request $request The REST API request object.
 *
 * @return WP_Error|\WP_HTTP_Response|\WP_REST_Response
 */
function dispatch_get_validate_unique_email( WP_REST_Request $request ) {
	$params = $request->get_params();

	if ( empty( $params['post_id'] ) || empty( $params['slug'] ) || empty( $params['email'] ) ) {
		return new WP_Error(
			'acm_missing_required_parameters',
			__( 'Email, post_id, and slug are required parameters.', 'atlas-content-modeler' )
		);
	}
	$post_id = $params['post_id'];
	$slug    = $params['slug'];
	$email   = $params['email'];

	global $wpdb;

	// phpcs:disable
	// A direct database call is the quickest way to query
	// for unique emails.
	$identical_email_query = $wpdb->prepare(
		"SELECT COUNT(*)
		FROM `{$wpdb->postmeta}`
		WHERE post_id != %s
		AND meta_key = %s
		AND meta_value = %s;",
		$post_id,
		$slug,
		$email
	);

	$identical_emails = (int) $wpdb->get_var( $identical_email_query );
	// phpcs:enable

	if ( $identical_emails > 0 ) {
		return rest_ensure_response(
			[
				'data' => false,
			]
		);
	} else {
		return rest_ensure_response(
			[
				'data' => true,
			]
		);
	}
}

