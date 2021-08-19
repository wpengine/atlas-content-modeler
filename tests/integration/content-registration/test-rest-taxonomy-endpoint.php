<?php

/**
 * Class TestRestTaxonomyEndpoint
 *
 * Verifies plugin REST endpoints for CRUD operations on ACM taxonomies.
 */
class TestRestTaxonomyEndpoint extends WP_UnitTestCase {
	protected $server;

	protected $namespace = 'wpe';

	protected $route = 'atlas/taxonomy';

	protected $taxonomy_option = 'atlas_content_modeler_taxonomies';

	/**
	 * @var array Taxonomies installed during setup.
	 */
	protected $starting_taxonomies = [
		'ingredient' => [
			'slug'     => 'ingredient',
			'singular' => 'Ingredient',
			'plural'   => 'Ingredients',
		],
	];

	/**
	 * @var array Taxonomies used in tests.
	 */
	protected $test_taxonomies = [
		'category' => [
			'slug'     => 'category',
			'singular' => 'Test Existing WP Core Taxonomy',
			'plural'   => 'Test Existing WP Core Taxonomies',
		],
		'ingredient' => [
			'slug'     => 'ingredient',
			'singular' => 'Test Changing Singular Name',
			'plural'   => 'Test Changing Plural Name',
		],
		'new' => [
			'slug'     => 'new',
			'singular' => 'New',
			'plural'   => 'News',
		],
		'missingSlug' => [
			'singular' => 'Missing Slug',
			'plural'   => 'Missing Slugs',
		],
		'missingSingular' => [
			'slug'     => 'missingSingular',
			'plural'   => 'Missing Singulars',
		],
		'missingPlural' => [
			'slug'     => 'missingPlural',
			'singular' => 'Missing Plural',
		],
	];

	public function setUp(): void {
		parent::setUp();
		global $wp_rest_server;
		$this->server = $wp_rest_server = new \WP_REST_Server;
		update_option( $this->taxonomy_option, $this->starting_taxonomies );
		do_action( 'rest_api_init' );
	}

	public function test_taxonomy_routes_are_registered(): void {
		$routes = $this->server->get_routes( 'wpe' );
		// For POST and PUT requests
		self::assertArrayHasKey( "/{$this->namespace}/{$this->route}", $routes );
		// For DELETE requests
		self::assertArrayHasKey( "/{$this->namespace}/{$this->route}/(?P<taxonomy>[\\w-]+)", $routes );
	}

	public function test_can_create_new_taxonomy(): void {
		wp_set_current_user( 1 );

		$request = new WP_REST_Request( 'POST', "/{$this->namespace}/{$this->route}" );
		$request->set_header( 'content-type', 'application/json' );
		$request->set_body( json_encode( $this->test_taxonomies['new'] ) );
		$response = $this->server->dispatch( $request );

		self::assertSame( 200, $response->get_status() );
		self::assertSame( true, $response->data['success'] );
	}

	public function test_can_delete_acm_taxonomy(): void {
		wp_set_current_user( 1 );

		$slug     = $this->starting_taxonomies['ingredient']['slug'];
		$request  = new WP_REST_Request( 'DELETE', "/{$this->namespace}/{$this->route}/{$slug}" );
		$response = $this->server->dispatch( $request );

		self::assertSame( 200, $response->get_status() );
		self::assertSame( true, $response->data['success'] );
	}

	public function test_cannot_delete_non_acm_taxonomy(): void {
		wp_set_current_user( 1 );

		$slug     = 'category';
		$request  = new WP_REST_Request( 'DELETE', "/{$this->namespace}/{$this->route}/{$slug}" );
		$response = $this->server->dispatch( $request );

		self::assertSame( 404, $response->get_status() );
		self::assertSame( 'acm_invalid_taxonomy', $response->data['code'] );
	}

	public function test_cannot_create_taxonomy_when_slug_conflicts_with_existing_core_taxonomy(): void {
		wp_set_current_user( 1 );

		$request = new WP_REST_Request( 'POST', "/{$this->namespace}/{$this->route}" );
		$request->set_header( 'content-type', 'application/json' );
		$request->set_body( json_encode( $this->test_taxonomies['category'] ) );
		$response = $this->server->dispatch( $request );

		self::assertSame( 400, $response->get_status() );
		self::assertSame( 'atlas_content_modeler_taxonomy_exists', $response->data['code'] );
	}

	public function test_cannot_create_taxonomy_when_slug_conflicts_with_existing_acm_taxonomy(): void {
		wp_set_current_user( 1 );

		$request = new WP_REST_Request( 'POST', "/{$this->namespace}/{$this->route}" );
		$request->set_header( 'content-type', 'application/json' );
		$request->set_body( json_encode( $this->test_taxonomies['ingredient'] ) );
		$response = $this->server->dispatch( $request );

		self::assertSame( 400, $response->get_status() );
		self::assertSame( 'atlas_content_modeler_taxonomy_exists', $response->data['code'] );
	}

	public function test_cannot_create_taxonomy_without_slug(): void {
		wp_set_current_user( 1 );

		$request = new WP_REST_Request( 'POST', "/{$this->namespace}/{$this->route}" );
		$request->set_header( 'content-type', 'application/json' );
		$request->set_body( json_encode( $this->test_taxonomies['missingSlug'] ) );
		$response = $this->server->dispatch( $request );

		self::assertSame( 400, $response->get_status() );
		self::assertSame( 'atlas_content_modeler_invalid_id', $response->data['code'] );
	}

	public function test_cannot_create_taxonomy_without_singular_name(): void {
		wp_set_current_user( 1 );

		$request = new WP_REST_Request( 'POST', "/{$this->namespace}/{$this->route}" );
		$request->set_header( 'content-type', 'application/json' );
		$request->set_body( json_encode( $this->test_taxonomies['missingSingular'] ) );
		$response = $this->server->dispatch( $request );

		self::assertSame( 400, $response->get_status() );
		self::assertSame( 'atlas_content_modeler_invalid_labels', $response->data['code'] );
	}

	public function test_cannot_create_taxonomy_without_plural_name(): void {
		wp_set_current_user( 1 );

		$request = new WP_REST_Request( 'POST', "/{$this->namespace}/{$this->route}" );
		$request->set_header( 'content-type', 'application/json' );
		$request->set_body( json_encode( $this->test_taxonomies['missingPlural'] ) );
		$response = $this->server->dispatch( $request );

		self::assertSame( 400, $response->get_status() );
		self::assertSame( 'atlas_content_modeler_invalid_labels', $response->data['code'] );
	}

	public function test_can_update_taxonomy_with_put_request(): void {
		wp_set_current_user( 1 );

		$request = new WP_REST_Request( 'PUT', "/{$this->namespace}/{$this->route}" );
		$request->set_header( 'content-type', 'application/json' );
		$request->set_body( json_encode( $this->test_taxonomies['ingredient'] ) );
		$response = $this->server->dispatch( $request );
		$taxonomies = get_option( $this->taxonomy_option );

		self::assertSame( 200, $response->get_status() );
		self::assertSame( 'Test Changing Singular Name', $taxonomies['ingredient']['singular'] );
		self::assertSame( 'Test Changing Plural Name', $taxonomies['ingredient']['plural'] );
	}

	public function tearDown() {
		parent::tearDown();
		delete_option( $this->taxonomy_option );
	}
}
