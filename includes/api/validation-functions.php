<?php
/**
 * Validation functions for content model field types.
 *
 * @package AtlasContentModeler
 */

declare(strict_types=1);

namespace WPE\AtlasContentModeler\API\validation;

use function WPE\AtlasContentModeler\sanitize_field;
use function WPE\AtlasContentModeler\is_field_required;
use function WPE\AtlasContentModeler\is_field_repeatable;
use function WPE\AtlasContentModeler\API\Utility\array_get_key_value;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Validate data for model fields adheres to the field type.
 *
 * @param array $model_schema The content model schema.
 * @param array $data         The data to check against field types.
 *
 * @return bool|WP_Error True if valid, else WP_Error with errors.
 */
function validate_model_field_data( array $model_schema, array $data ) {
	$wp_error = new \WP_Error();

	foreach ( $model_schema['fields'] as $id => $field ) {
		$value = array_get_key_value( $field['slug'], $data );

		if ( is_field_required( $field ) && ( null === $value || '' === $value ) ) {
			$wp_error->add( 'invalid_model_field', "Field '{$field['name']}' is required" );
			continue;
		}

		if ( is_field_required( $field ) && is_field_repeatable( $field ) && [] === $value ) {
			$wp_error->add( 'invalid_model_field', "Field '{$field['name']}' is required" );
			continue;
		}

		$value = sanitize_field( $field['type'], $value );

		switch ( $field['type'] ) {
			case 'text':
			case 'richtext':
			case 'number':
			case 'date':
				if ( '' === $value ) {
					$wp_error->add( 'invalid_model_field', "{$field['name']} is invalid for value: {$field['value']}. Type: {$field['type']}." );
				}
				break;
			case 'boolean':
				if ( ! validate_boolean_field( $value ) ) {
					$wp_error->add( 'invalid_model_field', "{$field['name']} is invalid for value: {$field['value']}. Type: {$field['type']}." );
				}
				break;
			case 'multipleChoice':
				if ( ! validate_multiple_choice_field( $value ) ) {
					$wp_error->add( 'invalid_field', "{$field['name']} is invalid for value: {$field['value']}. Type: {$field['type']}." );
				}
				break;
		}
	}

	if ( $wp_error->has_errors() ) {
		return $wp_error;
	}

	return true;
}

/**
 * Check that a value is a string.
 *
 * @param string $value The field value.
 *
 * @return bool True if valid, false if else.
 */
function validate_string( $value ): bool {
	$valid = false;

	if ( ! empty( $value ) ) {
		$valid = true;
	}

	return $valid;
}

/**
 * Check that a value is a number.
 *
 * @param string $value The field value.
 *
 * @return bool True if valid, false if else.
 */
function validate_number( $value ): bool {
	$valid = false;

	if ( is_numeric( $value ) ) {
		$valid = true;
	}

	return $valid;
}

/**
 * Check that a value is an array.
 *
 * @param string $value The field value.
 *
 * @return bool True if valid, false if else.
 */
function validate_array( $value ): bool {
	$valid = false;

	if ( is_array( $value ) ) {
		$valid = true;
	}

	return $valid;
}

/**
 * Check that a value is a bool.
 *
 * @param string $value The string field value.
 *
 * @return bool True if valid, false if else.
 */
function validate_bool( $value ): bool {
	$valid = false;
	$value = strtolower( $value );

	if ( 'true' === $value || '1' === $value ) {
		$valid = true;
	}

	return $valid;
}


/**
 * Validate a text field value.
 *
 * @param string $value The string field value.
 *
 * @return bool True if valid, false if else.
 */
function validate_text_field( $value ): bool {
	$valid = validate_string( $value );

	return apply_filters( 'acm_validate_text_field', $valid, $value );
}

/**
 * Validate a rich text field value.
 *
 * @param string $value The string field value.
 *
 * @return bool True if valid, false if else.
 */
function validate_rich_text_field( $value ): bool {
	$valid = validate_string( $value );

	return apply_filters( 'acm_validate_rich_text_field', $valid, $value );
}

/**
 * Validate a number field value.
 *
 * @param string $value The string field value.
 *
 * @return bool True if valid, false if else.
 */
function validate_number_field( $value ): bool {
	$valid = validate_number( $value );

	return apply_filters( 'acm_validate_number_field', $valid, $value );
}

/**
 * Validate a date field value.
 *
 * @param string $value The string field value.
 *
 * @return bool True if valid, false if else.
 */
function validate_date_field( $value ): bool {
	$valid = validate_string( $value );

	return apply_filters( 'acm_validate_date_field', $valid, $value );
}

/**
 * Validate a media field value.
 *
 * @param string $value The string field value.
 *
 * @return bool True if valid, false if else.
 */
function validate_media_field( $value ): bool {
	$valid = false;

	return apply_filters( 'acm_validate_media_field', $valid, $value );
}

/**
 * Validate a boolean field value.
 *
 * @param string $value The string field value.
 *
 * @return bool True if valid, false if else.
 */
function validate_boolean_field( $value ): bool {
	$valid = validate_bool( $value );

	return apply_filters( 'acm_validate_boolean_field', $valid, $value );
}

/**
 * Validate a relationship field value.
 *
 * @param string $value The string field value.
 *
 * @return bool True if valid, false if else.
 */
function validate_relationship_field( string $value ): bool {
	$valid = false;

	return apply_filters( 'acm_validate_relationship_field', $valid, $value );
}

/**
 * Validate a multiple choice field value.
 *
 * @param string $value The string field value.
 *
 * @return bool True if valid, false if else.
 */
function validate_multiple_choice_field( string $value ): bool {
	$valid = validate_array( $value );

	return apply_filters( 'acm_validate_multiple_choice_field', $valid, $value );
}
