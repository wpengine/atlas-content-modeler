<?php
class TestRestContentModelEndpoint extends WP_UnitTestCase {
	protected $server;

	protected $namespace = 'wpe';

	protected $route = 'atlas/content-model';

	protected $test_models = [
		'rabbits'    => [
			'slug'        => 'rabbits',
			'singular'    => 'Rabbit',
			'plural'      => 'Rabbits',
			'description' => 'Rabbits like carrots.'
		],
		'mice'    => [
			'slug'        => 'mice',
			'singular'    => 'Mouse',
			'plural'      => 'Mice'
		],
		'dogs'    => [
			'slug'        => 'dogs',
			'singular'    => 'Dog',
			'plural'      => 'Dogs'
		],
		'cats'    => [ 'name' => 'Cats' ],
		'attachment' => [
			'slug'     => 'attachment',
			'singular' => 'Attachment',
			'plural'   => 'Attachments',
		]
	];

	protected $test_taxonomies = [
		'energylevel' => [
			'slug' => 'energylevel',
			'singular' => 'Energy Level',
			'plural' => 'Energy Levels',
			'types' => ['dogs']
		],
		'furriness' => [
			'slug' => 'furriness',
			'singular' => 'Furriness',
			'plural' => 'Furrinesses',
			'types' => ['rabbits', 'dogs']
		],
		'breeds' => [
			'slug' => 'breeds',
			'singular' => 'Breed',
			'plural' => 'Breeds',
			'types' => [ 'rabbits', 'mice', 'dogs' ]
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

	public function test_can_delete_model(): void
	{
		wp_set_current_user(1);

		$slug     = $this->test_models['rabbits']['slug'];
		$request  = new WP_REST_Request('DELETE', "/{$this->namespace}/{$this->route}/{$slug}");
		$response = $this->server->dispatch($request);

		self::assertSame(200, $response->get_status());
		self::assertSame(true, $response->data['success']);
		self::assertSame($slug, $response->data['model']['slug']);

		$models = get_option('atlas_content_modeler_post_types');

		self::assertArrayNotHasKey($slug, $models);
	}

	public function test_deleting_a_model_removes_taxonomy_associations(): void
	{
		update_option('atlas_content_modeler_taxonomies', $this->test_taxonomies);
		wp_set_current_user(1);

		$slug     = $this->test_models['dogs']['slug'];
		$request  = new WP_REST_Request('DELETE', "/{$this->namespace}/{$this->route}/{$slug}");
		$response = $this->server->dispatch($request);

		self::assertSame(200, $response->get_status());

		$saved_taxonomies = get_option('atlas_content_modeler_taxonomies', array());

		foreach ( $this->test_taxonomies as $tax_slug => $taxonomy ) {
			$expected_count = count($taxonomy['types']) - 1;
			self::assertCount($expected_count, $saved_taxonomies[$tax_slug]['types']);
			self::assertNotContains($slug, $saved_taxonomies[$tax_slug]['types']);
		}
	}
}
