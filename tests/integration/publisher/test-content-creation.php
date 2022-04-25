<?php
/**
 * Tests Content Creation.
 *
 * @package AtlasContentModeler
 */

use WPE\AtlasContentModeler\FormEditingExperience;
use function WPE\AtlasContentModeler\ContentRegistration\update_registered_content_types;

/**
 * Class TestContentCreation
 */
class TestContentCreation extends WP_UnitTestCase {

	private $models;
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

		$this->models = $this->get_models();

		update_registered_content_types( $this->models );

		// @todo why is this not running automatically?
		do_action( 'init' );

		$this->all_registered_post_types = get_post_types( [], 'objects' );

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
	 * Ensure set_post_attributes() does not manipulate slug in post creation.
	 *
	 * @return void
	 */
	public function test_regular_post_slug(): void {
		$expected = 'custom_regular_post_slug';

		$slug = 'custom_regular_post_slug';
		$post = $this->factory()->post->create_and_get(
			[
				'post_name'   => $slug,
				'post_status' => 'publish',
				'post_type'   => 'post',
			]
		);
		$this->assertSame(
			$expected,
			$post->post_name
		);
	}

	/**
	 * Ensure post title is correctly set during post creation.
	 */
	public function test_correct_post_title(): void {
		$form = new FormEditingExperience();

		// Get the initial post.
		$post = get_post( $this->post_ids['public_post_id'] );

		// Set the post attributes and update the post.
		$form->set_post_attributes( $this->post_ids['public_post_id'], $post, false );
		$post = get_post( $this->post_ids['public_post_id'] );
		self::assertSame( 'Test dog', $post->post_title );

		// Get initial auto-draft post.
		$auto_draft_post = get_post( $this->post_ids['auto_draft_post_id'] );

		// Set the post attributes, update the post, and get the updated post.
		$form->set_post_attributes( $this->post_ids['auto_draft_post_id'], $auto_draft_post, false );
		$auto_draft_post = get_post( $this->post_ids['auto_draft_post_id'] );
		// Confirm auto-draft post title is 'entry{xx}', where xx is the post ID.
		self::assertSame( 'entry' . $this->post_ids['auto_draft_post_id'], $auto_draft_post->post_title );
	}

	public function test_post_title_synced_from_postmeta_table_to_posts_table(): void {
		$meta_title = 'This is a title from meta that should be synced to the posts table';

		$post_id = wp_insert_post(
			[
				'post_title'  => 'moo',
				'post_name'   => 'moo',
				'post_status' => 'publish',
				'post_type'   => 'public-fields',
			]
		);

		update_post_meta( $post_id, 'singleLineRequired', $meta_title ); // singleLineRequired is configured as a title field.
		self::assertSame( get_post_field( 'post_title', $post_id ), $meta_title );
	}

	public function test_correct_post_name(): void {
		$form = new FormEditingExperience();

		// Get the initial post.
		$post = get_post( $this->post_ids['public_post_id'] );

		// Set the post attributes and update the post.
		$form->set_post_attributes( $this->post_ids['public_post_id'], $post, false );
		$post = get_post( $this->post_ids['public_post_id'] );
		// Post slug should match the sanitized version of the post title. e.g. "Test dog" becomes "test-dog".
		self::assertSame( 'test-dog', $post->post_name );

		// Get initial auto-draft post.
		$auto_draft_post = get_post( $this->post_ids['auto_draft_post_id'] );

		// Set the post attributes, update the post, and get the updated post.
		$form->set_post_attributes( $this->post_ids['auto_draft_post_id'], $auto_draft_post, false );
		$auto_draft_post = get_post( $this->post_ids['auto_draft_post_id'] );
		/**
		 * Confirm auto-draft post slug/name is '{xx}', where xx is the post ID.
		 * This casts the post_name value to an int, because WP stores it as a string.
		 * Casting to an int should result in it matching the post ID, which is an integer.
		 */
		self::assertSame( $this->post_ids['auto_draft_post_id'], (int) $auto_draft_post->post_name );

		// Confirm auto-draft post title is 'entry{xx}', where xx is the post ID.
		self::assertSame( 'entry' . $this->post_ids['auto_draft_post_id'], $auto_draft_post->post_title );

		// Publish the post and confirm the post_title and post_name values are untouched.
		$auto_draft_post->post_status = 'publish';
		wp_update_post( $auto_draft_post, false, false );
		self::assertSame( $this->post_ids['auto_draft_post_id'], (int) $auto_draft_post->post_name );
		self::assertSame( 'entry' . $this->post_ids['auto_draft_post_id'], $auto_draft_post->post_title );

		// Save the title value and confirm the post_title and post_name are updated.
		update_post_meta( $this->post_ids['auto_draft_post_id'], 'singleLineRequired', 'This meta value should become the post title' );
		self::assertSame( 'This meta value should become the post title', get_post_field( 'post_title', $this->post_ids['auto_draft_post_id'] ) );
		self::assertSame( 'this-meta-value-should-become-the-post-title', get_post_field( 'post_name', $this->post_ids['auto_draft_post_id'] ) );
	}
}
