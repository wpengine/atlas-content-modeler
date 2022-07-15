<?php
class PublisherCallbacksTestCases extends WP_UnitTestCase {
	private $class;

	public function set_up() {
		parent::set_up();
		global $wpdb;
		$option_name = $wpdb->prefix . 'acm_post_to_post_schema_version';
		delete_option( $option_name );
		\WPE\AtlasContentModeler\ContentConnect\Plugin::instance()->setup();
		$this->class = new WPE\AtlasContentModeler\FormEditingExperience();
	}

	public function tear_down() {
		parent::tear_down();
		unset( $this->class );
	}

	public function test_publisher_experience_callbacks_attached(): void {
		self::assertSame( 10, has_action( 'init', [ $this->class, 'remove_post_type_supports' ] ) );
		self::assertSame( 10, has_action( 'rest_api_init', [ $this->class, 'support_title_in_api_responses' ] ) );
		self::assertSame( 10, has_action( 'init_graphql_request', [ $this->class, 'support_title_in_api_responses' ] ) );
		self::assertSame( 10, has_action( 'rest_api_init', [ $this->class, 'add_related_posts_to_rest_responses' ] ) );
		self::assertSame( 10, has_filter( 'use_block_editor_for_post_type', [ $this->class, 'disable_block_editor' ] ) );
		self::assertSame( 10, has_action( 'current_screen', [ $this->class, 'current_screen' ] ) );
		self::assertSame( 10, has_action( 'admin_enqueue_scripts', [ $this->class, 'enqueue_assets' ] ) );
		self::assertSame( 10, has_action( 'edit_form_after_title', [ $this->class, 'render_app_container' ] ) );
		self::assertSame( 10, has_action( 'save_post', [ $this->class, 'save_post' ] ) );
		self::assertSame( 10, has_action( 'wp_insert_post', [ $this->class, 'set_post_attributes' ] ) );
		self::assertSame( 10, has_filter( 'redirect_post_location', [ $this->class, 'append_error_to_location' ] ) );
		self::assertSame( 10, has_action( 'admin_notices', [ $this->class, 'display_save_post_errors' ] ) );
		self::assertSame( 10, has_filter( 'the_title', [ $this->class, 'filter_post_titles' ] ) );
		self::assertSame( 10, has_action( 'load-post.php', [ $this->class, 'feedback_notice_handler' ] ) );
		self::assertSame( 10, has_action( 'load-post-new.php', [ $this->class, 'feedback_notice_handler' ] ) );
		self::assertSame( 10, has_action( 'do_meta_boxes', [ $this->class, 'move_meta_boxes' ] ) );
		self::assertSame( 10, has_action( 'do_meta_boxes', [ $this->class, 'remove_thumbnail_meta_box' ] ) );
		self::assertSame( 10, has_action( 'transition_post_status', [ $this->class, 'maybe_add_location_callback' ] ) );
		self::assertSame( 10, has_action( 'added_post_meta', [ $this->class, 'sync_title_field_to_posts_table' ] ) );
		self::assertSame( 10, has_action( 'updated_postmeta', [ $this->class, 'sync_title_field_to_posts_table' ] ) );
	}

	/**
	 * @covers ::\WPE\AtlasContentModeler\FormEditingExperience\add_published_query_arg_to_location()
	 */
	public function test_add_published_query_arg_to_location_returns_adjusted_url_when_usage_tracking_enabled(): void {
		update_option( 'atlas_content_modeler_usage_tracking', true );
		self::assertSame(
			'/?acm-post-published=true',
			$this->class->add_published_query_arg_to_location( '/' )
		);
	}

	/**
	 * @covers ::\WPE\AtlasContentModeler\FormEditingExperience\add_published_query_arg_to_location()
	 */
	public function test_add_published_query_arg_to_location_returns_original_url_when_usage_tracking_disabled(): void {
		delete_option( 'atlas_content_modeler_usage_tracking' );
		self::assertSame(
			'/',
			$this->class->add_published_query_arg_to_location( '/' )
		);
	}
}
