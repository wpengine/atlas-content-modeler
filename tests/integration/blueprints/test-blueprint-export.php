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
		self::assertArrayHasKey( 'wordpress', $meta['requires'] ); // phpcs:ignore WordPress.WP.CapitalPDangit.Misspelled
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

	public function test_collect_posts_with_no_posts() {
		$posts = collect_posts();

		self::assertEmpty( $posts );
	}

	public function test_collect_posts_of_invalid_type() {
		$posts = collect_posts( [ 'post-type-does-not-exist' ] );

		self::assertEmpty( $posts );
	}

	public function test_collect_posts_gets_core_post_page_types() {
		$post_id = $this->factory->post->create(
			[
				'post_title'   => 'Test Post',
				'post_content' => 'Hello Post',
				'post_status'  => 'publish',
				'post_type'    => 'post',
			]
		);

		$page_id = $this->factory->post->create(
			[
				'post_title'   => 'Test Page',
				'post_content' => 'Hello Page',
				'post_status'  => 'publish',
				'post_type'    => 'page',
			]
		);

		$posts = collect_posts();

		self::assertCount( 2, $posts );
		self::assertArrayHasKey( $post_id, $posts );
		self::assertArrayHasKey( $page_id, $posts );
		self::assertEquals( 'Test Post', $posts[ $post_id ]['post_title'] );
		self::assertEquals( 'Hello Post', $posts[ $post_id ]['post_content'] );
		self::assertEquals( 'Test Page', $posts[ $page_id ]['post_title'] );
		self::assertEquals( 'Hello Page', $posts[ $page_id ]['post_content'] );
	}

	public function test_collect_posts_skips_drafts() {
		$this->factory->post->create(
			[
				'post_title'  => 'Draft Post',
				'post_status' => 'draft',
				'post_type'   => 'post',
			]
		);

		$this->factory->post->create(
			[
				'post_title'  => 'Published Post',
				'post_status' => 'publish',
				'post_type'   => 'post',
			]
		);

		$posts = collect_posts();

		self::assertCount( 1, $posts );
	}

	public function test_collect_posts_can_override_post_types() {
		$this->factory->post->create(
			[
				'post_title'  => 'Post',
				'post_status' => 'publish',
				'post_type'   => 'post',
			]
		);

		$this->factory->post->create(
			[
				'post_title'  => 'Page',
				'post_status' => 'publish',
				'post_type'   => 'page',
			]
		);

		$posts = collect_posts( [ 'page' ] );

		self::assertCount( 1, $posts );
	}

	public function test_collect_posts_can_get_acm_entries() {
		$models = [
			'rabbit' => [
				'show_in_rest'    => true,
				'show_in_graphql' => true,
				'singular'        => 'Rabbit',
				'plural'          => 'Rabbits',
				'slug'            => 'rabbit',
				'api_visibility'  => 'public',
				'model_icon'      => 'dashicons-admin-post',
				'description'     => '',
				'fields'          => [],
			],
		];

		update_option( 'atlas_content_modeler_post_types', $models );

		$post_id = $this->factory->post->create(
			[
				'post_title'  => 'Rabbit',
				'post_status' => 'publish',
				'post_type'   => 'rabbit',
			]
		);

		$posts = collect_posts();

		self::assertCount( 1, $posts );
		self::assertEquals( 'Rabbit', $posts[ $post_id ]['post_title'] );
	}
}
