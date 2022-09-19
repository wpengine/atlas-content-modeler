<?php
/**
 * Tests model ID migration.
 *
 * @package AtlasContentModeler
 */

use function WPE\AtlasContentModeler\API\add_relationship;
use function WPE\AtlasContentModeler\ContentConnect\Helpers\get_related_ids_by_name;
use function WPE\AtlasContentModeler\ContentRegistration\update_registered_content_types;
use function WPE\AtlasContentModeler\ContentRegistration\get_registered_content_types;
use function WPE\AtlasContentModeler\ContentRegistration\register_relationships;
use function WPE\AtlasContentModeler\ContentRegistration\Taxonomies\get_acm_taxonomies;
use function WPE\AtlasContentModeler\ContentRegistration\Taxonomies\register as register_acm_taxonomies;
use function WPE\AtlasContentModeler\REST_API\Taxonomies\save_taxonomy;

require_once ATLAS_CONTENT_MODELER_INCLUDES_DIR . '/wp-cli/class-model.php';

/**
 * Class ModelChangeIdTest
 *
 * @covers WPE\AtlasContentModeler\WP_CLI\Model
 */
class ModelChangeIdTest extends WP_UnitTestCase {
	/**
	 * Model instance to access the change_id method.
	 *
	 * @var Model
	 */
	private $model;

	/**
	 * Number of posts to insert in the original post type.
	 *
	 * @var int
	 */
	private $post_count = 2;

	private $term_id;

	public function set_up() {
		parent::set_up();

		// Start each test with a fresh relationships registry.
		\WPE\AtlasContentModeler\ContentConnect\Plugin::instance()->setup();

		$models = [
			'old-id' => [
				'slug'     => 'old-id',
				'singular' => 'Old ID',
				'plural'   => 'Old IDs',
				'fields'   => [
					'123' => [
						'show_in_rest'    => true,
						'show_in_graphql' => true,
						'type'            => 'relationship',
						'id'              => '123',
						'position'        => '10000',
						'name'            => 'Relationship',
						'slug'            => 'relationship',
						'description'     => '',
						'required'        => false,
						'reference'       => 'old-id',
						'cardinality'     => 'one-to-one',
						'enableReverse'   => false,
						'reverseName'     => 'Old IDs',
						'reverseSlug'     => 'old-ids',
					],
				],
			],
		];

		$test_taxonomy = [
			'types'    => [ 'old-id' ],
			'singular' => 'Demo',
			'plural'   => 'Demos',
			'slug'     => 'demo',
		];

		update_registered_content_types( $models );
		save_taxonomy( $test_taxonomy, false );
		register_acm_taxonomies();
		register_relationships( \WPE\AtlasContentModeler\ContentConnect\Plugin::instance()->get_registry() );

		$this->term_id = $this->factory()->term->create(
			[
				'taxonomy' => 'demo',
			]
		);

		$this->posts = $this->factory->post->create_many(
			$this->post_count,
			[
				'post_type' => 'old-id',
			]
		);

		foreach ( $this->posts as $post_id ) {
			wp_set_post_terms( $post_id, [ $this->term_id ], 'demo' );
		}

		// Relate the posts to each other via the 'relationship' field.
		add_relationship( $this->posts[0], 'relationship', $this->posts[1] );

		$this->model = new \WPE\AtlasContentModeler\WP_CLI\Model();
	}

	public function test_model_id_is_not_updated_if_old_id_not_in_model_list() {
		$this->expectException( \Exception::class );
		$this->expectExceptionMessage( 'No model exists with the old ID of ‘bad-old-id’.' );

		$this->model->change_id( [ 'bad-old-id', 'new-id' ] );
	}

	public function test_model_id_is_not_updated_if_new_id_is_longer_than_20_chars() {
		$this->expectException( \Exception::class );
		$this->expectExceptionMessage( 'New model ID must not exceed 20 characters.' );

		$this->model->change_id( [ 'old-id', 'bad-new-id-that-is-longer-than-20-characters' ] );
	}

