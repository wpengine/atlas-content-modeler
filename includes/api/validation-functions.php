<?php
/**
 * Validation functions for content model field types.
 *
 * @package AtlasContentModeler
 */

declare(strict_types=1);

namespace WPE\AtlasContentModeler\API\validation;

use WPE\AtlasContentModeler\Validation_Exception;

use function WPE\AtlasContentModeler\is_field_required;
use function WPE\AtlasContentModeler\is_field_repeatable;

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
		try {
			if ( is_field_required( $field ) ) {
				validate_array_key_exists( $field['slug'], $data, "{$field['name']} field is required" );
			}

			if ( ! \array_key_exists( $field['slug'], $data ) ) {
				continue;
			}

			$value = $data[ $field['slug'] ];
			switch ( $field['type'] ) {
				case 'text':
					validate_text_field( $value, $field );
					break;
				case 'richtext':
					validate_richtext_field( $value, $field );
					break;
				case 'number':
					validate_number_field( $value, $field );
					break;
				case 'date':
					validate_date_field( $value, $field );
					break;
				case 'multipleChoice':
					validate_multiple_choice_field( $value, $field );
					break;
			}
		} catch ( Validation_Exception $exception ) {
			$wp_error->merge_from(
				$exception->as_wp_error( 'invalid_model_field' )
			);
		}
	}

	if ( $wp_error->has_errors() ) {
		return $wp_error;
	}

	return true;
}

/**
 * Validate a text field value.
 *
 * @param mixed $value The field value.
 * @param array $field The model field.
 *
 * @throws Validation_Exception Exception when value is invalid.
 *
 * @return void
 */
function validate_text_field( $value, array $field ): void {
	if ( is_field_repeatable( $field ) ) {
		validate_array( $value, "{$field['name']} must be an array of {$field['type']}" );
	} else {
		validate_string( $value, "{$field['name']} must be valid {$field['type']}" );
	}

	if ( is_field_required( $field ) ) {
		validate_not_empty( $value, "{$field['name']} cannot be empty" );
	}
}

/**
 * Validate a rich text field value.
 *
 * Alias for validate_text_field().
 *
 * @param mixed $value The field value.
 * @param array $field The model field.
 *
 * @throws Validation_Exception Exception when value is invalid.
 *
 * @return void
 */
function validate_richtext_field( $value, array $field ): void {
	validate_text_field( $value, $field );
}

/**
 * Validate a number field value.
 *
 * @param mixed $value The field value.
 * @param array $field The model field.
 *
 * @throws Validation_Exception Exception when value is invalid.
 *
 * @return void
 */
function validate_number_field( $value, array $field ): void {
	if ( is_field_required( $field ) ) {
		validate_not_empty( $value, "{$field['name']} cannot be empty" );
	}

	if ( is_field_repeatable( $field ) ) {
		validate_array( $value, "{$field['name']} must be an array of {$field['type']}" );
	} else {
		validate_number( $value, "{$field['name']} must be a valid {$field['type']}" );
	}
}

/**
 * Validate a date field value.
 *
 * @param mixed $value The field value.
 * @param array $field The model field.
 *
 * @throws Validation_Exception Exception when value is invalid.
 *
 * @return void
 */
function validate_date_field( $value, array $field ): void {
	if ( is_field_repeatable( $field ) ) {
		validate_array( $value, "{$field['name']} must be an array of {$field['type']}" );
	} else {
		validate_date( $value, "{$field['name']} must be a valid {$field['type']}" );
	}

	if ( is_field_required( $field ) ) {
		validate_not_empty( $value, "{$field['name']} cannot be empty" );
	}
}

/**
 * Validate multiple choice field value(s).
 *
 * @param mixed $value The field value.
 * @param array $field The model field.
 *
 * @throws Validation_Exception Exception when value is invalid.
 *
 * @return void
 */
function validate_multiple_choice_field( $value, array $field ): void {
	if ( is_field_required( $field ) ) {
		validate_not_empty( $value, "{$field['name']} cannot be empty" );
	}

	validate_array( $value, "{$field['name']} must be an array of choices" );

	if ( 'single' === $field['listType'] && \count( $value ) > 1 ) {
		throw new Validation_Exception( "{$field['name']} cannot have more than one choice" );
	}

	$choices = wp_list_pluck( $field['choices'], 'slug' );
	validate_in_array( $value, $choices, "{$field['name']} must only contain choice values" );
}

/**
 * Validate for valid number.
 *
 * @param mixed  $value The value.
 * @param string $message Optional. The error message.
 *
 * @throws Validation_Exception Exception when value is invalid.
 *
 * @return void
 */
function validate_number( $value, $message = 'Value must be a valid number' ): void {
	if ( ! \is_numeric( $value ) ) {
		throw new Validation_Exception( $message );
	}
}

