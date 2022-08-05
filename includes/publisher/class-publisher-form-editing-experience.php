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
use function WPE\AtlasContentModeler\ContentConnect\Helpers\get_related_ids_by_name;
use function WPE\AtlasContentModeler\append_reverse_relationship_fields;
use WPE\AtlasContentModeler\ContentConnect\Plugin as ContentConnect;


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
		add_action( 'save_post', [ $this, 'save_post' ], 10, 3 );
		add_action( 'wp_insert_post', [ $this, 'set_post_attributes' ], 10, 3 );
		add_filter( 'redirect_post_location', [ $this, 'append_error_to_location' ], 10, 2 );
		add_action( 'admin_notices', [ $this, 'display_save_post_errors' ] );
		add_filter( 'the_title', [ $this, 'filter_post_titles' ], 10, 2 );
		add_action( 'load-post.php', [ $this, 'feedback_notice_handler' ] );
		add_action( 'load-post-new.php', [ $this, 'feedback_notice_handler' ] );
		add_action( 'do_meta_boxes', [ $this, 'move_meta_boxes' ] );
		add_action( 'do_meta_boxes', [ $this, 'remove_thumbnail_meta_box' ] );
		add_action( 'transition_post_status', [ $this, 'maybe_add_location_callback' ], 10, 3 );
		add_action( 'updated_postmeta', [ $this, 'sync_title_field_to_posts_table' ], 10, 4 );
		add_action( 'added_post_meta', [ $this, 'sync_title_field_to_posts_table' ], 10, 4 );
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
		if ( ! empty( $post ) && ! empty( $model['fields'] ) ) {
			foreach ( $model['fields'] as $key => $field ) {
				if ( isset( $post->ID ) ) {
					if ( 'relationship' === $field['type'] ) {
						$models[ $this->screen->post_type ]['fields'][ $key ]['value'] = $this->get_relationship_field( $post, $field );
					} else {
						$value = get_post_meta( $post->ID, $field['slug'], true );
						if ( ! empty( $field['isTitle'] ) && $value !== $post->post_title ) {
							$post->post_title = $value;
							$this->update_post( $post );
						}
						$models[ $this->screen->post_type ]['fields'][ $key ]['value'] = $value;
					}
				}
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
	 * Sets the slug for a newly published post to the ID of that post.
	 *
	 * @param int     $post_ID The currently saving post ID.
	 * @param WP_Post $post    The post object being edited.
	 * @param bool    $update  Whether this is an existing post being updated.
	 * @return void
	 */
	public function set_post_attributes( int $post_ID, WP_Post $post, bool $update ): void {
		if ( ! array_key_exists( $post->post_type, $this->models ) ) {
			return;
		}

		if ( $post->post_status !== 'auto-draft' ) {
			return;
		}

		$post->post_title = 'entry' . $post_ID;
		$post->post_name  = $post_ID;
		$this->update_post( $post );
	}

	/**
	 * Saves metadata related to our content models.
	 *
	 * @param int     $post_id The post ID.
	 * @param WP_Post $post    The post object being saved.
	 * @param bool    $update  Whether this is an existing post being updated.
	 */
	public function save_post( int $post_id, WP_Post $post, bool $update = false ): void {
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

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash -- sanitized below before use.
		$posted_values = $_POST['atlas-content-modeler'][ $post->post_type ];

		$saved_relationships = array();

		// Sanitize field values.
		foreach ( $posted_values as $field_id => &$field_value ) {
			$field_id    = sanitize_text_field( wp_unslash( $field_id ) ); // retains camelCase.
			$field_type  = get_field_type_from_slug( $field_id, $this->models, $post->post_type );
			$field_value = sanitize_field( $field_type, wp_unslash( $field_value ) );

			if ( 'relationship' === $field_type ) {
				unset( $posted_values[ $field_id ] );
				$this->save_relationship_field( $field_id, $post, $field_value );
				$saved_relationships[] = $field_id;
			}
		}

		// Delete any meta values missing from the submitted data.
		$all_field_slugs = array_values(
			wp_list_pluck(
				$this->models[ $post->post_type ]['fields'],
				'slug'
			)
		);

		$unique_emails_to_skip_saving = [];
		foreach ( $all_field_slugs as $slug ) {
			$field_type = get_field_type_from_slug(
				$slug,
				$this->models,
				$post->post_type
			);

			if ( ! array_key_exists( $slug, $posted_values ) ) {
				if ( 'relationship' === $field_type ) {
					if ( ! in_array(
						$slug,
						$saved_relationships,
						true
					) ) {
						$this->save_relationship_field( $slug, $post, '' );
					}
				} else {
					$existing = get_post_meta( $post_id, sanitize_text_field( $slug ), true );
					// Clean up empty values already saved in db.
					if ( $existing === '' || $existing === [] ) {
						$deleted = delete_post_meta( $post_id, sanitize_text_field( $slug ) );
						if ( ! $deleted ) {
							/* translators: %s: atlas content modeler field slug */
							$this->error_save_post = sprintf( __( 'There was an error deleting the %s field data.', 'atlas-content-modeler' ), $slug );
						}
						continue;
					}

					if ( empty( $existing ) ) {
						continue;
					}

					$deleted = delete_post_meta( $post_id, sanitize_text_field( $slug ) );
					if ( ! $deleted ) {
						/* translators: %s: atlas content modeler field slug */
						$this->error_save_post = sprintf( __( 'There was an error deleting the %s field data.', 'atlas-content-modeler' ), $slug );
					}
				}
			}

			$is_field_unique = get_field_from_slug( $slug, $this->models, $post->post_type )['isUnique'] ?? false;

			if ( 'email' === $field_type && $is_field_unique ) {
				global $wpdb;

				$email_value = $posted_values[ $slug ];

				// Only validate uniqueness of non-repeating email fields for now.
				if ( ! is_array( $email_value ) ) {
					// phpcs:disable
					// A direct database call is the quickest way to query
					// for unique emails.
					$identical_emails_query = $wpdb->prepare(
						"SELECT COUNT(*)
						FROM `{$wpdb->postmeta}`
						INNER JOIN {$wpdb->posts} ON {$wpdb->postmeta}.post_id = {$wpdb->posts}.ID
						WHERE {$wpdb->posts}.post_type = %s
						AND post_id != %s
						AND meta_key = %s
						AND meta_value = %s;",
						$post->post_type,
						$post_id,
						$slug,
						$email_value
					);

					$identical_emails = (int) $wpdb->get_var( $identical_emails_query );
					// phpcs:enable

					if ( $identical_emails > 0 ) {
						array_push( $unique_emails_to_skip_saving, $slug );
						$this->error_save_post = sprintf(
							// translators: 1: field name 2: submitted email address value.
							__( 'The email field %1$s must be unique and was not saved. Another entry uses %2$s.', 'atlas-content-modeler' ),
							$slug,
							$email_value
						);
					}
				}
			}

			// Media field.
			if ( 'media' === $field_type &&
				is_field_featured_image(
					$slug,
					$this->models[ $post->post_type ]['fields'] ?? []
				) &&
				isset( $posted_values[ $slug ] )
			) {
				// featured image.
				if ( has_post_thumbnail( $post ) ) {
					if ( '' === $posted_values[ $slug ] ) {
						if ( ! delete_post_thumbnail( $post ) ) {
							/* translators: %s: atlas content modeler field slug */
							$this->error_save_post = sprintf( __( 'There was an error updating the %s field data.', 'atlas-content-modeler' ), $posted_values[ $slug ] );
						}
					} else {
						delete_post_thumbnail( $post ); // Delete first is innefficient but avoids weird behavior of set which otherwise treats an existing thumbnail as a bug.
						if ( ! set_post_thumbnail( $post, $posted_values[ $slug ] ) ) {
							/* translators: %s: atlas content modeler field slug */
							$this->error_save_post = sprintf( __( 'There was an error updating the %s field data.', 'atlas-content-modeler' ), $posted_values[ $slug ] );
						}
					}
				} else {
					if ( $posted_values[ $slug ] && ! set_post_thumbnail( $post, $posted_values[ $slug ] ) ) {
						/* translators: %s: atlas content modeler field slug */
						$this->error_save_post = sprintf( __( 'There was an error updating the %s field data.', 'atlas-content-modeler' ), $posted_values[ $slug ] );
					}
				}
			}
		}

		foreach ( $posted_values as $key => $value ) {
			$key = sanitize_text_field( $key );

			// Delete or ignore empty values, but allow 0 values.
			if ( empty( $value ) && $value !== '0' && $value !== 0 ) {
				if ( $update ) {
					delete_post_meta( $post_id, $key );
				}
				continue;
			}

			/**
			 * Check if an existing value matches the submitted value
			 * and short-circuit the loop. Otherwise `update_post_meta`
			 * will return `false`, which we use to indicate a failure.
			 */
			$existing = get_post_meta( $post_id, $key, true );
			if ( $existing === $value ) {
				continue;
			}

			if ( in_array( $key, $unique_emails_to_skip_saving, true ) ) {
				continue;
			}
			$updated = update_post_meta( $post_id, $key, $value );
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
	public function save_relationship_field( string $field_id, WP_Post $post, string $field_value ): void {
		$field = get_field_from_slug(
			$field_id,
			$this->models,
			$post->post_type
		);

		$registry      = ContentConnect::instance()->get_registry();
		$relationship  = $registry->get_post_to_post_relationship( $post->post_type, $field['reference'], $field['id'] );
		$related_posts = array();

		if ( $relationship ) {
			if ( '' !== $field_value ) {
				$related_posts = explode( ',', $field_value );
			}

			$relationship->replace_relationships( $post->ID, $related_posts );
		}
	}

	/**
	 * Retrieves the related post ids
	 *
	 * @param WP_Post $post The parent post.
	 * @param array   $field The relationship field.
	 *
	 * @return string A comma separated list of connected posts
	 */
	public function get_relationship_field( WP_Post $post, array $field ): string {
		$registry     = ContentConnect::instance()->get_registry();
		$relationship = $registry->get_post_to_post_relationship(
			$post->post_type,
			$field['reference'],
			$field['id']
		);

		if ( false === $relationship ) {
			return '';
		}

		$relationship_ids = $relationship->get_related_object_ids( $post->ID );

		return implode( ',', $relationship_ids );
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
		$post = get_post( $id );
		if ( ! $post instanceof \WP_Post ) {
			return $title;
		}

		// Only filter titles for post types created with this plugin.
		if ( ! array_key_exists( $post->post_type, $this->models ) ) {
			return $title;
		}

		$fields = $this->models[ $post->post_type ]['fields'] ?? [];

		$title_field = get_entry_title_field( $fields );

		if ( isset( $title_field['slug'] ) ) {
			$title_value = get_post_meta( $id, $title_field['slug'], true );

			if ( ! empty( $title_value ) ) {
				if ( $post->post_title !== $title_value ) {
					$post->post_title = $title_value;
					$this->update_post( $post );
				}
				return $title_value;
			}
		}

		// Use a generated title when entry title fields or field data are absent.
		$post_type_singular = $this->models[ $post->post_type ]['singular_name'] ?? esc_html__( 'No Title', 'atlas-content-modeler' );
		return $post_type_singular . ' ' . $id;
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
	 * Syncs title field data to the wp_posts table.
	 *
	 * @param int    $meta_id ID of updated metadata entry.
	 * @param int    $object_id Post ID.
	 * @param string $meta_key Metadata key.
	 * @param mixed  $meta_value Metadata value.
	 * @return void
	 */
	public function sync_title_field_to_posts_table( $meta_id, $object_id, $meta_key, $meta_value ): void {
		$post = get_post( $object_id );
		if ( ! $post instanceof \WP_Post ) {
			return;
		}
		$models = get_registered_content_types();
		if ( ! array_key_exists( $post->post_type, $models ) ) {
			return;
		}

		$title_field = get_entry_title_field( $models[ $post->post_type ]['fields'] ?? [] );
		if ( empty( $title_field['slug'] ) ) {
			return;
		}

		if ( $title_field['slug'] !== $meta_key ) {
			return;
		}

		if ( $post->post_title === $meta_value ) {
			return;
		}

		$post->post_title = $meta_value;
		if ( empty( $post->post_name ) || (int) $post->post_name === $post->ID ) {
			$post->post_name = wp_unique_post_slug( sanitize_title( $meta_value, 'save' ), $post->ID, $post->post_status, $post->post_type, $post->post_parent );
		}
		$this->update_post( $post );
	}

	/**
	 * Updates the post with the provided data.
	 *
	 * Removes ACM callbacks attached to `wp_insert_post`
	 * to prevent them from running again when we update the post.
	 *
	 * @param \WP_Post $post The post data to be saved.
	 *
	 * @return void
	 */
	private function update_post( $post ): void {
		remove_action( 'wp_insert_post', [ $this, 'set_post_attributes' ] );
		wp_update_post( $post, false, false );
		add_action( 'wp_insert_post', [ $this, 'set_post_attributes' ], 10, 3 );
	}
}
