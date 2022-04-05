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
