<?php

use function WPE\AtlasContentModeler\ContentRegistration\update_registered_content_types;

class RestModelDataTests extends WP_UnitTestCase {

	/**
	 * The REST API server instance.
	 *
	 * @var \WP_REST_Server
	 */
	private $server;
	private $namespace           = '/wp/v2';
	private $public_fields_route = '/publics-fields';
	private $public_route        = '/publics';
	private $private_route       = '/privates';
	private $post_ids;

	public function set_up(): void {
		parent::set_up();

		update_registered_content_types( $this->get_models() );

		// Start each test with a fresh relationships registry.
		\WPE\AtlasContentModeler\ContentConnect\Plugin::instance()->setup();

		// Initialize the publisher logic, which includes additional filters.
		new \WPE\AtlasContentModeler\FormEditingExperience();

		// @todo why is this not running automatically?
		do_action( 'init' );

		/**
		 * WP_Rest_Server instance.
		 */
		global $wp_rest_server;

		$wp_rest_server = new \WP_REST_Server();

		$this->server = $wp_rest_server;

		do_action( 'rest_api_init' );

		$this->post_ids = $this->get_post_ids();
	}

	public function tear_down() {
		parent::tear_down();
		wp_set_current_user( null );
		global $wp_rest_server;
		$wp_rest_server = null;
		$this->server   = null;
		delete_option( 'atlas_content_modeler_post_types' );
	}

	private function get_models() {
		return include __DIR__ . '/test-data/models.php';
	}

	private function get_post_ids() {
		include_once __DIR__ . '/test-data/posts.php';

		return create_test_posts( $this );
	}

	/**
	 * Test that all fields are accounted for in REST
	 */
	public function test_post_meta_that_is_configured_to_show_in_rest_is_accessible(): void {
		wp_set_current_user( 1 );
		$request       = new \WP_REST_Request( 'GET', $this->namespace . $this->public_fields_route . '/' . $this->post_ids['public_fields_post_id'] );
		$response      = $this->server->dispatch( $request );
		$response_data = $response->get_data();

		self::assertArrayHasKey( 'acm_fields', $response_data );

		self::assertArrayHasKey( 'singleLineRequired', $response_data['acm_fields'] );
		self::assertSame( 'This is required single line text', $response_data['acm_fields']['singleLineRequired'] );

		self::assertArrayHasKey( 'singleLineTextRepeater', $response_data['acm_fields'] );
		self::assertSame( [ 'This is one line of repeater text', 'This is another line of repeater text' ], $response_data['acm_fields']['singleLineTextRepeater'] );

		self::assertArrayHasKey( 'numberIntergerRequired', $response_data['acm_fields'] );
		self::assertEquals( '13', $response_data['acm_fields']['numberIntergerRequired'] );

		self::assertArrayHasKey( 'dateRequired', $response_data['acm_fields'] );
		self::assertEquals( '2021/02/13', $response_data['acm_fields']['dateRequired'] );

		self::assertArrayHasKey( 'booleanRequired', $response_data['acm_fields'] );
		self::assertEquals( 'true', $response_data['acm_fields']['booleanRequired'] );

		self::assertArrayHasKey( 'mediaRequired', $response_data['acm_fields'] );
		self::assertArrayHasKey( 'mediaPDF', $response_data['acm_fields'] );
		self::assertArrayHasKey( 'mediaFeatured', $response_data['acm_fields'] );
		self::assertArrayHasKey( 'featured_media', $response_data );
	}

	/**
	 * Test for full response for a required media field with image
	 */
	public function test_post_meta_media_field_rest_response(): void {
		wp_set_current_user( 1 );
		$request       = new \WP_REST_Request( 'GET', $this->namespace . $this->public_fields_route . '/' . $this->post_ids['public_fields_post_id'] );
		$response      = $this->server->dispatch( $request );
		$response_data = $response->get_data();

		self::assertArrayHasKey( 'acm_fields', $response_data );
		self::assertArrayHasKey( 'mediaRequired', $response_data['acm_fields'] );
		self::assertArrayHasKey( 'mediaPDF', $response_data ['acm_fields'] );

		$image         = $response_data['acm_fields']['mediaRequired'];
		$file          = $response_data['acm_fields']['mediaPDF'];
		$expected_keys = [
			'caption',
			'alt_text',
			'media_type',
			'mime_type',
			'media_details',
			'source_url',
		];

		// Images and files have same structure.
		foreach ( $expected_keys as $key ) {
			self::assertArrayHasKey( $key, $image );
			self::assertArrayHasKey( $key, $file );
		}

		// Images.
		self::assertArrayHasKey( 'rendered', $image['caption'] );
		self::assertEquals( 'image', $image['media_type'] );
		self::assertEquals( 'image/png', $image['mime_type'] );
		self::assertEquals( 4, count( $image['media_details'] ) );
		self::assertArrayHasKey( 'sizes', $image['media_details'] );
		self::assertEquals( 2, count( $image['media_details']['sizes'] ) );

		// Files.
		self::assertArrayHasKey( 'rendered', $file['caption'] );
		self::assertEquals( 'file', $file['media_type'] );
		self::assertEquals( 'application/pdf', $file['mime_type'] );
		self::assertInstanceOf( 'stdClass', $file['media_details'] );
	}

