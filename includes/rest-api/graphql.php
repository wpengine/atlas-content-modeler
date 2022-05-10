<?php
/**
 * GraphQL introspection queries used in REST API callbacks.
 *
 * @package AtlasContentModeler
 */

declare(strict_types=1);

namespace WPE\AtlasContentModeler\REST_API\GraphQL;

use function WPE\AtlasContentModeler\ContentRegistration\camelcase;

/**
 * Types that should not be used for model or taxonomy labels in case they
 * cause conflicts when the WPGraphQL plugin is activated later.
 *
 * Sourced from running this query in GraphiQL with no other plugins active
 * and no ACM models or taxonomies present:
 *
 * {
 *   __type(name: "RootQuery") {
 *     fields {
 *       name
 *     }
 *   }
 * }
 */
const ACM_DEFAULT_GRAPHQL_ROOT_FIELDS = [
	'allSettings',
	'categories',
	'category',
	'comment',
	'comments',
	'contentNode',
	'contentNodes',
	'contentType',
	'contentTypes',
	'discussionSettings',
	'generalSettings',
	'mediaItem',
	'mediaItems',
	'menu',
	'menuItem',
	'menuItems',
	'menus',
	'node',
	'nodeByUri',
	'page',
	'pages',
	'plugin',
	'plugins',
	'post',
	'postFormat',
	'postFormats',
	'posts',
	'readingSettings',
	'registeredScripts',
	'registeredStylesheets',
	'revisions',
	'tag',
	'tags',
	'taxonomies',
	'taxonomy',
	'termNode',
	'terms',
	'theme',
	'themes',
	'user',
	'userRole',
	'userRoles',
	'users',
	'viewer',
	'writingSettings',
];

/**
 * Reserved field names in use by WPGraphQL for the WordPress Core “Post” type.
 *
 * We can't use these values as field names because an ACM field named “id”
 * would conflict with the WP/WPGraphQL “id”, for example.
 *
 * We report conflicts instead of namespacing ACM fields under an `acmFields`
 * group to GraphQL responses to improve developer ergonomics: every field is a
 * top-level property of its model and can be used alongside built-in reserved
 * properties without namespacing.
 *
 * Default reserved names are derived from the WPGraphQL response to this query:
 *
 * ```
 * query GetTypeAndFields {
 *     __type(name: "Post" ) {
 *         fields {
 *             name
 *         }
 *     }
 * }
 * ```
 */
const ACM_RESERVED_GRAPHQL_FIELD_IDS = [
	'author',
	'authorDatabaseId',
	'authorId',
	'categories',
	'commentCount',
	'commentStatus',
	'comments',
	'content',
	'contentType',
	'databaseId',
	'date',
	'dateGmt',
	'desiredSlug',
	'editingLockedBy',
	'enclosure',
	'enqueuedScripts',
	'enqueuedStylesheets',
	'excerpt',
	'featuredImage',
	'featuredImageDatabaseId',
	'featuredImageId',
	'guid',
	'id',
	'isContentNode',
	'isPreview',
	'isRestricted',
	'isRevision',
	'isSticky',
	'isTermNode',
	'lastEditedBy',
	'link',
	'modified',
	'modifiedGmt',
	'pingStatus',
	'pinged',
	'postFormats',
	'preview',
	'previewRevisionDatabaseId',
	'previewRevisionId',
	'revisionOf',
	'revisions',
	'slug',
	'status',
	'tags',
	'template',
	'terms',
	'title',
	'toPing',
	'uri',
];

/**
 * Determines if the passed `$name` already exists in the root
 * WPGraphQL namespace.
 *
 * @param string $name Model or taxonomy singular or plural name.
 * @return bool True if there is a name collision.
 */
function root_type_exists( string $name = '' ): bool {
	$root_fields = get_registered_root_fields();

	return in_array( camelcase( $name ), $root_fields, true );
}

