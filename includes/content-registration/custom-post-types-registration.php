<?php
/**
 * Registers custom content types and custom fields.
 *
 * @package WPE_Content_Model
 */

declare(strict_types=1);

namespace WPE\ContentModel\ContentRegistration;

use InvalidArgumentException;
use WPGraphQL\Model\Post;
use WPGraphQL\Registry\TypeRegistry;

add_action( 'init', __NAMESPACE__ . '\register_content_types' );
/**
 * Registers custom content types.
 */
function register_content_types(): void {
	$content_types = get_option( 'wpe_content_model_post_types', false );

	if ( ! $content_types ) {
		return;
	}

	foreach ( $content_types as $slug => $args ) {
		$fields = $args['fields'] ?? false;
		unset( $args['fields'] );

		// @todo normalize things to avoid this?
		$args['singular'] = $args['singular_name'];
		$args['plural']   = $args['name'];

		try {
			$args = generate_custom_post_type_args( $args );
		} catch ( InvalidArgumentException $exception ) {
			// Do nothing and let WP use defaults.
		}

		register_post_type( $slug, $args );

		if ( $fields ) {
			register_meta_types( $slug, $fields );
		}
	}
}

/**
 * Registers custom meta with the specific custom post type.
 *
 * @param string $post_type_slug The custom post type slug.
 * @param array  $fields Custom fields to be registered with the custom post type.
 */
function register_meta_types( string $post_type_slug, array $fields ): void {
	foreach ( $fields as $field ) {
		// Register only parent fields, not children of repeater fields.
		if ( isset( $field['parent'] ) ) {
			continue;
		}

		$args = [
			'object_subtype' => $post_type_slug,
			'show_in_rest'   => true, // TODO: add show_in_rest.schema.items for repeater fields and other array types.
			'single'         => true, // TODO: make this false for repeater fields and images where 'multiple' is true.
			'type'           => get_field_meta_type( $field['type'] ),
			'auth_callback'  => function() {
				return current_user_can( 'edit_posts' );
			},
		];

		register_meta( 'post', '_' . $field['slug'], $args );
	}
}

/**
 * Gets WordPress meta field type from the Content Model field type.
 *
 * WordPress supports types of 'string', 'boolean', 'integer', 'number',
 * 'array' and 'object' as meta fields. Content Model field types need
 * to be adjusted so that data is stored as a valid type.
 *
 * @link https://developer.wordpress.org/reference/functions/register_meta/#parameters See 'type' under '$args'.
 * @param string $type Content Model field type, such as 'repeater' or 'media'.
 * @return string Meta field type.
 */
function get_field_meta_type( string $type ): string {
	switch ( $type ) {
		case 'date':
		case 'text':
		case 'richtext':
			return 'string';
		case 'repeater':
			return 'array';
		case 'media':
			return 'object';
		default:
			return $type; // 'number' and 'boolean' need no adjustment.
	}
}

/**
 * Generates an array of labels for use when registering custom post types.
 *
 * @see get_post_type_labels()
 *
 * @param array $labels {
 *     Singular and plural labels.
 *     @type string $singular Singular name of post type.
 *     @type string $plural Plural name of post type.
 * }
 *
 * @throws InvalidArgumentException When missing singular or plural arguments.
 * @return array
 */
