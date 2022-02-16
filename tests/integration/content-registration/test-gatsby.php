<?php
/**
 * Class TestGatsby
 *
 * @package AtlasContentModeler
 */

use function WPE\AtlasContentModeler\ContentRegistration\update_registered_content_types;
use function WPE\AtlasContentModeler\ContentRegistration\Gatsby\monitor_acm_post_types;

class TestGatsby extends WP_UnitTestCase {
	private $models;

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
	}

	public function test_wp_gatsby_filter_is_added(): void {
		$expected_filter_priority = 10;

		self::assertSame(
			$expected_filter_priority,
			has_filter(
				'gatsby_action_monitor_tracked_post_types',
				'WPE\AtlasContentModeler\ContentRegistration\Gatsby\monitor_acm_post_types'
			)
		);
	}

	public function test_gatsby_filter_adds_only_public_acm_post_types(): void {
		$original_post_types = [
			'post'       => 'post',
			'page'       => 'page',
			'custom-cpt' => 'custom-cpt',
		];

		$new_post_types = monitor_acm_post_types( $original_post_types );

		$expected_new_post_types = [
			'post'          => 'post',
			'page'          => 'page',
			'custom-cpt'    => 'custom-cpt',
			'public'        => 'public',
			'public-fields' => 'public-fields',
		];

		// Public models should now appear in the post types list.
		self::assertSame( $expected_new_post_types, $new_post_types );

		// Models with 'api_visibility' set to 'private' should not appear.
		self::assertArrayNotHasKey( 'private', $new_post_types ); // 'api_visibility' is private.
		self::assertArrayNotHasKey( 'private-fields', $new_post_types ); // 'api_visibility' is private.
	}

	private function get_models() {
		return include dirname( __DIR__ ) . '/api-validation/test-data/models.php';
	}
}
