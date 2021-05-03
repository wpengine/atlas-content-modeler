<?php
/**
 * Functions to manipulate and query fields from content models.
 *
 * @package WPE_Content_Model
 */

declare(strict_types=1);

namespace WPE\ContentModel;

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
	$fields = get_top_level_fields( $fields );
	$fields = order_fields( $fields );

	foreach ( $fields as $field ) {
		if ( isset( $field['isTitle'] ) && $field['isTitle'] ) {
			return $field;
		}
	}

	return [];
}

/**
 * Gives the first field of the named $type.
 *
 * @param array  $fields Fields to check for a text field.
 * @param string $type The type of field to look for.
 * @return array First text field data or an empty array.
 */
function get_first_field_of_type( array $fields, string $type ): array {
	$fields = get_top_level_fields( $fields );
	$fields = order_fields( $fields );

	foreach ( $fields as $field ) {
		if ( isset( $field['type'] ) && $field['type'] === $type ) {
			return $field;
		}
	}

	return [];
}

/**
 * Removes fields with parents to leave top-level fields.
 *
 * @param array $fields All fields.
 * @return array Top-level fields with children omitted.
 */
function get_top_level_fields( array $fields ): array {
	$fields = array_filter(
		$fields,
		function( $field ) {
			return ! isset( $field['parent'] );
		}
	);

	return array_values( $fields );
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
