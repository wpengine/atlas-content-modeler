<?php
/**
 * An experiment with form-based publishing.
 *
 * @package WPE_Content_Model
 */

declare(strict_types=1);

namespace WPE\ContentModel;

use WP_Post;
use function WPE\ContentModel\ContentRegistration\get_registered_content_types;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class FormEditingExperience.
 *
 * A convenience class for sharing data across functions that run
 * on different hooks.
 *
 * @package WPE\ContentModel
 */
final class FormEditingExperience {

	/**
	 * Content models created by this plugin.
	 *
	 * @var array
	 */
	private $models;

	/**
	 * The post type of the post on this screen.
	 *
	 * @var string
	 */
	private $current_screen_post_type;

	/**
	 * FormEditingExperience constructor.
	 */
	public function __construct() {
		$this->bootstrap();
	}

	/**
	 * Bootstraps the plugin.
	 */
	public function bootstrap(): void {
		$this->models = get_registered_content_types();

		add_filter( 'use_block_editor_for_post_type', [ $this, 'disable_block_editor' ], 10, 2 );
		add_action( 'current_screen', [ $this, 'current_screen' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
		add_action( 'edit_form_after_title', [ $this, 'render_app_container' ] );
		add_action( 'save_post', [ $this, 'save_post' ], 10, 2 );
	}

	/**
	 * Saves the post type of the content being edited.
	 *
	 * @param object $screen The current screen object.
	 */
	public function current_screen( $screen ): void {
		$this->current_screen_post_type = $screen->post_type;
	}

	/**
	 * Enqueues scripts and styles related to our app.
	 */
	public function enqueue_assets(): void {
		// Bail if this isn't a model created by our plugin.
		if ( ! array_key_exists( $this->current_screen_post_type, $this->models ) ) {
			return;
		}

		global $post;

		$plugin = get_plugin_data( WPE_CONTENT_MODEL_FILE );

		wp_register_script(
			'wpe-content-model-form-editing-experience',
			WPE_CONTENT_MODEL_URL . 'includes/publisher/dist/index.js',
			[ 'react', 'react-dom', 'wp-tinymce' ],
			$plugin['Version'],
			true
		);

		wp_enqueue_style(
			'styles',
			WPE_CONTENT_MODEL_URL . '/includes/publisher/dist/index.css',
			false,
			$plugin['Version'],
			'all'
		);

		$models = get_registered_content_types();
		$model  = $models[ $this->current_screen_post_type ];

		// Add existing field values to models data.
		if ( ! empty( $post ) && ! empty( $model['fields'] ) ) {
			foreach ( $model['fields'] as $key => $field ) {
				// @todo wire up repeaters. for now, remove child fields to avoid confusion.
				if ( ! empty( $field['parent'] ) ) {
					unset( $models[ $this->current_screen_post_type ]['fields'][ $key ] );
					continue;
				}

				if ( isset( $post->ID ) ) {
					$models[ $this->current_screen_post_type ]['fields'][ $key ]['value'] = get_post_meta( $post->ID, $field['slug'], true );
				}
			}
		}

		wp_localize_script(
			'wpe-content-model-form-editing-experience',
			'wpeContentModelFormEditingExperience',
			[
				'models'   => $models,
				'postType' => $this->current_screen_post_type,
			]
		);

		wp_enqueue_script( 'wpe-content-model-form-editing-experience' );

		// UPLOAD ENGINE
		add_action( 'admin_enqueue_scripts', [ $this, 'load_wp_media_files' ], 10, 2 );
	}

	/**
	 * Load WordPress media files for uploader
	 */
	function load_wp_media_files() {
		// jquery is a dependency for the media uploader
		wp_enqueue_script( 'jquery' );
		wp_enqueue_media();
	}

	/**
	 * Disables the block editor on post types created by our plugin.
	 *
	 * @param bool   $use_block_editor Whether or not to use the block editor.
	 * @param string $post_type The post type.
	 *
	 * @return bool
	 */
	public function disable_block_editor( bool $use_block_editor, string $post_type ): bool {
		// Bail if this isn't a model created by our plugin.
		if ( ! array_key_exists( $post_type, $this->models ) ) {
			return $use_block_editor;
		}

		$use_block_editor = false;

		// @todo move to another action
		remove_post_type_support( $post_type, 'editor' );
		remove_post_type_support( $post_type, 'title' );
		remove_post_type_support( $post_type, 'custom-fields' );

		return $use_block_editor;
	}

	/**
	 * Renders the container used to mount the publisher experience app.
	 *
	 * @param WP_Post $post The post object being edited.
	 */
	public function render_app_container( WP_Post $post ): void {
		if ( ! array_key_exists( $post->post_type, $this->models ) ) {
			return;
		}

		$model = $this->models[ $post->post_type ] ?? false;
		if ( ! $model ) {
			return;
		}

		if ( empty( $model['fields'] ) ) {
			return;
		}

		wp_nonce_field( 'wpe-content-model-pubex-nonce', 'wpe-content-model-pubex-nonce' );
		echo '<div id="wpe-content-model-fields-app"></div>';
	}

	/**
	 * Saves metadata related to our content models.
	 *
	 * @param int     $post_id The post ID.
	 * @param WP_Post $post    The post object being saved.
	 */
	public function save_post( int $post_id, WP_Post $post ): void {
		if ( empty( $_POST['wpe-content-model'] ) || empty( $_POST['wpe-content-model'][ $post->post_type ] ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if (
			! isset( $_POST['wpe-content-model-pubex-nonce'] ) ||
			! wp_verify_nonce(
				sanitize_text_field(
					wp_unslash( $_POST['wpe-content-model-pubex-nonce'] )
				),
				'wpe-content-model-pubex-nonce'
			) ) {
			return;
		}

		// @todo sanitize function for array of varying data types. see: https://github.com/WordPress/WordPress-Coding-Standards/wiki/Sanitizing-array-input-data
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		$posted_values = $_POST['wpe-content-model'][ $post->post_type ];

		// Delete any meta values missing from the submitted data.
		$all_field_slugs = array_values(
			wp_list_pluck(
				$this->models[ $post->post_type ]['fields'],
				'slug'
			)
		);

		foreach ( $all_field_slugs as $slug ) {
			if ( ! array_key_exists( $slug, $posted_values ) ) {
				delete_post_meta( $post_id, sanitize_key( $slug ) );
			}
		}

		// @todo legit data type sanitization. e.g. wp_kses_post is inappropriate for plain text.
		foreach ( $posted_values as $key => $value ) {
			update_post_meta( $post_id, sanitize_text_field( $key ), wp_kses_post( $value ) );
		}
	}
}
