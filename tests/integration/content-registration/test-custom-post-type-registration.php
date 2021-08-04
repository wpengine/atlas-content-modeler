<?php
/**
 * Class PostTypeRegistrationTestCases
 *
 * @package AtlasContentModeler
 */

use function WPE\AtlasContentModeler\ContentRegistration\generate_custom_post_type_args;
use function \WPE\AtlasContentModeler\ContentRegistration\generate_custom_post_type_labels;
use PHPUnit\Runner\Exception as PHPUnitRunnerException;
use function WPE\AtlasContentModeler\ContentRegistration\update_registered_content_types;
use function WPE\AtlasContentModeler\ContentRegistration\is_protected_meta;

/**
 * Post type registration case.
 */
class PostTypeRegistrationTestCases extends WP_UnitTestCase {

	/**
	 * The REST API server instance.
	 *
	 * @var \WP_REST_Server
	 */
	private $server;
	private $namespace = '/wp/v2';
	private $dog_route = '/dogs';
	private $dog_post_id;
	private $dog_image_id;
	private $dog_pdf_id;
	private $draft_post_id;
	private $cat_post_id;
	private $goose_post_id;
	private $all_registered_post_types;

	public function setUp() {
		parent::setUp();

		/**
		 * Reset the WPGraphQL schema before each test.
		 * Lazy loading types only loads part of the schema,
		 * so we refresh for each test.
		 */
		WPGraphQL::clear_schema();

		update_registered_content_types( $this->mock_post_types() );

		// @todo why is this not running automatically?
		do_action( 'init' );

		$this->all_registered_post_types = get_post_types( [], 'objects' );

		/**
		 * WP_Rest_Server instance.
		 */
		global $wp_rest_server;

		$wp_rest_server = new \WP_REST_Server();

		$this->server = $wp_rest_server;

		do_action( 'rest_api_init' );

		$this->dog_post_id = $this->factory->post->create( [
			'post_title' => 'Test dog',
			'post_content' => 'Hello dog',
			'post_status' => 'publish',
			'post_type' => 'dog',
		] );

		$this->draft_post_id = self::factory()->post->create( [
			'post_title' => 'Draft dog',
			'post_content' => 'This dog has a status of draft',
			'post_status' => 'draft',
			'post_type' => 'dog',
		] );

		$this->dog_image_id = $this->factory->attachment->create( array(
			'post_mime_type' => 'image/png',
			'post_title' => 'dog_image',
		) );

		$this->dog_pdf_id = $this->factory->attachment->create( array(
			'post_mime_type' => 'application/pdf',
			'post_title' => 'dog_pdf',
		) );

		update_post_meta( $this->dog_post_id, 'dog-test-field', 'dog-test-field string value' );
		update_post_meta( $this->dog_post_id, 'dog-weight', '100.25' );
		update_post_meta( $this->dog_post_id, 'dog-rich-text', 'dog-rich-text string value' );
		update_post_meta( $this->dog_post_id, 'dog-boolean', 'this string will be cast to a boolean by WPGraphQL' );
		update_post_meta( $this->dog_post_id, 'dog-image', $this->dog_image_id );
		update_post_meta( $this->dog_post_id, 'dog-pdf', $this->dog_pdf_id );

		$media_meta = array(
			'width' => 1000,
			'height' => 1000,
			'file' => '2021/06/chris-avatar_PNG-bg.png',
			'sizes' => array(
				'medium' => array(
					'file' => 'chris-avatar_PNG-bg-300x300.png',
					'width' => 300,
					'height' => 300,
					'mime-type' => 'image/png',
				),
			),
		);

		update_post_meta( $this->dog_image_id, '_wp_attachment_metadata', $media_meta );
		update_post_meta( $this->dog_image_id, '_wp_attachment_image_alt', 'This is alt text' );
		update_post_meta( $this->dog_image_id, '_wp_attached_file', '2021/06/chris-avatar_PNG-bg.png' );

		$this->cat_post_id = self::factory()->post->create( [
			'post_title' => 'Test cat',
			'post_content' => 'Hello cat',
			'post_status' => 'publish',
			'post_type' => 'cat',
		] );

		$this->goose_post_id = self::factory()->post->create( [
			'post_title' => 'Test goose',
			'post_content' => 'Hello goose',
			'post_status' => 'publish',
			'post_type' => 'goose',
		] );
	}

