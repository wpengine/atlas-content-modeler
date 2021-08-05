<?php

use function WPE\AtlasContentModeler\ContentRegistration\update_registered_content_types;

class RestFieldEndpointTests extends WP_UnitTestCase {

	/**
	 * The REST API server instance.
	 *
	 * @var \WP_REST_Server
	 */
	private $server;
	private $namespace = '/wpe';
	private $route = '/atlas/content-model-field';
	private $test_models;

	public function setUp(): void {
		parent::setUp();

		$this->test_models = $this->get_models();

		update_registered_content_types( $this->test_models );

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
	 * Test the ability to add a field to a model via REST
	 */
	public function test_posting_fields_stores_them() {
		wp_set_current_user( 1 );
		$model   = 'public-fields';
		$request = new WP_REST_Request( 'POST', $this->namespace . $this->route );
		$request->set_header( 'content-type', 'application/json' );
		$field = array(
			'type'      => 'text',
			'id'        => '123',
			'model'     => $model,
			'position'  => '0',
			'name'      => 'Name',
			'inputType' => 'single',
			'slug'      => 'name',
		);
		$request->set_body( json_encode( $field ) );

		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();
		$models   = get_option( 'atlas_content_modeler_post_types' );

		$this->assertEquals( 200, $response->get_status() );
		$this->assertArrayHasKey( 'success', $data );
		$this->assertEquals( true, $data['success'] );
		$this->assertArrayHasKey( '123', $models[ $model ]['fields'] );
	}

	/**
	 * Test adding field to non-existent model throws appropriate error
	 */
	public function test_posting_fields_to_unknown_model_gives_error() {
		wp_set_current_user( 1 );
		$model   = 'nomodel';
		$request = new WP_REST_Request( 'POST', $this->namespace . $this->route );
		$request->set_header( 'content-type', 'application/json' );
		$field = array(
			'type'      => 'text',
			'id'        => '123',
			'model'     => $model,
			'position'  => '0',
			'name'      => 'Name',
			'inputType' => 'single',
			'slug'      => 'name',
		);
		$request->set_body( json_encode( $field ) );

		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		$this->assertEquals( 400, $response->get_status() );
		$this->assertArrayHasKey( 'message', $data );
		$this->assertEquals( 'The specified content model does not exist.', $data['message'] );
	}

	/**
	 * Test we can't add a field with a duplicate slug to a given model
	 */
	public function test_field_slugs_must_be_unique_to_each_model() {
		wp_set_current_user( 1 );
		$model   = 'public-fields';
		$request = new WP_REST_Request( 'POST', $this->namespace . $this->route );
		$request->set_header( 'content-type', 'application/json' );
		$field1 = array(
			'type'      => 'text',
			'id'        => '123',
			'model'     => $model,
			'position'  => '0',
			'name'      => 'Name',
			'inputType' => 'single',
			'slug'      => 'name',
		);
		$request->set_body( json_encode( $field1 ) );
		$this->server->dispatch( $request );

		// Send a second request with the same slug but a new ID. It should fail to update because the slugs collide.
		$field2 = array(
			'type'      => 'text',
			'id'        => '456',
			'model'     => $model,
			'position'  => '0',
			'name'      => 'Name',
			'inputType' => 'single',
			'slug'      => 'name',
		);
		$request->set_body( json_encode( $field2 ) );
		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		$this->assertEquals( 400, $response->get_status() );
		$this->assertArrayHasKey( 'message', $data );
		$this->assertEquals( 'Another field in this model has the same API identifier.', $data['message'] );
	}

	/**
	 * Test crud operations for a field
	 */
	public function test_field_can_be_created_and_updated_and_deleted() {
		wp_set_current_user( 1 );
		$model = 'public-fields';

		// First request to create the field.
		$request = new WP_REST_Request( 'POST', $this->namespace . $this->route );
		$request->set_header( 'content-type', 'application/json' );
		$field = array(
			'type'      => 'text',
			'id'        => '123',
			'model'     => $model,
			'position'  => '0',
			'name'      => 'Name',
			'inputType' => 'single',
			'slug'      => 'name',
		);
		$request->set_body( json_encode( $field ) );
		$this->server->dispatch( $request );

		// Second request to update the field name.
		$request2 = new WP_REST_Request( 'PUT', $this->namespace . $this->route );
		$request2->set_header( 'content-type', 'application/json' );
		$field2 = array(
			'type'      => 'text',
			'id'        => '123',
			'model'     => $model,
			'position'  => '0',
			'name'      => 'New Name',
			'inputType' => 'single',
			'slug'      => 'name',
		);
		$request2->set_body( json_encode( $field2 ) );

		$response = $this->server->dispatch( $request2 );
		$models   = get_option( 'atlas_content_modeler_post_types' );

		$this->assertEquals( 200, $response->get_status() );
		$this->assertEquals( 'New Name', $models[ $model ]['fields']['123']['name'] );

		// Third request to delete the field.
		$request3 = new WP_REST_Request( 'DELETE', $this->namespace . $this->route . '/123' );
		$request3->set_header( 'content-type', 'application/json' );
		$body = array(
			'model' => $model,
		);
		$request3->set_body( json_encode( $body ) );

		$request3_response = $this->server->dispatch( $request3 );
		$updated_models    = get_option( 'atlas_content_modeler_post_types' );

		self::assertEquals( 200, $request3_response->get_status() );
		self::assertArrayNotHasKey( '123', $updated_models[ $model ]['fields'] );
	}

	/**
	 * Test fields with the same slug can exist on different models.
	 */
	public function test_different_models_can_have_fields_with_same_slug() {
		wp_set_current_user( 1 );
		$model   = 'public';
		$request = new WP_REST_Request( 'POST', $this->namespace . $this->route );
		$request->set_header( 'content-type', 'application/json' );
		$field = array(
			'type'      => 'text',
			'id'        => '123',
			'model'     => $model,
			'position'  => '0',
			'name'      => 'Name',
			'inputType' => 'single',
			'slug'      => 'name',
		);
		$request->set_body( json_encode( $field ) );
		$this->server->dispatch( $request );

		$model2   = 'public-fields';
		$field2   = array(
			'type'      => 'text',
			'id'        => '123',
			'model'     => $model2,
			'position'  => '0',
			'name'      => 'Name',
			'inputType' => 'single',
			'slug'      => 'name',
		);
		$request2 = new WP_REST_Request( 'POST', $this->namespace . $this->route );
		$request2->set_header( 'content-type', 'application/json' );
		$request2->set_body( json_encode( $field2 ) );

		$response = $this->server->dispatch( $request2 );
		$data     = $response->get_data();
		$models   = get_option( 'atlas_content_modeler_post_types' );

		$this->assertArrayHasKey( 'success', $data );
		$this->assertEquals( true, $data['success'] );
		$this->assertArrayHasKey( '123', $models['public']['fields'] );
		$this->assertArrayHasKey( '123', $models['public-fields']['fields'] );
	}

	/**
	 * Test updating multiple fields in a single request
	 */
	public function test_can_update_multiple_fields() {
		wp_set_current_user( 1 );
		$model          = 'public-fields';
		$new_field_data = [
			'fields' => [
				'1628084952497' => [ 'position' => '10' ],
				'1628084963946' => [ 'position' => '20' ],
			],
		];

		$request = new WP_REST_Request( 'PATCH', $this->namespace . $this->route . 's/' . $model );
		$request->set_header( 'content-type', 'application/json' );
		$request->set_body( json_encode( $new_field_data ) );
		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		$models = get_option( 'atlas_content_modeler_post_types' );

		$this->assertArrayHasKey( 'success', $data );
		$this->assertEquals( true, $data['success'] );
		$this->assertEquals( 10, $models[ $model ]['fields']['1628084952497']['position'] );
		$this->assertEquals( 20, $models[ $model ]['fields']['1628084963946']['position'] );
	}

	/**
	 * Ensures we can't update a field without valid field data
	 */
	public function test_cannot_update_fields_without_field_data() {
		wp_set_current_user( 1 );
		$model   = 'public';
		$request = new WP_REST_Request( 'PATCH', $this->namespace . $this->route . 's/' . $model );
		$request->set_header( 'content-type', 'application/json' );
		$request->set_body( json_encode( array( 'no_field_data' => 'this_should_error' ) ) );

		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		$this->assertEquals( 400, $response->get_status() );
		$this->assertArrayHasKey( 'message', $data );
		$this->assertEquals( 'Expected a fields key with fields to update.', $data['message'] );
	}

	/**
	 * Ensure attempting to edit fields on an invalid model fails appropriately
	 */
	public function test_cannot_update_fields_of_invalid_model() {
		wp_set_current_user( 1 );
		$model   = 'invalid';
		$request = new WP_REST_Request( 'PATCH', $this->namespace . $this->route . 's/' . $model );
		$request->set_header( 'content-type', 'application/json' );
		$request->set_body( json_encode( array( 'fields' => array( '111' => array( 'position' => '10' ) ) ) ) );

		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();

		$this->assertEquals( 400, $response->get_status() );
		$this->assertArrayHasKey( 'message', $data );
		$this->assertEquals( 'The specified content model does not exist.', $data['message'] );
	}

	/**
	 * Ensure we cannot update a non-existent field
	 */
	public function test_cannot_update_field_properties_if_field_id_not_present() {
		wp_set_current_user( 1 );
		$model   = 'public-fields';
		$request = new WP_REST_Request( 'PATCH', $this->namespace . $this->route . 's/' . $model );
		$request->set_header( 'content-type', 'application/json' );
		$request->set_body( json_encode( array( 'fields' => array( '111' => array( 'position' => '10' ) ) ) ) );

		$response = $this->server->dispatch( $request );
		$data     = $response->get_data();
		$models   = get_option( 'atlas_content_modeler_post_types' );

		$this->assertEquals( 200, $response->get_status() );
		$this->assertArrayHasKey( 'success', $data );
		$this->assertEquals( false, $data['success'] ); // The WP option was not updated.
		$this->assertEquals( $this->test_models[ $model ], $models[ $model ] ); // Data is unaltered.
	}

	/**
	 * Ensure setting a new field as a title field removes the previous title field
	 */
	public function test_setting_new_title_field_removes_existing_one() {
		wp_set_current_user( 1 );
		$model = 'public-fields';

		$request = new WP_REST_Request( 'PUT', $this->namespace . $this->route );
		$request->set_header( 'content-type', 'application/json' );
		$request->set_body( json_encode( array( 'id'      => '222',
		                                        'isTitle' => true,
		                                        'model'   => $model,
		                                        'slug'    => 'b'
		) ) );

		$response = $this->server->dispatch( $request );
		$models   = get_option( 'atlas_content_modeler_post_types' );

		$this->assertEquals( 200, $response->get_status() );
		$this->assertArrayHasKey( 'isTitle', $models[ $model ]['fields']['222'] );
		$this->assertArrayNotHasKey( 'isTitle', $models[ $model ]['fields']['1628084420404'] );
	}

	/**
	 * Ensure we cannot delete an existing field if we do not specify a model
	 */
	public function test_delete_request_without_model_gives_error(): void {
		wp_set_current_user( 1 );

		// Send a DELETE request without specifying a model.
		$request = new WP_REST_Request( 'DELETE', $this->namespace . $this->route . '/1628083572151' );
		$request->set_header( 'content-type', 'application/json' );
		$response = $this->server->dispatch( $request );

		self::assertEquals( 400, $response->get_status() );
	}

	/**
	 * Ensure we can't delete a known field on an invalid model
	 */
	public function test_delete_request_with_unknown_model_gives_error(): void {
		wp_set_current_user( 1 );
		$model = 'invalid';

		// Send the DELETE request with an invalid model.
		$request = new WP_REST_Request( 'DELETE', $this->namespace . $this->route . '/1628083572151' );
		$request->set_header( 'content-type', 'application/json' );
		$field = array(
			'model' => $model,
		);
		$request->set_body( json_encode( $field ) );
		$response = $this->server->dispatch( $request );

		self::assertEquals( 400, $response->get_status() );
		self::assertSame( 'wpe_invalid_content_model', $response->get_data()['code'] );
	}
}
