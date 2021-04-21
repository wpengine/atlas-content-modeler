<?php
/**
 * Registers block templates for content model post types.
 *
 * So that new entries for each model have pre-placed blocks for every field.
 *
 * @package WPE_Content_Model
 */

declare(strict_types=1);

namespace WPE\ContentModel\BlockTemplates;

add_action( 'init', __NAMESPACE__ . '\content_model_templates' );
/**
 * Sets the default blocks that appear when creating new post types.
 */
function content_model_templates() {
	$models = get_option( 'wpe_content_model_post_types', [] );

	foreach ( $models as $id => $model ) {
		$block_names = get_block_names( $model );

		// Update the template for the model.
		$model_data           = get_post_type_object( $id );
		$model_data->template = get_template( $block_names );
	}
}

/**
 * Gets block names for the `$model` post type in the order that blocks should
 * appear in the post editor.
 *
 * A 'rabbits' model with two fields with IDs 1234 and 5678 returns block names
 * of [ 'wpe-content-model/rabbit-1234', 'wpe-content-model/rabbit-5678' ].
 *
 * @param array $model The model to get block names from.
 * @return array Block names based on the model and field ids.
 */
function get_block_names( $model ) {
	$names = [];

	if ( isset( $model['fields'] ) ) {
		$fields = $model['fields'];

		// TODO: filter out fields with a parent (one block per top-level field item only).

		// Show the field with the lowest position first.
		usort(
			$fields,
			function( $a, $b ) {
				return (int) $a['position'] > (int) $b['position'];
			}
		);

		foreach ( $fields as $field ) {
			$names[] = "wpe-content-model/{$model['slug']}-{$field['id']}";
		}
	}

	return $names;
}

/**
 * Generates a WordPress block template from an array of block names.
 *
 * A WordPress block template is an array of arrays whose first value is
 * the block name, so this function just wraps each string in an array.
 *
 * @link https://developer.wordpress.org/block-editor/reference-guides/block-api/block-templates/.
 * @param array $block_names Array of blocks.
 * @return array
 */
function get_template( $block_names ) {
	return array_map(
		function( $name ) {
			return [ $name ];
		},
		$block_names
	);
}