	public function tearDown() {
		parent::tearDown();
		wp_set_current_user( null );
		global $wp_rest_server;
		$wp_rest_server = null;
		$this->server = null;
		delete_option( 'atlas_content_modeler_post_types' );
		$this->all_registered_post_types = null;
	}

	public function test_dog_post_type_accessible_via_rest_api(): void {
		wp_set_current_user( 1 );
		$request  = new \WP_REST_Request( 'GET', $this->namespace . $this->dog_route . '/' . $this->dog_post_id );
		$response = $this->server->dispatch( $request );
		$response_data = $response->get_data();
		$this->assertSame( $response_data['title']['rendered'], 'Test dog' );
	}

	public function test_draft_posts_for_models_with_public_api_visibility_cannot_be_read_via_rest_api_when_not_authenticated(): void {
		wp_set_current_user( null );
		$request  = new \WP_REST_Request( 'GET', $this->namespace . $this->dog_route . '/' . $this->draft_post_id );
		$response = $this->server->dispatch( $request );
		self::assertTrue( $response->is_error() );
	}

	public function test_draft_posts_for_models_with_public_api_visibility_can_be_read_via_rest_api_when_authenticated(): void {
		wp_set_current_user( 1 );
		$request  = new \WP_REST_Request( 'GET', $this->namespace . $this->dog_route . '/' . $this->draft_post_id );
		$response = $this->server->dispatch( $request );
		$response_data = $response->get_data();
		self::assertSame( $response->get_status(), 200 );
		self::assertSame( $response_data['title']['rendered'], 'Draft dog' );
	}

	public function test_post_type_with_private_api_visibility_cannot_be_read_via_rest_api_when_not_authenticated(): void {
		$request  = new \WP_REST_Request( 'GET', $this->namespace . '/cats' . '/' . $this->cat_post_id );
		$response = $this->server->dispatch( $request );
		self::assertTrue( $response->is_error() );
	}

	public function test_post_type_with_private_api_visibility_can_be_read_via_rest_api_when_authenticated(): void {
		wp_set_current_user( 1 );
		$request  = new \WP_REST_Request( 'GET', $this->namespace . '/cats' . '/' . $this->cat_post_id );
		$response = $this->server->dispatch( $request );
		$response_data = $response->get_data();
		self::assertSame( $response->get_status(), 200 );
		self::assertSame( $response_data['title']['rendered'], 'Test cat' );
	}

	public function test_post_type_with_private_api_visibility_cannot_be_read_via_graphql_when_not_authenticated(): void {
		try {
			$results = graphql( [
				'query' => '
				{
					geese {
						nodes {
							databaseId
						}
					}
				}
				'
			] );

			self::assertEmpty( $results['data']['geese']['nodes'] );
		} catch ( Exception $exception ) {
			throw new PHPUnitRunnerException( sprintf( __FUNCTION__ . ' failed with exception: %s', $exception->getMessage() ) );
		}
	}

	public function test_post_type_with_private_api_visibility_can_be_read_via_graphql_when_authenticated(): void {
		wp_set_current_user( 1 );
		try {
			$results = graphql( [
				'query' => '
				{
					geese {
						nodes {
							databaseId
						}
					}
				}
				'
			] );

			self::assertSame( $results['data']['geese']['nodes'][0]['databaseId'], $this->goose_post_id );
		} catch ( Exception $exception ) {
			throw new PHPUnitRunnerException( sprintf( __FUNCTION__ . ' failed with exception: %s', $exception->getMessage() ) );
		}
	}

	public function test_post_meta_that_is_configured_to_show_in_rest_is_accessible(): void {
		wp_set_current_user( 1 );
		$request  = new \WP_REST_Request( 'GET', $this->namespace . $this->dog_route . '/' . $this->dog_post_id );
		$response = $this->server->dispatch( $request );
		$response_data = $response->get_data();

		self::assertArrayHasKey( 'acm_fields', $response_data );

		self::assertArrayHasKey( 'dog-test-field', $response_data['acm_fields'] );
		self::assertSame( $response_data['acm_fields']['dog-test-field'], 'dog-test-field string value' );

		self::assertArrayHasKey( 'dog-weight', $response_data['acm_fields'] );
		self::assertEquals( '100.25', $response_data['acm_fields']['dog-weight'] );

		self::assertArrayHasKey( 'dog-image', $response_data['acm_fields'] );
		self::assertArrayHasKey( 'dog-pdf', $response_data['acm_fields'] );
	}

