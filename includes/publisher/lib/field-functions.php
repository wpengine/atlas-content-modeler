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
 * Gets the field type from the field slug.
 *
 * @param string $slug The slug of the field to look for.
 * @param array  $fields Fields to search for the `$slug`.
 *
 * @return string Field type if found, or 'unknown'.
 */
function get_field_type_from_slug( string $slug, array $fields ): string {
	$field_type = 'unknown';

	foreach ( $fields as $field ) {
		if ( $field['slug'] === $slug ) {
			$field_type = $field['type'] ?? 'unknown';
			break;
		}
	}

	return $field_type;
}

/**
 * Determins if the field is the featured image or not.
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
 * @param string $slug The slug of the field to look for.
 * @param array  $fields Fields to search for the `$slug`.
 *
 * @return array The field array of the associated slugs or false if not found.
 */
function get_field_from_slug( string $slug, array $fields ): ?array {
	$found = false;

	foreach ( $fields as $field ) {
		if ( $field['slug'] === $slug ) {
			$found = $field;
			break;
		}
	}

	return $found;
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
			return $value;
		default:
			return $value;
	}
}