function generate_custom_post_type_labels( array $labels ): array {
	if ( empty( $labels['singular'] ) || empty( $labels['plural'] ) ) {
		throw new InvalidArgumentException(
			__( 'You must provide both singular and plural labels to generate content type labels.', 'wpe-content-model' )
		);
	}

	$singular = $labels['singular'];
	$plural   = $labels['plural'];

	/**
	 * Ignoring these values and using WP defaults:
	 * insert_into_item
	 * add_new
	 * featured_image
	 * set_featured_image
	 * remove_featured_image
	 * use_featured_image
	 * menu_name (same as name)
	 * name_admin_bar (same as singular or name)
	 *
	 * @todo i18n
	 */
	return [
		'name'                     => $plural,
		'singular_name'            => $singular,
		'add_new_item'             => sprintf( 'Add new %s', $singular ),
		'edit_item'                => sprintf( 'Edit %s', $singular ),
		'new_item'                 => sprintf( 'New %s', $singular ),
		'view_item'                => sprintf( 'View %s', $singular ),
		'view_items'               => sprintf( 'View %s', $plural ),
		'search_items'             => sprintf( 'Search %s', $plural ),
		'not_found'                => sprintf( 'No %s found', $plural ),
		'not_found_in_trash'       => sprintf( 'No %s found in trash', $plural ),
		'parent_item_colon'        => sprintf( 'Parent %s:', $singular ),
		'all_items'                => sprintf( 'All %s', $plural ),
		'archives'                 => sprintf( '%s archives', $singular ),
		'attributes'               => sprintf( '%s Attributes', $singular ),
		'uploaded_to_this_item'    => sprintf( 'Uploaded to this %s', $singular ),
		'filter_items_list'        => sprintf( 'Filter %s list', $plural ),
		'items_list_navigation'    => sprintf( '%s list navigation', $plural ),
		'items_list'               => sprintf( '%s list', $plural ),
		'item_published'           => sprintf( '%s published.', $singular ),
		'item_published_privately' => sprintf( '%s published privately.', $singular ),
		'item_reverted_to_draft'   => sprintf( '%s reverted to draft.', $singular ),
		'item_scheduled'           => sprintf( '%s scheduled.', $singular ),
		'item_updated'             => sprintf( '%s updated.', $singular ),
		'parent'                   => sprintf( 'Parent %s', $singular ),
	];
}

/**
 * Generates an array of arguments for use when registering custom post types.
 *
 * @param array $args Arguments including the singular and plural name of the post type.
 *
 * @throws \InvalidArgumentException When the given arguments are invalid.
 * @return array
 */
function generate_custom_post_type_args( array $args ): array {
	if ( empty( $args['singular'] ) || empty( $args['plural'] ) ) {
		throw new InvalidArgumentException(
			__( 'You must provide both a singular and plural name to register a custom content type.', 'wpe-content-model' )
		);
	}

	$singular = $args['singular'];
	$plural   = $args['plural'];
	$labels   = generate_custom_post_type_labels(
		[
			'singular' => $singular,
			'plural'   => $plural,
		]
	);

	return [
		'slug'                => $args['postTypeSlug'] ?? camelcase( $plural ),
		'name'                => ucfirst( $plural ),
		'singular_name'       => ucfirst( $singular ),
		'description'         => $args['description'] ?? '',
		'public'              => $args['public'] ?? false,
		'publicly_queryable'  => $args['publicly_queryable'] ?? false,
		'show_ui'             => $args['show_ui'] ?? true,
		'show_in_nav_menus'   => $args['show_in_nav_menus'] ?? true,
		'delete_with_user'    => $args['delete_with_user'] ?? false,
		'show_in_rest'        => $args['show_in_rest'] ?? true,
		'has_archive'         => $args['has_archive'] ?? true,
		'has_archive_string'  => $args['has_archive_string'] ?? '',
		'exclude_from_search' => $args['exclude_from_search'] ?? false,
		'capability_type'     => $args['capability_type'] ?? 'post',
		'hierarchical'        => $args['hierarchical'] ?? false,
		'rewrite'             => $args['rewrite'] ?? true,
		'rewrite_slug'        => $args['rewrite_slug'] ?? '',
		'rewrite_withfront'   => $args['rewrite_withfront'] ?? true,
		'query_var'           => $args['query_var'] ?? true,
		'query_var_slug'      => $args['query_var_slug'] ?? '',
		'show_in_menu'        => $args['show_in_menu'] ?? true,
		'supports'            => $args['supports'] ??
								[
									'title',
									'editor',
									'thumbnail',
									'custom-fields',
								],
		'taxonomies'          => $args['taxonomies'] ?? [],
		'labels'              => $labels,
		'show_in_graphql'     => $args['show_in_graphql'] ?? true,
		'graphql_single_name' => $args['graphql_single_name'] ?? lcfirst( $singular ),
		'graphql_plural_name' => $args['graphql_plural_name'] ?? lcfirst( $plural ),
	];
}

/**
 * Gets all content types registered with this plugin.
 *
 * @return array
 */
function get_registered_content_types(): array {
	return get_option( 'wpe_content_model_post_types', array() );
}

/**
 * Saves the registered content types to the database.
 *
 * This is not a sophisticated function or storage method.
 * It requires you to pass in the full array of content types.
 *
 * @access private This could go away in the future.
 *
 * @param array $args All of the content types and their configuration.
 *
 * @return bool
 */
function update_registered_content_types( array $args ): bool {
	return update_option( 'wpe_content_model_post_types', $args );
}