/**
 * Validate a date.
 *
 * @param string $value The value.
 * @param string $message Optional. The error message.
 *
 * @throws Validation_Exception Exception when value is invalid.
 *
 * @return void
 */
function validate_date( $value, $message = 'Value must be of format YYYY-MM-DD' ): void {
	$date_format = '/\d{4}-(0?[1-9]|1[012])-(0?[1-9]|[12][0-9]|3[01])/';

	if ( ! \preg_match( $date_format, (string) $value ) ) {
		throw new Validation_Exception( $message );
	}
}

/**
 * Validate for valid string type.
 *
 * @param mixed  $value The value.
 * @param string $message Optional. The error message.
 *
 * @throws Validation_Exception Exception when value is invalid.
 *
 * @return void
 */
function validate_string( $value, string $message = 'Value is not of type string' ): void {
	if ( ! \is_string( $value ) ) {
		throw new Validation_Exception( $message );
	}
}

/**
 * Validate for valid array type.
 *
 * @param mixed  $value The value.
 * @param string $message Optional. The error message.
 *
 * @throws Validation_Exception Exception when value is invalid.
 *
 * @return void
 */
function validate_array( $value, string $message = 'Value is not of type array' ): void {
	if ( ! \is_array( $value ) ) {
		throw new Validation_Exception( $message );
	}
}

/**
 * Validate if an item or array of items is within a given array.
 *
 * @param mixed  $value The value.
 * @param array  $array The array of items to check.
 * @param string $message Optional. The error message.
 *
 * @throws Validation_Exception Exception when value is invalid.
 *
 * @return void
 */
function validate_in_array( $value, array $array, $message = 'Values not found within array' ): void {
	if ( ! \is_array( $value ) ) {
		$value = (array) $value;
	}

	if ( \count( $value ) !== \count( \array_intersect( $array, $value ) ) ) {
		throw new Validation_Exception( $message );
	}
}

/**
 * Validate a key exists within a given array.
 *
 * @param mixed  $key The key to check.
 * @param array  $array The array to check.
 * @param string $message Optional. The error message.
 *
 * @throws Validation_Exception Exception when value is invalid.
 *
 * @return void
 */
function validate_array_key_exists( $key, array $array, $message = 'The key is required' ): void {
	if ( ! \array_key_exists( $key, $array ) ) {
		throw new Validation_Exception( $message );
	}
}

/**
 * Validate an item is not empty.
 *
 * Will only check for empty string or empty array.
 *
 * @param mixed  $value The value.
 * @param string $message Optional. The error message.
 *
 * @throws Validation_Exception Exception when value is invalid.
 *
 * @return void
 */
function validate_not_empty( $value, string $message = 'The field cannot be empty' ): void {
	if ( ( \is_string( $value ) || \is_array( $value ) ) && empty( $value ) ) {
		throw new Validation_Exception( $message );
	}
}

/**
 * Validate a string, array, or number is at least the given minimum.
 *
 * @param mixed  $value The value.
 * @param int    $min The minimum criteria.
 * @param string $message The optional error message.
 *
 * @throws Validation_Exception Exception when value is invalid.
 *
 * @return void
 */
function validate_min( $value, int $min, string $message = 'The field must be at least the minimum' ): void {
	if ( \is_string( $value ) && \strlen( $value ) < $min ) {
		throw new Validation_Exception( $message );
	}

	if ( \is_array( $value ) && \count( $value ) < $min ) {
		throw new Validation_Exception( $message );
	}

	if ( \is_numeric( $value ) && (float) $value < $min ) {
		throw new Validation_Exception( $message );
	}
}

/**
 * Validate a string, array, or number is at most the given maximum.
 *
 * @param mixed  $value The value.
 * @param int    $max The maximum criteria.
 * @param string $message The optional error message.
 *
 * @throws Validation_Exception Exception when value is invalid.
 *
 * @return void
 */
function validate_max( $value, int $max, string $message = 'The field cannot exceed the maximum' ): void {
	if ( \is_string( $value ) && \strlen( $value ) > $max ) {
		throw new Validation_Exception( $message );
	}

	if ( \is_array( $value ) && \count( $value ) > $max ) {
		throw new Validation_Exception( $message );
	}

	if ( \is_numeric( $value ) && (float) $value > $max ) {
		throw new Validation_Exception( $message );
	}
}

/**
 * Validate a post object exists.
 *
 * @param  int         $id      The post id.
 * @param  string|null $message Optional exception message.
 *
 * @throws Validation_Exception Exception when post object does not exist.
 *
 * @return void
 */
function validate_post_exists( int $id, ?string $message = null ): void {
	$message = $message ?? \__( 'The post object was not found', 'atlas-content-modeler' );
	$post    = \get_post( $id );

	if ( ! $post ) {
		throw new Validation_Exception( $message );
	}
}