/**
 * Determines if the passed field has a slug that is allowed for the `$model`.
 *
 * @param array  $field Field to check for collisions with existing fields.
 * @param string $model The case-sensitive WPGraphQL type.
 *                      (Singular form, camel case, initial capital).
 * @return bool True if the passed `$name` is allowed.
 */
function is_allowed_field_id( array $field, string $model = '' ): bool {
	if ( is_field_id_exception( $field ) ) {
		return true;
	}

	return ! in_array( $field['slug'], get_registered_field_ids( $model ), true );
}

/**
 * Determines if the passed `$field` has a slug that we can allow due to an
 * exception that we handle elsewhere.
 *
 * For example, 'title' is normally not permitted as a field ID because it
 * conflicts with the default 'title' registered by WPGraphQL. But when isTitle
 * is set for that field, we allow it and take steps in WPGraphQL field
 * registration to handle it as a special case.
 *
 * @param array $field Field properties.
 * @return bool True if the `$field` has a slug that is allowed to be used.
 */
function is_field_id_exception( $field ) {
	$slug     = $field['slug'] ?? '';
	$is_title = $field['isTitle'] ?? false;

	// Fields set as a title field can use 'title' for their ID.
	if ( $slug === 'title' && $is_title ) {
		return true;
	}

	return false;
}

/**
 * Gets WPGraphQL field names registered at the root level.
 *
 * Used to reject new models and taxonomies that a user wishes to create
 * if they would conflict with existing GraphQL types when registered.
 *
 * WPGraphQL registers many types at the root level, so the namespace for
 * models, taxonomies and more is shared. For example:
 *
 * - A model can not be named 'plugin' or 'plugins' because WPGraphQL already
 *   registers 'plugin' and 'plugins' types.
 * - A taxonomy can not have a plural label named 'camels' if a model already
 *   exists with a plural label of 'camels', because WPGraphQL registers a type
 *   for the singular and plural names of models and taxonomies at the root.
 *
 * @return array
 */
function get_registered_root_fields(): array {
	if ( ! function_exists( 'graphql' ) ) {
		// The WPGraphQL plugin is not active. Fall back to default types.
		return ACM_DEFAULT_GRAPHQL_ROOT_FIELDS;
	}

	$root_fields = graphql(
		[
			'query' => '{
						__type(name: "RootQuery") {
							fields {
								name
							}
						}
					}',
		]
	);

	$field_names = array_column(
		$root_fields['data']['__type']['fields'] ?? [],
		'name'
	);

	return is_array( $field_names ) ? $field_names : [];
}

/**
 * Gets field IDs already in use for the given `$model` by making a GraphQL
 * request using introspection to find current registered field names.
 *
 * When WPGraphQL is available, this allows us to check for conflicts against
 * additional fields registered at runtime by WPGraphQL or other plugins.
 * Falls back to `ACM_RESERVED_GRAPHQL_FIELD_IDS` when WPGraphQL is not active.
 *
 * Note that the response includes names of existing ACM fields, which are not
 * currently filterable with WPGraphQL introspection, and not just fields
 * registered by WPGraphQL itself and other plugins.
 *
 * @param string $model The case-sensitive WPGraphQL type.
 *                      (Singular form, camel case, initial capital).
 * @return array Reserved field ids for the given `$model`.
 */
function get_registered_field_ids( string $model = '' ): array {
	if ( ! function_exists( 'graphql' ) || empty( $model ) ) {
		// We can't use introspection so fall back to default field IDs.
		return ACM_RESERVED_GRAPHQL_FIELD_IDS;
	}

	$registered_fields = graphql(
		[
			'query'     => '
				query GetTypeAndFields($model: String!) {
					__type(name: $model) {
					fields {
						name
						}
					}
				}
		  ',
			'variables' => [
				'model' => $model,
			],
		]
	);

	return array_column(
		$registered_fields['data']['__type']['fields'] ?? [],
		'name'
	);
}
