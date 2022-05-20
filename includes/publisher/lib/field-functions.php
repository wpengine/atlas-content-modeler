<?php
/**
 * Functions to manipulate and query fields from content models.
 *
 * @package AtlasContentModeler
 */

declare(strict_types=1);

namespace WPE\AtlasContentModeler;

use function WPE\AtlasContentModeler\API\get_model;

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
 * Determine if the given field is required.
 *
 * @param array $field The field schema.
 *
 * @return boolean True if required, false if else.
 */
function is_field_required( array $field ): bool {
	return \array_key_exists( 'required', $field ) && true === $field['required'];
}

/**
 * Determine if the given field is repeatable.
 *
 * @param array $field The field schema.
 *
 * @return boolean True if required, false if else.
 */
function is_field_repeatable( array $field ): bool {
	$repeatable = false;
	$key        = '';

	switch ( $field['type'] ) {
		case 'text':
			$key = 'isRepeatable';
			break;
		case 'email':
			$key = 'isRepeatableEmail';
			break;
		case 'richtext':
			$key = 'isRepeatableRichText';
			break;
		case 'number':
			$key = 'isRepeatableNumber';
			break;
		case 'date':
			$key = 'isRepeatableDate';
			break;
		case 'media':
			$key = 'isRepeatableMedia';
			break;
	}

	if ( $key && \array_key_exists( $key, $field ) ) {
		$repeatable = ( true === $field[ $key ] || 'true' === $field[ $key ] );
	}

	return $repeatable;
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
 * Sanitize field values.
 *
 * @param array $model The model schema.
 * @param array $data The unvalidated data.
 *
 * @return array The sanitized data based on if field key exists.
 */
function sanitize_fields( array $model, array $data ) {
	$model_slug_types = \array_combine(
		\wp_list_pluck( $model['fields'], 'slug' ),
		\wp_list_pluck( $model['fields'], 'type' )
	);

	\array_walk(
		$data,
		function ( &$value, $key ) use ( $model_slug_types ) {
			if ( \array_key_exists( $key, $model_slug_types ) ) {
				$value = sanitize_field( $model_slug_types[ $key ], $value );
			}
		}
	);

	return $data;
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
			if ( is_array( $value ) ) {
				return array_filter( array_map( 'wp_kses_post', $value ) );
			}
			return wp_kses_post( $value );
		case 'relationship':
			// Sanitizes each value as an integer and saves as a comma-separated string.
			$relationship_ids = $value['relationshipEntryId'] ?? $value;

			if ( is_string( $relationship_ids ) ) {
				$relationship_ids = explode( ',', $relationship_ids );
			} elseif ( is_int( $relationship_ids ) ) {
				$relationship_ids = (array) $relationship_ids;
			}

			$relationship_ids = array_filter(
				$relationship_ids,
				function ( $id ) {
					return filter_var( $id, FILTER_SANITIZE_NUMBER_INT );
				}
			);

			return implode( ',', $relationship_ids );
		case 'number':
			if ( is_array( $value ) ) {
				return array_filter(
					array_map(
						function( $val ) {
							return filter_var( $val, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION );
						},
						$value
					),
					'is_numeric'
				);
			}
			return filter_var( $value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION );
		case 'date':
			$y_m_d_format = '/\d{4}-(0?[1-9]|1[012])-(0?[1-9]|[12][0-9]|3[01])/';

			if ( is_array( $value ) ) {
				return array_filter(
					array_map(
						function( $val ) use ( $y_m_d_format ) {
							if ( preg_match( $y_m_d_format, $val ) ) {
								return $val;
							}
							return [ '' ];
						},
						$value
					)
				);
			}
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
				$choice_keys = [];
				foreach ( $value as $choice ) {
					$choice_keys[] = is_array( $choice ) ? key( $choice ) : $choice;
				}
				return $choice_keys;
			}
			return [ $value ];
		case 'email':
			if ( is_array( $value ) ) {
				return array_filter(
					array_map(
						function( $value ) {
							return filter_var( $value, FILTER_SANITIZE_EMAIL );
						},
						$value
					)
				);
			}

			return filter_var( $value, FILTER_SANITIZE_EMAIL );
		default:
			return $value;
	}
}

/**
 * Get an array of field attributes given field type and model.
 *
 * @param string $attribute The field attribute.
 * @param string $type      The model type.
 * @param string $model     The model name.
 *
 * @return array Array of attribute values for the given field and model.
 */
function get_attributes_for_field_type( string $attribute, string $type, string $model ): array {
	$fields = get_fields_by_type( $type, $model );

	return \wp_list_pluck( $fields, $attribute );
}

/**
 * Get model fields for the given type.
 *
 * @param string $type  The model type.
 * @param string $model The model name.
 *
 * @return array Array of model fields of type.
 */
function get_fields_by_type( string $type, string $model ): array {
	$model_schema = get_model( $model );
	$fields       = $model_schema['fields'] ?? [];

	return \array_filter(
		$fields,
		static function ( $field ) use ( $type ) {
			return $type === $field['type'];
		}
	);
}
