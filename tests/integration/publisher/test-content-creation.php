<?php
/**
 * Tests Content Creation.
 *
 * @package AtlasContentModeler
 */

use WPE\AtlasContentModeler\FormEditingExperience;
use function WPE\AtlasContentModeler\ContentRegistration\update_registered_content_types;

/**
 * Class TestContentCreation
 */
class TestContentCreation extends WP_UnitTestCase {

	private $models;
	private $post_ids;

	public function set_up() {
		parent::set_up();

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

	private function get_models() {
		return include dirname( __DIR__ ) . '/api-validation/test-data/models.php';
	}

	private function get_post_ids() {
		include_once dirname( __DIR__ ) . '/api-validation/test-data/posts.php';

		return create_test_posts( $this );
	}

	/**
	 * Ensure set_post_attributes() does not manipulate slug in post creation.
	 *
	 * @return void
	 */
	public function test_regular_post_slug(): void {
		$expected = 'custom_regular_post_slug';

		$slug = 'custom_regular_post_slug';
		$post = $this->factory()->post->create_and_get(
			[
				'post_name'   => $slug,
				'post_status' => 'publish',
				'post_type'   => 'post',
			]
		);
		$this->assertSame(
			$expected,
			$post->post_name
		);
	}

	/**
	 * Ensure post title is correctly set during post creation.
	 */
	public function test_correct_post_title(): void {
		$post = get_post( $this->post_ids['public_post_id'] );
		self::assertSame( $post->post_title, 'Test dog' );
	}
}