	public function test_post_meta_that_is_configured_to_not_show_in_rest_is_not_accessible(): void {
		wp_set_current_user( 1 );
		$request  = new \WP_REST_Request( 'GET', $this->namespace . $this->dog_route . '/' . $this->dog_post_id );
		$response = $this->server->dispatch( $request );
		$response_data = $response->get_data();
		$this->assertFalse( array_key_exists( 'another-dog-test-field', $response_data['acm_fields'] ) );
	}

	public function test_post_meta_media_field_rest_response(): void {
		wp_set_current_user( 1 );
		$request  = new \WP_REST_Request( 'GET', $this->namespace . $this->dog_route . '/' . $this->dog_post_id );
		$response = $this->server->dispatch( $request );
		$response_data = $response->get_data();

		self::assertArrayHasKey( 'acm_fields', $response_data );
		self::assertArrayHasKey( 'dog-image', $response_data['acm_fields'] );
		self::assertArrayHasKey( 'dog-pdf', $response_data ['acm_fields'] );

		$image = $response_data['acm_fields']['dog-image'];
		$file = $response_data['acm_fields']['dog-pdf'];
		$expected_keys = [
			'caption',
			'alt_text',
			'media_type',
			'mime_type',
			'media_details',
			'source_url',
		];

		// Images and files have same structure
		foreach ( $expected_keys as $key ) {
			self::assertArrayHasKey( $key, $image );
			self::assertArrayHasKey( $key, $file );
		}

		// Images
		self::assertArrayHasKey( 'rendered', $image['caption'] );
		self::assertEquals( 'image', $image['media_type'] );
		self::assertEquals( 'image/png', $image['mime_type'] );
		self::assertEquals( 4, count( $image['media_details'] ) );
		self::assertArrayHasKey( 'sizes', $image['media_details'] );
		self::assertEquals( 2, count( $image['media_details']['sizes'] ) );

		// Files
		self::assertArrayHasKey( 'rendered', $file['caption'] );
		self::assertEquals( 'file', $file['media_type'] );
		self::assertEquals( 'application/pdf', $file['mime_type'] );
		self::assertInstanceOf( 'stdClass', $file['media_details'] );
	}

	/**
	 * @covers ::\WPE\AtlasContentModeler\ContentRegistration\register_content_types()
	 */
	public function test_content_registration_init_hook(): void {
		$this->assertSame( 10, has_action( 'init', 'WPE\AtlasContentModeler\ContentRegistration\register_content_types' ) );
	}

	public function test_defined_custom_post_types_are_registered(): void {
		$this->assertArrayHasKey( 'cat', $this->all_registered_post_types );
		$this->assertArrayHasKey( 'dog', $this->all_registered_post_types );
	}

	public function test_custom_post_type_labels_match_expected_format(): void {
		$labels = generate_custom_post_type_labels( [
			'singular' => 'Dog',
			'plural'   => 'Dogs',
		] );

		$this->assertSame( $labels, $this->expected_post_types()['dog']['labels'] );
	}

	public function test_defined_custom_post_types_have_show_in_graphql_argument(): void {
		$this->assertTrue( $this->all_registered_post_types['dog']->show_in_graphql );
		$this->assertFalse( $this->all_registered_post_types['cat']->show_in_graphql );
	}

	public function test_graphql_query_result_has_custom_fields_data(): void {
		try {
			$results = graphql( [
				'query' => '
				{
					dogs {
						nodes {
							databaseId
							title
							content
							dogTestField
							dogWeight
							dogRichText
							dogBoolean
						}
					}
				}
				'
			] );

			self::assertArrayHasKey( 'dogTestField', $results['data']['dogs']['nodes'][0] );
			self::assertSame( $results['data']['dogs']['nodes'][0]['dogTestField'], 'dog-test-field string value' );

			self::assertArrayHasKey( 'dogWeight', $results['data']['dogs']['nodes'][0] );
			self::assertSame( $results['data']['dogs']['nodes'][0]['dogWeight'], 100.25 );

			self::assertArrayHasKey( 'dogRichText', $results['data']['dogs']['nodes'][0] );
			self::assertSame( $results['data']['dogs']['nodes'][0]['dogRichText'], 'dog-rich-text string value' );

			self::assertArrayHasKey( 'dogBoolean', $results['data']['dogs']['nodes'][0] );
			self::assertTrue( $results['data']['dogs']['nodes'][0]['dogBoolean'] );

		} catch ( Exception $exception ) {
			throw new PHPUnitRunnerException( sprintf( __FUNCTION__ . ' failed with exception: %s', $exception->getMessage() ) );
		}
	}

