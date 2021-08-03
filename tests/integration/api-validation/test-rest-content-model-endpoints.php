<?php

use function WPE\AtlasContentModeler\ContentRegistration\update_registered_content_types;

class RestContentModelEndpointTests extends WP_UnitTestCase {

    /**
	 * The REST API server instance.
	 *
	 * @var \WP_REST_Server
	 */
	private $server;
    private $namespace = '/wp/v2';
	private $public_route = '/publics';
    private $private_route = '/privates';
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
     * Tests public post type is available and populated in the rest api.
     */
    public function test_public_post_type_accessible_via_rest_api(): void {
		wp_set_current_user( 1 );
		$request  = new \WP_REST_Request( 'GET', $this->namespace . $this->public_route . '/' . $this->post_ids['public_post_id'] );
		$response = $this->server->dispatch( $request );
		$response_data = $response->get_data();
		self::assertSame( $response_data['title']['rendered'], 'Test dog' );
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
		$request  = new \WP_REST_Request( 'GET', $this->namespace . $this->public_route . '/' . $this->post_ids['draft_public_post_id'] );
		$response = $this->server->dispatch( $request );
		$response_data = $response->get_data();
		self::assertSame( $response->get_status(), 200 );
		self::assertSame( $response_data['title']['rendered'], 'Draft dog' );
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
		$request  = new \WP_REST_Request( 'GET', $this->namespace . $this->private_route . '/' . $this->post_ids['private_post_id'] );
		$response = $this->server->dispatch( $request );
		$response_data = $response->get_data();
		self::assertSame( $response->get_status(), 200 );
		self::assertSame( $response_data['title']['rendered'], 'Test cat' );
	}
}
