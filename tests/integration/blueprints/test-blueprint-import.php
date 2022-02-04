<?php
/**
 * Tests the ACM blueprint import process.
 *
 * @package AtlasContentModeler
 */

use function WPE\AtlasContentModeler\Blueprint\Import\{
	check_versions,
	cleanup,
	get_manifest,
	import_acm_relationships,
	import_media,
	import_post_meta,
	import_posts,
	import_taxonomies,
	import_terms,
	tag_posts,
	unzip_blueprint
};
use function WPE\AtlasContentModeler\REST_API\Models\create_models;

/**
 * Class BlueprintImportTest
 *
 * @covers WPE\AtlasContentModeler\Blueprint\Import
 */
class BlueprintImportTest extends WP_UnitTestCase {

	private $manifest;

	public function setUp() {
		parent::setUp();
		$this->manifest = get_manifest( __DIR__ . '/test-data/blueprint-good' );
	}

	public function test_get_manifest_gives_manifest_data(): void {
		self::assertArrayHasKey( 'meta', $this->manifest );
	}

	public function test_get_missing_manifest_gives_error(): void {
		$bad_manifest = get_manifest( __DIR__ . '/test-data/this-folder-does-not-exist' );
		self::assertInstanceOf( 'WP_Error', $bad_manifest );
		self::assertSame( 'acm_manifest_error', $bad_manifest->get_error_code() );
	}

	public function test_check_versions_passes(): void {
		$check = check_versions( $this->manifest );
		self::assertTrue( $check );
	}

	public function test_check_version_fails_if_needs_newer_wordpress(): void {
		$manifest = get_manifest( __DIR__ . '/test-data/blueprint-needs-newer-wordpress' );
		$check    = check_versions( $manifest );

		self::assertInstanceOf( 'WP_Error', $check );
		self::assertContains(
			'acm.json requires a WordPress version of 100000000000000000000 but the current WordPress version is',
			$check->get_error_message()
		);
	}

	public function test_check_version_fails_if_needs_newer_acm(): void {
		$manifest = get_manifest( __DIR__ . '/test-data/blueprint-needs-newer-acm' );
		$check    = check_versions( $manifest );

		self::assertInstanceOf( 'WP_Error', $check );
		self::assertContains(
			'acm.json requires an ACM version of 100000000000000000000 but the current ACM version is',
			$check->get_error_message()
		);
	}

	public function test_import_taxonomies() {
		import_taxonomies( $this->manifest['taxonomies'] );

		$taxonomies = get_option( 'atlas_content_modeler_taxonomies' );
		self::assertArrayHasKey( 'breed', $taxonomies );
	}

	public function test_import_posts() {
		$expected_post_count = count( $this->manifest['posts'] );

		create_models( $this->manifest['models'] );
		import_posts( $this->manifest['posts'] );

		$posts = get_posts( [ 'post_type' => 'rabbit' ] );
		self::assertCount( $expected_post_count, $posts );
	}
}
