<?php
/**
 * Validation functions for content model field types.
 *
 * @package AtlasContentModeler
 */

declare(strict_types=1);

namespace WPE\AtlasContentModeler\API\validation;

use WPE\AtlasContentModeler\Validation_Exception;
use WPE\AtlasContentModeler\WP_Error;

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
 * @return bool|\WPE\AtlasContentModeler\WP_Error True if valid, else WP_Error with errors.
 */
function validate_model_field_data( array $model_schema, array $data ) {
	$wp_error = new WP_Error();

	foreach ( $model_schema['fields'] as $id => $field ) {
		try {
			if ( is_field_required( $field ) ) {
				validate_array_key_exists(
					$field['slug'],
					$data,
					// translators: The name of the field.
					\sprintf( \__( '%s is required', 'atlas-content-modeler' ), $field['name'] )
				);
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
				case 'media':
					validate_media_field( $value, $field );
					break;
				case 'relationship':
					validate_relationship_field( $value, $field );
					break;
				case 'email':
					validate_email_field( $value, $field );
					break;
			}
		} catch ( Validation_Exception $exception ) {
			$wp_error->merge_from(
				$exception->as_wp_error( $field['slug'] )
			);
		}
	}

	if ( $wp_error->has_errors() ) {
		return $wp_error;
	}

	return true;
}

/**
 * Validates a string value for text field min and max.
 *
 * @param string $value String value of the text field.
 * @param array  $field Array of values from the field.
 * @return void
 */
function validate_text_min_max( $value, $field ) {
	$min_message = \__( 'Value must meet the minimum length', 'atlas-content-modeler' );
	$max_message = \__( 'Value exceeds the maximum length', 'atlas-content-modeler' );

	if ( \is_numeric( $field['minChars'] ?? '' ) ) {
		validate_min( $value, $field['minChars'], $min_message );
	}

	if ( \is_numeric( $field['maxChars'] ?? '' ) ) {
		validate_max( $value, $field['maxChars'], $max_message );
	}
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
		// translators: The name and type of the field.
		$message = \sprintf( \__( '%1$s must be an array of %2$s', 'atlas-content-modeler' ), $field['name'], $field['type'] );

		validate_array( $value, $message );
		validate_row_count_within_repeatable_limits( count( $value ), $field );

		if ( is_field_required( $field ) ) {
			validate_not_empty( $value, $message );
		}
	}

	validate_array_of(
		(array) $value,
		static function ( $field_value ) use ( $field ) {
			validate_string(
				$field_value,
				// translators: The name and type of the field.
				\sprintf( \__( '%1$s must be valid %2$s', 'atlas-content-modeler' ), $field['name'], $field['type'] )
			);

			validate_text_min_max( $field_value, $field );

			if ( is_field_required( $field ) ) {
				validate_not_empty(
					$field_value,
					// translators: The name of the field.
					\sprintf( \__( '%s is required', 'atlas-content-modeler' ), $field['name'] )
				);
			}
		}
	);
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
	if ( is_field_repeatable( $field ) ) {
		validate_array(
			$value,
			// translators: The field name and field type.
			\sprintf( \__( '%1$s must be an array of %2$s', 'atlas-content-modeler' ), $field['name'], $field['type'] )
		);

		if ( is_field_required( $field ) ) {
			validate_not_empty(
				$value,
				// translators: The field name.
				\sprintf( \__( '%s cannot be empty', 'atlas-content-modeler' ), $field['name'] )
			);
		}

		validate_row_count_within_repeatable_limits( count( $value ), $field );
	}

	validate_array_of(
		(array) $value,
		static function ( $field_value ) use ( $field ) {
			if ( is_field_required( $field ) ) {
				validate_not_empty(
					$field_value,
					// translators: The field name.
					\sprintf( \__( '%s cannot be empty', 'atlas-content-modeler' ), $field['name'] )
				);
			}

			validate_number(
				$field_value,
				// translators: The field name and field type.
				\sprintf( \__( '%1$s must be a valid %2$s', 'atlas-content-modeler' ), $field['name'], $field['type'] )
			);

			validate_number_type(
				$field_value,
				$field['numberType'],
				// translators: The field name and field type.
				\sprintf( \__( '%1$s must be of type %2$s', 'atlas-content-modeler' ), $field['name'], $field['numberType'] )
			);

			validate_number_min_max_step( $field_value, $field );
		}
	);
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
		// translators: The name and type of the field.
		$message = \sprintf( \__( '%1$s must be an array of %2$s', 'atlas-content-modeler' ), $field['name'], $field['type'] );

		validate_array( $value, $message );

		if ( is_field_required( $field ) ) {
			validate_not_empty( $value, $message );
		}
	}

	validate_array_of(
		(array) $value,
		static function ( $field_value ) use ( $field ) {
			if ( is_field_required( $field ) ) {
				validate_not_empty(
					$field_value,
					// translators: The name of the field.
					\sprintf( \__( '%s is required', 'atlas-content-modeler' ), $field['name'] )
				);
			}

			if ( $field_value ) {
				validate_date(
					$field_value,
					// translators: The name and type of the field.
					\sprintf( \__( '%1$s must be a valid %2$s', 'atlas-content-modeler' ), $field['name'], $field['type'] )
				);
			}
		}
	);
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
 * Validate a media field value.
 *
 * @param mixed $value The field value.
 * @param array $field The model field.
 *
 * @throws Validation_Exception Exception when value is invalid.
 *
 * @return void
 */
