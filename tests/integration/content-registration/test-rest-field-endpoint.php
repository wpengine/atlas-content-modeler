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
		update_option(
			'wpe_content_model_post_types',
			[
				'rabbits' => [ 'name' => 'Rabbits' ],
				'cats'    => [ 'name' => 'Cats' ],
			]
		);
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

		$this->assertEquals( 400, $response->get_status() );
		$this->assertArrayHasKey( 'message', $data );
		$this->assertEquals( 'The specified content model does not exist.', $data[ 'message' ] );
	}

	public function test_field_slugs_must_be_unique_to_each_model() {
		wp_set_current_user( 1 );
		$model   = 'rabbits';
		$request = new WP_REST_Request( 'POST', "/{$this->namespace}/{$this->route}" );
		$request->set_header( 'content-type', 'application/json' );
		$request->set_body( "{\"type\":\"text\",\"id\":\"123\",\"model\":\"{$model}\",\"position\":\"0\",\"name\":\"Name\",\"textLength\":\"short\",\"slug\":\"name\"}" );
		$this->server->dispatch( $request );

		// Send a second request with the same slug but a new ID. It should fail to update because the slugs collide.
		$request->set_body( "{\"type\":\"text\",\"id\":\"456\",\"model\":\"{$model}\",\"position\":\"0\",\"name\":\"Name\",\"textLength\":\"short\",\"slug\":\"name\"}" );
		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		$this->assertEquals( 400, $response->get_status() );
		$this->assertArrayHasKey( 'message', $data );
		$this->assertEquals( 'Another field in this model has the same API identifier.', $data[ 'message' ] );
	}

	public function test_field_can_be_created_and_updated() {
		wp_set_current_user( 1 );
		$model   = 'rabbits';

		// First request to create the field.
		$request = new WP_REST_Request( 'POST', "/{$this->namespace}/{$this->route}" );
		$request->set_header( 'content-type', 'application/json' );
		$request->set_body( "{\"type\":\"text\",\"id\":\"123\",\"model\":\"{$model}\",\"position\":\"0\",\"name\":\"Name\",\"textLength\":\"short\",\"slug\":\"name\"}" );
		$this->server->dispatch( $request );

		// Second request to update the field name.
		$request2 = new WP_REST_Request( 'PUT', "/{$this->namespace}/{$this->route}" );
		$request2->set_header( 'content-type', 'application/json' );
		$request2->set_body( "{\"type\":\"text\",\"id\":\"123\",\"model\":\"{$model}\",\"position\":\"0\",\"name\":\"New Name\",\"textLength\":\"short\",\"slug\":\"name\"}" );

		$response = $this->server->dispatch( $request2 );
		$models   = get_option( 'wpe_content_model_post_types' );

		$this->assertEquals( 200, $response->get_status() );
		$this->assertEquals( 'New Name', $models['rabbits']['fields']['123']['name'] );
	}

	public function test_different_models_can_have_fields_with_same_slug() {
		wp_set_current_user( 1 );
		$model   = 'rabbits';
		$request = new WP_REST_Request( 'POST', "/{$this->namespace}/{$this->route}" );
		$request->set_header( 'content-type', 'application/json' );
		$request->set_body( "{\"type\":\"text\",\"id\":\"123\",\"model\":\"{$model}\",\"position\":\"0\",\"name\":\"Name\",\"textLength\":\"short\",\"slug\":\"name\"}" );
		$this->server->dispatch( $request );

		$model   = 'cats';
		$request2 = new WP_REST_Request( 'POST', "/{$this->namespace}/{$this->route}" );
		$request2->set_header( 'content-type', 'application/json' );
		$request2->set_body( "{\"type\":\"text\",\"id\":\"123\",\"model\":\"{$model}\",\"position\":\"0\",\"name\":\"Name\",\"textLength\":\"short\",\"slug\":\"name\"}" );

		$response = $this->server->dispatch( $request2 );
		$data     = $response->get_data();
		$models   = get_option( 'wpe_content_model_post_types' );

		$this->assertArrayHasKey( 'success', $data );
		$this->assertEquals( true, $data[ 'success' ] );
		$this->assertArrayHasKey( '123', $models['rabbits']['fields'] );
		$this->assertArrayHasKey( '123', $models['cats']['fields'] );
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
