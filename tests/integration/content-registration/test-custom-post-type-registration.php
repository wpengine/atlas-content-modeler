<?php
/**
 * Class PostTypeRegistrationTestCases
 *
 * @package AtlasContentModeler
 */

use function WPE\AtlasContentModeler\ContentRegistration\generate_custom_post_type_args;
use function \WPE\AtlasContentModeler\ContentRegistration\generate_custom_post_type_labels;
use function WPE\AtlasContentModeler\ContentRegistration\update_registered_content_types;
use function WPE\AtlasContentModeler\ContentRegistration\is_protected_meta;

/**
 * Post type registration case.
 */
class PostTypeRegistrationTestCases extends WP_UnitTestCase {

	private $models;
	private $all_registered_post_types;

	public function setUp() {
		parent::setUp();

		/**
		 * Reset the WPGraphQL schema before each test.
		 * Lazy loading types only loads part of the schema,
		 * so we refresh for each test.
		 */
		WPGraphQL::clear_schema();

		// Start each test with a fresh relationships registry.
		\WPE\AtlasContentModeler\ContentConnect\Plugin::instance()->setup();

		$this->models = $this->get_models();

		update_registered_content_types( $this->models );

		// @todo why is this not running automatically?
		do_action( 'init' );

		$this->all_registered_post_types = get_post_types( [], 'objects' );

		$this->post_ids = $this->get_post_ids();
	}

	public function tearDown() {
		parent::tearDown();
		wp_set_current_user( null );
		delete_option( 'atlas_content_modeler_post_types' );
		$this->all_registered_post_types = null;
	}

	private function get_models() {
		return include dirname( __DIR__ ) . '/api-validation/test-data/models.php';
	}

	private function get_post_ids() {
		include_once dirname( __DIR__ ) . '/api-validation/test-data/posts.php';

		return create_test_posts( $this );
	}

	/**
	 * @covers ::\WPE\AtlasContentModeler\ContentRegistration\register_content_types()
	 */
	public function test_content_registration_init_hook(): void {
		self::assertSame( 10, has_action( 'init', 'WPE\AtlasContentModeler\ContentRegistration\register_content_types' ) );
	}

	/**
	 * @covers ::\WPE\AtlasContentModeler\ContentRegistration\register_relationships()
	 */
	public function test_relationship_registration_init_hook(): void {
		self::assertSame( 10, has_action( 'acm_content_connect_init', 'WPE\AtlasContentModeler\ContentRegistration\register_relationships' ) );
	}

	public function test_defined_custom_post_types_are_registered(): void {
		self::assertArrayHasKey( 'public', $this->all_registered_post_types );
		self::assertArrayHasKey( 'public-fields', $this->all_registered_post_types );
		self::assertArrayHasKey( 'private', $this->all_registered_post_types );
		self::assertArrayHasKey( 'private-fields', $this->all_registered_post_types );
	}

	public function test_relationships_are_registered(): void {
		$registry = \WPE\AtlasContentModeler\ContentConnect\Helpers\get_registry();

		foreach ( $this->models as $post_type => $model ) {
			foreach ( $model['fields'] as $field ) {
				if ( $field['type'] === 'relationship' ) {
					$relationship = $registry->get_post_to_post_relationship( $post_type, $field['reference'], $field['id'] );
					self::assertInstanceOf('WPE\AtlasContentModeler\ContentConnect\Relationships\PostToPost', $relationship);
				}
			}
		}
	}

	public function test_custom_post_type_labels_match_expected_format(): void {
		$labels = generate_custom_post_type_labels(
			[
				'singular' => 'Public',
				'plural'   => 'Publics',
			]
		);

		self::assertSame( $labels['singular_name'], $this->all_registered_post_types['public']->labels->singular_name );
		self::assertSame( $labels['name'], $this->all_registered_post_types['public']->labels->name );
	}

	public function test_defined_custom_post_types_have_show_in_graphql_argument(): void {
		self::assertTrue( $this->all_registered_post_types['public']->show_in_graphql );
		self::assertTrue( $this->all_registered_post_types['private']->show_in_graphql );
	}

	public function test_generate_custom_post_type_args_throws_exception_when_invalid_arguments_passed(): void {
		$this->expectException( \InvalidArgumentException::class );
		generate_custom_post_type_args( [] );
	}

	public function test_generate_custom_post_type_args_generates_expected_data(): void {
		$generated_args = generate_custom_post_type_args(
			array(
				'singular'   => 'Public',
				'plural'     => 'Publics',
				'model_icon' => 'dashicons-admin-post',
			)
		);
		$expected_args  = $this->all_registered_post_types['public'];
		self::assertSame( $generated_args['name'], $expected_args->label );
		self::assertSame( $generated_args['menu_icon'], $expected_args->menu_icon );

		$generated_args = generate_custom_post_type_args(
			array(
				'singular' => 'Private',
				'plural'   => 'Privates',
				'public'   => false,
			)
		);
		$expected_args  = $this->all_registered_post_types['private'];
		self::assertSame( $generated_args['public'], $expected_args->public );
	}

	/**
	 * @covers ::\WPE\AtlasContentModeler\ContentRegistration\is_protected_meta()
	 */
	public function test_is_protected_meta_hook(): void {
		self::assertSame( 10, has_action( 'is_protected_meta', 'WPE\AtlasContentModeler\ContentRegistration\is_protected_meta' ) );
	}

	/**
	 * @covers ::\WPE\AtlasContentModeler\ContentRegistration\is_protected_meta()
	 */
	public function test_model_fields_are_protected(): void {
		$fields = $this->models['public-fields']['fields'];
		foreach ( $fields as $field ) {
			self::assertTrue( is_protected_meta( false, $field['slug'], 'post' ) );
		}
	}

	/**
	 * @covers ::\WPE\AtlasContentModeler\ContentRegistration\is_protected_meta()
	 */
	public function test_fields_not_attached_to_a_model_are_not_affected(): void {
		self::assertFalse( is_protected_meta( false, 'this-key-is-unprotected-and-not-ours-and-should-remain-unprotected', 'post' ) );
		self::assertTrue( is_protected_meta( true, 'this-key-is-already-protected-and-should-remain-protected', 'post' ) );
	}

	/**
	 * @covers ::\WPE\AtlasContentModeler\ContentRegistration\is_protected_meta()
	 */
	public function test_model_supports_author(): void {
		self::assertTrue(post_type_supports('public', 'author'));
	}
}
