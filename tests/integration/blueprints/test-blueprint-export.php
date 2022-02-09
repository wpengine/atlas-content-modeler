<?php
/**
 * Tests the ACM blueprint export process.
 *
 * @package AtlasContentModeler
 */

use function WPE\AtlasContentModeler\Blueprint\Export\{
	collect_media,
	collect_post_meta,
	collect_post_tags,
	collect_posts,
	collect_relationships,
	collect_terms,
	generate_meta,
	get_acm_temp_dir,
	write_manifest,
	zip_blueprint
};

/**
 * Class BlueprintExportTest
 *
 * @covers WPE\AtlasContentModeler\Blueprint\Export
 */
class BlueprintExportTest extends WP_UnitTestCase {

	public function setUp() {
		parent::setUp();
		delete_option( 'atlas_content_modeler_post_types' );
		delete_option( 'atlas_content_modeler_taxonomies' );
	}

	public function test_generate_meta() {
		$meta = generate_meta( [] );

		self::assertArrayHasKey( 'schema', $meta );
		self::assertArrayHasKey( 'version', $meta );
		self::assertArrayHasKey( 'name', $meta );
		self::assertArrayHasKey( 'description', $meta );
		self::assertArrayHasKey( 'requires', $meta );
		self::assertArrayHasKey( 'WordPress', $meta['requires'] );
		self::assertArrayHasKey( 'acm', $meta['requires'] );
	}

	public function test_generate_meta_with_overrides() {
		$overrides = [
			'version'     => '100',
			'name'        => 'Override Name',
			'description' => 'Override Description',
			'min-wp'      => '1.0',
			'min-acm'     => '2.0',
		];

		$meta = generate_meta( $overrides );

		self::assertSame( $overrides['version'], $meta['version'] );
		self::assertSame( $overrides['name'], $meta['name'] );
		self::assertSame( $overrides['description'], $meta['description'] );
		self::assertSame( $overrides['min-wp'], $meta['requires']['wordpress'] );
		self::assertSame( $overrides['min-acm'], $meta['requires']['acm'] );
	}

}
