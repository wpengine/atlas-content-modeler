<?php
/**
 * REST API: Custom REST Terms Controller class
 *
 * Extends WP_REST_Terms_Controller read access with a check for the
 * API Visibility of the Atlas Content Modeler taxonomy, so that private
 * terms cannot be queried directly.
 *
 * @since 0.6.0
 * @package AtlasContentModeler
 */

declare(strict_types=1);

namespace WPE\AtlasContentModeler\ContentRegistration\Taxonomies;

/**
 * Class REST_Terms_Controller
 */
final class REST_Terms_Controller extends \WP_REST_Terms_Controller {
	/**
	 * Checks if a request has access to read the specified term
	 * based on the API Visibility of the ACM Taxonomy.
	 *
	 * @since 0.6.0
	 *
	 * @param \WP_REST_Request $request Full details about the request.
	 * @return bool|\WP_Error True if the request has read access for the item, otherwise false or WP_Error.
	 */
	public function get_item_permissions_check( $request ) {
		$term = $this->get_term( $request['id'] );

		if ( is_wp_error( $term ) ) {
			return $term;
		}

		$taxonomies = get_taxonomies();

		if ( 'private' === ( $taxonomies[ $term->taxonomy ]['api_visibility'] ?? '' ) ) {
			return current_user_can( 'edit_term', $term->term_id );
		}

		return parent::get_item_permissions_check( $request );
	}
}
