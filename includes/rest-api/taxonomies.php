<?php
/**
 * Taxonomy helpers used in REST API callbacks.
 *
 * @package AtlasContentModeler
 */

declare(strict_types=1);

namespace WPE\AtlasContentModeler\REST_API\Taxonomies;

use WP_Error;
use function WPE\AtlasContentModeler\ContentRegistration\get_registered_content_types;

/**
 * Saves a taxonomy.
 *
 * @param array $params Parameters passed from the taxonomy form.
 * @param bool  $is_update True if `$params` came from a PUT request.
 * @return array|WP_Error
 * @since 0.6.0
 */
function save_taxonomy( array $params, bool $is_update ) {
	// Get models for checking slug, plural and singular labels.
	$content_types = get_registered_content_types();

	// Sanitize key allows hyphens, but it's close enough to register_taxonomy() requirements.
	$params['slug']     = isset( $params['slug'] ) ? sanitize_key( $params['slug'] ) : '';
	$params['singular'] = isset( $params['singular'] ) ? sanitize_key( $params['singular'] ) : '';
	$params['plural']   = isset( $params['plural'] ) ? sanitize_key( $params['plural'] ) : '';
	$reserved_tax_terms = include ATLAS_CONTENT_MODELER_INCLUDES_DIR . 'settings/reserved-taxonomy-terms.php';

	if ( empty( $params['slug'] ) || strlen( $params['slug'] ) > 32 ) {
		return new WP_Error(
			'acm_invalid_taxonomy_id',
			esc_html__( 'Taxonomy slug must be between 1 and 32 characters in length.', 'atlas-content-modeler' ),
			[ 'status' => 400 ]
		);
	}

	// Prevents use of reserved taxonomy terms as slugs during new taxonomy creation.
	if ( in_array( $params['slug'], $reserved_tax_terms, true ) ) {
		return new WP_Error(
			'acm_reserved_taxonomy_term',
			__( 'Taxonomy slug is reserved.', 'atlas-content-modeler' ),
			array( 'status' => 400 )
		);
	}

	$acm_taxonomies     = get_option( 'atlas_content_modeler_taxonomies', array() );
	$wp_taxonomies      = get_taxonomies();
	$non_acm_taxonomies = array_diff( array_keys( $wp_taxonomies ), array_keys( $acm_taxonomies ) );

	// Prevents creation of a taxonomy if one with the same slug exists that was not created in ACM.
	if ( in_array( $params['slug'], $non_acm_taxonomies, true ) || in_array( $params['slug'], $content_types, true ) ) {
		return new WP_Error(
			'acm_taxonomy_exists',
			esc_html__( 'A taxonomy with this Taxonomy ID already exists.', 'atlas-content-modeler' ),
			[ 'status' => 400 ]
		);
	}

	// Allows updates of existing ACM taxonomies, but prevents creation of ACM taxonomies with identical slugs and models.
	if ( ! $is_update && array_key_exists( $params['slug'], $acm_taxonomies ) || in_array( $params['slug'], $content_types, true ) ) {
		return new WP_Error(
			'acm_taxonomy_exists',
			esc_html__( 'A taxonomy with this Taxonomy ID already exists.', 'atlas-content-modeler' ),
			[ 'status' => 400 ]
		);
	}

	// Check for label conflicts in taxonomies and models.
	if ( ! $is_update && ( array_key_exists( $params['plural'], $acm_taxonomies ) || array_key_exists( $params['singular'], $acm_taxonomies ) || in_array( $params['singular'], $content_types, true ) || in_array( $params['plural'], $content_types, true ) ) ) {
		return new WP_Error(
			'acm_taxonomy_exists',
			esc_html__( 'A taxonomy with this Taxonomy label already exists.', 'atlas-content-modeler' ),
			[ 'status' => 400 ]
		);
	}

	// Check for required singular and plural params.
	if ( empty( $params['singular'] ) || empty( $params['plural'] ) ) {
		return new WP_Error(
			'acm_invalid_labels',
			esc_html__( 'Please provide singular and plural labels when creating a taxonomy.', 'atlas-content-modeler' ),
			[ 'status' => 400 ]
		);
	}

	$defaults = [
		'types'           => [],
		'show_in_rest'    => true,
		'show_in_graphql' => true,
		'hierarchical'    => false,
		'api_visibility'  => 'private',
	];

	$taxonomy                            = wp_parse_args( $params, $defaults );
	$acm_taxonomies[ $taxonomy['slug'] ] = $taxonomy;
	$created                             = update_option( 'atlas_content_modeler_taxonomies', $acm_taxonomies );

	if ( ! $created ) {
		return new WP_Error(
			'acm_taxonomy_not_created',
			esc_html__( 'Taxonomy not created. Reason unknown.', 'atlas-content-modeler' )
		);
	}

	return $taxonomy;
}
