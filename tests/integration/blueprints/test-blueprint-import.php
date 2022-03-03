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
use \WPE\AtlasContentModeler\ContentConnect\Plugin as ContentConnect;

/**
 * Class BlueprintImportTest
 *
 * @covers WPE\AtlasContentModeler\Blueprint\Import
 */
class BlueprintImportTest extends WP_UnitTestCase {

	private $manifest;
	private $upload_dir;
	private $blueprint_folder;

	public function setUp() {
		parent::setUp();
		delete_option( 'atlas_content_modeler_post_types' );
		delete_option( 'atlas_content_modeler_taxonomies' );

		$this->manifest         = get_manifest( __DIR__ . '/test-data/blueprint-good' );
		$this->upload_dir       = wp_upload_dir()['path'];
		$this->blueprint_folder = $this->upload_dir . '/blueprint-good';
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

	public function test_import_duplicate_taxonomies_records_error() {
		$first_import  = import_taxonomies( $this->manifest['taxonomies'] );
		$second_import = import_taxonomies( $this->manifest['taxonomies'] );

		self::assertNotInstanceOf( 'WP_Error', $first_import );
		self::assertInstanceOf( 'WP_Error', $second_import );
		$errors = $second_import->get_error_codes();
		self::assertContains( 'acm_taxonomy_import_error', $errors );
		self::assertContains( 'acm_taxonomy_exists', $errors );
	}

	public function test_import_posts() {
		create_models( $this->manifest['models'] );
		import_posts( $this->manifest['posts'] );

		$posts               = get_posts( [ 'post_type' => 'rabbit' ] );
		$expected_post_count = 2; // See 'posts' in tests/integration/blueprints/test-data/blueprint-good/acm.json.

		self::assertCount( $expected_post_count, $posts );
	}

	public function test_import_terms() {
		import_taxonomies( $this->manifest['taxonomies'] );
		$import_result = import_terms( $this->manifest['post_terms'] );

		$breed_terms               = get_terms( 'breed', [ 'hide_empty' => false ] );
		$expected_breed_term_count = 2; // See 'post_terms' in tests/integration/blueprints/test-data/blueprint-good/acm.json.

		$category_terms               = get_terms( 'category', [ 'hide_empty' => false ] );
		$expected_category_term_count = 2; // "Uncategorized" and "films". See 'post_terms' in tests/integration/blueprints/test-data/blueprint-good/acm.json.

		$post_tag_terms               = get_terms( 'post_tag', [ 'hide_empty' => false ] );
		$expected_post_tag_term_count = 2; // See 'post_terms' in tests/integration/blueprints/test-data/blueprint-good/acm.json.

		self::assertFalse( $import_result['errors'] );
		self::assertCount( $expected_breed_term_count, $breed_terms );
		self::assertCount( $expected_category_term_count, $category_terms );
		self::assertCount( $expected_post_tag_term_count, $post_tag_terms );
	}

	public function test_import_bad_terms_records_error() {
		$manifest = get_manifest( __DIR__ . '/test-data/blueprint-bad-terms' );
		import_taxonomies( $manifest['taxonomies'] );
		$import_terms = import_terms( $manifest['post_terms'] );

		self::assertInstanceOf( 'WP_Error', $import_terms['errors'] );
		$errors = $import_terms['errors']->get_error_codes();
		self::assertContains( 'acm_term_import_error', $errors );
		self::assertContains( 'invalid_taxonomy', $errors );
	}

	public function test_tag_posts() {
		create_models( $this->manifest['models'] );
		import_taxonomies( $this->manifest['taxonomies'] );

		$post_ids_old_new = import_posts( $this->manifest['posts'] );
		$term_ids_old_new = import_terms( $this->manifest['post_terms'] )['ids'];

		tag_posts(
			$this->manifest['post_terms'],
			$post_ids_old_new,
			$term_ids_old_new
		);

		$posts = get_posts( [ 'post_type' => 'rabbit' ] );

		foreach ( $posts as $post ) {
			$saved_terms        = wp_get_post_terms( $post->ID, 'breed' );
			$expected_term_name = $this->manifest['post_terms'][ $post_ids_old_new[ $post->ID ] ?? $post->ID ][0]['name'];
			$actual_term_name   = $saved_terms[0]->name;
			self::assertSame( $expected_term_name, $actual_term_name );
		}
	}

	public function test_import_bad_tag_records_error() {
		$manifest = get_manifest( __DIR__ . '/test-data/blueprint-bad-tags' );
		create_models( $manifest['models'] );
		import_taxonomies( $manifest['taxonomies'] );

		$post_ids_old_new = import_posts( $manifest['posts'] );
		$term_ids_old_new = import_terms( $manifest['post_terms'] )['ids'];

		$tag_posts = tag_posts(
			$manifest['post_terms'],
			$post_ids_old_new,
			$term_ids_old_new
		);

		self::assertInstanceOf( 'WP_Error', $tag_posts );
		$errors = $tag_posts->get_error_codes();
		self::assertContains( 'acm_tag_import_error', $errors );
		self::assertContains( 'invalid_taxonomy', $errors );
	}

	public function test_import_media() {
		$this->copy_media_to_wp_uploads();
		$media_ids_old_new = import_media( $this->manifest['media'], $this->blueprint_folder );

		foreach ( $this->manifest['media'] as $media_id => $media_path ) {
			$imported_media_data = wp_get_attachment_metadata( $media_ids_old_new[ $media_id ] ?? $media_id );
			self::assertContains( $media_path, $imported_media_data['file'] ); // File was imported.
			self::assertNotEmpty( $imported_media_data['sizes'] ); // Thumbnails were created.
		}
	}

	public function test_import_post_meta() {
		create_models( $this->manifest['models'] );
		import_taxonomies( $this->manifest['taxonomies'] );
		$post_ids_old_new = import_posts( $this->manifest['posts'] );

		$this->copy_media_to_wp_uploads();
		$media_ids_old_new = import_media( $this->manifest['media'], $this->blueprint_folder );

		import_post_meta(
			$this->manifest,
			$post_ids_old_new,
			$media_ids_old_new
		);

		foreach ( $this->manifest['post_meta'] as $original_post_id => $original_metas ) {
			foreach ( $original_metas as $original_meta ) {
				$expected_meta_value = $original_meta['meta_value'];

				if (
					$original_meta['meta_key'] === 'photo' ||
					$original_meta['meta_key'] === '_thumbnail_id'
				) {
					$expected_meta_value =
						$media_ids_old_new[ $original_meta['meta_value'] ] ??
						$original_meta['meta_value'];
				}

				$actual_meta_value = get_post_meta(
					$post_ids_old_new[ $original_post_id ] ?? $original_post_id,
					$original_meta['meta_key'],
					true
				);

				self::assertEquals( $expected_meta_value, $actual_meta_value );
			}
		}
	}

	public function test_import_acm_relationships() {
		global $wpdb;

		create_models( $this->manifest['models'] );
		$post_ids_old_new = import_posts( $this->manifest['posts'] );

		import_acm_relationships(
			$this->manifest['relationships'],
			$post_ids_old_new
		);

		$table                  = ContentConnect::instance()->get_table( 'p2p' );
		$post_to_post           = $table->get_table_name();
		$imported_relationships = $wpdb->get_results( "SELECT * FROM {$post_to_post};", ARRAY_A ); // phpcs:ignore

		foreach ( $this->manifest['relationships'] as $index => $original_relationship ) {
			$imported_relationship = $imported_relationships[ $index ];
			$expected_id1          = $post_ids_old_new[ $original_relationship['id1'] ]
										?? $original_relationship['id1'];
			$expected_id2          = $post_ids_old_new[ $original_relationship['id2'] ]
										?? $original_relationship['id2'];
			$actual_id1            = $imported_relationship['id1'];
			$actual_id2            = $imported_relationship['id2'];

			self::assertEquals( $expected_id1, $actual_id1 );
			self::assertEquals( $expected_id2, $actual_id2 );
		}
	}

	public function test_unzip_blueprint() {
		$this->copy_media_to_wp_uploads();

		$upload_dir = wp_upload_dir()['path'];

		unzip_blueprint( $upload_dir . '/acm-rabbits.zip' );

		self::assertTrue( is_readable( $upload_dir . '/acm-rabbits/acm.json' ) );
	}

	public function test_cleanup() {
		$this->copy_media_to_wp_uploads();

		$upload_dir = wp_upload_dir()['path'];

		cleanup( $upload_dir . '/acm-rabbits.zip', $upload_dir . '/blueprint-good/' );

		self::assertFalse( is_readable( $upload_dir . '/acm-rabbits.zip' ) );
		self::assertFalse( is_readable( $upload_dir . '/blueprint-good/acm.json' ) );
	}

	private function copy_media_to_wp_uploads() {
		global $wp_filesystem;

		if ( ! defined( 'FS_METHOD' ) ) {
			define( 'FS_METHOD', 'direct' ); // Allows direct filesystem copy operations without FTP/SSH passwords. This only takes effect during testing.
		}

		if ( empty( $wp_filesystem ) ) {
			require_once ABSPATH . '/wp-admin/includes/file.php';
			\WP_Filesystem();
		}

		copy_dir( __DIR__ . '/test-data/', $this->upload_dir );
	}

	/**
	 * Unregisters taxonomies to clean up scope between taxonomy tests.
	 */
	protected function reset_taxonomies() {
		foreach ( get_taxonomies() as $tax ) {
			_unregister_taxonomy( $tax );
		}
	}

	public function tearDown() {
		$this->reset_taxonomies();

		parent::tearDown();
	}
}
