<?php
/**
 * Tests the ACM blueprint export process.
 *
 * @package AtlasContentModeler
 */

use function WPE\AtlasContentModeler\ContentRegistration\Taxonomies\register as register_taxonomies;
use function WPE\AtlasContentModeler\Blueprint\Import\import_acm_relationships;
use function WPE\AtlasContentModeler\Blueprint\Export\{
	collect_media,
	collect_post_meta,
	collect_post_tags,
	collect_posts,
	collect_relationships,
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

	private $models = [
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

	private $taxonomies = [
		'breed' => [
			'types'           => [
				0 => 'rabbit',
			],
			'show_in_rest'    => true,
			'show_in_graphql' => true,
			'hierarchical'    => false,
			'api_visibility'  => 'private',
			'singular'        => 'Breed',
			'plural'          => 'Breeds',
			'slug'            => 'breed',
		],
	];

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

		$posts = collect_posts( [ 'post', 'page' ] );

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

		$posts = collect_posts( [ 'post' ] );

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
		update_option( 'atlas_content_modeler_post_types', $this->models );

		$post_id = $this->factory->post->create(
			[
				'post_title'  => 'Rabbit',
				'post_status' => 'publish',
				'post_type'   => 'rabbit',
			]
		);

		$posts = collect_posts( [ 'rabbit' ] );

		self::assertCount( 1, $posts );
		self::assertEquals( 'Rabbit', $posts[ $post_id ]['post_title'] );
	}

	public function test_collect_post_tags_empty_posts() {
		$tags = collect_post_tags( [], [] );

		self::assertEmpty( $tags );
	}

	public function test_collect_post_tags_from_tagged_acm_post() {
		update_option( 'atlas_content_modeler_post_types', $this->models );
		update_option( 'atlas_content_modeler_taxonomies', $this->taxonomies );
		register_taxonomies();

		$term_id = $this->factory->term->create(
			[
				'taxonomy'    => 'breed',
				'description' => 'test',
				'slug'        => 'chinchilla',
				'name'        => 'American Chinchilla',
			]
		);

		// Create a post and assign the term to it.
		$post_id = $this->factory->post->create(
			[
				'post_title'  => 'Rabbit',
				'post_status' => 'publish',
				'post_type'   => 'rabbit',
			]
		);

		wp_set_post_terms( $post_id, [ $term_id ], 'breed' );

		$posts = [
			$post_id => get_post( $post_id )->to_array(),
		];

		$tags     = collect_post_tags( $posts, [ 'breed' ] );
		$term_ids = wp_list_pluck( $tags[ $post_id ], 'term_id' );
		$names    = wp_list_pluck( $tags[ $post_id ], 'name' );
		$slugs    = wp_list_pluck( $tags[ $post_id ], 'slug' );

		self::assertCount( 1, $tags[ $post_id ] );
		self::assertContains( $term_id, $term_ids );
		self::assertContains( 'American Chinchilla', $names );
		self::assertContains( 'chinchilla', $slugs );
	}

	public function test_collect_post_tags_and_categories_from_core_post() {
		// Create a tag.
		$tag_id = $this->factory->term->create(
			[
				'taxonomy'    => 'post_tag',
				'description' => 'Tag test.',
				'slug'        => 'tag-test',
				'name'        => 'Tag Test',
			]
		);

		// Create a category.
		$category_id = $this->factory->term->create(
			[
				'taxonomy'    => 'category',
				'description' => 'Category test.',
				'slug'        => 'category-test',
				'name'        => 'Category Test',
			]
		);

		// Create a post.
		$post_id = $this->factory->post->create(
			[
				'post_title'  => 'Post',
				'post_status' => 'publish',
				'post_type'   => 'post',
			]
		);

		// Assign the tag and category to it.
		wp_set_post_terms( $post_id, [ $tag_id ], 'post_tag', true );
		wp_set_post_terms( $post_id, [ $category_id ], 'category', true );

		$posts = [
			$post_id => get_post( $post_id )->to_array(),
		];

		$tags     = collect_post_tags( $posts, [] );
		$term_ids = wp_list_pluck( $tags[ $post_id ], 'term_id' );
		$names    = wp_list_pluck( $tags[ $post_id ], 'name' );

		self::assertCount( 3, $tags[ $post_id ] ); // 3 because the post is also tagged 'uncategorized'.
		self::assertContains( $tag_id, $term_ids );
		self::assertContains( $category_id, $term_ids );
		self::assertContains( 'Tag Test', $names );
		self::assertContains( 'Category Test', $names );
	}

	public function test_collect_post_meta_no_posts() {
		$post_meta = collect_post_meta( [] );

		self::assertEmpty( $post_meta );
	}

	public function test_collect_post_meta_empty_meta() {
		$post_id = $this->factory->post->create(
			[
				'post_title'  => 'Post',
				'post_status' => 'publish',
				'post_type'   => 'post',
			]
		);

		$posts = [
			$post_id => get_post( $post_id )->to_array(),
		];

		$post_meta = collect_post_meta( $posts );

		self::assertEmpty( $post_meta );
	}

	public function test_collect_post_meta() {
		$post_id = $this->factory->post->create(
			[
				'post_title'  => 'Post',
				'post_status' => 'publish',
				'post_type'   => 'post',
				'meta_input'  => [
					'_thumbnail_id' => '123',
					'hello'         => 'Custom meta',
				],
			]
		);

		$posts = [
			$post_id => get_post( $post_id )->to_array(),
		];

		$post_meta = collect_post_meta( $posts );
		$keys      = wp_list_pluck( $post_meta[ $post_id ], 'meta_key' );
		$values    = wp_list_pluck( $post_meta[ $post_id ], 'meta_value' );

		self::assertContains( '_thumbnail_id', $keys );
		self::assertContains( 'hello', $keys );
		self::assertContains( '123', $values );
		self::assertContains( 'Custom meta', $values );
	}

	public function test_collect_media_with_no_media() {
		$empty_manifest = [];
		$empty_path     = '';
		$media          = collect_media( $empty_manifest, $empty_path );

		self::assertEmpty( $media );
	}

	public function test_collect_media() {
		$media_id = $this->insert_test_image();

		$post_id = $this->factory->post->create(
			[
				'post_title'  => 'Post',
				'post_status' => 'publish',
				'post_type'   => 'post',
				'meta_input'  => [
					'_thumbnail_id' => $media_id,
				],
			]
		);

		$manifest = [
			'meta'      => [
				'name' => 'test-media',
			],
			'post_meta' => [
				$post_id => [
					[
						'meta_key'   => '_thumbnail_id',
						'meta_value' => $media_id,
					],
				],
			],
			'posts'     => [
				$post_id => get_post( $post_id )->to_array(),
			],
		];

		$path  = get_acm_temp_dir( $manifest );
		$media = collect_media( $manifest, $path );

		self::assertArrayHasKey( $media_id, $media ); // Original media ID was recorded.
		self::assertEquals( "media/{$media_id}/roger.jpg", $media[ $media_id ] ); // Media path is correct.
		self::assertTrue( is_readable( $path . '/' . $media[ $media_id ] ) ); // Media file was copied from WP to temp dir.
	}

	public function test_collect_relationships_with_no_relationships() {
		$empty_posts   = [];
		$relationships = collect_relationships( $empty_posts );

		self::assertEmpty( $relationships );
	}

	public function test_collect_relationships() {
		$mocked_post_data = [
			'123' => [],
			'124' => [],
		];

		$mocked_relationship_data = [
			[
				'id1'   => 123,
				'id2'   => 124,
				'name'  => 'field-id',
				'order' => 0,
			],
			[
				'id1'   => 124,
				'id2'   => 123,
				'name'  => 'field-id',
				'order' => 0,
			],
			[
				'id1'   => 123,
				'id2'   => 999,
				'name'  => 'this-refers-to-an-unrelated-id-and-should-not-be-collected',
				'order' => 0,
			],
		];

		import_acm_relationships( $mocked_relationship_data, [] );

		$relationships = collect_relationships( $mocked_post_data );
		$names         = wp_list_pluck( $relationships, 'name' );

		self::assertCount( 2, $relationships );
		self::assertContains( 'field-id', $names );
		self::assertNotContains(
			'this-refers-to-an-unrelated-id-and-should-not-be-collected',
			$names
		);
	}

	public function test_get_acm_temp_dir_missing_manifest_name() {
		$bad_manifest = [
			'meta' => [
				'nome' => 'The nome typo should cause a WP_Error.',
			],
		];

		$dir = get_acm_temp_dir( $bad_manifest );

		self::assertInstanceOf( 'WP_Error', $dir );
		self::assertSame( 'acm_manifest_name_missing', $dir->get_error_code() );
	}

	public function test_get_acm_temp_dir_empty_manifest_name() {
		$bad_manifest = [
			'meta' => [
				'name' => '   ',
			],
		];

		$dir = get_acm_temp_dir( $bad_manifest );

		self::assertInstanceOf( 'WP_Error', $dir );
		self::assertSame( 'acm_manifest_name_missing', $dir->get_error_code() );
	}

	public function test_get_acm_temp_dir_bad_manifest_name() {
		$bad_manifest = [
			'meta' => [
				'name' => '!!!!!!',
			],
		];

		$dir = get_acm_temp_dir( $bad_manifest );

		self::assertInstanceOf( 'WP_Error', $dir );
		self::assertSame( 'acm_manifest_name_bad', $dir->get_error_code() );
	}

	public function test_get_acm_temp_dir_contains_meta_name() {
		$good_manifest = [
			'meta' => [
				'name' => 'Amazing Blueprint',
			],
		];

		$dir = get_acm_temp_dir( $good_manifest );

		self::assertIsString( $dir );
		self::assertContains(
			sanitize_title_with_dashes( $good_manifest['meta']['name'] ),
			$dir
		);
	}

	public function test_write_manifest() {
		$temp_dir = get_temp_dir();

		$write_path = write_manifest( [ 'key' => 'value' ], $temp_dir );

		self::assertIsString( $write_path );
		self::assertContains( $temp_dir, $write_path );
		self::assertTrue( is_readable( $write_path ) );

		$raw_manifest = file_get_contents( $write_path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$manifest     = json_decode( $raw_manifest, true );

		self::assertEquals( 'value', $manifest['key'] );
	}

	public function test_zip_blueprint() {
		global $wp_filesystem;

		if ( ! defined( 'FS_METHOD' ) ) {
			define( 'FS_METHOD', 'direct' ); // Allows direct filesystem copy operations without FTP/SSH passwords. This only takes effect during testing.
		}

		if ( empty( $wp_filesystem ) ) {
			require_once ABSPATH . '/wp-admin/includes/file.php';
			\WP_Filesystem();
		}

		$temp_dir = get_temp_dir();
		copy_dir( __DIR__ . '/test-data/', $temp_dir );

		$zip_path = zip_blueprint( $temp_dir, 'blueprint-good' );

		self::assertIsString( $zip_path );
		self::assertContains( $temp_dir, $zip_path );
		self::assertTrue( is_readable( $zip_path ) );
	}

	private function insert_test_image() {
		global $wp_filesystem;

		if ( ! defined( 'FS_METHOD' ) ) {
			define( 'FS_METHOD', 'direct' ); // Allows direct filesystem copy operations without FTP/SSH passwords. This only takes effect during testing.
		}

		if ( empty( $wp_filesystem ) ) {
			require_once ABSPATH . '/wp-admin/includes/file.php';
			\WP_Filesystem();
		}

		$upload_dir = wp_upload_dir()['path'];
		copy_dir( __DIR__ . '/test-data/', $upload_dir );

		$test_image_path = $upload_dir . '/blueprint-good/media/roger.jpg';
		$file_info       = wp_check_filetype( $test_image_path );

		$attachment = [
			'post_title'     => sanitize_title( basename( $test_image_path, '.' . $file_info['ext'] ) ),
			'post_mime_type' => $file_info['type'],
		];

		return wp_insert_attachment( $attachment, $test_image_path );
	}

}
