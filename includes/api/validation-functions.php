<?php
/**
 * Validation functions for content model field types.
 *
 * @package AtlasContentModeler
 */

declare(strict_types=1);

namespace WPE\AtlasContentModeler\API\validation;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Validate a string field value.
 *
 * @param string $value The string field value.
 *
 * @return bool True if valid, false if else.
 */
function validate_string_field( string $value ): bool {
	$valid = false;

	if ( ! empty( $value ) ) {
		$valid = true;
	}

	return apply_filters( 'acm_validate_string_field', $valid, $value );
}
