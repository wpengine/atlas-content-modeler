<?php

class TestRestFieldEndpoint extends WP_UnitTestCase {
	protected $server;

	protected $namespace = 'wpe';

	protected $route = 'content-model-field';

	public function setUp() {
		parent::setUp();
		global $wp_rest_server;
		$this->server = $wp_rest_server = new \WP_REST_Server;
		do_action( 'rest_api_init' );
		update_option( 'wpe_content_model_post_types',  [ 'rabbits' => [ 'name' => 'Rabbits' ] ]);
	}

	public function test_content_model_field_route_is_registered() {
		$routes = $this->server->get_routes( 'wpe' );
		$this->assertArrayHasKey( "/{$this->namespace}/{$this->route}", $routes );
	}

	public function test_posting_fields_stores_them() {
		wp_set_current_user( 1 );
		$model   = 'rabbits';
		$request = new WP_REST_Request( 'POST', "/{$this->namespace}/{$this->route}" );
		$request->set_header( 'content-type', 'application/json' );
		$request->set_body( "{\"type\":\"text\",\"id\":\"123\",\"model\":\"{$model}\",\"position\":\"0\",\"name\":\"Name\",\"textLength\":\"short\",\"slug\":\"name\"}" );

		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();
		$models   = get_option( 'wpe_content_model_post_types' );

		$this->assertEquals( 200, $response->get_status() );
		$this->assertArrayHasKey( 'success', $data );
		$this->assertEquals( true, $data[ 'success' ] );
		$this->assertArrayHasKey( '123', $models['rabbits']['fields'] );
	}


	public function test_posting_fields_to_unknown_model_gives_error() {
		wp_set_current_user( 1 );
		$model   = 'onions';
		$request = new WP_REST_Request( 'POST', "/{$this->namespace}/{$this->route}" );
		$request->set_header( 'content-type', 'application/json' );
		$request->set_body( "{\"type\":\"text\",\"id\":\"123\",\"model\":\"{$model}\",\"position\":\"0\",\"name\":\"Name\",\"textLength\":\"short\",\"slug\":\"name\"}" );

		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		$this->assertEquals( 200, $response->get_status() );
		$this->assertArrayHasKey( 'success', $data );
		$this->assertEquals( false, $data[ 'success' ] );
	}

	public function test_posting_duplicate_field_id_gives_error() {
		wp_set_current_user( 1 );
		$model   = 'rabbits';
		$request = new WP_REST_Request( 'POST', "/{$this->namespace}/{$this->route}" );
		$request->set_header( 'content-type', 'application/json' );
		$request->set_body( "{\"type\":\"text\",\"id\":\"123\",\"model\":\"{$model}\",\"position\":\"0\",\"name\":\"Name\",\"textLength\":\"short\",\"slug\":\"name\"}" );

		// Send the request twice. The second time should fail due to the duplicate field slug.
		$this->server->dispatch( $request );
		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		$this->assertEquals( 400, $response->get_status() );
		$this->assertArrayHasKey( 'message', $data );
		$this->assertEquals( 'Another field in this model has the same API identifier.', $data[ 'message' ] );
	}

	public function tearDown() {
		parent::tearDown();
		wp_set_current_user( null );
		global $wp_rest_server;
		$wp_rest_server = null;
		$this->server = null;
		delete_option( 'wpe_content_model_post_types' );
	}
}
