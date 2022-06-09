<?php
/**
 * Block Editor Field
 *
 * @package AtlasContentModeler
 */

declare(strict_types=1);

namespace WPE\AtlasContentModeler\Fields\BlockEditor;

defined( 'WPINC' ) || die;

add_action( 'init', __NAMESPACE__ . '\register_block_editor_field_post_type' );
/**
 * Registers the block editor field post type.
 *
 * @return void
 */
function register_block_editor_field_post_type(): void {
	register_post_type(
		'acm_field_type_block',
		[
			'public'       => false,
			'has_archive'  => false,
			'show_ui'      => true,
			'show_in_menu' => false,
			'show_in_rest' => true,
			'supports'     => [ 'editor' ],
			'labels'       => [
				'name'          => 'Block Editor Fields',
				'singular_name' => 'Block Editor Field',
			],
		]
	);
}

add_action( 'enqueue_block_editor_assets', __NAMESPACE__ . '\load_block_editor_field_scripts' );
/**
 * Loads the scripts and styles for the block editor field.
 *
 * @return void
 */
function load_block_editor_field_scripts(): void {
	if ( 'acm_field_type_block' !== get_post_type() ) {
		return;
	}

	wp_enqueue_style(
		'acm_field_block_editor',
		ATLAS_CONTENT_MODELER_URL . 'includes/publisher/js/src/components/BlockEditor/field-block-editor.css',
		[],
		get_plugin_data( ATLAS_CONTENT_MODELER_FILE )['Version']
	);

	wp_enqueue_script(
		'acm_field_block_editor_message',
		ATLAS_CONTENT_MODELER_URL . 'includes/publisher/js/src/components/BlockEditor/field-block-editor-message.js',
		[],
		get_plugin_data( ATLAS_CONTENT_MODELER_FILE )['Version'],
		false
	);
}