function validate_media_field( $value, array $field ): void {
	if ( is_field_required( $field ) ) {
		validate_not_empty(
			$value,
			// translators: The name of the field.
			\sprintf( \__( '%s cannot be empty', 'atlas-content-modeler' ), $field['name'] )
		);
	}

	if ( is_field_repeatable( $field ) ) {
		// translators: %1$s: Field name, such as “Colors”. %2$s: Field type, such as “string”.
		$message = \sprintf( \__( '%1$s must be an array of %2$s', 'atlas-content-modeler' ), $field['name'], $field['type'] );

		validate_array( $value, $message );

		if ( is_field_required( $field ) ) {
			validate_not_empty( $value, $message );
		}
	}

	validate_array_of(
		(array) $value,
		static function ( $field_value ) use ( $field ) {
			if ( is_field_required( $field ) ) {
				validate_not_empty(
					$field_value,
					// translators: The name of the field.
					\sprintf( \__( '%s is required', 'atlas-content-modeler' ), $field['name'] )
				);
			}

			// If not required and empty, then return.
			if ( '' === $field_value || is_null( $field_value ) ) {
				return;
			}

			validate_post_is_attachment(
				$field_value,
				// translators: The name and type of the field.
				\sprintf( \__( '%1$s must be a valid %2$s', 'atlas-content-modeler' ), $field['name'], 'attachment id' )
			);

			if ( ! empty( $field['allowedTypes'] ) ) {
				$allowed_types = \explode( ',', $field['allowedTypes'] );

				validate_attachment_file_type(
					(int) $field_value,
					$allowed_types,
					// translators: The name of the field and file type extensions.
					\sprintf( \__( '%1$s must be of type %2$s', 'atlas-content-modeler' ), $field['name'], $field['allowedTypes'] )
				);
			}
		}
	);
}

/**
 * Validate a relationship field value.
 *
 * @param mixed $value The field value.
 * @param array $field The model field.
 *
 * @throws Validation_Exception Exception when value is invalid.
 *
 * @return void
 */
