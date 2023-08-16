<?php

use function WPE\AtlasContentModeler\ContentRegistration\get_registered_content_types;
use function WPE\AtlasContentModeler\ContentRegistration\update_registered_content_types;
use \WPE\AtlasContentModeler\ContentConnect\Plugin as ContentConnect;

require_once __DIR__ . '/test-data/fields.php';

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

	public $factory;

	public function set_up(): void {
		parent::set_up();

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

	public function tear_down() {
		global $wp_post_types;
		global $wp_rest_server;
		parent::tear_down();
		wp_set_current_user( null );
		$wp_rest_server = null;
		$this->server   = null;
		$wp_post_types  = null;
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
	public function test_cannot_create_model_when_slug_conflicts_with_existing_acm_model_slug(): void {
		wp_set_current_user( 1 );
		$model = 'public'; // Exists as a model in `tests/integration/api-validation/test-data/models.php`.

		// Attempt to create the model.
		$request = new WP_REST_Request( 'POST', $this->namespace . $this->route );
		$request->set_header( 'content-type', 'application/json' );
		$request->set_body( json_encode( $this->test_models[ $model ] ) );
		$this->server->dispatch( $request );

		$response = $this->server->dispatch( $request );
		self::assertSame( 400, $response->get_status() );
		self::assertSame( 'acm_model_exists', $response->data['code'] );
	}

	public function test_cannot_create_model_when_slug_conflicts_with_reserved_name(): void {
		wp_set_current_user( 1 );

		// Attempt to create the model.
		$request = new WP_REST_Request( 'POST', $this->namespace . $this->route );
		$request->set_header( 'content-type', 'application/json' );
		$request->set_body(
			json_encode(
				[
					'slug'     => 'post', // Reserved name, registration attempt should fail.
					'singular' => 'Post',
					'plural'   => 'Posts',
					'fields'   => [],
				]
			)
		);
		$this->server->dispatch( $request );

		$response = $this->server->dispatch( $request );
		self::assertSame( 400, $response->get_status() );
		self::assertSame( 'acm_model_id_used', $response->data['code'] );
	}

	public function test_cannot_create_model_if_singular_name_is_reserved(): void {
		wp_set_current_user( 1 );
		$model_with_reserved_singular_label = array(
			'slug'     => 'new',
			'singular' => 'post', // 'post' is reserved.
			'plural'   => 'cats',
		);

		// Attempt to create the model.
		$request = new WP_REST_Request( 'POST', $this->namespace . $this->route );
		$request->set_header( 'content-type', 'application/json' );
		$request->set_body( json_encode( $model_with_reserved_singular_label ) );
		$this->server->dispatch( $request );

		$response = $this->server->dispatch( $request );
		self::assertSame( 400, $response->get_status() );
		self::assertSame( 'acm_singular_label_exists', $response->data['code'] );
	}

	public function test_cannot_create_model_if_plural_name_is_reserved(): void {
		wp_set_current_user( 1 );
		$model_with_reserved_singular_label = array(
			'slug'     => 'new',
			'singular' => 'cat',
			'plural'   => 'posts', // 'posts' is reserved.
		);

		// Attempt to create the model.
		$request = new WP_REST_Request( 'POST', $this->namespace . $this->route );
		$request->set_header( 'content-type', 'application/json' );
		$request->set_body( json_encode( $model_with_reserved_singular_label ) );
		$this->server->dispatch( $request );

		$response = $this->server->dispatch( $request );
		self::assertSame( 400, $response->get_status() );
		self::assertSame( 'acm_plural_label_exists', $response->data['code'] );
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
			// Test returned content.
			self::assertSame( $create_test_model[ $key ], $data['model'][ $key ] );
			// Test saved content.
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

	public function test_can_change_model_singular_and_plural_name_case(): void {
		wp_set_current_user( 1 );
		$model = 'public';

		$request  = new WP_REST_Request( 'GET', $this->namespace . $this->route . '/' . $model );
		$response = $this->server->dispatch( $request );

		// Update the model's singular and plural names, but only change the case of both strings.
		$new_model             = $this->test_models[ $model ];
		$new_model['singular'] = 'public'; // Original is 'Public'.
		$new_model['plural']   = 'publics'; // Original is 'Publics'.
		$request               = new WP_REST_Request( 'PATCH', $this->namespace . $this->route . '/' . $model );
		$request->set_header( 'content-type', 'application/json' );
		$request->set_body( json_encode( $new_model ) );

		$response = $this->server->dispatch( $request );
		$models   = get_registered_content_types();

		self::assertSame( 200, $response->get_status() );
		self::assertSame( $new_model['singular'], $models['public']['singular'] );
		self::assertSame( $new_model['plural'], $models['public']['plural'] );
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

	public function test_cannot_update_model_if_singular_name_is_reserved(): void {
		wp_set_current_user( 1 );

		$model = 'public'; // Update an existing model.

		$model_with_reserved_singular_label = array(
			'slug'     => $model,
			'singular' => 'post', // 'post' is reserved.
			'plural'   => 'cats',
		);

		// Attempt to update the model.
		$request = new WP_REST_Request( 'PATCH', $this->namespace . $this->route . '/' . $model );
		$request->set_header( 'content-type', 'application/json' );
		$request->set_body( json_encode( $model_with_reserved_singular_label ) );
		$this->server->dispatch( $request );

		$response = $this->server->dispatch( $request );
		self::assertSame( 400, $response->get_status() );
		self::assertSame( 'acm_singular_label_exists', $response->data['code'] );
	}

	public function test_cannot_update_model_if_plural_name_is_reserved(): void {
		wp_set_current_user( 1 );

		$model = 'public'; // Update an existing model.

		$model_with_reserved_singular_label = array(
			'slug'     => $model,
			'singular' => 'cat',
			'plural'   => 'posts', // 'posts' is reserved.
		);

		// Attempt to update the model.
		$request = new WP_REST_Request( 'PATCH', $this->namespace . $this->route . '/' . $model );
		$request->set_header( 'content-type', 'application/json' );
		$request->set_body( json_encode( $model_with_reserved_singular_label ) );
		$this->server->dispatch( $request );

		$response = $this->server->dispatch( $request );
		self::assertSame( 400, $response->get_status() );
		self::assertSame( 'acm_plural_label_exists', $response->data['code'] );
	}

	/**
	 * Test that all models can be retrieved via REST.
	 */
	public function test_can_get_all_models_via_rest_api(): void {
		wp_set_current_user( 1 );

		$request  = new WP_REST_Request( 'GET', $this->namespace . $this->route . 's' );
		$response = $this->server->dispatch( $request );

		self::assertSame( 200, $response->get_status() );
		self::assertSame( $this->test_models, $response->get_data() );
	}

	/**
	 * Test that all models cannot be retrieved via REST by unauthorized users.
	 */
	public function test_unauthorized_user_cannot_get_all_models_via_rest_api(): void {
		wp_set_current_user( null );

		$request  = new WP_REST_Request( 'GET', $this->namespace . $this->route . 's' );
		$response = $this->server->dispatch( $request );

		self::assertSame( 401, $response->get_status() );
		self::assertSame( $response->get_data()['code'], 'rest_forbidden' );
	}

	public function test_can_delete_a_model(): void {
		wp_set_current_user( 1 );
		$model_to_delete = 'public';
		$models          = get_option( 'atlas_content_modeler_post_types' );
		self::assertArrayHasKey( $model_to_delete, $models );

		// Send DELETE request to the model deletion endpoint.
		$request = new WP_REST_Request(
			'DELETE',
			$this->namespace . $this->route . '/' . $model_to_delete
		);
		$request->set_header( 'content-type', 'application/json' );
		$response = $this->server->dispatch( $request );
		$models   = get_option( 'atlas_content_modeler_post_types' );

		self::assertSame( 200, $response->get_status() );
		self::assertArrayNotHasKey( $model_to_delete, $models );
	}

	public function test_cannot_delete_a_model_without_manage_options_capability(): void {
		wp_set_current_user( null );
		$model_to_delete = 'public';
		$models          = get_option( 'atlas_content_modeler_post_types' );
		self::assertArrayHasKey( $model_to_delete, $models );

		// Send DELETE request to the model deletion endpoint.
		$request = new WP_REST_Request(
			'DELETE',
			$this->namespace . $this->route . '/' . $model_to_delete
		);
		$request->set_header( 'content-type', 'application/json' );
		$response = $this->server->dispatch( $request );
		$models   = get_option( 'atlas_content_modeler_post_types' );

		self::assertSame( 401, $response->get_status() );
	}

	/**
	 * Verifies that relationship fields in other models that refer to the
	 * deleted model are deleted.
	 *
	 * @covers WPE\AtlasContentModeler\REST_API\Relationships\cleanup_detached_relationship_fields
	 */
	public function test_deleting_a_model_removes_related_relationship_fields(): void {
		wp_set_current_user( 1 );
		$model_to_delete          = 'public';
		$model_with_relationships = 'public-fields';
		$relationship_fields      = array_keys(
			array_filter(
				get_test_fields(),
				function( $field ) use ( $model_to_delete ) {
					return $field['type'] === 'relationship' &&
						$field['reference'] === $model_to_delete;
				}
			)
		);

		// Send DELETE request to the model deletion endpoint.
		$request = new WP_REST_Request(
			'DELETE',
			$this->namespace . $this->route . '/' . $model_to_delete
		);
		$request->set_header( 'content-type', 'application/json' );
		$response = $this->server->dispatch( $request );
		$models   = get_option( 'atlas_content_modeler_post_types' );

		self::assertSame( 200, $response->get_status() );

		// Relationship fields for the deleted model should be removed from other models.
		foreach ( $relationship_fields as $field_id ) {
			self::assertArrayNotHasKey(
				$field_id,
				$models[ $model_with_relationships ]['fields']
			);
		}
	}

	/**
	 * Verifies that entries in the post-to-post table are removed when related
	 * models are deleted.
	 *
	 * @covers WPE\AtlasContentModeler\REST_API\Relationships\cleanup_detached_relationship_references
	 */
	public function test_deleting_a_model_removes_post_to_post_data(): void {
		global $wpdb;
		wp_set_current_user( 1 );

		$model_to_delete = 'private';

		// Create posts to relate via an entry in the post-to-post table.
		$post_from_id = $this->factory->post->create(
			[
				'post_title'  => 'Test cat',
				'post_status' => 'publish',
				'post_type'   => 'private',
			]
		);

		$post_to_id = $this->factory->post->create(
			[
				'post_title'  => 'Test dog',
				'post_status' => 'publish',
				'post_type'   => 'public',
			]
		);

		// Manually record a relationship between the two posts.
		$table        = ContentConnect::instance()->get_table( 'p2p' );
		$post_to_post = $table->get_table_name();

		// phpcs:disable
		$wpdb->query(
			$wpdb->prepare(
				"
				INSERT INTO {$post_to_post} (`id1`, `id2`, `name`, `order`)
				VALUES (%s, %s, 'test', 0);
				",
				$post_from_id,
				$post_to_id
			)
		);

		// Confirm the relationship entry was added.
		$relationship_count = $wpdb->prepare(
			"SELECT COUNT(*)
			FROM {$post_to_post}
			WHERE id1 = %s;
			",
			$post_from_id
		);
		self::assertEquals( 1, $wpdb->get_var( $relationship_count ) );
		// phpcs:enable

		// Delete the model whose entry was stored as the 'from' reference.
		// This should trigger a deletion of the recorded relationship.
		$request = new WP_REST_Request(
			'DELETE',
			$this->namespace . $this->route . '/' . $model_to_delete
		);
		$request->set_header( 'content-type', 'application/json' );
		$response = $this->server->dispatch( $request );
		self::assertSame( 200, $response->get_status() );

		// Confirm the row in the post-to-post table was deleted.
		self::assertEquals( 0, $wpdb->get_var( $relationship_count ) ); // phpcs:ignore
	}
}