	public function test_post_meta_that_is_configured_to_not_show_in_rest_is_not_accessible(): void {
		wp_set_current_user( 1 );
		$request       = new \WP_REST_Request( 'GET', $this->namespace . $this->public_fields_route . '/' . $this->post_ids['public_fields_post_id'] );
		$response      = $this->server->dispatch( $request );
		$response_data = $response->get_data();
		self::assertFalse( array_key_exists( 'hiddenField', $response_data['acm_fields'] ) );
	}

	/**
	 * Tests public post type is available and populated in the rest api.
	 */
	public function test_public_post_type_accessible_via_rest_api(): void {
		wp_set_current_user( 1 );
		$request       = new \WP_REST_Request( 'GET', $this->namespace . $this->public_route . '/' . $this->post_ids['public_post_id'] );
		$response      = $this->server->dispatch( $request );
		$response_data = $response->get_data();
		self::assertEquals( $this->post_ids['public_post_id'], $response_data['slug'] );
	}

	/**
	 * Ensures that a post set to "draft" is not available in the rest api.
	 */
	public function test_draft_posts_for_models_with_public_api_visibility_cannot_be_read_via_rest_api_when_not_authenticated(): void {
		wp_set_current_user( null );
		$request  = new \WP_REST_Request( 'GET', $this->namespace . $this->public_route . '/' . $this->post_ids['draft_public_post_id'] );
		$response = $this->server->dispatch( $request );
		self::assertTrue( $response->is_error() );
	}

	/**
	 * Ensures draft posts can be read by an authenticated user in the rest api.
	 */
	public function test_draft_posts_for_models_with_public_api_visibility_can_be_read_via_rest_api_when_authenticated(): void {
		wp_set_current_user( 1 );
		$request       = new \WP_REST_Request( 'GET', $this->namespace . $this->public_route . '/' . $this->post_ids['draft_public_post_id'] );
		$response      = $this->server->dispatch( $request );
		$response_data = $response->get_data();
		self::assertSame( 200, $response->get_status() );
		self::assertEquals( $this->post_ids['draft_public_post_id'], $response_data['slug'] );
	}

	/**
	 * Ensure a post in a private post type is not available to an unauthenticated user in the rest api.
	 */
	public function test_post_type_with_private_api_visibility_cannot_be_read_via_rest_api_when_not_authenticated(): void {
		$request  = new \WP_REST_Request( 'GET', $this->namespace . $this->private_route . '/' . $this->post_ids['private_post_id'] );
		$response = $this->server->dispatch( $request );
		self::assertTrue( $response->is_error() );
	}

	/**
	 * Ensure a post in a private post type is available to an authenticated user in the rest api.
	 */
	public function test_post_type_with_private_api_visibility_can_be_read_via_rest_api_when_authenticated(): void {
		wp_set_current_user( 1 );
		$request       = new \WP_REST_Request( 'GET', $this->namespace . $this->private_route . '/' . $this->post_ids['private_post_id'] );
		$response      = $this->server->dispatch( $request );
		$response_data = $response->get_data();
		self::assertSame( 200, $response->get_status() );
		self::assertEquals( $this->post_ids['private_post_id'], $response_data['slug'] );
	}

	/**
	 * Ensures the acm_related_posts REST field was registered and is present in returned post data.
	 */
	public function test_rest_fields_include_acm_related_posts(): void {
		wp_set_current_user( 1 );
		$request       = new \WP_REST_Request( 'GET', $this->namespace . $this->public_fields_route . '/' . $this->post_ids['public_fields_post_id'] );
		$response      = $this->server->dispatch( $request );
		$response_data = $response->get_data();
		self::assertIsArray( $response_data['acm_related_posts'] );
	}
}
