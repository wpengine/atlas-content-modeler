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
	 * Error messages related to saving posts.
	 *
	 * @var string
	 */
	private $error_save_post = '';

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
		$this->models = array_change_key_case( get_registered_content_types(), CASE_LOWER );

		add_filter( 'use_block_editor_for_post_type', [ $this, 'disable_block_editor' ], 10, 2 );
		add_action( 'current_screen', [ $this, 'current_screen' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
		add_action( 'edit_form_after_title', [ $this, 'render_app_container' ] );
		add_action( 'save_post', [ $this, 'save_post' ], 10, 2 );
		add_filter( 'redirect_post_location', [ $this, 'append_error_to_location' ], 10, 2 );
		add_action( 'admin_notices', [ $this, 'display_save_post_errors' ] );
		add_filter( 'the_title', [ $this, 'filter_post_titles' ], 10, 2 );
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
	 *
	 * @param string $hook The current admin page.
	 */
	public function enqueue_assets( string $hook ): void {
		// Bail if this isn't a model created by our plugin.
		if ( ! array_key_exists( $this->current_screen_post_type, $this->models ) ) {
			return;
		}

		// Only load in the post editor.
		if ( 'post.php' !== $hook && 'post-new.php' !== $hook ) {
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

		$models = $this->models;
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

		add_action( 'admin_enqueue_scripts', [ $this, 'load_wp_media_files' ], 10, 2 );
	}

	/**
	 * Load WordPress media files for uploader
	 */
	public function load_wp_media_files() {
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
		echo '<div id="wpe-content-model-fields-app" class="wpe"></div>';
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
			$this->error_save_post = 'You do not have permission to edit this content.';
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
			$this->error_save_post = 'Nonce verification failed when saving your content. Please try again.';
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
				$existing = get_post_meta( $post_id, sanitize_text_field( $slug ), true );
				if ( empty( $existing ) ) {
					continue;
				}

				$deleted = delete_post_meta( $post_id, sanitize_text_field( $slug ) );
				if ( ! $deleted ) {
					$this->error_save_post = sprintf( 'There was an error deleting the %s field data.', $slug );
				}
			}
		}

		// @todo legit data type sanitization. e.g. wp_kses_post is inappropriate for plain text.
		foreach ( $posted_values as $key => $value ) {
			$value = wp_unslash( $value );

			/**
			 * Check if an existing value matches the submitted value
			 * and short-circuit the loop. Otherwise `update_post_meta`
			 * will return `false`, which we use to indicate a failure.
			 */
			$existing = get_post_meta( $post_id, sanitize_text_field( $key ), true );
			if ( $existing === $value ) {
				continue;
			}

			$updated = update_post_meta( $post_id, sanitize_text_field( $key ), wp_kses_post( $value ) );
			if ( ! $updated ) {
				$this->error_save_post = sprintf( 'There was an error updating the %s field data.', $key );
			}
		}
	}

	/**
	 * Adds error messages to the post edit URL
	 * when saving a post fails.
	 *
	 * Runs on the `redirect_post_location` hook.
	 *
	 * @param string $location The destination URL.
	 * @param int    $post_id  The post ID.
	 *
	 * @return string
	 */
	public function append_error_to_location( $location, $post_id ): string {
		$post_type = get_post_type( $post_id );

		// Only show errors for post types managed by our plugin.
		if ( array_key_exists( $post_type, $this->models ) && ! empty( $this->error_save_post ) ) {
			$location = remove_query_arg( 'wpe-content-model-publisher-save-error', $location );
			$location = add_query_arg( 'wpe-content-model-publisher-save-error', $this->error_save_post, $location );
		}

		return $location;
	}

	/**
	 * Displays error messages when saving a post fails.
	 *
	 * Runs on `admin_notices` hook.
	 */
	public function display_save_post_errors(): void {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- False positive. Only used to display a message. Nonce checked earlier.
		if ( ! empty( $_GET['wpe-content-model-publisher-save-error'] ) ) {
			?>
				<div class="error">
					<?php // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- False positive. Only used to display a message. Nonce checked earlier. ?>
					<p><?php echo esc_html( sanitize_text_field( wp_unslash( $_GET['wpe-content-model-publisher-save-error'] ) ) ); ?></p>
				</div>
			<?php
		}
	}

	/**
	 * Filters post titles to use the value of the field set as the entry title.
	 *
	 * Applies to admin pages as well as WPGraphQL and REST responses.
	 *
	 * Uses the post type plus the post ID if there is no field set as the entry
	 * title, or if that field has no stored value.
	 *
	 * @param string $title The original post title.
	 * @param int    $id    Post ID.
	 *
	 * @return string The adjusted post title.
	 */
	public function filter_post_titles( string $title, int $id ) {
		$post_type = get_post_type( $id );

		// Only filter titles for post types created with this plugin.
		if ( ! array_key_exists( $post_type, $this->models ) ) {
			return $title;
		}

		$fields = $this->models[ $post_type ]['fields'] ?? [];

		$title_field = get_entry_title_field( $fields );

		if ( isset( $title_field['slug'] ) ) {
			$title_value = get_post_meta( $id, $title_field['slug'], true );

			if ( ! empty( $title_value ) ) {
				return $title_value;
			}
		}

		// Use a generated title when entry title fields or field data are absent.
		$post_type_singular = $this->models[ $post_type ]['singular_name'] ?? esc_html__( 'No Title', 'wpe-content-model' );
		return $post_type_singular . ' ' . $id;
	}
}
