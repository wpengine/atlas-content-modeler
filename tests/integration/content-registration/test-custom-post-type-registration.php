<?php
/**
 * Class PostTypeRegistrationTestCases
 *
 * @package WPE_Content_Model
 */

use function WPE\ContentModel\ContentRegistration\generate_custom_post_type_args;
use function \WPE\ContentModel\ContentRegistration\generate_custom_post_type_labels;
use PHPUnit\Runner\Exception as PHPUnitRunnerException;
use function WPE\ContentModel\ContentRegistration\update_registered_content_types;

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
	private $dog_route = '/dog';
	private $dog_post_id;
	private $all_registered_post_types;

	public function setUp() {
		parent::setUp();

		update_registered_content_types( $this->expected_post_types() );

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
			'status' => 'publish',
			'post_type' => 'dog',
		] );

		update_post_meta( $this->dog_post_id, '_dog-test-field', 'dog-test-field string value' );
		update_post_meta( $this->dog_post_id, '_dog-weight', '100.25' );
		update_post_meta( $this->dog_post_id, '_dog-rich-text', 'dog-rich-text string value' );
		update_post_meta( $this->dog_post_id, '_dog-boolean', 'this string will be cast to a boolean by WPGraphQL' );
		update_post_meta( $this->dog_post_id, '_dog-repeater', 'dog-repeater string value' );
	}

	public function tearDown() {
		parent::tearDown();
		wp_set_current_user( null );
		global $wp_rest_server;
		$wp_rest_server = null;
		$this->server = null;
		delete_option( 'wpe_content_model_post_types' );
		$this->all_registered_post_types = null;
	}

	public function test_dog_post_type_accessible_via_rest_api(): void {
		wp_set_current_user( 1 );
		$request  = new \WP_REST_Request( 'GET', $this->namespace . $this->dog_route . '/' . $this->dog_post_id );
		$response = $this->server->dispatch( $request );
		$response_data = $response->get_data();
		$this->assertSame( $response_data['title']['rendered'], 'Test dog' );
	}

	public function test_post_meta_that_is_configured_to_show_in_rest_is_accessible(): void {
		wp_set_current_user( 1 );
		$request  = new \WP_REST_Request( 'GET', $this->namespace . $this->dog_route . '/' . $this->dog_post_id );
		$response = $this->server->dispatch( $request );
		$response_data = $response->get_data();
		$this->assertArrayHasKey( '_dog-test-field', $response_data['meta'] );
		$this->assertSame( $response_data['meta']['_dog-test-field'], 'dog-test-field string value' );

		self::assertArrayHasKey( '_dog-weight', $response_data['meta'] );
		self::assertEquals( '100.25', $response_data['meta']['_dog-weight'] );
	}

	public function test_post_meta_that_is_configured_to_not_show_in_rest_is_not_accessible(): void {
		wp_set_current_user( 1 );
		$request  = new \WP_REST_Request( 'GET', $this->namespace . $this->dog_route . '/' . $this->dog_post_id );
		$response = $this->server->dispatch( $request );
		$response_data = $response->get_data();
		$this->assertFalse( array_key_exists( 'another-dog-test-field', $response_data['meta'] ) );
	}

	/**
	 * @covers ::\WPE\ContentModel\ContentRegistration\register_content_types()
	 */
	public function test_content_registration_init_hook(): void {
		$this->assertSame( 10, has_action( 'init', 'WPE\ContentModel\ContentRegistration\register_content_types' ) );
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
							dogRepeater
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

			self::assertArrayHasKey( 'dogRepeater', $results['data']['dogs']['nodes'][0] );
			self::assertSame( $results['data']['dogs']['nodes'][0]['dogRepeater'], 'dog-repeater string value' );

		} catch ( Exception $exception ) {
			throw new PHPUnitRunnerException( sprintf( __FUNCTION__ . ' failed with exception: %s', $exception->getMessage() ) );
		}
	}

	public function test_generate_custom_post_type_args_throws_exception_when_invalid_arguments_passed(): void {
		self::expectException( \InvalidArgumentException::class );
		generate_custom_post_type_args( [] );
	}

	public function test_generate_custom_post_type_args_generates_expected_data(): void {
		$generated_args = generate_custom_post_type_args( [ 'singular' => 'Dog', 'plural' => 'Dogs' ] );
		$expected_args = $this->expected_post_types()['dog'];
		unset( $expected_args['fields'] );
		self::assertSame( $generated_args, $expected_args );
	}

	private function expected_post_types(): array {
		return include __DIR__ . '/example-data/expected-post-types.php';
	}
}
