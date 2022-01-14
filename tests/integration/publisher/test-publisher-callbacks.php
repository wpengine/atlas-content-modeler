<?php
class PublisherCallbacksTestCases extends WP_UnitTestCase {
	private $class;

	public function set_up() {
		parent::set_up();
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
	}
}
