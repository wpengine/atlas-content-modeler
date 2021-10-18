/**
 * Custom hook to fetch reserved field slugs (API identifiers).
 */

import { useEffect, useRef } from "react";

const { apiFetch } = wp;
const { graphQLUrl, isWPGraphQLActive } = atlasContentModeler;

/**
 * Reserved names in use by WPGraphQL for the WordPress Core Post type.
 *
 * We can't use these values as field names because an ACM field named “title”
 * would conflict with the WP/WPGraphQL “title”, for example.
 *
 * We report conflicts instead of namespacing ACM fields under an `acmFields`
 * group to improve developer ergonomics: every field is a top-level property
 * of its model and can be used alongside built-in reserved properties
 * without namespacing.
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
const defaultReservedNames = [
	"author",
	"authorDatabaseId",
	"authorId",
	"categories",
	"commentCount",
	"commentStatus",
	"comments",
	"content",
	"contentType",
	"databaseId",
	"date",
	"dateGmt",
	"desiredSlug",
	"editingLockedBy",
	"enclosure",
	"enqueuedScripts",
	"enqueuedStylesheets",
	"excerpt",
	"featuredImage",
	"featuredImageDatabaseId",
	"featuredImageId",
	"guid",
	"id",
	"isContentNode",
	"isPreview",
	"isRestricted",
	"isRevision",
	"isSticky",
	"isTermNode",
	"lastEditedBy",
	"link",
	"modified",
	"modifiedGmt",
	"pingStatus",
	"pinged",
	"postFormats",
	"preview",
	"previewRevisionDatabaseId",
	"previewRevisionId",
	"revisionOf",
	"revisions",
	"slug",
	"status",
	"tags",
	"template",
	"terms",
	"title",
	"toPing",
	"uri",
];

/**
 * Constructs the GraphQL query to introspect the passed model's fields.
 *
 * @param {string} model The case-sensitive GraphQL type.
 * @returns {object} The GraphQL query for field data from the named `model`.
 */
const fieldQuery = (model) => {
	return {
		query: `
			query GetTypeAndFields($model: String!) {
				__type(name: $model) {
				fields {
					name
					}
				}
			}
		  `,
		variables: {
			model,
		},
	};
};

/**
 * Gets known reserved WPGraphQL properties for the given `model` by making
 * a GraphQL request that uses introspection to find registered field names.
 *
 * When WPGraphQL is available, this allows us to check for conflicts against
 * additional types registered at runtime by WPGraphQL or other plugins,
 * instead of depending on the `defaultReservedNames`.
 *
 * Note that this includes names of existing ACM fields, which are not currently
 * filterable with WPGraphQL introspection.
 *
 * @param {string} model The case-sensitive WPGraphQL type.
 * @returns
 */
function getReservedNames(model) {
	const params = {
		url: graphQLUrl,
		method: "POST",
		data: fieldQuery(model),
		parse: false, // So the response status and headers are available.
	};

	return apiFetch(params).then((response) => {
		if (response.status !== 200) {
			console.error(
				sprintf(
					__(
						/* translators: %s The HTTP error code, such as 200. */
						"Received %s error when fetching entries.",
						"atlas-content-modeler"
					),
					response.status
				)
			);
			return;
		}

		return response.json();
	});
}

/**
 * Gets field names from the GraphQL introspection query response.
 *
 * @example
 * ```
 * const names = getNames({
 *     __type: { fields: [{ name: "author" }, { name: "id" }] },
 * });
 *
 * console.log(names); // => ["author", "id"];
 * ```
 * @param {object} graphQLResponse The response from the WPGraphQL server.
 * @returns {array} Field names in use by a model.
 */
const getNames = (graphQLResponse) => {
	return (graphQLResponse?.__type?.fields ?? [])
		.map((field) => field?.name)
		.filter((n) => n); // Remove empty or undefined.
};

/**
 * useReservedSlugs holds an array of reserved field names that a field should
 * not use as its slug (API identifier).
 *
 * This helps to prevent conflicts with internal WP and WPGraphQL properties,
 * such as 'title' and 'id'.
 *
 * If the WPGraphQL plugin is active, `useReservedSlugs` makes a GraphQL request
 * to introspect the passed `model` to find registered properties for that type.
 *
 * If WPGraphQL is not active, `useReservedSlugs` uses an array of default
 * reserved properties derived from the WordPress Core “Post” type, without
 * making a GraphQL request. This helps to reduce possible future conflicts if
 * someone creates fields before they activate WPGraphQL.
 *
 * @example
 * ```
 * import { useReservedSlugs } from "../hooks";
 *
 * // Before your component's return statement:
 * const reservedSlugs = useReservedSlugs("Cat"); // ["id", "author", …]
 *
 * // In an onBlur or onChange callback:
 * const fieldValue = event.target.value;
 * if ( reservedSlugs.includes( fieldValue ) ) {
 *     console.log( `${fieldValue}` is reserved for internal use.' );
 * }
 * ```
 * @param {string} model The case-sensitive WPGraphQL type. Cat != cat.
 * @returns {array} Reserved field names in use by the passed model.
 */
export function useReservedSlugs(model) {
	const reservedNames = useRef(defaultReservedNames);

	// Update reserved field names with data from WPGraphQL when available.
	useEffect(() => {
		if (!isWPGraphQLActive || !model) {
			return;
		}

		getReservedNames(model).then((response) => {
			const newReservedNames = getNames(response?.data);
			if (newReservedNames?.length > 0) {
				reservedNames.current = newReservedNames;
			}
		});
	}, [isWPGraphQLActive, model]);

	return reservedNames;
}
