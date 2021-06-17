<?php

class TestRestFieldEndpoint extends WP_UnitTestCase {
	protected $server;

	protected $namespace = 'wpe';

	protected $route = 'atlas/content-model-field';

	protected $test_models = [
		'rabbits' => [ 'name' => 'Rabbits' ],
		'cats'    => [ 'name' => 'Cats' ],
		'dogs'    => [
			'name' => 'Dogs',
			'fields' => [
				'111' => [ 'position' => '0' ],
				'222' => [ 'position' => '1' ],
			],
		],
		'title'   => [
			'name' => 'A model with a field with isTitle',
			'fields' => [
				'111' => [ 'id' => '111', 'isTitle' => true, 'slug' => 'a' ],
				'222' => [ 'id' => '222', 'slug' => 'b' ],
			],
		],
	];

	public function setUp() {
		parent::setUp();
		global $wp_rest_server;
		$this->server = $wp_rest_server = new \WP_REST_Server;
		do_action( 'rest_api_init' );
		update_option('atlas_content_modeler_post_types', $this->test_models );
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
		$request->set_body( "{\"type\":\"text\",\"id\":\"123\",\"model\":\"{$model}\",\"position\":\"0\",\"name\":\"Name\",\"inputType\":\"single\",\"slug\":\"name\"}" );

		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();
		$models   = get_option( 'atlas_content_modeler_post_types' );

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
		$request->set_body( "{\"type\":\"text\",\"id\":\"123\",\"model\":\"{$model}\",\"position\":\"0\",\"name\":\"Name\",\"inputType\":\"single\",\"slug\":\"name\"}" );

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
		$request->set_body( "{\"type\":\"text\",\"id\":\"123\",\"model\":\"{$model}\",\"position\":\"0\",\"name\":\"Name\",\"inputType\":\"single\",\"slug\":\"name\"}" );
		$this->server->dispatch( $request );

		// Send a second request with the same slug but a new ID. It should fail to update because the slugs collide.
		$request->set_body( "{\"type\":\"text\",\"id\":\"456\",\"model\":\"{$model}\",\"position\":\"0\",\"name\":\"Name\",\"inputType\":\"single\",\"slug\":\"name\"}" );
		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		$this->assertEquals( 400, $response->get_status() );
		$this->assertArrayHasKey( 'message', $data );
		$this->assertEquals( 'Another field in this model has the same API identifier.', $data[ 'message' ] );
	}

	public function test_field_can_be_created_and_updated_and_deleted() {
		wp_set_current_user( 1 );
		$model   = 'rabbits';

		// First request to create the field.
		$request = new WP_REST_Request( 'POST', "/{$this->namespace}/{$this->route}" );
		$request->set_header( 'content-type', 'application/json' );
		$request->set_body( "{\"type\":\"text\",\"id\":\"123\",\"model\":\"{$model}\",\"position\":\"0\",\"name\":\"Name\",\"inputType\":\"single\",\"slug\":\"name\"}" );
		$this->server->dispatch( $request );

		// Second request to update the field name.
		$request2 = new WP_REST_Request( 'PUT', "/{$this->namespace}/{$this->route}" );
		$request2->set_header( 'content-type', 'application/json' );
		$request2->set_body( "{\"type\":\"text\",\"id\":\"123\",\"model\":\"{$model}\",\"position\":\"0\",\"name\":\"New Name\",\"inputType\":\"single\",\"slug\":\"name\"}" );

		$response = $this->server->dispatch( $request2 );
		$models   = get_option( 'atlas_content_modeler_post_types' );

		$this->assertEquals( 200, $response->get_status() );
		$this->assertEquals( 'New Name', $models['rabbits']['fields']['123']['name'] );

		// Third request to delete the field.
		$request3 = new WP_REST_Request( 'DELETE', "/{$this->namespace}/{$this->route}/123" );
		$request3->set_header( 'content-type', 'application/json' );
		$request3->set_body( "{\"model\":\"{$model}\" }" );

		$request3_response = $this->server->dispatch( $request3 );
		$updated_models    = get_option( 'atlas_content_modeler_post_types' );

		self::assertEquals( 200, $request3_response->get_status() );
		self::assertArrayNotHasKey( '123', $updated_models[ $model ]['fields'] );
	}

	public function test_different_models_can_have_fields_with_same_slug() {
		wp_set_current_user( 1 );
		$model   = 'rabbits';
		$request = new WP_REST_Request( 'POST', "/{$this->namespace}/{$this->route}" );
		$request->set_header( 'content-type', 'application/json' );
		$request->set_body( "{\"type\":\"text\",\"id\":\"123\",\"model\":\"{$model}\",\"position\":\"0\",\"name\":\"Name\",\"inputType\":\"single\",\"slug\":\"name\"}" );
		$this->server->dispatch( $request );

		$model    = 'cats';
		$request2 = new WP_REST_Request( 'POST', "/{$this->namespace}/{$this->route}" );
		$request2->set_header( 'content-type', 'application/json' );
		$request2->set_body( "{\"type\":\"text\",\"id\":\"123\",\"model\":\"{$model}\",\"position\":\"0\",\"name\":\"Name\",\"inputType\":\"single\",\"slug\":\"name\"}" );

		$response = $this->server->dispatch( $request2 );
		$data     = $response->get_data();
		$models   = get_option( 'atlas_content_modeler_post_types' );

		$this->assertArrayHasKey( 'success', $data );
		$this->assertEquals( true, $data[ 'success' ] );
		$this->assertArrayHasKey( '123', $models['rabbits']['fields'] );
		$this->assertArrayHasKey( '123', $models['cats']['fields'] );
	}

