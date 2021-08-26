<?php
/**
 * Registers custom taxonomies.
 *
 * @since 0.6.0
 * @package AtlasContentModeler
 */

declare(strict_types=1);

namespace WPE\AtlasContentModeler\ContentRegistration\Taxonomies;

use function WPE\AtlasContentModeler\ContentRegistration\camelcase;

add_action( 'init', __NAMESPACE__ . '\register' );
/**
 * Registers taxonomies.
 *
 * @since 0.6.0
 */
function register(): void {
	foreach ( get_acm_taxonomies() as $slug => $args ) {
		$args       = set_defaults( $args );
		$properties = get_props( $args );
		register_taxonomy( $slug, (array) $args['types'], $properties );
	}
}

/**
 * Infers taxonomy labels from singular and plural names set by the user.
 *
 * @since 0.6.0
 * @see get_taxonomy_labels()
 * @link https://developer.wordpress.org/reference/functions/get_taxonomy_labels/
 * @param array $args Taxonomy properties.
 */
function get_labels( array $args ): array {
	/**
	 * These values are omitted to use WP defaults:
	 * - most_used ('Most Used')
	 */
	return [
		'name'                       => $args['plural'],
		'singular_name'              => $args['singular'],
		/* translators: %s: plural taxonomy name */
		'search_items'               => sprintf( __( 'Search %s', 'atlas-content-modeler' ), $args['plural'] ),
		/* translators: %s: plural taxonomy name */
		'popular_items'              => sprintf( __( 'Popular %s', 'atlas-content-modeler' ), $args['plural'] ),
		/* translators: %s: plural taxonomy name */
		'all_items'                  => sprintf( __( 'All %s', 'atlas-content-modeler' ), $args['plural'] ),
		/* translators: %s: singular taxonomy name */
		'parent_item'                => $args['hierarchical'] ? sprintf( __( 'Parent %s', 'atlas-content-modeler' ), $args['singular'] ) : null,
		/* translators: %s: singular taxonomy name */
		'parent_item_colon'          => $args['hierarchical'] ? sprintf( __( 'Parent %s:', 'atlas-content-modeler' ), $args['singular'] ) : null,
		/* translators: %s: singular taxonomy name */
		'edit_item'                  => sprintf( __( 'Edit %s', 'atlas-content-modeler' ), $args['singular'] ),
		/* translators: %s: singular taxonomy name */
		'view_item'                  => sprintf( __( 'View %s', 'atlas-content-modeler' ), $args['singular'] ),
		/* translators: %s: singular taxonomy name */
		'update_item'                => sprintf( __( 'Update %s', 'atlas-content-modeler' ), $args['singular'] ),
		/* translators: %s: singular taxonomy name */
		'add_new_item'               => sprintf( __( 'Add New %s', 'atlas-content-modeler' ), $args['singular'] ),
		/* translators: %s: singular taxonomy name */
		'new_item_name'              => sprintf( __( 'New %s Name', 'atlas-content-modeler' ), $args['singular'] ),
		/* translators: %s: plural taxonomy name */
		'separate_items_with_commas' => $args['hierarchical'] ? null : sprintf( __( 'Separate %s with commas', 'atlas-content-modeler' ), strtolower( $args['plural'] ) ),
		/* translators: %s: plural taxonomy name */
		'add_or_remove_items'        => $args['hierarchical'] ? null : sprintf( __( 'Add or remove %s', 'atlas-content-modeler' ), strtolower( $args['plural'] ) ),
		/* translators: %s: plural taxonomy name */
		'choose_from_most_used'      => $args['hierarchical'] ? null : sprintf( __( 'Choose from the most used %s', 'atlas-content-modeler' ), strtolower( $args['plural'] ) ),
		/* translators: %s: plural taxonomy name */
		'not_found'                  => sprintf( __( 'No %s found.', 'atlas-content-modeler' ), strtolower( $args['plural'] ) ),
		/* translators: %s: plural taxonomy name */
		'no_terms'                   => sprintf( __( 'No %s', 'atlas-content-modeler' ), strtolower( $args['plural'] ) ),
		/* translators: %s: singular taxonomy name */
		'filter_by_item'             => $args['hierarchical'] ? sprintf( __( 'Filter by %s', 'atlas-content-modeler' ), strtolower( $args['singular'] ) ) : null,
		/* translators: %s: plural taxonomy name */
		'items_list_navigation'      => sprintf( __( '%s list navigation', 'atlas-content-modeler' ), $args['plural'] ),
		/* translators: %s: plural taxonomy name */
		'items_list'                 => sprintf( __( '%s list', 'atlas-content-modeler' ), $args['plural'] ),
		/* translators: %s: plural taxonomy name */
		'back_to_items'              => sprintf( __( '&larr; Go to %s', 'atlas-content-modeler' ), $args['plural'] ),
	];
}

