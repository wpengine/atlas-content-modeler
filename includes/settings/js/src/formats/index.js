/**
 * Converts a string to a valid GraphQL name matching `[_A-Za-z][_0-9A-Za-z]`.
 * Also turns `input with spaces` into the camel cased `inputWithSpaces`.
 *
 * @see https://spec.graphql.org/June2018/#sec-Names
 * @param {string} value The input value to convert to a valid GraphQL ID.
 * @return {string} The valid API ID.
 */
export function toValidApiId(value) {
	// Strip all characters not in the range [_0-9A-Za-z ].
	// Preserve spaces for now to turn “camel case” into “camelCase” later.
	value = value.replace(/[^_0-9A-Za-z ]/g, "");

	// Strip leading numbers.
	value = value.replace(/^[0-9]+/, "");

	// Turn [space character] into the uppercase character.
	value = value.replace(/ ([a-z])/g, (match) => match.trim().toUpperCase());

	// Strip any remaining spaces.
	value = value.replace(/\s/g, "");

	// Lowercase the first letter. Not required by the GraphQL spec, but consistent with common usage.
	value = value.replace(/^[A-Z]/g, (match) => match.toLowerCase());

	return value;
}

/**
 * Formats a given string to a valid post type slug.
 *
 * @see https://developer.wordpress.org/reference/functions/sanitize_key/
 * @param {string} value The input value to convert to a valid post type slug.
 * @returns A valid post type slug.
 */
export function toPostTypeSlug(value) {
	// Must be all lower case.
	value = value.toLowerCase();

	// Strip all characters not in the range [a-z0-9_\-].
	value = value.replace(/[^a-z0-9_\-]/g, "");

	return value;
}