	public function test_can_update_multiple_fields() {
		wp_set_current_user( 1 );
		$model          = 'dogs';
		$new_field_data = [
			'fields' => [
				'111' => [ 'position' => '10' ],
				'222' => [ 'position' => '20' ],
			],
		];

		$request = new WP_REST_Request( 'PATCH', "/{$this->namespace}/atlas/content-model-fields/{$model}" );
		$request->set_header( 'content-type', 'application/json' );
		$request->set_body( json_encode( $new_field_data ) );
		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();
		$models   = get_option( 'atlas_content_modeler_post_types' );

		$this->assertArrayHasKey( 'success', $data );
		$this->assertEquals( true, $data[ 'success' ] );
		$this->assertEquals( 10, $models[$model][ 'fields' ][ '111' ][ 'position' ] );
		$this->assertEquals( 20, $models[$model][ 'fields' ][ '222' ][ 'position' ] );
	}

	public function test_cannot_update_fields_without_field_data() {
		wp_set_current_user( 1 );
		$model   = 'dogs';
		$request = new WP_REST_Request( 'PATCH', "/{$this->namespace}/atlas/content-model-fields/{$model}" );
		$request->set_header( 'content-type', 'application/json' );
		$request->set_body( json_encode( ['no_field_data' => 'this_should_error' ] ) );

		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		$this->assertEquals( 400, $response->get_status() );
		$this->assertArrayHasKey( 'message', $data );
		$this->assertEquals( 'Expected a fields key with fields to update.', $data[ 'message' ] );
	}

	public function test_cannot_update_fields_of_invalid_model() {
		wp_set_current_user( 1 );
		$model   = 'invalid';
		$request = new WP_REST_Request( 'PATCH', "/{$this->namespace}/atlas/content-model-fields/{$model}" );
		$request->set_header( 'content-type', 'application/json' );
		$request->set_body( json_encode( ['fields' => [ '111' => [ 'position' => '10' ] ] ] ) );

		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		$this->assertEquals( 400, $response->get_status() );
		$this->assertArrayHasKey( 'message', $data );
		$this->assertEquals( 'The specified content model does not exist.', $data[ 'message' ] );
	}

	public function test_cannot_update_field_properties_if_field_id_not_present() {
		wp_set_current_user( 1 );
		$model   = 'dogs';
		$request = new WP_REST_Request( 'PATCH', "/{$this->namespace}/atlas/content-model-fields/{$model}" );
		$request->set_header( 'content-type', 'application/json' );
		$request->set_body( json_encode( ['fields' => [ 'invalid-field-id' => [ 'position' => '10' ] ] ] ) );

		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();
		$models   = get_option( 'atlas_content_modeler_post_types' );

		$this->assertEquals( 200, $response->get_status() );
		$this->assertArrayHasKey( 'success', $data );
		$this->assertEquals( false, $data[ 'success' ] ); // The WP option was not updated.
		$this->assertEquals( $this->test_models[$model], $models[$model] ); // Data is unaltered.
	}

	public function test_setting_new_title_field_removes_existing_one() {
		wp_set_current_user( 1 );
		$model = 'title';

		$request = new WP_REST_Request( 'PUT', "/{$this->namespace}/{$this->route}" );
		$request->set_header( 'content-type', 'application/json' );
		$request->set_body( json_encode( ['id' => '222', 'isTitle' => true, 'model' => $model, 'slug' => 'b' ] ) );

		$response = $this->server->dispatch( $request );
		$models   = get_option( 'atlas_content_modeler_post_types' );

		$this->assertEquals( 200, $response->get_status() );
		$this->assertArrayHasKey( 'isTitle', $models[$model]['fields']['222'] );
		$this->assertArrayNotHasKey( 'isTitle', $models[$model]['fields']['111'] );
	}

	public function test_delete_request_without_model_gives_error(): void {
		wp_set_current_user( 1 );
		$model   = 'rabbits';

		// Send a DELETE request without specifying a model.
		$request = new WP_REST_Request( 'DELETE', "/{$this->namespace}/{$this->route}/123" );
		$request->set_header( 'content-type', 'application/json' );
		$response = $this->server->dispatch( $request );

		self::assertEquals( 400, $response->get_status() );
	}

	public function test_delete_request_with_unknown_model_gives_error(): void {
		wp_set_current_user( 1 );
		$model   = 'invalid';

		// Send the DELETE request with an invalid model.
		$request = new WP_REST_Request( 'DELETE', "/{$this->namespace}/{$this->route}/123" );
		$request->set_header( 'content-type', 'application/json' );
		$request->set_body( "{\"type\":\"text\",\"id\":\"123\",\"model\":\"{$model}\",\"position\":\"0\",\"name\":\"Name\",\"inputType\":\"single\",\"slug\":\"name\"}" );
		$response = $this->server->dispatch( $request );

		self::assertEquals( 400, $response->get_status() );
		self::assertSame( 'wpe_invalid_content_model', $response->get_data()['code'] );
	}

	public function tearDown() {
		parent::tearDown();
		wp_set_current_user( null );
		global $wp_rest_server;
		$wp_rest_server = null;
		$this->server = null;
		delete_option( 'atlas_content_modeler_post_types' );
	}
}