function validate_relationship_field( $value, array $field ): void {
	if ( is_field_required( $field ) ) {
		validate_not_empty(
			$value,
			// translators: The name of the field.
			\sprintf( \__( '%s is required', 'atlas-content-modeler' ), $field['name'] )
		);
	}

	// Due to sanitize_field() converting to comma delimted string.
	if ( is_string( $value ) ) {
		$value = explode( ',', $value );
	}

	validate_array_of(
		(array) $value,
		static function ( $item, $index ) use ( $field ) {
			validate_number(
				$item,
				\__( 'Invalid relationship id', 'atlas-content-modeler' )
			);

			validate_post_type(
				$item,
				$field['reference'],
				\__( 'Invalid post type for relationship', 'atlas-content-modeler' )
			);
		}
	);
}

/**
 * Validate an email field value.
 *
 * @param mixed $value The field value.
 * @param array $field The model field.
 *
 * @throws Validation_Exception Exception when value is invalid.
 *
 * @return void
 */
function validate_email_field( $value, array $field ): void {
	if ( is_field_repeatable( $field ) ) {
		// translators: The name and type of the field.
		$message = \sprintf( \__( '%1$s must be an array of %2$s', 'atlas-content-modeler' ), $field['name'], $field['type'] );

		validate_array( $value, $message );

		if ( is_field_required( $field ) ) {
			validate_not_empty( $value, $message );
		}
	}

	validate_array_of(
		(array) $value,
		static function ( $field_value ) use ( $field ) {
			if ( is_field_required( $field ) ) {
				validate_not_empty(
					$field_value,
					// translators: The name of the field.
					\sprintf( \__( '%s is required', 'atlas-content-modeler' ), $field['name'] )
				);
			}

			// If not required and empty, then return.
			if ( '' === $field_value || is_null( $field_value ) ) {
				return;
			}

			validate_email( // phpcs:ignore WordPress.WP.DeprecatedFunctions.validate_emailFound
				$field_value,
				// translators: The name and type of the field.
				\sprintf( \__( '%1$s must be a valid %2$s', 'atlas-content-modeler' ), $field['name'], $field['type'] )
			);
		}
	);
}

/**
 * Validate the number type.
 *
 * Type-checks numbers and numeric strings.
 *
 * Will not throw for strings such as "test" passed as integer types.
 * Use `validate_number()` to validate a string as numeric.
 *
 * The zero values 0, 0.0, -0 and -0.0 and their string equivalents are
 * considered valid for all types.
 *
 * @param mixed  $value The value as a number or string.
 * @param mixed  $number_type The number type.
 * @param string $message Optional. The error message.
 *
 * @throws Validation_Exception Exception when value is the invalid type.
 *
 * @return void
 */
function validate_number_type( $value, $number_type, string $message = '' ): void {
	if ( 'decimal' === $number_type ) {
		validate_decimal( $value, $message );
	} else {
		validate_integer( $value, $message );
	}
}

/**
 * Validate a value for an integer.
 *
 * @param mixed  $value   The value to validate.
 * @param string $message Optional error message.
 *
 * @throws Validation_Exception Exception when value is the invalid type.
 *
 * @return void
 */
function validate_integer( $value, string $message = '' ): void {
	$message = $message ?: \__( 'Value must be a valid integer', 'atlas-content-modeler' );

	validate_number( $value, $message );

	if ( false === \filter_var( (float) $value, FILTER_VALIDATE_INT ) ) {
		throw new Validation_Exception( $message );
	}
}

/**
 * Validate a value for a decimal.
 *
 * @param mixed  $value   The value to validate.
 * @param string $message Optional error message.
 *
 * @throws Validation_Exception Exception when value is the invalid type.
 *
 * @return void
 */
