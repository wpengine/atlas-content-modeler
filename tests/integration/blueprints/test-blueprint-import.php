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

/**
 * Class BlueprintImportTest
 *
 * @covers WPE\AtlasContentModeler\Blueprint\Import
 */
class BlueprintImportTest extends WP_UnitTestCase {
	public function test_get_manifest_gives_manifest_data(): void {
		$manifest = get_manifest( __DIR__ . '/test-data/blueprint-good' );
		self::assertArrayHasKey( 'meta', $manifest );
	}

	public function test_get_missing_manifest_gives_error(): void {
		$manifest = get_manifest( __DIR__ . '/test-data/this-folder-does-not-exist' );
		self::assertInstanceOf( 'WP_Error', $manifest );
		self::assertSame( 'acm_manifest_error', $manifest->get_error_code() );
	}

	public function test_check_versions_passes(): void {
		$manifest = get_manifest( __DIR__ . '/test-data/blueprint-good' );
		$check    = check_versions( $manifest );
		self::assertTrue( $check );
	}

	public function test_check_version_fails_if_needs_newer_wordpress(): void {
		$manifest = get_manifest( __DIR__ . '/test-data/blueprint-needs-higher-wordpress' );
		$check    = check_versions( $manifest );

		self::assertInstanceOf( 'WP_Error', $check );
		self::assertContains(
			'acm.json requires a WordPress version of 100000000000000000000 but the current WordPress version is',
			$check->get_error_message()
		);
	}

	public function test_check_version_fails_if_needs_newer_acm(): void {
		$manifest = get_manifest( __DIR__ . '/test-data/blueprint-needs-higher-acm' );
		$check    = check_versions( $manifest );

		self::assertInstanceOf( 'WP_Error', $check );
		self::assertContains(
			'acm.json requires an ACM version of 100000000000000000000 but the current ACM version is',
			$check->get_error_message()
		);
	}
}