/**
 * Updates an existing content type with the specified arguments.
 *
 * This merges the specified arguments with the existing arguments
 * and updates the content type definition with the merged values.
 *
 * @param string $slug The post type slug.
 * @param array  $args The post type arguments.
 *
 * @return bool
 */
function update_registered_content_type( string $slug, array $args ): bool {
	$types = get_registered_content_types();
	if ( empty( $types[ $slug ] ) ) {
		return false;
	}

	$args = wp_parse_args( $args, $types[ $slug ] );

	/**
	 * If no changes, return true.
	 * Why? update_option returns false and does not update
	 * when the new values match the old values.
	 */
	if ( $types[ $slug ] === $args ) {
		return true;
	}

	$types[ $slug ] = $args;

	return update_registered_content_types( $types );
}

/**
 * Returns post types that have `show_in_graphql` support
 * and were created with this plugin.
 *
 * @return array
 */
function get_graphql_enabled_post_types(): array {
	$gql_post_types = array();
	foreach ( get_registered_content_types() as $slug => $content_type ) {
		if ( ! empty( $content_type['show_in_graphql'] ) ) {
			$gql_post_types[ $slug ] = $content_type;
		}
	}
	return $gql_post_types;
}

add_action( 'graphql_register_types', __NAMESPACE__ . '\register_content_fields_with_graphql' );
/**
 * Registers custom fields with the WPGraphQL API.
 *
 * @param TypeRegistry $type_registry The WPGraphQL Type Registry.
 */
function register_content_fields_with_graphql( TypeRegistry $type_registry ) {
	$gql_post_types = get_graphql_enabled_post_types();

	foreach ( $gql_post_types as $post_type => $post_type_args ) {
		if ( empty( $post_type_args['fields'] ) ) {
			continue;
		}

		foreach ( $post_type_args['fields'] as $key => $field ) {
			if ( empty( $field['show_in_graphql'] ) || empty( $field['slug'] ) ) {
				continue;
			}

			$gql_field_type = map_html_field_type_to_graphql_field_type( $field['type'] );
			if ( empty( $gql_field_type ) ) {
				continue;
			}

			$field['type'] = $gql_field_type;

			$field['resolve'] = static function( Post $post, $args, $context, $info ) use ( $field ) {
				$value = get_post_meta( $post->databaseId, $field['slug'], true );

				/**
				 * If WPGraphQL expects a float and something else is returned instead
				 * it causes a runaway PHP process and it eventually dies due to
				 * to timeout issues. Casting to a float is a temporary fix until
				 * we get a proper fix upstream or build something more robust here.
				 *
				 * @todo
				 */
				if ( $field['type'] === 'Float' ) {
					return (float) $value;
				}

				return $value;
			};

			// @todo
			// WPGraphQL will use 'name' if present. Our 'name' is display friendly. WPGraphQL needs slug friendly.
			unset( $field['name'] );

			register_graphql_field(
				camelcase( $post_type_args['graphql_single_name'] ),
				camelcase( $field['slug'] ),
				$field
			);
		}
	}
}

/**
 * Maps an HTML field type to a WPGraphQL field type.
 *
 * @param string $field_type The HTML field type.
 * @access private
 *
 * @return string|null
 */
function map_html_field_type_to_graphql_field_type( string $field_type ): ?string {
	if ( empty( $field_type ) ) {
		return null;
	}

	switch ( $field_type ) {
		case 'text':
		case 'textarea':
		case 'string':
		case 'date':
		case 'media':
		case 'repeater':
		case 'richtext':
			return 'String';
		case 'number':
			return 'Float';
		case 'boolean':
			return 'Boolean';
		default:
			return null;
	}
}

/**
 * Converts string to camelCase. Added to ensure that fields are compliant with the GraphQL spec.
 *
 * @param string $str The string to be converted to camelCase.
 * @param array  $preserved_chars The characters to preserve.
 *
 * @credit http://www.mendoweb.be/blog/php-convert-string-to-camelcase-string/
 *
 * @return string camelCase'd string
 */
function camelcase( string $str, array $preserved_chars = array() ): string {
	/* Convert non-alpha and non-numeric characters to spaces. */
	$str = preg_replace( '/[^a-z0-9' . implode( '', $preserved_chars ) . ']+/i', ' ', $str );
	$str = trim( $str );

	/* Uppercase the first character of each word. */
	$str = ucwords( $str );
	$str = str_replace( ' ', '', $str );

	return lcfirst( $str );
}
