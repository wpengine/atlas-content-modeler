<?php
/**
 * Tests model ID migration.
 *
 * @package AtlasContentModeler
 */

use function WPE\AtlasContentModeler\ContentRegistration\update_registered_content_types;
use function WPE\AtlasContentModeler\ContentRegistration\get_registered_content_types;

require_once ATLAS_CONTENT_MODELER_INCLUDES_DIR . '/wp-cli/class-model.php';

/**
 * Class ModelChangeIdTest
 *
 * @covers WPE\AtlasContentModeler\WP_CLI\Model
 */
class ModelChangeIdTest extends WP_UnitTestCase {
	private $model;

	public function set_up() {
		parent::set_up();

		$models = [
			'old-id' => [
				'slug'     => 'old-id',
				'singular' => 'Old ID',
				'plural'   => 'Old IDs',
				'fields'   => [],
			],
		];

		update_registered_content_types( $models );

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
		$post_count = 2;
		$this->factory->post->create_many(
			$post_count,
			[ 'post_type' => 'old-id' ]
		);

		$this->model->change_id( [ 'old-id', 'new-id' ] );

		$old_posts = get_posts( [ 'post_type' => 'old-id' ] );
		$new_posts = get_posts( [ 'post_type' => 'new-id' ] );

		$this->assertCount( 0, $old_posts );
		$this->assertCount( $post_count, $new_posts );
	}

	public function tear_down() {
		global $wp_post_types;
		$wp_post_types = null;
		update_registered_content_types( [] );

		parent::tear_down();
	}

}
