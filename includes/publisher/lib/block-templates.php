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
 * Sets the default block that appears when creating new post types.
 */
function content_model_templates() {
	$models = get_option( 'wpe_content_model_post_types', [] );

	foreach ( $models as $id => $model ) {
		$model_data           = get_post_type_object( $id );
		$model_data->template = [ [ 'wpe-content-model/' .$id ] ];
	}
}
