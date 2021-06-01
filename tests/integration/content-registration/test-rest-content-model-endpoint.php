<?php
class TestRestContentModelEndpoint extends WP_UnitTestCase {
	protected $server;

	protected $namespace = 'wpe';

	protected $route = 'content-model';

	protected $test_models = [
		'rabbits'    => [
			'slug'        => 'rabbits',
			'singular'    => 'Rabbit',
			'plural'      => 'Rabbits',
			'description' => 'Rabbits like carrots.'
		],
		'cats'    => [ 'name' => 'Cats' ],
		'attachment' => [
			'slug'     => 'attachment',
			'singular' => 'Attachment',
			'plural'   => 'Attachments',
		]
	];

	public function setUp(): void {
		parent::setUp();
		global $wp_rest_server;
		$this->server = $wp_rest_server = new \WP_REST_Server;
		do_action( 'rest_api_init' );
		update_option('atlas_content_modeler_post_types', $this->test_models );
	}

	public function test_content_model_route_is_registered(): void {
		$routes = $this->server->get_routes( 'wpe' );
		self::assertArrayHasKey( "/{$this->namespace}/{$this->route}", $routes );
	}

	public function test_cannot_create_model_when_slug_conflicts_with_existing_post_type(): void {
		wp_set_current_user( 1 );
		$model = 'attachment'; // already exists by default in WP.

		// Attempt to create the model.
		$request = new WP_REST_Request( 'POST', "/{$this->namespace}/{$this->route}" );
		$request->set_header( 'content-type', 'application/json' );
		$request->set_body( json_encode( $this->test_models[ $model ] ) );
		$this->server->dispatch( $request );

		$response = $this->server->dispatch( $request );
		self::assertSame( 400, $response->get_status() );
		self::assertSame( 'atlas_content_modeler_already_exists', $response->data['code'] );
	}

	public function test_can_update_model(): void {
		wp_set_current_user( 1 );
		$model   = 'rabbits';

		// First request to create model.
		$request = new WP_REST_Request( 'POST', "/{$this->namespace}/{$this->route}" );
		$request->set_header( 'content-type', 'application/json' );
		$request->set_body( json_encode( $this->test_models[ $model ] ) );
		$this->server->dispatch( $request );

		// Second request to update model.
		$new_model = $this->test_models['rabbits'];
		$new_model['description'] = 'This is a new description of rabbits';
		$request2 = new WP_REST_Request( 'PATCH', "/{$this->namespace}/{$this->route}/{$model}" );
		$request2->set_header( 'content-type', 'application/json' );
		$request2->set_body( json_encode( $new_model ) );

		$response = $this->server->dispatch( $request2 );
		$models   = get_option( 'atlas_content_modeler_post_types' );

		self::assertSame( 200, $response->get_status() );
		self::assertSame( 'This is a new description of rabbits', $models['rabbits']['description'] );
	}

	public function test_cannot_update_model_with_invalid_data(): void {
		wp_set_current_user( 1 );
		$model   = 'rabbits';

		// First request to create model.
		$request = new WP_REST_Request( 'POST', "/{$this->namespace}/{$this->route}" );
		$request->set_header( 'content-type', 'application/json' );
		$request->set_body( json_encode( $this->test_models['rabbits'] ) );
		$this->server->dispatch( $request );

		// Second request to update model.
		$new_model = $this->test_models['rabbits'];
		unset( $new_model['plural'] ); // To make it an invalid request.
		$request2 = new WP_REST_Request( 'PATCH', "/{$this->namespace}/{$this->route}/{$model}" );
		$request2->set_header( 'content-type', 'application/json' );
		$request2->set_body( json_encode( $new_model ) );

		$response = $this->server->dispatch( $request2 );
		self::assertSame( 400, $response->get_status() );
	}
}
