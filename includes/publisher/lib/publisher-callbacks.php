<?php
/**
 * Publisher experience callbacks.
 *
 * @package WPE_Content_Model
 */

declare(strict_types=1);

namespace WPE\ContentModel\Publisher;

use function WPE\ContentModel\ContentRegistration\get_registered_content_types;

add_action( 'enqueue_block_editor_assets', __NAMESPACE__ . '\enqueue_block_editor_assets' );
/**
 * Enqueues editor assets for the publisher experience.
 */
function enqueue_block_editor_assets(): void {
	$plugin = get_plugin_data( WPE_CONTENT_MODEL_FILE );

	wp_register_script(
		'wpe-content-model-publisher-experience',
		WPE_CONTENT_MODEL_URL . 'includes/publisher/dist/index.js',
		[ 'wp-blocks', 'wp-element', 'wp-components', 'wp-data', 'wp-core-data', 'wp-block-editor' ],
		$plugin['Version'],
		true
	);

	wp_localize_script(
		'wpe-content-model-publisher-experience',
		'wpeContentModel',
		array(
			'models'   => get_registered_content_types(),
			'postType' => get_post_type(),
		)
	);

	// TODO: Restrict this to content model custom post types only?
	wp_enqueue_script( 'wpe-content-model-publisher-experience' );
}
