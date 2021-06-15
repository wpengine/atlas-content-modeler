<?php
/**
 * An experiment with form-based publishing.
 *
 * @package AtlasContentModeler
 */

declare(strict_types=1);

namespace WPE\AtlasContentModeler;

use WP_Post;
use function WPE\AtlasContentModeler\ContentRegistration\get_registered_content_types;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class FormEditingExperience.
 *
 * A convenience class for sharing data across functions that run
 * on different hooks.
 *
 * @package WPE\AtlasContentModeler
 */
final class FormEditingExperience {

	/**
	 * Content models created by this plugin.
	 *
	 * @var array
	 */
	private $models;

	/**
	 * An object representing the current screen.
	 *
	 * @var \WP_Screen
	 */
	private $screen;

	/**
	 * The post type of the post on this screen.
	 *
	 * @var string
	 *
	 * @deprecated To be removed in favor of $this->screen.
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

		add_action( 'init', [ $this, 'remove_post_type_supports' ] );
		add_filter( 'use_block_editor_for_post_type', [ $this, 'disable_block_editor' ], 10, 2 );
		add_action( 'current_screen', [ $this, 'current_screen' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
		add_action( 'edit_form_after_title', [ $this, 'render_app_container' ] );
		add_action( 'save_post', [ $this, 'save_post' ], 10, 2 );
		add_action( 'wp_insert_post', [ $this, 'set_slug' ], 10, 3 );
		add_filter( 'redirect_post_location', [ $this, 'append_error_to_location' ], 10, 2 );
		add_action( 'admin_notices', [ $this, 'display_save_post_errors' ] );
		add_filter( 'the_title', [ $this, 'filter_post_titles' ], 10, 2 );
		add_filter( 'screen_options_show_screen', [ $this, 'hide_screen_options' ], 10, 2 );
		add_action( 'load-post.php', [ $this, 'feedback_notice_handler' ] );
		add_action( 'load-post-new.php', [ $this, 'feedback_notice_handler' ] );
	}

	/**
	 * Removes unneeded post type features.
	 */
	public function remove_post_type_supports(): void {
		foreach ( $this->models as $model => $info ) {
			remove_post_type_support( $model, 'editor' );
			remove_post_type_support( $model, 'title' );
			remove_post_type_support( $model, 'custom-fields' );
			remove_post_type_support( $model, 'thumbnail' );
		}
	}

	/**
	 * Saves the post type of the content being edited.
	 *
	 * @param object $screen The current screen object.
	 */
	public function current_screen( $screen ): void {
		$this->screen = $screen;
		// @todo remove this and refactor code below that references it.
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

		$plugin = get_plugin_data( ATLAS_CONTENT_MODELER_FILE );

		wp_register_script(
			'atlas-content-modeler-form-editing-experience',
			ATLAS_CONTENT_MODELER_URL . 'includes/publisher/dist/index.js',
			[ 'react', 'react-dom', 'wp-tinymce' ],
			$plugin['Version'],
			true
		);

		wp_enqueue_style(
			'styles',
			ATLAS_CONTENT_MODELER_URL . '/includes/publisher/dist/index.css',
			false,
			$plugin['Version'],
			'all'
		);

		wp_enqueue_editor();

		$models = $this->models;
		$model  = $models[ $this->current_screen_post_type ];

		// Add existing field values to models data.
		if ( ! empty( $post ) && ! empty( $model['fields'] ) ) {
			foreach ( $model['fields'] as $key => $field ) {
				if ( isset( $post->ID ) ) {
					$models[ $this->current_screen_post_type ]['fields'][ $key ]['value'] = get_post_meta( $post->ID, $field['slug'], true );
				}
			}
		}

		wp_localize_script(
			'atlas-content-modeler-form-editing-experience',
			'atlasContentModelerFormEditingExperience',
			[
				'models'   => $models,
				'postType' => $this->current_screen_post_type,
			]
		);

		wp_enqueue_media();

		if ( $this->should_show_feedback_banner() ) {
			wp_enqueue_script( 'atlas-content-modeler-feedback-banner' );
		}

		wp_enqueue_script( 'atlas-content-modeler-form-editing-experience' );
	}

	/**
	 * Disables the block editor on post types created by our plugin.
	 *
	 * @param bool   $use_block_editor Whether or not to use the block editor.
	 * @param string $post_type The post type.
	 *
	 * @return bool True if the block editor should be used, false otherwise.
	 */
	public function disable_block_editor( bool $use_block_editor, string $post_type ): bool {
		// Bail if this isn't a model created by our plugin.
		if ( ! array_key_exists( $post_type, $this->models ) ) {
			return $use_block_editor;
		}

		return false;
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

		wp_nonce_field( 'atlas-content-modeler-pubex-nonce', 'atlas-content-modeler-pubex-nonce' );
		echo '<div id="atlas-content-modeler-fields-app" class="wpe atlas-content-modeler"></div>';
	}

