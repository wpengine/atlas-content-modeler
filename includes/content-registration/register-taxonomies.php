<?php
/**
 * Registers custom taxonomies.
 *
 * @since 0.6.0
 * @package AtlasContentModeler
 */

declare(strict_types=1);

namespace WPE\AtlasContentModeler\ContentRegistration\Taxonomies;

add_action( 'init', __NAMESPACE__ . '\register' );
/**
 * Registers taxonomies.
 *
 * @since 0.6.0
 */
function register(): void {
	foreach ( get_taxonomies() as $slug => $args ) {
		$properties = get_props( $args );
		register_taxonomy( $slug, $args['types'], $properties );
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
	$hierarchical     = $args['hierarchical'] ?? false;
	$default_singular = $hierarchical ? 'Category' : 'Tag';
	$default_plural   = $hierarchical ? 'Categories' : 'Tags';
	$singular         = $args['singular'] ?? $default_singular;
	$plural           = $args['plural'] ?? $default_plural;

	/**
	 * These values are omitted to use WP defaults:
	 * - most_used ('Most Used')
	 */
	return [
		'name'                       => $plural,
		'singular_name'              => $singular,
		/* translators: %s: plural taxonomy name */
		'search_items'               => sprintf( __( 'Search %s', 'atlas-content-modeler' ), $plural ),
		/* translators: %s: plural taxonomy name */
		'popular_items'              => sprintf( __( 'Popular %s', 'atlas-content-modeler' ), $plural ),
		/* translators: %s: plural taxonomy name */
		'all_items'                  => sprintf( __( 'All %s', 'atlas-content-modeler' ), $plural ),
		/* translators: %s: singular taxonomy name */
		'parent_item'                => $hierarchical ? sprintf( __( 'Parent %s', 'atlas-content-modeler' ), $singular ) : null,
		/* translators: %s: singular taxonomy name */
		'parent_item_colon'          => $hierarchical ? sprintf( __( 'Parent %s:', 'atlas-content-modeler' ), $singular ) : null,
		/* translators: %s: singular taxonomy name */
		'edit_item'                  => sprintf( __( 'Edit %s', 'atlas-content-modeler' ), $singular ),
		/* translators: %s: singular taxonomy name */
		'view_item'                  => sprintf( __( 'View %s', 'atlas-content-modeler' ), $singular ),
		/* translators: %s: singular taxonomy name */
		'update_item'                => sprintf( __( 'Update %s', 'atlas-content-modeler' ), $singular ),
		/* translators: %s: singular taxonomy name */
		'add_new_item'               => sprintf( __( 'Add New %s', 'atlas-content-modeler' ), $singular ),
		/* translators: %s: singular taxonomy name */
		'new_item_name'              => sprintf( __( 'New %s Name', 'atlas-content-modeler' ), $singular ),
		/* translators: %s: plural taxonomy name */
		'separate_items_with_commas' => $hierarchical ? null : sprintf( __( 'Separate %s with commas', 'atlas-content-modeler' ), strtolower( $plural ) ),
		/* translators: %s: plural taxonomy name */
		'add_or_remove_items'        => $hierarchical ? null : sprintf( __( 'Add or remove %s', 'atlas-content-modeler' ), strtolower( $plural ) ),
		/* translators: %s: plural taxonomy name */
		'choose_from_most_used'      => $hierarchical ? null : sprintf( __( 'Choose from the most used %s', 'atlas-content-modeler' ), strtolower( $plural ) ),
		/* translators: %s: plural taxonomy name */
		'not_found'                  => sprintf( __( 'No %s found.', 'atlas-content-modeler' ), strtolower( $plural ) ),
		/* translators: %s: plural taxonomy name */
		'no_terms'                   => sprintf( __( 'No %s', 'atlas-content-modeler' ), strtolower( $plural ) ),
		/* translators: %s: singular taxonomy name */
		'filter_by_item'             => $hierarchical ? sprintf( __( 'Filter by %s', 'atlas-content-modeler' ), strtolower( $singular ) ) : null,
		/* translators: %s: plural taxonomy name */
		'items_list_navigation'      => sprintf( __( '%s list navigation', 'atlas-content-modeler' ), $plural ),
		/* translators: %s: plural taxonomy name */
		'items_list'                 => sprintf( __( '%s list', 'atlas-content-modeler' ), $plural ),
		/* translators: %s: plural taxonomy name */
		'back_to_items'              => sprintf( __( '&larr; Go to %s', 'atlas-content-modeler' ), $plural ),
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
		'labels'                => get_labels( $args ),
		'public'                => ( $args['api_visibility'] ?? '' ) === 'public',
		'description'           => $args['description'] ?? '',
		'hierarchical'          => $args['hierarchical'] ?? false,
		'show_in_rest'          => $args['show_in_rest'] ?? true,
		'show_in_graphql'       => $args['show_in_graphql'] ?? true,
		'show_ui'               => true,
		'show_admin_column'     => true,
		'capabilities'          => array(
			'manage_terms' => 'manage_categories',
			'edit_terms'   => 'edit_categories',
			'delete_terms' => 'delete_categories',
			'assign_terms' => 'assign_categories',
		),
		'rest_controller_class' => 'WP_REST_Terms_Controller',
	);
}

/**
 * Gets custom ACM taxonomies saved by the user.
 *
 * @since 0.6.0
 */
function get_taxonomies(): array {
	return get_option( 'atlas_content_modeler_taxonomies', array() );
}