	public function test_generate_custom_post_type_args_throws_exception_when_invalid_arguments_passed(): void {
		self::expectException( \InvalidArgumentException::class );
		generate_custom_post_type_args( [] );
	}

	public function test_generate_custom_post_type_args_generates_expected_data(): void {
		$generated_args = generate_custom_post_type_args( [ 'singular' => 'Dog', 'plural' => 'Dogs', 'model_icon' => 'dashicons-saved' ] );
		$expected_args = $this->expected_post_types()['dog'];
		unset( $expected_args['fields'] );
		self::assertSame( $generated_args, $expected_args );

		$generated_args = generate_custom_post_type_args( [ 'singular' => 'Cat', 'plural' => 'Cats', 'show_in_graphql' => false ] );
		$expected_args = $this->expected_post_types()['cat'];
		self::assertSame( $generated_args, $expected_args );
	}

	/**
	 * @covers ::\WPE\AtlasContentModeler\ContentRegistration\is_protected_meta()
	 */
	public function test_is_protected_meta_hook(): void {
		$this->assertSame( 10, has_action( 'is_protected_meta', 'WPE\AtlasContentModeler\ContentRegistration\is_protected_meta' ) );
	}

	/**
	 * @covers ::\WPE\AtlasContentModeler\ContentRegistration\is_protected_meta()
	 */
	public function test_model_fields_are_protected(): void {
		$fields = $this->mock_post_types()['dog']['fields'];
		$slugs = array_keys( $fields );
		foreach ( $slugs as $slug ) {
			self::assertTrue( is_protected_meta( false, $slug, 'post' ) );
		}
	}

	/**
	 * @covers ::\WPE\AtlasContentModeler\ContentRegistration\is_protected_meta()
	 */
	public function test_fields_not_attached_to_a_model_are_not_affected(): void {
		self::assertFalse( is_protected_meta( false, 'this-key-is-unprotected-and-not-ours-and-should-remain-unprotected', 'post' ) );
		self::assertTrue( is_protected_meta( true, 'this-key-is-already-protected-and-should-remain-protected', 'post' ) );
	}

	private function expected_post_types(): array {
		return include __DIR__ . '/example-data/expected-post-types.php';
	}

	private function mock_post_types(): array {
		return [
			'dog' => [
				'slug' => 'dog',
				'singular' => 'Dog',
				'plural' => 'Dogs',
				'description' => '',
				'show_in_rest' => true,
				'show_in_graphql' => true,
				'api_visibility' => 'public',
				'fields' => [
					'dog-test-field' => [
						'slug' => 'dog-test-field',
						'type' => 'string',
						'description' => 'dog-test-field description',
						'show_in_rest' => true,
						'show_in_graphql' => true,
					],
					'another-dog-test-field' => [
						'slug' => 'another-dog-test-field',
						'type' => 'string',
						'description' => 'another-dog-test-field description',
						'show_in_rest' => false,
						'show_in_graphql' => false,
					],
					'dog-weight' => [
						'slug' => 'dog-weight',
						'type' => 'number',
						'description' => 'dog-weight description',
						'show_in_rest' => true,
						'show_in_graphql' => true,
					],
					'dog-rich-text' => [
						'slug' => 'dog-rich-text',
						'type' => 'richtext',
						'description' => 'dog-rich-text description',
						'show_in_rest' => true,
						'show_in_graphql' => true,
					],
					'dog-boolean' => [
						'slug' => 'dog-boolean',
						'type' => 'boolean',
						'description' => 'dog-boolean description',
						'show_in_rest' => true,
						'show_in_graphql' => true,
					],
					'dog-image' => [
						'slug' => 'dog-image',
						'type' => 'media',
						'description' => 'dog-image description',
						'show_in_rest' => true,
						'show_in_graphql' => true,
					],
					'dog-pdf' => [
						'slug' => 'dog-pdf',
						'type' => 'media',
						'description' => 'dog-pdf description',
						'show_in_rest' => true,
						'show_in_graphql' => true,
					],
				],
			],
			'cat' => [
				'slug' => 'cat',
				'singular' => 'Cat',
				'plural' => 'Cats',
				'description' => '',
				'show_in_graphql' => false,
				'api_visibility' => 'private',
				'fields' => [],
			],
			'goose' => [
				'slug' => 'goose',
				'singular' => 'Goose',
				'plural' => 'Geese',
				'show_in_graphql' => true,
				'api_visibility' => 'private',
				'fields' => [],
			]
		];
	}
}
