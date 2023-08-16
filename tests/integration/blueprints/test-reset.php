<?php
/**
 * Tests the ACM reset process.
 *
 * @package AtlasContentModeler
 */

// So that we can run tests outside of the WP_CLI process.
require_once ATLAS_CONTENT_MODELER_INCLUDES_DIR . '/wp-cli/class-reset.php';

// Functions used for test setup.
use function WPE\AtlasContentModeler\Blueprint\Import\{
	get_manifest,
	import_acm_relationships,
	import_media,
	import_post_meta,
	import_posts,
	import_taxonomies,
	import_terms,
	tag_posts,
};

use function WPE\AtlasContentModeler\ContentRegistration\get_registered_content_types;
use function WPE\AtlasContentModeler\ContentRegistration\Taxonomies\get_acm_taxonomies;
use function WPE\AtlasContentModeler\REST_API\Models\create_models;
use \WPE\AtlasContentModeler\ContentConnect\Plugin as ContentConnect;

/**
 * Class ResetTest
 */
class ResetTest extends WP_UnitTestCase {

	private $reset;
	private $manifest;
	private $upload_dir;
	private $blueprint_folder;
	private $media_ids_old_new;

	public function setUp(): void {
		parent::setUp();

		delete_option( 'atlas_content_modeler_post_types' );
		delete_option( 'atlas_content_modeler_taxonomies' );

		$this->manifest         = get_manifest( __DIR__ . '/test-data/blueprint-good' );
		$this->upload_dir       = wp_upload_dir()['path'];
		$this->blueprint_folder = $this->upload_dir . '/blueprint-good';

		// Restores ACM schema from the local test blueprint.
		create_models( $this->manifest['models'] );
		import_taxonomies( $this->manifest['taxonomies'] );

		// Restores posts.
		$post_ids_old_new = import_posts( $this->manifest['posts'] );

		// Restores taxonomies, terms and tags.
		import_taxonomies( $this->manifest['taxonomies'] );
		$term_ids_old_new = import_terms( $this->manifest['post_terms'] );
		tag_posts(
			$this->manifest['post_terms'],
			$post_ids_old_new,
			$term_ids_old_new
		);

		// Imports media.
		$this->copy_media_to_wp_uploads();
		$this->media_ids_old_new = import_media( $this->manifest['media'], $this->blueprint_folder );

		import_acm_relationships(
			$this->manifest['relationships'],
			$post_ids_old_new
		);

		import_post_meta(
			$this->manifest,
			$post_ids_old_new,
			$this->media_ids_old_new
		);

		$this->reset = new WPE\AtlasContentModeler\WP_CLI\Reset();
	}

	public function test_taxonomy_terms_are_reset() {
		// Check that terms exists to delete.
		$breed_terms = get_terms( 'breed', [ 'hide_empty' => false ] );
		self::assertCount( 2, $breed_terms );

		$this->reset->delete_taxonomy_terms();

		$breed_terms = get_terms( 'breed', [ 'hide_empty' => false ] );
		self::assertCount( 0, $breed_terms );
	}

	public function test_taxonomies_are_reset() {
		// Check that taxonomies exist to delete.
		$taxonomies = get_acm_taxonomies();
		self::assertArrayHasKey( 'breed', $taxonomies );

		$this->reset->delete_taxonomies();

		self::assertEmpty( get_acm_taxonomies() );
	}

	public function test_relationships_are_reset() {
		global $wpdb;

		// Check that relationships exist to delete.
		$table         = ContentConnect::instance()->get_table( 'p2p' );
		$post_to_post  = $table->get_table_name();
		$relationships = $wpdb->get_results( "SELECT * FROM {$post_to_post};", ARRAY_A ); // phpcs:ignore
		self::assertNotEmpty( $relationships );

		$this->reset->delete_relationships();

		$relationships = $wpdb->get_results( "SELECT * FROM {$post_to_post};", ARRAY_A ); // phpcs:ignore
		self::assertEmpty( $relationships );
	}

