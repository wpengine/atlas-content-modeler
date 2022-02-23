<?php
/**
 * Functions to manipulate and query fields from content models.
 *
 * @package AtlasContentModeler
 */

declare(strict_types=1);

namespace WPE\AtlasContentModeler;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Gets the field registered as the entry title.
 *
 * @param array $fields Fields to check for an entry title field.
 * @return array Entry title field data or an empty array.
 */
function get_entry_title_field( array $fields ): array {
	foreach ( $fields as $field ) {
		if ( isset( $field['isTitle'] ) && $field['isTitle'] ) {
			return $field;
		}
	}

	return [];
}

/**
 * Orders fields by position, lowest first.
 *
 * @param array $fields Fields to order.
 * @return array Fields in position order from low to high.
 */
function order_fields( array $fields ): array {
	usort(
		$fields,
		function( $field1, $field2 ) {
			$pos1 = (int) $field1['position'];
			$pos2 = (int) $field2['position'];
			if ( $pos1 === $pos2 ) {
				return 0;
			}
			return ( $pos1 < $pos2 ) ? -1 : 1;
		}
	);

	return $fields;
}

/**
 * Gets all field data for the field with the named $slug.
 *
 * @param string $slug Field slug to look for.
 * @param array  $models Models with field properties to search for the `$slug`.
 * @param string $post_type Current post type on the publisher screen.
 *
 * @return array The field data if found or an empty array.
 */
function get_field_from_slug( string $slug, array $models, string $post_type ): array {
	$models = append_reverse_relationship_fields( $models, $post_type );
	$fields = $models[ $post_type ]['fields'] ?? [];

	foreach ( $fields as $field ) {
		if ( $field['slug'] === $slug ) {
			return $field;
		}
	}

	return [];
}

/**
 * Determines if the field is the featured image or not.
 *
 * @param string $slug The slug of the field to look for.
 * @param array  $fields Fields to search for the `$slug`.
 *
 * @return bool True if the field is the featured image or false
 */
function is_field_featured_image( string $slug, array $fields ): bool {
	foreach ( $fields as $field ) {
		if ( $field['slug'] === $slug ) {
			return isset( $field['isFeatured'] ) ? $field['isFeatured'] : false;
		}
	}

	return false;
}

/**
 * Gets the field from the field slug.
 *
 * @param string $slug Field slug to look for the 'type' property.
 * @param array  $models Models with field properties to search for the `$slug`.
 * @param string $post_type Current post type on the publisher screen.
 *
 * @return string Field type if found, or 'unknown'.
 */
function get_field_type_from_slug( string $slug, array $models, string $post_type ): string {
	$field = get_field_from_slug( $slug, $models, $post_type );

	return $field['type'] ?? 'unknown';
}

/**
 * Appends relationship fields from other models to a model's field list for all
 * relationship fields with a back reference to the $post_type.
 *
 * This ensures that relationship fields appear on the reverse side on
 * publisher entry screens so they can be edited from either side.
 *
 * For example, a relationship field between Left and Right models with
 * back references enabled is stored with the Left model, but should also appear
 * in the Right model's list of fields.
 *
 * @param array  $models Complete models data.
 * @param string $post_type Current post type.
 * @return array Updated list of models with reverse relationship fields.
 */
function append_reverse_relationship_fields( array $models, string $post_type ): array {
	foreach ( $models as $slug => $model ) {
		if ( empty( $model['fields'] ) ) {
			continue;
		}

		foreach ( $model['fields'] as $field ) {
			if (
				$slug === $post_type ||
				( $field['type'] ?? '' ) !== 'relationship' ||
				( $field['reference'] ?? '' ) !== $post_type ||
				! $field['enableReverse']
			) {
				continue;
			}

			// Appends the relationship field to display it on the reverse side.
			$models[ $field['reference'] ]['fields'][ $field['id'] ] = $field;

			/**
			 * When appearing on the reverse side, relationship fields need to
			 * display the reverse label and refer to the “from” post type
			 * instead of their usual “to” reference type.
			 *
			 * For example, when a relationship field linking a “Left” post type
			 * to a “Right” post type appears on Left, its reference is “right”
			 * and its label might read “Rights” (the value of $field['name']).
			 * When appending that field to appear on the Right as a back
			 * reference, its reference needs to be adjusted to “left” and its
			 * label might read “Lefts” (the value of $field['reverseName']).
			 */
			$models[ $field['reference'] ]['fields'][ $field['id'] ]['reference'] = $slug;
			$models[ $field['reference'] ]['fields'][ $field['id'] ]['name']      = $field['reverseName'] ?? $slug;

			/**
			 * Reverse cardinality for the back reference.
			 * - one-to-many becomes many-to-one on the “Right” side.
			 * - many-to-one becomes one-to-many on the “Right” side.
			 * - one-to-one and many-to-many are unchanged.
			 */
			if ( $field['cardinality'] === 'one-to-many' ) {
				$models[ $field['reference'] ]['fields'][ $field['id'] ]['cardinality'] = 'many-to-one';
			} elseif ( $field['cardinality'] === 'many-to-one' ) {
				$models[ $field['reference'] ]['fields'][ $field['id'] ]['cardinality'] = 'one-to-many';
			}
		}
	}

	return $models;
}

/**
 * Sanitizes field data based on the field type.
 *
 * @param string $type The type of field.
 * @param mixed  $value The unsanitized field value already processed by `wp_unslash()`.
 *
 * @return mixed The sanitized field value.
 */
function sanitize_field( string $type, $value ) {
	switch ( $type ) {
		case 'text':
			if ( is_array( $value ) ) {
				return array_filter( array_map( 'wp_strip_all_tags', $value ) );
			}
			return wp_strip_all_tags( $value );
		case 'richtext':
			return wp_kses_post( $value );
		case 'relationship':
			// Sanitizes each value as an integer and saves as a comma-separated string.
			$relationships = explode( ',', $value['relationshipEntryId'] );
			foreach ( $relationships as $index => $id ) {
				$relationships[ $index ] = filter_var( $id, FILTER_SANITIZE_NUMBER_INT );
			}
			return implode( ',', $relationships );
		case 'number':
			if ( is_array( $value ) ) {
				return array_filter(
					array_map(
						function( $val ) {
							return filter_var( $val, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION );
						},
						$value
					)
				);
			}
			return filter_var( $value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION );
		case 'date':
			$y_m_d_format = '/\d{4}-(0?[1-9]|1[012])-(0?[1-9]|[12][0-9]|3[01])/';
			if ( preg_match( $y_m_d_format, $value ) ) {
				return $value;
			}
			return '';
		case 'media':
			return preg_replace( '/\D/', '', $value );
		case 'boolean':
			return $value === 'on' ? 'on' : 'off';
		case 'multipleChoice':
			if ( is_array( $value ) ) {
				$options_object = [];
				foreach ( $value as $option ) {
					$options_object[] = key( $option );
				}
				return $options_object;
			}
			$options_object[] = $value;
			return $options_object;
		default:
			return $value;
	}
}