	/**
	 * Sets the slug for a newly published post to the ID of that post.
	 *
	 * @param int     $post_ID The currently saving post ID.
	 * @param WP_Post $post    The post object being edited.
	 * @param bool    $update  Whether this is an existing post being updated.
	 * @return void
	 */
	public function set_slug( int $post_ID, WP_Post $post, bool $update ): void {
		if ( true === $update ) {
			// @todo Perhaps check that the slug has not been changed outside of the editor.
			return;
		}

		// Only enforce this slug on created models.
		if ( ! array_key_exists( $post->post_type, $this->models ) ) {
			return;
		}

		// An object to add more useful info to the slug, perhaps post_type ID.
		// @todo Add a filter to change the slug format for default model post slug.
		$model_post_slug = $post_ID;

		wp_update_post(
			array(
				'ID'        => $post_ID,
				'post_name' => $model_post_slug,
			)
		);
	}

	/**
	 * Saves metadata related to our content models.
	 *
	 * @param int     $post_id The post ID.
	 * @param WP_Post $post    The post object being saved.
	 */
	public function save_post( int $post_id, WP_Post $post ): void {
		if ( empty( $_POST['atlas-content-modeler'] ) || empty( $_POST['atlas-content-modeler'][ $post->post_type ] ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			$this->error_save_post = 'You do not have permission to edit this content.';
			return;
		}

		if (
			! isset( $_POST['atlas-content-modeler-pubex-nonce'] ) ||
			! wp_verify_nonce(
				sanitize_text_field(
					wp_unslash( $_POST['atlas-content-modeler-pubex-nonce'] )
				),
				'atlas-content-modeler-pubex-nonce'
			) ) {
			$this->error_save_post = 'Nonce verification failed when saving your content. Please try again.';
			return;
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		$posted_values = $_POST['atlas-content-modeler'][ $post->post_type ];

		// Sanitize field values.
		foreach ( $posted_values as $field_id => &$field_value ) {
			$field_type  = get_field_type_from_slug(
				$field_id,
				$this->models[ $post->post_type ]['fields'] ?? []
			);
			$field_value = sanitize_field( $field_type, wp_unslash( $field_value ) );
		}

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

		foreach ( $posted_values as $key => $value ) {
			/**
			 * Check if an existing value matches the submitted value
			 * and short-circuit the loop. Otherwise `update_post_meta`
			 * will return `false`, which we use to indicate a failure.
			 */
			$existing = get_post_meta( $post_id, sanitize_text_field( $key ), true );
			if ( $existing === $value ) {
				continue;
			}

			$updated = update_post_meta( $post_id, sanitize_text_field( $key ), $value );
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
			$location = remove_query_arg( 'atlas-content-modeler-publisher-save-error', $location );
			$location = add_query_arg( 'atlas-content-modeler-publisher-save-error', $this->error_save_post, $location );
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
		if ( ! empty( $_GET['atlas-content-modeler-publisher-save-error'] ) ) {
			?>
				<div class="error">
					<?php // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- False positive. Only used to display a message. Nonce checked earlier. ?>
					<p><?php echo esc_html( sanitize_text_field( wp_unslash( $_GET['atlas-content-modeler-publisher-save-error'] ) ) ); ?></p>
				</div>
			<?php
		}
	}

	/**
	 * Sets up the feedback admin notice if necessary
	 *
	 * @return void
	 */
	public function feedback_notice_handler(): void {
		if ( $this->should_show_feedback_banner() ) {
			add_action( 'admin_notices', [ $this, 'render_feedback_notice' ] );
		}
	}

	/**
	 * Determines whether the feedback form should be shown.
	 *
	 * @returns bool
	 */
	public function should_show_feedback_banner(): bool {
		if ( ! array_key_exists( $this->screen->post_type, $this->models ) || 'edit' === $this->screen->base || ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		$time_dismissed = get_user_meta( get_current_user_id(), 'acm_hide_feedback_banner', true );

		// Check for time elapsed and presence of the meta data.
		return ! ( ! empty( $time_dismissed ) && ( $time_dismissed + WEEK_IN_SECONDS * 2 > time() ) );
	}

	/**
	 * Displays notice for getting user feedback.
	 *
	 * Runs an `admin_notices` hook.
	 */
	public function render_feedback_notice(): void {
		include_once ATLAS_CONTENT_MODELER_DIR . '/includes/shared-assets/views/banners/atlas-content-modeler-feedback-banner.php';
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
		$post_type_singular = $this->models[ $post_type ]['singular_name'] ?? esc_html__( 'No Title', 'atlas-content-modeler' );
		return $post_type_singular . ' ' . $id;
	}

	/**
	 * Hides the “Screen Options” drop-down on post types registered by this plugin.
	 *
	 * @param bool       $show_screen The current state of the screen options dropdown.
	 * @param \WP_Screen $screen Information about the current screen.
	 *
	 * @return bool The new state of the screen options dropdown. (False to disable.)
	 */
	public function hide_screen_options( bool $show_screen, $screen ): bool {
		if ( in_array( $screen->post_type, array_keys( $this->models ), true ) ) {
			return false;
		}

		return $show_screen;
	}
}