function validate_decimal( $value, string $message = '' ): void {
	$message = $message ?: \__( 'Value must be a valid decimal', 'atlas-content-modeler' );

	validate_number( $value, $message );

	if ( false === \filter_var( $value, FILTER_VALIDATE_FLOAT ) ) {
		throw new Validation_Exception( $message );
	}
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
function validate_number( $value, string $message = '' ): void {
	$message = $message ?: \__( 'Value must be a valid number', 'atlas-content-modeler' );

	if ( ! \is_numeric( $value ) ) {
		throw new Validation_Exception( $message );
	}
}

/**
 * Validates a number value for number field min and max.
 *
 * @param string $value String value of the text field.
 * @param array  $field Array of values from the field.
 * @return void
 */
function validate_number_min_max_step( $value, $field ) {
	if ( \is_numeric( $field['minValue'] ?? '' ) ) {
		validate_min(
			$value,
			$field['minValue'],
			// translators: The field name and field minimum value.
			\sprintf( \__( '%1$s must be at least %2$d', 'atlas-content-modeler' ), $field['name'], $field['minValue'] )
		);
	}

	if ( \is_numeric( $field['maxValue'] ?? '' ) ) {
		validate_max(
			$value,
			$field['maxValue'],
			// translators: The field name and field maximum value.
			\sprintf( \__( '%1$s cannot be greater than %2$d', 'atlas-content-modeler' ), $field['name'], $field['maxValue'] )
		);
	}

	if ( \is_numeric( $field['step'] ?? '' ) ) {
		validate_step(
			$value,
			$field['step'],
			// translators: The field name and field step value.
			\sprintf( \__( '%1$s step must be a multiple of %2$d', 'atlas-content-modeler' ), $field['name'], $field['step'] )
		);
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
function validate_date( $value, string $message = '' ): void {
	$message = $message ?: \__( 'Value must be of format YYYY-MM-DD', 'atlas-content-modeler' );

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
function validate_string( $value, string $message = '' ): void {
	$message = $message ?: \__( 'Value is not of type string', 'atlas-content-modeler' );

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
function validate_array( $value, string $message = '' ): void {
	$message = $message ?: \__( 'Value is not of type array', 'atlas-content-modeler' );

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
function validate_in_array( $value, array $array, string $message = '' ): void {
	$message = $message ?: \__( 'Values not found within array', 'atlas-content-modeler' );

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
function validate_array_key_exists( $key, array $array, string $message = '' ): void {
	$message = $message ?: \__( 'The key is required', 'atlas-content-modeler' );

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
function validate_not_empty( $value, string $message = '' ): void {
	$message = $message ?: \__( 'The field cannot be empty', 'atlas-content-modeler' );

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
function validate_min( $value, int $min, string $message = '' ): void {
	$message = $message ?: \__( 'The field must be at least the minimum', 'atlas-content-modeler' );

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
function validate_max( $value, int $max, string $message = '' ): void {
	$message = $message ?: \__( 'The field cannot exceed the maximum', 'atlas-content-modeler' );

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
 * Validate a number against step.
 *
 * @param mixed  $value The value.
 * @param int    $step The step value.
 * @param string $message The optional error message.
 *
 * @throws Validation_Exception Exception when value is invalid.
 *
 * @return void
 */
function validate_step( $value, $step, string $message = '' ): void {
	if ( \is_numeric( $value ) &&
			\is_numeric( $step ) &&
			! ( $value % $step === 0 ) ) {
		throw new Validation_Exception( $message );
	}
}

/**
 * Validates the provided `$count` of rows is within the `minRepeatable` and
 * `maxRepeatable` limits optionally offered by repeater fields.
 *
 * @param int   $count The number of rows in repeatable field content.
 * @param array $field Field data with minRepeatable and maxRepeatable values.
 *
 * @throws Validation_Exception Exception when `$count` is outside min/max.
 *
 * @return void
 */
function validate_row_count_within_repeatable_limits( int $count, array $field ): void {
	if ( \is_numeric( $field['minRepeatable'] ?? '' ) ) {
		validate_min(
			$count,
			$field['minRepeatable'],
			sprintf(
				// translators: number of rows.
				__( 'The field requires at least %s rows.', 'atlas-content-modeler' ),
				$field['minRepeatable']
			)
		);
	}
	if ( \is_numeric( $field['maxRepeatable'] ?? '' ) ) {
		validate_max(
			$count,
			$field['maxRepeatable'],
			sprintf(
				// translators: number of rows.
				__( 'The field must have no more than %s rows.', 'atlas-content-modeler' ),
				$field['maxRepeatable']
			)
		);
	}
}

/**
 * Validate a post object exists.
 *
 * @param int         $id      The post id.
 * @param string|null $message Optional exception message.
 *
 * @throws Validation_Exception Exception when post object does not exist.
 *
 * @return void
 */
function validate_post_exists( $id, ?string $message = '' ): void {
	$message = $message ?: \__( 'The post object was not found', 'atlas-content-modeler' );
	$post    = \get_post( $id );

	if ( ! $post ) {
		throw new Validation_Exception( $message );
	}
}

/**
 * Validate a post against the given post type.
 *
 * @param int         $id        The post id.
 * @param string      $post_type The post type.
 * @param string|null $message   Optional exception message.
 *
 * @throws Validation_Exception Exception when post object is not a post type.
 *
 * @return void
 */
function validate_post_type( $id, string $post_type, ?string $message = '' ): void {
	$message = $message ?: \__( 'Invalid post type', 'atlas-content-modeler' );

	if ( $post_type !== \get_post_type( $id ) ) {
		throw new Validation_Exception( $message );
	}
}

/**
 * Validate a post id is an attachment.
 *
 * @param int         $id      The post id.
 * @param string|null $message Optional exception message.
 *
 * @throws Validation_Exception Exception when post object is not an attachment.
 *
 * @return void
 */
function validate_post_is_attachment( $id, ?string $message = '' ): void {
	$message = $message ?: \__( 'Post is not an attachment post type', 'atlas-content-modeler' );

	validate_post_type( $id, 'attachment', $message );
}

/**
 * Validate an attachment file type against given types.
 *
 * @param int         $id      The post id.
 * @param array       $types   Array of file type extensions.
 * @param string|null $message Optional exception message.
 *
 * @throws Validation_Exception Exception when attachment type is not valid.
 *
 * @return void
 */
function validate_attachment_file_type( $id, array $types, ?string $message = '' ): void {
	$metadata = \wp_get_attachment_metadata( $id );
	$message  = $message ?: \sprintf(
		// translators: The file type extensions.
		\__( 'File must be of %s types', 'atlas-content-modeler' ),
		\implode( ', ', $types )
	);

	if ( ! $metadata || empty( $metadata['file'] ) ) {
		throw new Validation_Exception( $message );
	}

	$file_extension = \wp_check_filetype( $metadata['file'] );
	if ( ! $file_extension['ext'] || ! \in_array( $file_extension['ext'], $types, true ) ) {
		throw new Validation_Exception( $message );
	}
}

/**
 * Validate for valid email.
 *
 * @param mixed  $value The value.
 * @param string $message Optional. The error message.
 *
 * @throws Validation_Exception Exception when value is invalid.
 *
 * @return void
 */
function validate_email( $value, string $message = '' ): void {
	$message = $message ?: \__( 'A valid email is required', 'atlas-content-modeler' );

	if ( ! \is_email( (string) $value ) ) {
		throw new Validation_Exception( $message );
	}
}

/**
 * Validate an array of data given a callable.
 *
 * Bundles exception messages and throws as a single exception.
 *
 * @param array $array    Array of data.
 * @param mixed $callback Callback used for each item of data.
 *
 * @throws Validation_Exception Exception when errors occur.
 *
 * @return void
 */
function validate_array_of( array $array, $callback ): void {
	$errors = [];

	foreach ( $array as $index => $value ) {
		try {
			\call_user_func( $callback, $value, $index );
		} catch ( Validation_Exception $exception ) {
			$errors[ $index ] = $exception->getMessage();
		}
	}

	if ( ! empty( $errors ) ) {
		$exception = new Validation_Exception();
		$exception->add_messages( $errors );

		throw $exception;
	}
}
