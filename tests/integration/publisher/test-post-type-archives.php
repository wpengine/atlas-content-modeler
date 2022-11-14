<?php
/**
 * Tests Post Type Archives
 *
 * @package AtlasContentModeler
 */

use function WPE\AtlasContentModeler\ContentRegistration\update_registered_content_types;

class TestPostTypeArchives extends WP_UnitTestCase {
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

		update_registered_content_types( $this->get_models() );

		do_action( 'init' );

		$this->post_ids = $this->get_post_ids();
	}

	private function get_models() {
		return include dirname( __DIR__ ) . '/api-validation/test-data/models.php';
	}

	private function get_post_ids() {
		include_once dirname( __DIR__ ) . '/api-validation/test-data/posts.php';

		return create_test_posts( $this );
	}

	public function test_has_archive_is_not_used_by_default(): void {
		$post_type    = get_post_type( $this->post_ids['public_fields_post_id'] );
		$archive_link = get_post_type_archive_link( $post_type );
		self::assertSame( false, $archive_link );
	}

	public function test_has_archive_is_used_when_model_is_configured_to_have_an_archive(): void {
		$this->set_permalink_structure( '/moo/%postname%/' );
		$post_type    = get_post_type( $this->post_ids['public_post_id'] );
		$archive_link = get_post_type_archive_link( $post_type );
		self::assertSame( 'http://example.org/moo/public/', $archive_link );

		$this->set_permalink_structure( '/%postname%/' );
		$archive_link = get_post_type_archive_link( $post_type );
		self::assertSame( 'http://example.org/public/', $archive_link );
	}
}
