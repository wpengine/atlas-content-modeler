<?php

use WPE\AtlasContentModeler\ContentConnect\Plugin;
use WPE\AtlasContentModeler\ContentConnect\Relationships\PostToPost;

use function WPE\AtlasContentModeler\API\add_relationship;
use function WPE\AtlasContentModeler\API\get_relationship;
use function WPE\AtlasContentModeler\API\replace_relationship;
use function WPE\AtlasContentModeler\API\fetch_model;
use function WPE\AtlasContentModeler\API\fetch_model_field;
use function WPE\AtlasContentModeler\API\insert_model_entry;

use function WPE\AtlasContentModeler\ContentRegistration\update_registered_content_types;

class TestApiFunctions extends Integration_TestCase {
	/**
	 * Content models.
	 *
	 * @var array
	 */
	protected $content_models;

	/**
	 * Override of parent::set_up.
	 *
	 * @return void
	 */
	public function set_up(): void {
		parent::set_up();

		$this->content_models = $this->get_models( __DIR__ . '/test-data/content-models.php' );
		update_registered_content_types( $this->content_models );
		Plugin::instance()->setup();
		do_action( 'init' );
	}

	public function test_insert_model_entry_will_return_WP_Error_if_model_schema_does_not_exist() {
		$model_slug = 'model_does_not_exist';
		$result     = insert_model_entry( $model_slug, [] );

		$this->assertEquals( 'model_schema_not_found', $result->get_error_code() );
		$this->assertEquals( "The content model {$model_slug} was not found", $result->get_error_message() );
	}

	public function test_fetch_model_returns_the_model_schema_if_exists() {
		$this->assertEquals( $this->content_models['person'], fetch_model( 'person' ) );
	}

	public function test_fetch_model_returns_null_if_the_model_does_not_exist() {
		$this->assertNull( fetch_model( 'does_not_exist' ) );
	}

	public function test_fetch_model_field_returns_the_field_if_exists() {
		$model_field = fetch_model_field( 'person', 'name' );

		$this->assertEquals(
			$this->content_models['person']['fields']['1648575961490'],
			$model_field
		);
	}

	public function test_fetch_model_field_returns_null_if_the_field_does_not_exist() {
		$model_field = fetch_model_field( 'person', 'does_not_exist' );

		$this->assertNull( $model_field );
	}

	public function test_fetch_model_field_returns_null_if_the_model_does_not_exist() {
		$model_field = fetch_model_field( 'does_not_exist', 'name' );

		$this->assertNull( $model_field );
	}

	public function test_replace_relationship_will_associate_relationship_ids() {
		$post_id          = $this->factory->post->create( [ 'post_type' => 'person' ] );
		$relationship_ids = $this->factory->post->create_many( 3, [ 'post_type' => 'car' ] );

		$this->assertTrue( replace_relationship( $post_id, 'cars', $relationship_ids ) );
		$this->assertEquals(
			$relationship_ids,
			$this->get_relationship_ids( $post_id )
		);
	}

	public function test_replace_relationship_will_return_WP_Error_if_invalid_post() {
		$result = replace_relationship( 999, 'cars', [] );

		$this->assertEquals( 'invalid_post_object', $result->get_error_code() );
		$this->assertEquals( 'The post object was invalid', $result->get_error_message() );
	}

	public function test_replace_relationship_will_return_WP_Error_if_invalid_field() {
		$post_id = $this->factory->post->create( [ 'post_type' => 'person' ] );
		$result  = replace_relationship( $post_id, 'does_not_exist', [] );

		$this->assertEquals( 'field_not_found', $result->get_error_code() );
		$this->assertEquals( 'Content model field not found', $result->get_error_message() );
	}

	public function test_replace_relationship_will_return_WP_Error_if_invalid_content_model_relationship() {
		$this->content_models['person']['fields']['1648576059444']['reference'] = 'does_not_exist';
		update_option( 'atlas_content_modeler_post_types', $this->content_models );

		$post_id          = $this->factory->post->create( [ 'post_type' => 'person' ] );
		$relationship_ids = $this->factory->post->create_many( 3, [ 'post_type' => 'car' ] );
		$result           = replace_relationship( $post_id, 'cars', $relationship_ids );

		$this->assertEquals( 'content_relationship_not_found', $result->get_error_code() );
		$this->assertEquals( 'Content model relationship not found', $result->get_error_message() );
	}

	public function test_replace_relationship_will_return_false_if_relationship_could_not_be_associated() {
		$this->content_models['person']['fields']['1648576059444']['cardinality'] = 'one-to-one';
		update_registered_content_types( $this->content_models );
		Plugin::instance()->setup();
		do_action( 'init' );

		$post_id          = $this->factory->post->create( [ 'post_type' => 'person' ] );
		$relationship_ids = $this->factory->post->create_many( 3, [ 'post_type' => 'car' ] );

		$this->assertFalse( replace_relationship( $post_id, 'cars', $relationship_ids ) );
	}

	public function test_add_relationship_will_append_a_relationship_id() {
		$post_id         = $this->factory->post->create( [ 'post_type' => 'person' ] );
		$relationship_id = $this->factory->post->create( [ 'post_type' => 'car' ] );

		$this->assertTrue( add_relationship( $post_id, 'cars', $relationship_id ) );
		$this->assertEquals(
			$relationship_id,
			$this->get_relationship_ids( $post_id )[0]
		);
	}

