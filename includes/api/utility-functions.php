<?php
/**
 * Utility functions.
 *
 * @package AtlasContentModeler
 */

declare(strict_types=1);

namespace WPE\AtlasContentModeler\API\Utility;

/**
 * Retrieve data for matching model field slugs.
 *
 * Returns an associative array with keys that match model field slugs.
 *
 * @param array $model_fields Array of fields for a content model.
 * @param array $data Associative array of data.
 *
 * @return array The data for a model field.
 */
function get_data_for_fields( array $model_fields, array $data ): array {
	$model_field_names = array_fill_keys(
		wp_list_pluck( $model_fields, 'slug' ),
		null
	);

	return array_intersect_key( $data, $model_field_names );
}

/**
 * Get the key value of an array.
 *
 * @param int|string $key The index or key.
 * @param array      $data The array to search.
 * @param mixed      $default The value to return if key not found. Default null.
 *
 * @return mixed The value or default value if not found.
 */
function array_get_key_value( string $key, array $data, $default = null ): mixed {
	return \array_key_exists( $key, $data )
		? $data[ $key ]
		: $default;
}
