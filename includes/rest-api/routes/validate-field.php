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
use WP_REST_Server;

add_action( 'rest_api_init', __NAMESPACE__ . '\register_rest_routes' );

/**
 * Registers custom routes with the WP REST API.
 */
function register_rest_routes(): void {
	register_rest_route(
		'wpe',
		'/atlas/validate-unique-email',
		[
			'methods'             => WP_REST_Server::EDITABLE,
			'callback'            => __NAMESPACE__ . '\dispatch_get_validate_unique_email',
			'permission_callback' => static function () {
				return current_user_can( 'edit_posts' );
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
	$post_id   = $request->get_param( 'post_id' );
	$post_type = $request->get_param( 'post_type' );
	$slug      = $request->get_param( 'slug' );
	$email     = $request->get_param( 'email' );

	if ( null === $post_id && null === $post_type && null === $slug && null === $email ) {
		return new WP_Error( 'acm_missing_fields', __( 'post_id, post_type, slug, and email are required parameters.', 'atlas-content-modeler' ) );
	}

	global $wpdb;

	// phpcs:disable
	// A direct database call is the quickest way to query
	// for unique emails.
	$identical_email_query = $wpdb->prepare(
		"SELECT COUNT(*)
		FROM `{$wpdb->postmeta}`
		INNER JOIN {$wpdb->posts} ON {$wpdb->postmeta}.post_id = {$wpdb->posts}.ID
		WHERE {$wpdb->posts}.post_type = %s
		AND post_id != %s
		AND meta_key = %s
		AND meta_value = %s;",
		$post_type,
		$post_id,
		$slug,
		$email
	);

	$identical_emails = (int) $wpdb->get_var( $identical_email_query );
	// phpcs:enable

	if ( $identical_emails > 0 ) {
		return new WP_Error(
			'acm_invalid_unique_email',
			sprintf(
				// translators: 1: field name 2: submitted email address value.
				__( 'The email field %1$s must be unique. Another entry uses %2$s.', 'atlas-content-modeler' ),
				$slug,
				$email
			)
		);
	}

	return rest_ensure_response(
		[
			'data' => true,
		]
	);
}

