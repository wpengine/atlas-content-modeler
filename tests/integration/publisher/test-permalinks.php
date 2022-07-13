<?php
/**
 * Tests Content Creation.
 *
 * @package AtlasContentModeler
 */

use function WPE\AtlasContentModeler\ContentRegistration\update_registered_content_types;

/**
 * Class TestPermalinks
 */
class TestPermalinks extends WP_UnitTestCase {

	private $post_ids;

	public function set_up() {
		parent::set_up();

		$this->set_permalink_structure( '/posts/%postname%/' );

		/**
		 * Reset the WPGraphQL schema before each test.
		 * Lazy loading types only loads part of the schema,
		 * so we refresh for each test.
		 */
		WPGraphQL::clear_schema();

		// Start each test with a fresh relationships registry.
		\WPE\AtlasContentModeler\ContentConnect\Plugin::instance()->setup();

		update_registered_content_types( $this->get_models() );

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
	 * Ensures ACM post URLs use the '/posts/' prefix from our custom permalink
	 * structure in set_up() by default (if they have not specified with_front).
	 */
	public function test_with_front_is_used_by_default(): void {
		$permalink = get_permalink( $this->post_ids['public_post_id'] );

		$this->assertContains( '/posts/', $permalink );
	}

	/**
	 * Ensures an ACM post whose model has with_front set to false will not use
	 * the '/posts/' prefix from our custom permalink structure in set_up() in
	 * its URL.
	 */
	public function test_with_front_is_not_used_if_disabled(): void {
		$permalink = get_permalink( $this->post_ids['public_fields_post_id'] );

		$this->assertNotContains( '/posts/', $permalink );
	}
}
