<?php

use function WPE\AtlasContentModeler\ContentRegistration\update_registered_content_types;

class RestModelEndpointTests extends WP_UnitTestCase {

	/**
	 * The REST API server instance.
	 *
	 * @var \WP_REST_Server
	 */
	private $server;
	private $namespace = '/wpe';
	private $route     = '/atlas/content-model';
	private $test_models;

	public function setUp(): void {
		parent::setUp();

		$this->test_models = $this->get_models();

		update_registered_content_types( $this->test_models );

		// Start each test with a fresh relationships registry.
		\WPE\AtlasContentModeler\ContentConnect\Plugin::instance()->setup();

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
	 * Test that the wpe route is available via REST
	 */
	public function test_content_model_route_is_registered(): void {
		$routes = $this->server->get_routes( 'wpe' );
		self::assertArrayHasKey( $this->namespace . $this->route, $routes );
	}

	/**
	 * Test that we cannot create a model where the slug conflicts with an existing post type
	 *
	 * @return void
	 */
	public function test_cannot_create_model_when_slug_conflicts_with_existing_post_type(): void {
		wp_set_current_user( 1 );
		$model = 'attachment'; // already exists by default in WP.

		// Attempt to create the model.
		$request = new WP_REST_Request( 'POST', $this->namespace . $this->route );
		$request->set_header( 'content-type', 'application/json' );
		$request->set_body( json_encode( $this->test_models[ $model ] ) );
		$this->server->dispatch( $request );

		$response = $this->server->dispatch( $request );
		self::assertSame( 400, $response->get_status() );
		self::assertSame( 'atlas_content_modeler_already_exists', $response->data['code'] );
	}

	/**
	 * Tests that a model can be created via the REST API.
	 *
	 * @return void
	 */
	public function test_can_create_model(): void {
		wp_set_current_user( 1 );

		$slug              = 'bookreviews';
		$create_test_model = array(
			'slug'        => $slug,
			'singular'    => 'Book Review',
			'plural'      => 'Book Reviews',
			'description' => 'Reviews of books.',
		);

		$request = new WP_REST_Request( 'POST', $this->namespace . $this->route );
		$request->set_header( 'content-type', 'application/json' );
		$request->set_body( json_encode( $create_test_model ) );
		$response = $this->server->dispatch( $request );

		self::assertSame( 200, $response->get_status() );

		$data   = $response->get_data();
		$models = get_option( 'atlas_content_modeler_post_types' );

		foreach ( array_keys( $create_test_model ) as $key ) {
			// Test returned content
			self::assertSame( $create_test_model[ $key ], $data['model'][ $key ] );
			// Test saved content
			self::assertSame( $create_test_model[ $key ], $models[ $slug ][ $key ] );
		}
	}

	/**
	 * Tests that a model can be successfully updated via REST
	 *
	 * @return void
	 */
	public function test_can_update_model(): void {
		wp_set_current_user( 1 );
		$model = 'public';

		$request  = new WP_REST_Request( 'GET', $this->namespace . $this->route . '/' . $model );
		$response = $this->server->dispatch( $request );

		// Request to update model.
		$new_model                = $this->test_models[ $model ];
		$new_model['description'] = 'This is a new description of the public model';
		$request                  = new WP_REST_Request( 'PATCH', $this->namespace . $this->route . '/' . $model );
		$request->set_header( 'content-type', 'application/json' );
		$request->set_body( json_encode( $new_model ) );

		$response = $this->server->dispatch( $request );
		$models   = get_option( 'atlas_content_modeler_post_types' );

		self::assertSame( 200, $response->get_status() );
		self::assertSame( $new_model['description'], $models['public']['description'] );
	}

	/**
	 * Tests that a model update will fail in REST if the model is invalid
	 */
	public function test_cannot_update_model_with_invalid_data(): void {
		wp_set_current_user( 1 );
		$model = 'public';

		// Request to update model.
		$new_model = $this->test_models[ $model ];
		unset( $new_model['plural'] ); // To make it an invalid request.
		$request = new WP_REST_Request( 'PATCH', $this->namespace . $this->route . '/' . $model );
		$request->set_header( 'content-type', 'application/json' );
		$request->set_body( json_encode( $new_model ) );

		$response = $this->server->dispatch( $request );
		self::assertSame( 400, $response->get_status() );
	}

	/**
	 * Test that we cannot change a model's slug via REST.
	 */
	public function test_cannot_update_model_slug(): void {
		wp_set_current_user( 1 );
		$model = 'public';

		// Request to update model.
		$new_model             = $this->test_models[ $model ];
		$new_model['slug']     = 'edited-public-slug-2'; // Slug updates should be ignored.
		$new_model['singular'] = 'Public2'; // Must change something successfuly to get 200 response.
		$request               = new WP_REST_Request( 'PATCH', $this->namespace . $this->route . '/' . $model );
		$request->set_header( 'content-type', 'application/json' );
		$request->set_body( json_encode( $new_model ) );

		$response = $this->server->dispatch( $request );
		$models   = get_option( 'atlas_content_modeler_post_types' );

		self::assertSame( 200, $response->get_status() );
		self::assertSame( $this->test_models['public']['slug'], $models['public']['slug'] );
		self::assertSame( $new_model['singular'], $models['public']['singular'] );
	}
}