	public function test_add_relationship_will_append_a_relationship_id_to_existing_relationship_ids() {
		$post_id           = $this->factory->post->create( [ 'post_type' => 'person' ] );
		$relationship_id   = $this->factory->post->create( [ 'post_type' => 'car' ] );
		$relationship_2_id = $this->factory->post->create( [ 'post_type' => 'car' ] );

		add_relationship( $post_id, 'cars', $relationship_id );
		add_relationship( $post_id, 'cars', $relationship_2_id );
		$this->assertEquals(
			[ $relationship_id, $relationship_2_id ],
			$this->get_relationship_ids( $post_id )
		);
	}

	public function test_add_relationship_will_return_WP_Error_if_invalid_post() {
		$result = add_relationship( 999, 'cars', 1 );

		$this->assertEquals( 'invalid_post_object', $result->get_error_code() );
		$this->assertEquals( 'The post object was invalid', $result->get_error_message() );
	}

	public function test_add_relationship_will_return_WP_Error_if_invalid_field() {
		$post_id = $this->factory->post->create( [ 'post_type' => 'person' ] );
		$result  = add_relationship( $post_id, 'does_not_exist', 1 );

		$this->assertEquals( 'field_not_found', $result->get_error_code() );
		$this->assertEquals( 'Content model field not found', $result->get_error_message() );
	}

	public function test_add_relationship_will_return_WP_Error_if_invalid_content_model_relationship() {
		$this->content_models['person']['fields']['1648576059444']['reference'] = 'does_not_exist';
		update_option( 'atlas_content_modeler_post_types', $this->content_models );

		$post_id         = $this->factory->post->create( [ 'post_type' => 'person' ] );
		$relationship_id = $this->factory->post->create( [ 'post_type' => 'car' ] );
		$result          = add_relationship( $post_id, 'cars', $relationship_id );

		$this->assertEquals( 'content_relationship_not_found', $result->get_error_code() );
		$this->assertEquals( 'Content model relationship not found', $result->get_error_message() );
	}

	public function test_add_relationship_will_return_false_if_relationship_could_not_be_associated() {
		$post_id           = $this->factory->post->create( [ 'post_type' => 'person' ] );
		$relationship_id   = $this->factory->post->create( [ 'post_type' => 'car' ] );
		$relationship_2_id = $this->factory->post->create( [ 'post_type' => 'car' ] );
		add_relationship( $post_id, 'cars', $relationship_id );

		$this->content_models['person']['fields']['1648576059444']['cardinality'] = 'one-to-one';
		update_registered_content_types( $this->content_models );
		Plugin::instance()->setup();
		do_action( 'init' );

		$this->assertFalse( add_relationship( $post_id, 'cars', $relationship_2_id ) );
	}

	public function test_get_relationship_will_return_the_relationship_object() {
		$post_id      = $this->factory->post->create( [ 'post_type' => 'person' ] );
		$relationship = get_relationship( $post_id, 'cars' );

		$this->assertInstanceOf( PostToPost::class, $relationship );
	}

	public function test_get_relationship_will_return_WP_Error_if_invalid_post() {
		$result = get_relationship( 999, 'cars' );

		$this->assertEquals( 'invalid_post_object', $result->get_error_code() );
		$this->assertEquals( 'The post object was invalid', $result->get_error_message() );
	}

	public function test_get_relationship_will_return_WP_Error_if_invalid_field() {
		$post_id = $this->factory->post->create( [ 'post_type' => 'person' ] );
		$result  = get_relationship( $post_id, 'does_not_exist' );

		$this->assertEquals( 'field_not_found', $result->get_error_code() );
		$this->assertEquals( 'Content model field not found', $result->get_error_message() );
	}

	public function test_get_relationship_will_return_WP_Error_if_invalid_content_model_relationship() {
		$this->content_models['person']['fields']['1648576059444']['reference'] = 'does_not_exist';
		update_option( 'atlas_content_modeler_post_types', $this->content_models );

		$post_id          = $this->factory->post->create( [ 'post_type' => 'person' ] );
		$relationship_ids = $this->factory->post->create_many( 3, [ 'post_type' => 'car' ] );
		$result           = get_relationship( $post_id, 'cars' );

		$this->assertEquals( 'content_relationship_not_found', $result->get_error_code() );
		$this->assertEquals( 'Content model relationship not found', $result->get_error_message() );
	}

	/**
	 * Get associated relationship post ids for the given post id.
	 *
	 * @global $wpdb
	 *
	 * @param int $post_id The post id.
	 *
	 * @return array Array of relationship post ids.
	 */
	protected function get_relationship_ids( int $post_id ) {
		global $wpdb;

		$sql    = "SELECT `id2`
			FROM `{$wpdb->prefix}acm_post_to_post`
			WHERE `id1` IN( %d );";
		$result = $wpdb->get_results( $wpdb->prepare( $sql, $post_id ) );

		return array_map( 'intval', wp_list_pluck( $result, 'id2' ) );
	}
}
