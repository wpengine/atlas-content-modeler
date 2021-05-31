<?php
/**
 * Registers custom content types and custom fields.
 *
 * @package AtlasContentModeler
 */

declare(strict_types=1);

namespace WPE\AtlasContentModeler\ContentRegistration;

use InvalidArgumentException;
use WPGraphQL\Model\Post;
use WPGraphQL\Registry\TypeRegistry;
use WPGraphQL\Data\DataSource;

add_action( 'init', __NAMESPACE__ . '\register_content_types' );
/**
 * Registers custom content types.
 */
function register_content_types(): void {
	$content_types = get_registered_content_types();

	if ( ! $content_types ) {
		return;
	}

	foreach ( $content_types as $slug => $args ) {
		$fields = $args['fields'] ?? false;
		unset( $args['fields'] );

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
	foreach ( $fields as $key => $field ) {
		$field['object_subtype'] = $post_type_slug;
		register_meta( 'post', $field['slug'], $field );
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
			__( 'You must provide both singular and plural labels to generate content type labels.', 'atlas-content-modeler' )
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
			__( 'You must provide both a singular and plural name to register a custom content type.', 'atlas-content-modeler' )
		);
	}

	$singular = $args['singular'];
	$plural   = $args['plural'];
	$icon     = require ATLAS_CONTENT_MODELER_DIR . '/includes/settings/views/admin-entry-icon.php';
	$labels   = generate_custom_post_type_labels(
		[
			'singular' => $singular,
			'plural'   => $plural,
		]
	);

	return [
		'name'                => ucfirst( $plural ),
		'singular_name'       => ucfirst( $singular ),
		'description'         => $args['description'] ?? '',
		'show_ui'             => $args['show_ui'] ?? true,
		'show_in_rest'        => $args['show_in_rest'] ?? true,
		'rest_base'           => $args['rest_base'] ?? strtolower( str_replace( ' ', '', $plural ) ),
		'capability_type'     => $args['capability_type'] ?? 'post',
		'show_in_menu'        => $args['show_in_menu'] ?? true,
		'supports'            => $args['supports'] ??
								[
									'title',
									'editor',
									'thumbnail',
									'custom-fields',
								],
		'labels'              => $labels,
		'show_in_graphql'     => $args['show_in_graphql'] ?? true,
		'graphql_single_name' => $args['graphql_single_name'] ?? camelcase( $singular ),
		'graphql_plural_name' => $args['graphql_plural_name'] ?? camelcase( $plural ),
		'menu_icon'           => $icon,
	];
}

/**
 * Gets all content types registered with this plugin.
 *
 * @return array
 */
function get_registered_content_types(): array {
	return get_option( 'atlas_content_modeler_post_types', array() );
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
	return update_option( 'atlas_content_modeler_post_types', $args );
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

			$rich_text = false;

			if ( 'richtext' === $field['type'] ) {
				$rich_text = true;
			}

			$gql_field_type = map_html_field_type_to_graphql_field_type( $field['type'] );
			if ( empty( $gql_field_type ) ) {
				continue;
			}

			$field['type'] = $gql_field_type;

			$field['resolve'] = static function( Post $post, $args, $context, $info ) use ( $field, $rich_text ) {
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

				if ( $field['type'] === 'MediaItem' ) {
					return DataSource::resolve_post_object( (int) $value, $context );
				}

				// fixes caption shortcode for graphql output.
				if ( $rich_text ) {
					return do_shortcode( $value );
				}

				return $value;
			};

			// @todo
			// WPGraphQL will use 'name' if present. Our 'name' is display friendly. WPGraphQL needs slug friendly.
			unset( $field['name'] );

			register_graphql_field(
				camelcase( $post_type_args['singular'] ),
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
		case 'richtext':
			return 'String';
		case 'number':
			return 'Float';
		case 'boolean':
			return 'Boolean';
		case 'media':
			return 'MediaItem';
		default:
			return null;
	}
}

add_filter( 'is_protected_meta', __NAMESPACE__ . '\is_protected_meta', 10, 3 );
/**
 * Designates fields from this plugin as protected to prevent them
 * from showing in the Custom Fields metabox on other post types.
 *
 * @param bool   $protected Whether the key is considered protected.
 * @param string $meta_key  Metadata key.
 * @param string $meta_type Type of object metadata is for. Accepts 'post', 'comment', 'term', 'user',
 *                          or any other object type with an associated meta table.
 */
function is_protected_meta( bool $protected, string $meta_key, string $meta_type ): bool {
	// Return early if already protected.
	if ( true === $protected ) {
		return $protected;
	}

	if ( 'post' !== $meta_type ) {
		return $protected;
	}

	$fields = wp_list_pluck( get_registered_content_types(), 'fields' );
	$fields = array_merge( ...array_values( $fields ) );
	$slugs  = wp_list_pluck( $fields, 'slug' );

	return in_array( $meta_key, $slugs, true );
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
