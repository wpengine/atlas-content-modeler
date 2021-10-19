<?php
/**
 * Reserved field names in use by WPGraphQL for the WordPress Core “Post” type.
 *
 * We can't use these values as field names because an ACM field named “title”
 * would conflict with the WP/WPGraphQL “title”, for example.
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
 *
 * @package AtlasContentModeler
 */

return [
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