	public function test_posts_are_reset() {
		$rabbit_posts = get_posts( [ 'post_type' => 'rabbit' ] );
		self::assertNotEmpty( $rabbit_posts );

		$this->reset->delete_posts( false ); // Only delete ACM post types.

		$rabbit_posts = get_posts( [ 'post_type' => 'rabbit' ] );
		self::assertEmpty( $rabbit_posts );

		/**
		 * Regular posts should not be deleted unless 'true'
		 * is passed to delete_posts().
		 */
		$core_posts = get_posts( [ 'post_type' => 'post' ] );
		self::assertNotEmpty( $core_posts );
	}

	public function test_core_posts_are_reset() {
		$core_posts = get_posts( [ 'post_type' => 'post' ] );
		self::assertNotEmpty( $core_posts );

		$rabbit_posts = get_posts( [ 'post_type' => 'rabbit' ] );
		self::assertNotEmpty( $rabbit_posts );

		$this->reset->delete_posts( true ); // Delete ACM and core posts.

		$core_posts = get_posts( [ 'post_type' => 'post' ] );
		self::assertEmpty( $core_posts );

		$rabbit_posts = get_posts( [ 'post_type' => 'rabbit' ] );
		self::assertEmpty( $rabbit_posts );
	}

	public function test_media_is_reset() {
		// Confirm media exists to delete.
		foreach ( $this->manifest['media'] as $media_id => $media_path ) {
			$imported_media_data = wp_get_attachment_metadata( $this->media_ids_old_new[ $media_id ] ?? $media_id );
			self::assertContains( $media_path, $imported_media_data['file'] ); // File was imported.
			self::assertNotEmpty( $imported_media_data['sizes'] ); // Thumbnails were created.
		}

		// Delete media related to ACM posts.
		$this->reset->delete_media( false );

		$remaining_media = [];

		// Confirm media relating to ACM is gone.
		foreach ( $this->manifest['media'] as $media_id => $media_path ) {
			$remaining_media[] = wp_get_attachment_metadata( $this->media_ids_old_new[ $media_id ] ?? $media_id );
		}

		$remaining_media = array_filter( $remaining_media ); // Filters out `false` values.

		/**
		 * There should be one media item left (the one attached to the 'post'; the other two media items attached to
		 * the ACM 'rabbit' entries should be gone).
		 */
		self::assertCount( 1, $remaining_media );
	}

	public function test_core_media_is_reset() {
		// Confirm media exists to delete.
		foreach ( $this->manifest['media'] as $media_id => $media_path ) {
			$imported_media_data = wp_get_attachment_metadata( $this->media_ids_old_new[ $media_id ] ?? $media_id );
			self::assertContains( $media_path, $imported_media_data['file'] ); // File was imported.
			self::assertNotEmpty( $imported_media_data['sizes'] ); // Thumbnails were created.
		}

		// Delete all media, including that related to ACM posts and core posts.
		$this->reset->delete_media( true );

		$remaining_media = [];

		// Confirm all media was deleted.
		foreach ( $this->manifest['media'] as $media_id => $media_path ) {
			$remaining_media[] = wp_get_attachment_metadata( $this->media_ids_old_new[ $media_id ] ?? $media_id );
		}

		$remaining_media = array_filter( $remaining_media ); // Filters out `false` values.
		self::assertEmpty( $remaining_media );
	}

	public function test_models_are_reset() {
		// Check that models exist to delete.
		$models = get_registered_content_types();
		self::assertArrayHasKey( 'rabbit', $models );

		$this->reset->delete_models();

		self::assertEmpty( get_registered_content_types() );
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
	 * Unregisters taxonomies to clean up scope between tests.
	 */
	protected function reset_taxonomies() {
		foreach ( get_taxonomies() as $tax ) {
			unregister_taxonomy( $tax );
		}
	}

	/**
	 * Unregisters post types to clean up scope between tests.
	 *
	 * Without this, the call to `create_models()` in setUp() does not write to
	 * the `atlas_content_modeler_post_types` option because ACM post types from
	 * previous tests are still registered in memory: `create_models()` only
	 * saves ACM post types that aren't already registered.
	 */
	protected function reset_registered_post_types() {
		foreach ( get_registered_content_types() as $post_type ) {
			unregister_post_type( $post_type['slug'] );
		}
	}

	public function tearDown(): void {
		$this->reset_taxonomies();
		$this->reset_registered_post_types();
		parent::tearDown();
	}
}
