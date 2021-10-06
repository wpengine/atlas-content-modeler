<?php

use function WPE\AtlasContentModeler\ContentRegistration\update_registered_content_types;
use \WPE\AtlasContentModeler\ContentConnect\Plugin as ContentConnect;
use function WPE\AtlasContentModeler\ContentRegistration\get_registered_content_types;

require_once __DIR__ . '/test-data/fields.php';

class RestModelsEndpointTests extends WP_UnitTestCase {

	/**
	 * The REST API server instance.
	 *
	 * @var \WP_REST_Server
	 */
	private $server;
	private $namespace = '/wpe';
	private $route     = '/atlas/content-models';
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

		$create_test_models = array(
			array(
				'slug'        => 'public',
				'singular'    => 'Book Review 1',
				'plural'      => 'Book Reviews 1',
				'description' => 'Reviews of books.',
			),
			array(
				'slug'        => 'private',
				'singular'    => 'Book Review 2',
				'plural'      => 'Book Reviews 2',
				'description' => 'Reviews of books.',
			)
		);

		$request = new WP_REST_Request( 'PUT', $this->namespace . $this->route );
		$request->set_header( 'content-type', 'application/json' );
		$request->set_body( json_encode( $create_test_models ) );
		$response = $this->server->dispatch( $request );

		self::assertSame( 400, $response->get_status() );
	}

	/**
	 * Tests that a model can be created via the REST API.
	 *
	 * @return void
	 */
	public function test_can_create_models(): void {
		wp_set_current_user( 1 );

		$create_test_models = array(
			array(
				'slug'        => 'bookreviews',
				'singular'    => 'Book Review 1',
				'plural'      => 'Book Reviews 1',
				'description' => 'Reviews of books.',
			),
			array(
				'slug'        => 'rabbits',
				'singular'    => 'Book Review 2',
				'plural'      => 'Book Reviews 2',
				'description' => 'Reviews of books.',
			),
			array(
				'slug'        => 'cheeses',
				'singular'    => 'Book Review 3',
				'plural'      => 'Book Reviews 3',
				'description' => 'Reviews of books.',
			)
		);

		$request = new WP_REST_Request( 'PUT', $this->namespace . $this->route );
		$request->set_header( 'content-type', 'application/json' );
		$request->set_body( json_encode( $create_test_models ) );
		$response = $this->server->dispatch( $request );

		self::assertSame( 200, $response->get_status() );

		$data   = $response->get_data();
		$models = get_registered_content_types();

		self::assertArrayHasKey('success', $data);
		self::assertArrayHasKey('cheeses', $models);
		self::assertArrayHasKey('rabbits', $models);
		self::assertArrayHasKey('bookreviews', $models);
	}
}
