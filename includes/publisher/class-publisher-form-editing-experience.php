<?php
/**
 * An experiment with form-based publishing.
 *
 * @package AtlasContentModeler
 */

declare(strict_types=1);

namespace WPE\AtlasContentModeler;

use WP_Error;
use WP_Post;
use function WPE\AtlasContentModeler\ContentRegistration\get_registered_content_types;
use function WPE\AtlasContentModeler\ContentConnect\Helpers\get_related_ids_by_name;
use WPE\AtlasContentModeler\ContentConnect\Plugin as ContentConnect;
use function WPE\AtlasContentModeler\get_field_value;
use function WPE\AtlasContentModeler\save_field_value;
use function WPE\AtlasContentModeler\delete_field_value;

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
		$this->models = get_registered_content_types();

		add_action( 'init', [ $this, 'remove_post_type_supports' ] );
		add_action( 'rest_api_init', [ $this, 'support_title_in_api_responses' ] );
		add_action( 'init_graphql_request', [ $this, 'support_title_in_api_responses' ] );
		add_action( 'rest_api_init', [ $this, 'add_related_posts_to_rest_responses' ] );
		add_filter( 'use_block_editor_for_post_type', [ $this, 'disable_block_editor' ], 10, 2 );
		add_action( 'current_screen', [ $this, 'current_screen' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
		add_action( 'edit_form_after_title', [ $this, 'render_app_container' ] );
		add_action( 'save_post', [ $this, 'save_post' ], 10, 2 );
		add_action( 'wp_insert_post', [ $this, 'set_post_attributes' ], 10, 3 );
		add_filter( 'redirect_post_location', [ $this, 'append_error_to_location' ], 10, 2 );
		add_action( 'admin_notices', [ $this, 'display_save_post_errors' ] );
		add_action( 'load-post.php', [ $this, 'feedback_notice_handler' ] );
		add_action( 'load-post-new.php', [ $this, 'feedback_notice_handler' ] );
		add_action( 'do_meta_boxes', [ $this, 'move_meta_boxes' ] );
		add_action( 'do_meta_boxes', [ $this, 'remove_thumbnail_meta_box' ] );
		add_action( 'transition_post_status', [ $this, 'maybe_add_location_callback' ], 10, 3 );
		add_action( 'the_post', [ $this, 'migrate_post_title' ], 10, 2 );
	}

	/**
	 * Removes unneeded post type features.
	 */
	public function remove_post_type_supports(): void {
		foreach ( $this->models as $model => $config ) {
			remove_post_type_support( $model, 'editor' );
			remove_post_type_support( $model, 'title' );
			remove_post_type_support( $model, 'custom-fields' );
			$remove_thumbnail = true;
			if ( isset( $config['fields'] ) ) {
				foreach ( $config['fields'] as $field ) {
					if ( 'media' === $field['type'] && isset( $field['isFeatured'] ) && true === $field['isFeatured'] ) {
						$remove_thumbnail = false;
						break;
					}
				}
				if ( $remove_thumbnail ) {
					remove_post_type_support( $model, 'thumbnail' );
				}
			}
		}
	}

	/**
	 * Reinstates the title so that it appears in API responses.
	 */
	public function support_title_in_api_responses(): void {
		foreach ( $this->models as $model => $info ) {
			add_post_type_support( $model, 'title' );
		}
	}

	/**
	 * Adds related posts to each ACM post in REST response data.
	 *
	 * Related posts are linked to other posts via an ACM relationship field.
	 *
	 * A REST GET request for /wp-json/wp/v2/cats?per_page=5&page=1&field_id=123
	 * will return a list of cats. Each cat gains an `acm_related_posts`
	 * property with an array of entry IDs that the cat is linked to via a
	 * relationship field with the passed field_id of "123".
	 *
	 * The `acm_related_posts` property is used to prevent publishers from
	 * picking posts in the relationship modal that would violate one-to-one or
	 * one-to-many cardinality rules.
	 *
	 * @since 0.9.0
	 * @return void
	 */
	public function add_related_posts_to_rest_responses(): void {
		foreach ( $this->models as $model => $info ) {
			register_rest_field(
				$model,
				'acm_related_posts',
				array(
					'get_callback' => function( $post, $attr, $request ) {
						$params   = $request->get_query_params();
						$field_id = $params['field_id'] ?? 0;

						return get_related_ids_by_name( $post['id'], $field_id );
					},
				)
			);
		}
	}

	/**
	 * Saves the post type of the content being edited.
	 *
	 * @param object $screen The current screen object.
	 */
	public function current_screen( $screen ): void {
		$this->screen = $screen;
	}

	/**
	 * Enqueues scripts and styles related to our app.
	 *
	 * @param string $hook The current admin page.
	 */
	public function enqueue_assets( string $hook ): void {
		// Bail if this isn't a model created by our plugin.
		if ( ! array_key_exists( $this->screen->post_type, $this->models ) ) {
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
			[ 'react', 'react-dom', 'wp-tinymce', 'wp-i18n', 'wp-api-fetch', 'wp-date' ],
			$plugin['Version'],
			true
		);

		wp_set_script_translations( 'atlas-content-modeler-form-editing-experience', 'atlas-content-modeler' );

		wp_enqueue_style(
			'styles',
			ATLAS_CONTENT_MODELER_URL . '/includes/publisher/dist/index.css',
			false,
			$plugin['Version'],
			'all'
		);

		wp_enqueue_editor();

		$models = append_reverse_relationship_fields( $this->models, $this->screen->post_type );

		// Adds the wp-json rest base for utilizing model data in admin.
		foreach ( $models as $model => $data ) {
			$models[ $model ]['wp_rest_base'] = sanitize_key( $data['plural'] );
		}

		$model = $models[ $this->screen->post_type ];

		// Add existing field values to models data.
		if ( ! empty( $post->ID ) && ! empty( $model['fields'] ) ) {
			foreach ( $model['fields'] as $key => $field ) {
				$models[ $this->screen->post_type ]['fields'][ $key ]['value'] = get_field_value( $field, $post );
			}
		}

		wp_localize_script(
			'atlas-content-modeler-form-editing-experience',
			'atlasContentModelerFormEditingExperience',
			[
				'models'               => $models,
				'postType'             => $this->screen->post_type,
				'allowedMimeTypes'     => get_allowed_mime_types(),
				'adminUrl'             => admin_url(),
				'postHasReferences'    => isset( $post->ID ) ? $this->has_relationship_references( (string) $post->ID ) : false,
				'usageTrackingEnabled' => acm_usage_tracking_enabled(),
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

		wp_nonce_field( 'atlas-content-modeler-pubex-nonce', 'atlas-content-modeler-pubex-nonce' );
		echo '<div id="atlas-content-modeler-fields-app" class="wpe atlas-content-modeler"></div>';
	}

	/**
	 * Sets the post_name and post_title values.
	 *
	 * @param int     $post_id The currently saving post ID.
	 * @param WP_Post $post    The post object being edited.
	 * @param bool    $update  Whether this is an existing post being updated.
	 * @return void
	 */
	public function set_post_attributes( int $post_id, WP_Post $post, bool $update ): void {
		if ( $update ) {
			return;
		}

		if ( ! array_key_exists( $post->post_type, $this->models ) ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			$this->error_save_post = __( 'You do not have permission to edit this content.', 'atlas-content-modeler' );
			return;
		}

		wp_update_post(
			[
				'ID'         => $post_id,
				'post_name'  => $post_id,
				'post_title' => 'entry' . $post_id,
			]
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
			$this->error_save_post = __( 'You do not have permission to edit this content.', 'atlas-content-modeler' );
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
			$this->error_save_post = __( 'Nonce verification failed when saving your content. Please try again.', 'atlas-content-modeler' );
			return;
		}

		// Avoid infinite loop.
		remove_filter( 'save_post', [ $this, 'save_post' ] );

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- sanitized below before use.
		$posted_values = $_POST['atlas-content-modeler'][ $post->post_type ];

		// Sanitize field values.
		foreach ( $posted_values as $field_id => &$field_value ) {
			$field       = get_field_from_slug( $field_id, $this->models, $post->post_type );
			$field_id    = sanitize_text_field( wp_unslash( $field_id ) ); // retains camelCase.
			$field_type  = get_field_type_from_slug( $field_id, $this->models, $post->post_type );
			$field_value = sanitize_field( $field_type, wp_unslash( $field_value ) );
		}

		// Delete any values missing from the submitted data.
		$all_field_slugs = array_values(
			wp_list_pluck(
				$this->models[ $post->post_type ]['fields'],
				'slug'
			)
		);

		foreach ( $all_field_slugs as $slug ) {
			if ( ! array_key_exists( $slug, $posted_values ) ) {
				$field   = get_field_from_slug( $slug, $this->models, $post->post_type );
				$deleted = delete_field_value( $field, $post );
				if ( ! $deleted ) {
					/* translators: %s: atlas content modeler field slug */
					$this->error_save_post = sprintf( __( 'There was an error deleting the %s field data.', 'atlas-content-modeler' ), $slug );
				}
			}
		}

		foreach ( $posted_values as $key => $value ) {
			$key   = sanitize_text_field( $key );
			$field = get_field_from_slug( $key, $this->models, $post->post_type );
			/**
			 * Check if an existing value matches the submitted value
			 * and short-circuit the loop. Otherwise `update_post_meta`
			 * will return `false`, which we use to indicate a failure.
			 */
			if ( $value === get_field_value( $field, $post ) ) {
				continue;
			}

			$updated = save_field_value( $field, $value, $post );
			if ( ! $updated ) {
				/* translators: %s: atlas content modeler field slug */
				$this->error_save_post = sprintf( __( 'There was an error updating the %s field data.', 'atlas-content-modeler' ), $key );
			}
		}
	}

	/**
	 * Saves relationship field data using the post-to-posts library
	 *
	 * @param string  $field_id The name of the field being saved.
	 * @param WP_Post $post The post being saved.
	 * @param string  $field_value The post IDs of the relationship's destination posts.
	 */
	public function save_relationship_field( string $field_id, WP_Post $post, string $field_value ): bool {
		$field = get_field_from_slug(
			$field_id,
			$this->models,
			$post->post_type
		);

		$registry      = ContentConnect::instance()->get_registry();
		$relationship  = $registry->get_post_to_post_relationship( $post->post_type, $field['reference'], $field['id'] );
		$related_posts = array();

		if ( ! $relationship ) {
			return false;
		}

		if ( ! empty( $field_value ) ) {
			$related_posts = explode( ',', $field_value );
		}

		return $relationship->replace_relationships( $post->ID, $related_posts );
	}

	/**
	 * Tests if `$post_id` is referenced by any model in the post-to-post table.
	 * Used to determine if warnings should be shown before entries are trashed.
	 *
	 * @param string $post_id The post ID.
	 * @return bool True if the post is referenced in a relationship field.
	 */
	public function has_relationship_references( string $post_id ): bool {
		global $wpdb;

		$table        = ContentConnect::instance()->get_table( 'p2p' );
		$post_to_post = $table->get_table_name();

		// phpcs:disable
		// The `$post_to_post` table does not need to be escaped.
		// It is derived from an unfilterable string literal.
		$relationship_count = $wpdb->prepare(
			"SELECT COUNT(*)
			FROM `{$post_to_post}`
			WHERE id1 = %s;
			",
			$post_id
		);

		return (int) $wpdb->get_var( $relationship_count ) > 0;
		// phpcs:enable
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
		include_once ATLAS_CONTENT_MODELER_INCLUDES_DIR . 'shared-assets/views/banners/atlas-content-modeler-feedback-banner.php';
	}

	/**
	 * Removes the featured image meta box.
	 *
	 * Adding featured image support to the media field creates a confusing
	 * UX whereas WordPress wants to add its own meta box to the sidebar
	 * for the functionality. This removes that metabox.
	 */
	public function remove_thumbnail_meta_box(): void {
		// Only remove for for post types created by this plugin.
		if ( ! array_key_exists( $this->screen->post_type, $this->models ) ) {
			return;
		}

		remove_meta_box( 'postimagediv', null, 'side' );
	}

	/**
	 * Moves the meta boxes to the sidebar.
	 *
	 * Improves usability by moving the meta box away from the main editor area
	 * so that it does not appear there before the publisher React application.
	 *
	 * Users can still override meta box position. Their preference is stored
	 * in the user meta table under a key named `meta-box-order_[post-slug]`.
	 */
	public function move_meta_boxes(): void {
		// Only change placement for post types created by this plugin.
		if ( ! array_key_exists( $this->screen->post_type, $this->models ) ) {
			return;
		}

		remove_meta_box( 'authordiv', null, 'normal' );
		remove_meta_box( 'slugdiv', null, 'normal' );

		add_meta_box(
			'authordiv',
			__( 'Author' ), // phpcs:ignore -- use translation from WordPress Core.
			'post_author_meta_box',
			null,
			'side', // Move to the sidebar.
			'default',
			array( '__back_compat_meta_box' => true )
		);

		add_meta_box(
			'slugdiv',
			__( 'Slug' ), // phpcs:ignore -- use translation from WordPress Core.
			'post_slug_meta_box',
			null,
			'side', // Move to the sidebar.
			'default',
			array( '__back_compat_meta_box' => true )
		);
	}

	/**
	 * Adds a callback to the `redirect_post_location` filter
	 * when a post transitions to the 'publish' status.
	 *
	 * @param string   $new_status New post status.
	 * @param string   $old_status Old post status.
	 * @param \WP_Post $post       Post object.
	 * @return void
	 */
	public function maybe_add_location_callback( string $new_status, string $old_status, \WP_Post $post ): void {
		if ( ! acm_usage_tracking_enabled() ) {
			return;
		}

		if ( ! array_key_exists( $post->post_type, get_registered_content_types() ) ) {
			return;
		}

		if ( $old_status !== 'publish' && $new_status === 'publish' ) {
			add_filter( 'redirect_post_location', [ $this, 'add_published_query_arg_to_location' ] );
		}
	}

	/**
	 * Adds a query arg to the post edit URL when a
	 * post is saved, which is used to send usage
	 * tracking events when enabled.
	 *
	 * Runs on the `redirect_post_location` hook.
	 *
	 * @param string $location The destination URL.
	 *
	 * @return string
	 */
	public function add_published_query_arg_to_location( string $location ): string {
		remove_filter( 'redirect_post_location', __NAMESPACE__ . '\add_published_query_arg_to_location' );
		if ( ! acm_usage_tracking_enabled() ) {
			return $location;
		}
		$location = remove_query_arg( 'acm-post-published', $location );
		$location = add_query_arg( 'acm-post-published', 'true', $location );
		return $location;
	}

	/**
	 * Migrates legacy post title data from the
	 * postmeta table to the posts table.
	 *
	 * @param \WP_Post  $post The post object.
	 * @param \WP_Query $query The query object.
	 * @return void
	 */
	public function migrate_post_title( \WP_Post $post, \WP_Query $query ): void {
		if ( ! array_key_exists( $post->post_type, $this->models ) ) {
			return;
		}

		if ( empty( $this->models[ $post->post_type ]['fields'] ) ) {
			return;
		}

		foreach ( $this->models[ $post->post_type ]['fields'] as $field ) {
			if ( $field['type'] === 'text' && ! empty( $field['isTitle'] ) ) {
				get_field_value( $field, $post ); // triggers migration.
			}
		}
	}
}