	public function test_model_id_is_not_updated_if_new_id_starts_with_number() {
		$this->expectException( \Exception::class );
		$this->expectExceptionMessage( 'New model ID must not start with a number.' );

		$this->model->change_id( [ 'old-id', '7bad-new-id' ] );
	}

	public function test_model_id_is_not_updated_if_new_id_has_invalid_characters() {
		$this->expectException( \Exception::class );
		$this->expectExceptionMessage( 'New model ID must only contain lowercase alphanumeric characters, underscores and hyphens.' );

		$this->model->change_id( [ 'old-id', 'bad-new-id-@!!!' ] );
	}

	public function test_model_id_is_not_updated_if_new_id_is_in_use_by_another_model() {
		$this->expectException( \Exception::class );
		$this->expectExceptionMessage( 'New ID of ‘old-id’ is in use by another model.' );

		$this->model->change_id( [ 'old-id', 'old-id' ] );
	}

	public function test_model_id_is_not_updated_if_new_id_is_a_reserved_slug() {
		$this->expectException( \Exception::class );
		$this->expectExceptionMessage( 'New ID of ‘theme’ is reserved or in use by WordPress Core.' );

		$this->model->change_id( [ 'old-id', 'theme' ] );
	}

	public function test_model_id_is_not_updated_if_new_id_is_in_use_by_a_custom_post_type() {
		register_post_type( 'custom-post-type', [] );
		$this->model = new \WPE\AtlasContentModeler\WP_CLI\Model();

		$this->expectException( \Exception::class );
		$this->expectExceptionMessage( 'New ID of ‘custom-post-type’ is in use by a custom post type.' );

		$this->model->change_id( [ 'old-id', 'custom-post-type' ] );
	}

	public function test_model_id_is_updated() {
		$this->model->change_id( [ 'old-id', 'new-id' ] );

		$models = get_registered_content_types();

		$this->assertArrayHasKey( 'new-id', $models );
		$this->assertArrayNotHasKey( 'old-id', $models );
		$this->assertEquals( 'new-id', $models['new-id']['slug'] );
	}

	public function test_posts_are_updated() {
		$this->model->change_id( [ 'old-id', 'new-id' ] );

		$old_posts = get_posts(
			[
				'post_type'              => 'old-id',
				'cache_results'          => false,
				'update_post_term_cache' => false,
			]
		);

		$new_posts = get_posts(
			[
				'post_type'              => 'new-id',
				'cache_results'          => false,
				'update_post_term_cache' => false,
			]
		);

		$this->assertCount( 0, $old_posts );
		$this->assertCount( $this->post_count, $new_posts );
	}

	public function test_taxonomies_are_updated() {
		$this->model->change_id( [ 'old-id', 'new-id' ] );

		// Updated taxonomy data should have been written to the database.
		$taxonomies = get_acm_taxonomies();
		$this->assertContains( 'new-id', $taxonomies['demo']['types'] );

		// There should still be the same number of posts with the assigned term.
		$term = get_term( $this->term_id );
		$this->assertEquals( $this->post_count, $term->count );
	}

	public function test_relationship_fields_are_updated() {
		$this->model->change_id( [ 'old-id', 'new-id' ] );

		// Reference for the relationship field should be updated in the new model record.
		$models = get_registered_content_types();
		$this->assertContains( 'new-id', $models['new-id']['fields']['123']['reference'] );

		// Posts should still be connected.
		$relationship_field_id = '123';
		$related_posts         = get_related_ids_by_name( $this->posts[0], $relationship_field_id );
		$this->assertEquals( [ $this->posts[1] ], $related_posts );
	}

	public function tear_down() {
		global $wp_post_types;
		$wp_post_types = null;

		delete_option( 'atlas_content_modeler_post_types' );
		delete_option( 'atlas_content_modeler_taxonomies' );

		// Prevents 'taxonomy exists' warnings during test set_up.
		unregister_taxonomy( 'demo' );

		register_acm_taxonomies();

		parent::tear_down();
	}

}
