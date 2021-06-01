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
		add_action( 'admin_notices', [ $this, 'render_feedback_notice' ] );
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

		// TODO: remove when final icon is chosen for feedback.
		wp_register_style(
			'material-icons',
			'https://fonts.googleapis.com/css?family=Material+Icons|Material+Icons+Outlined',
			[],
			$plugin['Version']
		);

		wp_enqueue_style(
			'styles',
			WPE_CONTENT_MODEL_URL . '/includes/publisher/dist/index.css',
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

		wp_enqueue_media();
		wp_enqueue_style( 'material-icons' );
		wp_enqueue_script( 'wpe-content-model-form-editing-experience' );
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

		wp_nonce_field( 'wpe-content-model-pubex-nonce', 'wpe-content-model-pubex-nonce' );
		echo '<div id="wpe-content-model-fields-app" class="wpe"></div>';
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

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized, WordPress.Security.ValidatedSanitizedInput.MissingUnslash
		$posted_values = $_POST['wpe-content-model'][ $post->post_type ];

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
	 * Displays notice for getting user feedback.
	 *
	 * Runs an `admin_notices` hook.
	 */
	public function render_feedback_notice(): void {
		$post_type = get_post_type();
		// Only enforce this slug on created models.
		if ( ! array_key_exists( $post_type, $this->models ) ) {
			return;
		}

		if ( current_user_can( 'manage_options' ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- False positive. Only used to display a message. Nonce checked earlier.
			?>
			<div style="background-color: #F2EFFD;" class="wpe-content-model notice notice-info is-dismissible">
				<div class="d-flex d-sm-flex flex-sm-row flex-column p-4">
					<div class="align-self-center">
						<svg width="76" height="80" viewBox="0 0 76 80" fill="none" xmlns="http://www.w3.org/2000/svg">
							<g clip-path="url(#clip0)">
								<path d="M37.2916 70.8948C53.9177 70.8948 67.3959 57.5169 67.3959 41.0143C67.3959 24.5118 53.9177 11.1339 37.2916 11.1339C20.6654 11.1339 7.18729 24.5118 7.18729 41.0143C7.18729 57.5169 20.6654 70.8948 37.2916 70.8948Z" fill="white"/>
							</g>
							<g clip-path="url(#clip1)">
								<path d="M42.8995 59.9972C57.0419 59.9972 68.5065 48.6178 68.5065 34.5805C68.5065 20.5433 57.0419 9.16385 42.8995 9.16385C28.7571 9.16385 17.2924 20.5433 17.2924 34.5805C17.2924 48.6178 28.7571 59.9972 42.8995 59.9972Z" fill="white"/>
							</g>
							<path d="M18.6568 44.5256V44.5357C18.6814 44.6333 18.708 44.7391 18.7387 44.8285L18.7633 44.9078C18.7879 44.9831 18.8125 45.0583 18.8391 45.1356C18.8657 45.2128 18.917 45.3389 18.96 45.4507C19.463 46.6723 20.3035 47.728 21.3846 48.4961C22.4658 49.2641 23.7436 49.7133 25.0708 49.7919C25.4156 49.8125 25.7614 49.8078 26.1054 49.7777C26.3376 49.7584 26.5688 49.7271 26.7978 49.6841C27.2671 49.5942 27.7262 49.458 28.1683 49.2775L36.1577 46.0241L43.6206 47.6976L45.2594 48.0636L53.55 49.9241L54.8447 50.2128L41.1787 17.0695L34.3672 27.5818L33.2508 29.3061L30.813 33.0677L22.8236 36.3109C21.2967 36.9311 20.0369 38.0623 19.2627 39.5083C18.4884 40.9542 18.2487 42.6236 18.5851 44.2267C18.6117 44.3202 18.6302 44.43 18.6568 44.5256Z" fill="#C3F2F4"/>
							<path d="M45.2554 44.0091L43.6165 43.6431L37.7904 42.3377C36.7265 42.0993 35.6146 42.1909 34.6049 42.6L28.1724 45.2148C27.7303 45.3954 27.2712 45.5316 26.8019 45.6215C26.5729 45.6645 26.3417 45.6957 26.1095 45.715C25.7655 45.7452 25.4197 45.7499 25.0749 45.7293C23.7468 45.65 22.4684 45.1996 21.3871 44.43C20.3058 43.6605 19.4658 42.6031 18.9641 41.38C18.9211 41.2742 18.8801 41.1766 18.8432 41.0648C18.8063 40.953 18.792 40.9123 18.7674 40.8371L18.7428 40.7618C18.4001 41.8829 18.3459 43.0713 18.5851 44.2185C18.6076 44.3222 18.6302 44.4218 18.6568 44.5276V44.5377C18.6814 44.6353 18.708 44.7411 18.7387 44.8305L18.7633 44.9098C18.7879 44.9851 18.8125 45.0603 18.8391 45.1376C18.8657 45.2148 18.917 45.3409 18.96 45.4527C19.463 46.6743 20.3035 47.73 21.3847 48.4981C22.4658 49.2661 23.7436 49.7154 25.0709 49.7939C25.4156 49.8146 25.7614 49.8098 26.1054 49.7797C26.3377 49.7604 26.5688 49.7292 26.7978 49.6862C27.2671 49.5963 27.7262 49.46 28.1683 49.2795L36.1577 46.0261L43.6206 47.6996L45.2595 48.0656L53.55 49.9261L54.8447 50.2148L53.001 45.7415L45.2554 44.0091Z" fill="#87E4E9"/>
							<path fill-rule="evenodd" clip-rule="evenodd" d="M55.4408 50.7659L45.1283 48.4519L45.3086 47.6589L54.0867 49.6272L41.0722 18.0658L34.7216 27.8665L34.0312 27.4272L41.236 16.313L55.4408 50.7659ZM20.8046 48.5087C22.1411 49.5969 23.8175 50.1893 25.5461 50.1843C25.6795 50.1842 25.8127 50.1804 25.9456 50.1732L35.201 60.9386C35.575 61.3737 36.0399 61.7229 36.5634 61.962C37.087 62.2011 37.6566 62.3244 38.2328 62.3233C38.9943 62.3225 39.7395 62.1049 40.3801 61.6964C41.0208 61.2879 41.5299 60.7055 41.8471 60.0184C42.1643 59.3313 42.2763 58.5684 42.1698 57.82C42.0633 57.0717 41.7428 56.3693 41.2463 55.7963L34.4266 47.8663L33.812 48.395L40.6317 56.325C41.1766 56.9584 41.4458 57.7807 41.3799 58.611C41.314 59.4413 40.9185 60.2117 40.2804 60.7526C39.6423 61.2935 38.8138 61.5606 37.9772 61.4952C37.1407 61.4298 36.3646 61.0372 35.8196 60.4039L26.9151 50.0568C27.4105 49.9643 27.8963 49.8219 28.3649 49.6313L36.1986 46.4491L43.5038 48.088L43.6841 47.295L36.1269 45.5992L30.5985 47.8456L25.5837 35.6791L31.1079 33.4358L33.6092 29.5867L32.9189 29.1332L30.563 32.7709L22.7293 35.951C21.1273 36.5954 19.8012 37.7732 18.9787 39.2823C18.1562 40.7913 17.8885 42.5375 18.2217 44.2211C18.5549 45.9047 19.4681 47.4206 20.8046 48.5087ZM24.8253 35.9871L23.0366 36.7135C21.4113 37.3741 20.1169 38.6485 19.4383 40.2564C18.7596 41.8642 18.7522 43.6738 19.4178 45.287C20.0834 46.9003 21.3673 48.185 22.9872 48.8586C24.6071 49.5322 26.4302 49.5395 28.0556 48.8789L29.8401 48.1538L24.8253 35.9871Z" fill="#002838"/>
							<g clip-path="url(#clip2)">
								<path d="M61.8801 13.4552L61.269 12.9133L60.0655 14.2505L60.6766 14.7924L61.8801 13.4552Z" fill="#BFC9CD"/>
								<path d="M59.8714 15.687L59.2603 15.1451L58.0022 16.543L58.6133 17.0848L59.8714 15.687Z" fill="#80939B"/>
								<path d="M57.5351 18.2828L56.924 17.7409L55.666 19.1387L56.277 19.6806L57.5351 18.2828Z" fill="#405E6A"/>
								<g clip-path="url(#clip3)">
									<path d="M55.2691 20.8004L54.6581 20.2585L50.3858 25.0052L50.9969 25.5471L55.2691 20.8004Z" fill="#002838"/>
								</g>
							</g>
							<g clip-path="url(#clip4)">
								<path d="M71.7119 44.9524L71.924 44.1668L70.1791 43.7027L69.967 44.4883L71.7119 44.9524Z" fill="#BFC9CD"/>
								<path d="M68.7995 44.1778L69.0116 43.3922L67.1875 42.9071L66.9755 43.6927L68.7995 44.1778Z" fill="#80939B"/>
								<path d="M65.4122 43.277L65.6243 42.4913L63.8003 42.0062L63.5882 42.7918L65.4122 43.277Z" fill="#405E6A"/>
								<g clip-path="url(#clip5)">
									<path d="M62.1269 42.4032L62.339 41.6176L56.1449 39.9702L55.9328 40.7558L62.1269 42.4032Z" fill="#002838"/>
								</g>
							</g>
							<g clip-path="url(#clip6)">
								<path d="M70.4116 26.4688L70.0839 25.7233L68.4281 26.4402L68.7558 27.1857L70.4116 26.4688Z" fill="#BFC9CD"/>
								<path d="M67.6479 27.6654L67.3203 26.9199L65.5894 27.6693L65.9171 28.4148L67.6479 27.6654Z" fill="#80939B"/>
								<path d="M64.4337 29.0571L64.106 28.3116L62.3752 29.061L62.7028 29.8065L64.4337 29.0571Z" fill="#405E6A"/>
								<g clip-path="url(#clip7)">
									<path d="M61.3161 30.4069L60.9885 29.6614L55.1108 32.2063L55.4384 32.9518L61.3161 30.4069Z" fill="#002838"/>
								</g>
							</g>
							<g clip-path="url(#clip8)">
								<path d="M18.3766 39.9478C17.1364 39.7738 15.9159 39.4787 14.7324 39.0667L15.4808 36.8754C16.5242 37.2448 17.5997 37.5146 18.6933 37.6812L18.3766 39.9478ZM25.8205 39.5884L25.2794 37.3333C26.354 37.0703 27.4009 36.7033 28.4055 36.2377L29.3611 38.3478C28.2218 38.8713 27.0365 39.2866 25.8205 39.5884ZM8.26716 35.316C7.32166 34.4905 6.45437 33.5784 5.67647 32.5913L7.4842 31.1536C8.17637 32.0255 8.94728 32.8308 9.78704 33.5594L8.26716 35.316ZM35.4349 33.9826L33.7653 32.3884C34.528 31.579 35.2141 30.6999 35.8148 29.7623L37.7492 31.0203C37.0732 32.0797 36.2982 33.0716 35.4349 33.9826ZM2.24525 25.9188C1.88872 24.7103 1.64964 23.4697 1.53137 22.2145L3.83421 21.9942C3.93872 23.1018 4.14886 24.1967 4.46173 25.2638L2.24525 25.9188ZM40.5241 24.0464L38.2616 23.6058C38.4691 22.5129 38.5732 21.4026 38.5725 20.2899V20.2145H40.8753V20.2899C40.874 21.5503 40.7564 22.8079 40.5241 24.0464ZM4.46173 15.3855L2.25101 14.7362C2.60093 13.5344 3.06558 12.3696 3.63846 11.258L5.68223 12.3189C5.18062 13.3002 4.77198 14.327 4.46173 15.3855ZM37.3347 13.7102C36.919 12.6778 36.4062 11.6877 35.8033 10.7536L37.7377 9.48987C38.4149 10.5507 38.993 11.6724 39.4648 12.8406L37.3347 13.7102ZM9.71795 7.06668L8.20384 5.32755C9.14939 4.50198 10.1689 3.76646 11.2493 3.13045L12.4008 5.13624C11.4486 5.6939 10.5504 6.34021 9.71795 7.06668ZM31.2092 5.96523C30.3033 5.32307 29.3396 4.76797 28.3306 4.30726L29.2805 2.19711C30.4216 2.71435 31.5125 3.33732 32.5391 4.05798L31.2092 5.96523ZM18.6242 2.93914L18.296 0.620301C19.5329 0.440725 20.7837 0.376683 22.0324 0.428995L21.9345 2.74784C20.8275 2.70704 19.7192 2.77109 18.6242 2.93914Z" fill="#7E5CEF"/>
							</g>
							<g clip-path="url(#clip9)">
								<path d="M54.473 73.9635C53.6048 73.8416 52.7505 73.635 51.922 73.3467L52.4459 71.8128C53.1763 72.0713 53.9292 72.2602 54.6947 72.3768L54.473 73.9635ZM59.6837 73.7119L59.3049 72.1333C60.0572 71.9492 60.79 71.6923 61.4932 71.3664L62.1622 72.8435C61.3646 73.2099 60.5349 73.5006 59.6837 73.7119ZM47.3964 70.7212C46.7345 70.1433 46.1274 69.5048 45.5829 68.8139L46.8483 67.8075C47.3328 68.4178 47.8725 68.9816 48.4603 69.4916L47.3964 70.7212ZM66.4138 69.7878L65.2451 68.6719C65.7789 68.1053 66.2592 67.4899 66.6798 66.8336L68.0338 67.7142C67.5606 68.4558 67.0181 69.1501 66.4138 69.7878ZM43.181 64.1432C42.9315 63.2972 42.7641 62.4287 42.6813 61.5501L44.2933 61.3959C44.3665 62.1713 44.5136 62.9377 44.7326 63.6846L43.181 64.1432ZM69.9763 62.8325L68.3925 62.5241C68.5377 61.759 68.6106 60.9818 68.6101 60.2029V60.1501H70.2221V60.2029C70.2212 61.0852 70.1389 61.9655 69.9763 62.8325ZM44.7326 56.7699L43.1851 56.3154C43.43 55.4741 43.7553 54.6587 44.1563 53.8806L45.5869 54.6232C45.2358 55.3101 44.9498 56.0289 44.7326 56.7699ZM67.7437 55.5971C67.4527 54.8744 67.0937 54.1814 66.6717 53.5275L68.0258 52.6429C68.4998 53.3855 68.9045 54.1707 69.2348 54.9884L67.7437 55.5971ZM48.4119 50.9467L47.3521 49.7293C48.0139 49.1514 48.7276 48.6365 49.4839 48.1913L50.2899 49.5954C49.6234 49.9857 48.9946 50.4381 48.4119 50.9467ZM63.4558 50.1757C62.8217 49.7261 62.1471 49.3376 61.4408 49.0151L62.1058 47.538C62.9045 47.9 63.6681 48.3361 64.3867 48.8406L63.4558 50.1757ZM54.6463 48.0574L54.4166 46.4342C55.2824 46.3085 56.158 46.2637 57.032 46.3003L56.9635 47.9235C56.1887 47.8949 55.4128 47.9398 54.6463 48.0574Z" fill="#7E5CEF"/>
							</g>
							<g clip-path="url(#clip10)">
								<path d="M3.95393 16.466H2.68027V21.4369H3.95393V16.466Z" fill="#7E5CEF"/>
								<path d="M6.18286 18.3991H0.451355V19.5038H6.18286V18.3991Z" fill="#7E5CEF"/>
								<path d="M3.95393 16.466H2.68027V21.4369H3.95393V16.466Z" fill="#7E5CEF"/>
								<path d="M6.18286 18.3991H0.451355V19.5038H6.18286V18.3991Z" fill="#7E5CEF"/>
								<g opacity="0.5">
									<path opacity="0.5" d="M62.2505 75.0291H60.9768V80H62.2505V75.0291Z" fill="#BFADF7"/>
									<path opacity="0.5" d="M64.4794 76.9622H58.7479V78.0669H64.4794V76.9622Z" fill="#BFADF7"/>
									<path opacity="0.5" d="M62.2505 75.0291H60.9768V80H62.2505V75.0291Z" fill="#BFADF7"/>
									<path opacity="0.5" d="M64.4794 76.9622H58.7479V78.0669H64.4794V76.9622Z" fill="#BFADF7"/>
								</g>
							</g>
							<defs>
								<clipPath id="clip0">
									<rect width="60.4494" height="60" fill="white" transform="translate(7.05243 11)"/>
								</clipPath>
								<clipPath id="clip1">
									<rect width="51.419" height="51.0367" fill="white" transform="translate(17.1777 9.04999)"/>
								</clipPath>
								<clipPath id="clip2">
									<rect width="0.816727" height="16.3209" fill="white" transform="matrix(0.748219 0.663452 -0.668981 0.74328 61.269 12.9133)"/>
								</clipPath>
								<clipPath id="clip3">
									<rect width="0.816727" height="6.49125" fill="white" transform="matrix(0.748219 0.663452 -0.668981 0.74328 54.6932 20.2195)"/>
								</clipPath>
								<clipPath id="clip4">
									<rect width="0.813742" height="16.3804" fill="white" transform="matrix(-0.260627 0.96544 -0.966406 -0.257022 71.924 44.1668)"/>
								</clipPath>
								<clipPath id="clip5">
									<rect width="0.813742" height="6.51493" fill="white" transform="matrix(-0.260627 0.96544 -0.966406 -0.257022 62.39 41.6311)"/>
								</clipPath>
								<clipPath id="clip6">
									<rect width="0.81431" height="16.3691" fill="white" transform="matrix(0.40234 0.91549 -0.917677 0.397327 70.0839 25.7233)"/>
								</clipPath>
								<clipPath id="clip7">
									<rect width="0.81431" height="6.51043" fill="white" transform="matrix(0.40234 0.91549 -0.917677 0.397327 61.0369 29.6404)"/>
								</clipPath>
								<clipPath id="clip8">
									<rect width="40.2996" height="40" fill="white" transform="translate(1.00748)"/>
								</clipPath>
								<clipPath id="clip9">
									<rect width="28.2097" height="28" fill="white" transform="translate(42.3146 46)"/>
								</clipPath>
								<clipPath id="clip10">
									<rect width="64.4794" height="64" fill="white" transform="translate(0 16)"/>
								</clipPath>
							</defs>
						</svg>
					</div>
					<div class="ms-auto align-self-center">
						<h2>Welcome to WP Engine's Content Modeler!</h2>
						<p>Welcome to our beta. Please send us any feedback or ideas you may have that can improve the overall experience.</p>
					</div>
					<div class="ms-auto align-self-end">
						<a href="#" target="_blank" role="button" class="btn btn-primary content-modeler btn-primary btn-lg"><span class="material-icons-outlined">feedback</span> Send Feedback</a>
					</div>
				</div>
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