/**
 * Gets taxonomy properties specific to ACM taxonomies.
 *
 * @since 0.6.0
 * @see WP_Taxonomy::set_props()
 * @link https://developer.wordpress.org/reference/classes/wp_taxonomy/set_props/
 * @param array $args Arguments including the singular and plural name of the post type.
 */
function get_props( array $args ): array {
	/**
	 * These values are omitted to use WP defaults:
	 * - rewrite (true)
	 * - query_var ($this->name)
	 * - update_count_callback ('')
	 * - publicly_queryable (same as 'public')
	 * - rest_base (false)
	 * - meta_box_cb (null)
	 * - meta_box_sanitize_cb (null)
	 * - show_in_menu (null)
	 * - show_in_nav_menus (null)
	 * - show_tagcloud (null)
	 * - show_in_quick_edit (null)
	 */
	return array(
		'labels'              => get_labels( $args ),
		'public'              => $args['api_visibility'] === 'public',
		'description'         => $args['description'],
		'hierarchical'        => $args['hierarchical'],
		'show_in_rest'        => $args['api_visibility'] === 'public'
			? $args['show_in_rest']
			: current_user_can( 'read' ),
		'show_in_graphql'     => $args['show_in_graphql'],
		'graphql_single_name' => camelcase( $args['singular'] ),
		'graphql_plural_name' => camelcase( $args['plural'] ),
		'show_ui'             => true,
		'show_admin_column'   => true,
		'capabilities'        => array(
			'manage_terms' => 'manage_categories',
			'edit_terms'   => 'edit_categories',
			'delete_terms' => 'delete_categories',
			'assign_terms' => 'assign_categories',
		),
	);
}

/**
 * Gets custom ACM taxonomies saved by the user.
 *
 * @since 0.6.0
 */
function get_acm_taxonomies(): array {
	return (array) get_option( 'atlas_content_modeler_taxonomies', array() );
}

/**
 * Fills default taxonomy arguments for required missing values.
 *
 * @since 0.6.0
 * @param array $args The taxonomy arguments.
 */
function set_defaults( array $args ): array {
	$hierarchical = $args['hierarchical'] ?? false;
	$defaults     = array(
		'api_visibility'  => 'private',
		'description'     => '',
		'types'           => [],
		'show_in_rest'    => true,
		'show_in_graphql' => true,
		'hierarchical'    => $hierarchical,
		'singular'        => $hierarchical ? __( 'Category', 'atlas-content-modeler' ) : __( 'Tag', 'atlas-content-modeler' ),
		'plural'          => $hierarchical ? __( 'Categories', 'atlas-content-modeler' ) : __( 'Tags', 'atlas-content-modeler' ),
	);

	return wp_parse_args( $args, $defaults );
}

add_filter( 'graphql_data_is_private', __NAMESPACE__ . '\graphql_data_is_private', 10, 6 );
/**
 * Determines whether or not taxonomy data should be considered private in WPGraphQL.
 *
 * Accessing private data requires authentication.
 *
 * @since 0.6.0
 * @param boolean     $is_private Whether or not the model is private.
 * @param string      $model_name Name of the model the filter is being executed in.
 * @param mixed       $data The incoming data.
 * @param string|null $visibility The visibility that has currently been set for the data.
 * @param int|null    $owner The user ID for the owner of this piece of data.
 * @param \WP_User    $current_user The current user for the session.
 */
function graphql_data_is_private( bool $is_private, string $model_name, $data, $visibility, $owner, \WP_User $current_user ): bool {
	if ( ! is_object( $data ) ) {
		return $is_private;
	}

	if ( 'WP_Term' !== get_class( $data ) ) {
		return $is_private;
	}

	$taxonomies = get_acm_taxonomies();

	if ( 'private' === ( $taxonomies[ $data->taxonomy ]['api_visibility'] ?? '' ) ) {
		return ! user_can( $current_user, 'edit_term', $data->term_id );
	}

	return $is_private;
}
