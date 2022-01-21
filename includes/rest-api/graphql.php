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
