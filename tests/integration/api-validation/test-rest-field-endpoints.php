<?php

use function WPE\AtlasContentModeler\ContentRegistration\update_registered_content_types;

class RestFieldEndpointTests extends WP_UnitTestCase {

    /**
	 * The REST API server instance.
	 *
	 * @var \WP_REST_Server
	 */
	private $server;
    private $namespace = '/wp/v2';
	private $public_fields_route = '/publics-fields';
    private $post_ids;

    public function setUp(): void {
		parent::setUp();

        update_registered_content_types( $this->get_models() );

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

    public function tearDown() {
		parent::tearDown();
		wp_set_current_user( null );
		global $wp_rest_server;
		$wp_rest_server = null;
		$this->server = null;
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
		$request  = new \WP_REST_Request( 'GET', $this->namespace . $this->public_fields_route . '/' . $this->post_ids['public_fields_post_id'] );
		$response = $this->server->dispatch( $request );
		$response_data = $response->get_data();

		self::assertArrayHasKey( 'acm_fields', $response_data );

		self::assertArrayHasKey( 'singleLineRequired', $response_data['acm_fields'] );
		self::assertSame( $response_data['acm_fields']['singleLineRequired'], 'This is required single line text' );

		self::assertArrayHasKey( 'numberIntergerRequired', $response_data['acm_fields'] );
		self::assertEquals( '13', $response_data['acm_fields']['numberIntergerRequired'] );

		self::assertArrayHasKey( 'dateRequired', $response_data['acm_fields'] );
		self::assertEquals( '2021/02/13', $response_data['acm_fields']['dateRequired'] );

		self::assertArrayHasKey( 'booleanRequired', $response_data['acm_fields'] );
		self::assertEquals( 'true', $response_data['acm_fields']['booleanRequired'] );

		self::assertArrayHasKey( 'mediaRequired', $response_data['acm_fields'] );
		self::assertArrayHasKey( 'mediaPDF', $response_data['acm_fields'] );

	}

	/**
	 * Test for full response for a required media field with image
	 */
	public function test_post_meta_media_field_rest_response(): void {
		wp_set_current_user( 1 );
		$request  = new \WP_REST_Request( 'GET', $this->namespace . $this->public_fields_route . '/' . $this->post_ids['public_fields_post_id'] );
		$response = $this->server->dispatch( $request );
		$response_data = $response->get_data();

		self::assertArrayHasKey( 'acm_fields', $response_data );
		self::assertArrayHasKey( 'mediaRequired', $response_data['acm_fields'] );
		self::assertArrayHasKey( 'mediaPDF', $response_data ['acm_fields'] );

		$image = $response_data['acm_fields']['mediaRequired'];
		$file = $response_data['acm_fields']['mediaPDF'];
		$expected_keys = [
			'caption',
			'alt_text',
			'media_type',
			'mime_type',
			'media_details',
			'source_url',
		];

		// Images and files have same structure
		foreach ( $expected_keys as $key ) {
			self::assertArrayHasKey( $key, $image );
			self::assertArrayHasKey( $key, $file );
		}

		// Images
		self::assertArrayHasKey( 'rendered', $image['caption'] );
		self::assertEquals( 'image', $image['media_type'] );
		self::assertEquals( 'image/png', $image['mime_type'] );
		self::assertEquals( 4, count( $image['media_details'] ) );
		self::assertArrayHasKey( 'sizes', $image['media_details'] );
		self::assertEquals( 2, count( $image['media_details']['sizes'] ) );

		// Files
		self::assertArrayHasKey( 'rendered', $file['caption'] );
		self::assertEquals( 'file', $file['media_type'] );
		self::assertEquals( 'application/pdf', $file['mime_type'] );
		self::assertInstanceOf( 'stdClass', $file['media_details'] );
	}
}
