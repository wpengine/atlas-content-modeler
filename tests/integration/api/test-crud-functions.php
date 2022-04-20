<?php

use WPE\AtlasContentModeler\ContentConnect\Plugin;
use WPE\AtlasContentModeler\ContentConnect\Relationships\PostToPost;

use function WPE\AtlasContentModeler\API\add_relationship;
use function WPE\AtlasContentModeler\API\get_relationship;
use function WPE\AtlasContentModeler\API\replace_relationship;
use function WPE\AtlasContentModeler\API\fetch_model;
use function WPE\AtlasContentModeler\API\fetch_model_field;
use function WPE\AtlasContentModeler\API\insert_model_entry;
use function WPE\AtlasContentModeler\API\update_model_entry;

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

	public function test_insert_model_entry_will_save_post_meta_and_return_post_id_on_success() {
		$data = $this->get_insert_model_entry_data();

		$model_id = insert_model_entry( 'validation', $data );

		$this->assertTrue( is_int( $model_id ) );
		$this->assertEquals( $data['textField'], get_post_meta( $model_id, 'textField', true ) );
		$this->assertEquals( $data['repeatableTextField'], get_post_meta( $model_id, 'repeatableTextField', true ) );
		$this->assertEquals( $data['richTextField'], get_post_meta( $model_id, 'richTextField', true ) );
		$this->assertEquals( $data['repeatableRichTextField'], get_post_meta( $model_id, 'repeatableRichTextField', true ) );
		$this->assertEquals( $data['numberField'], get_post_meta( $model_id, 'numberField', true ) );
		$this->assertEquals( $data['repeatableNumberField'], get_post_meta( $model_id, 'repeatableNumberField', true ) );
		$this->assertEquals( $data['dateField'], get_post_meta( $model_id, 'dateField', true ) );
		$this->assertEquals( $data['repeatableDateField'], get_post_meta( $model_id, 'repeatableDateField', true ) );
		$this->assertEquals( [ $data['singleMultipleChoiceField'] ], get_post_meta( $model_id, 'singleMultipleChoiceField', true ) );
		$this->assertEquals( $data['multiMultipleChoiceField'], get_post_meta( $model_id, 'multiMultipleChoiceField', true ) );
	}

	public function test_insert_model_entry_will_return_WP_Error_if_model_schema_does_not_exist() {
		$model_slug = 'model_does_not_exist';
		$result     = insert_model_entry( $model_slug, [] );

		$this->assertEquals( 'model_schema_not_found', $result->get_error_code() );
		$this->assertEquals( "The content model {$model_slug} was not found", $result->get_error_message() );
	}

	public function test_insert_model_entry_will_trigger_validation_by_default_if_invalid_data() {
		$model_id = insert_model_entry( 'validation', [ 'numberField' => 'not a number value' ] );

		$this->assertEquals( [ 'Number Field must be a valid number' ], $model_id->get_error_messages( 'invalid_model_field' ) );
	}

	public function test_insert_model_entry_will_not_validate_field_data_if_skip_validation_true() {
		$model_id = insert_model_entry( 'validation', [ 'numberField' => 'not a number value' ], [], true );

		$this->assertEquals( 'not a number value', get_post_meta( $model_id, 'numberField', true ) );
	}

	public function test_insert_model_entry_will_insert_post_data_if_exists() {
		$data = $this->get_insert_model_entry_data();

		$model_id = insert_model_entry( 'validation', $data, [ 'post_content' => '<p>This is my post content.</p>' ] );

		$wp_post = get_post( $model_id );
		$this->assertEquals( '<p>This is my post content.</p>', $wp_post->post_content );
	}

	public function test_insert_model_entry_will_set_the_post_title_if_field_is_a_title_field() {
		$data = $this->get_insert_model_entry_data();

		$model_id = insert_model_entry( 'validation', $data );

		$wp_post = get_post( $model_id );
		$this->assertEquals( $data['textField'], $wp_post->post_title );
	}

	public function test_insert_model_entry_will_not_set_the_post_title_if_title_field_is_not_set() {
		$data = $this->get_insert_model_entry_data();

		$this->content_models['validation']['fields'][1649787479673]['isTitle'] = false;
		update_registered_content_types( $this->content_models );

		$model_id = insert_model_entry( 'validation', $data );

		$wp_post = get_post( $model_id );
		$this->assertEquals( '', $wp_post->post_title );
	}

	public function test_update_model_entry_will_update_post_meta_and_return_post_id_on_success() {
		$data        = $this->get_insert_model_entry_data();
		$update_data = $this->get_insert_model_entry_update_data();

		$update_id = insert_model_entry( 'validation', $data );

		$updated_id = update_model_entry( $update_id, $update_data );

		$this->assertTrue( is_int( $update_id ) );
		$this->assertEquals( $update_data['textField'], get_post_meta( $updated_id, 'textField', true ) );
		$this->assertEquals( $update_data['repeatableTextField'], get_post_meta( $updated_id, 'repeatableTextField', true ) );
		$this->assertEquals( $update_data['richTextField'], get_post_meta( $updated_id, 'richTextField', true ) );
		$this->assertEquals( $update_data['repeatableRichTextField'], get_post_meta( $updated_id, 'repeatableRichTextField', true ) );
		$this->assertEquals( $update_data['numberField'], get_post_meta( $updated_id, 'numberField', true ) );
		$this->assertEquals( $update_data['repeatableNumberField'], get_post_meta( $updated_id, 'repeatableNumberField', true ) );
		$this->assertEquals( $update_data['dateField'], get_post_meta( $updated_id, 'dateField', true ) );
		$this->assertEquals( $update_data['repeatableDateField'], get_post_meta( $updated_id, 'repeatableDateField', true ) );
		$this->assertEquals( [ $update_data['singleMultipleChoiceField'] ], get_post_meta( $updated_id, 'singleMultipleChoiceField', true ) );
		$this->assertEquals( $update_data['multiMultipleChoiceField'], get_post_meta( $updated_id, 'multiMultipleChoiceField', true ) );
	}

	public function test_update_model_entry_will_update_post_meta_without_validation() {
		$data      = $this->get_insert_model_entry_data();
		$update_id = insert_model_entry( 'validation', $data );

		$updated_id = update_model_entry( $update_id, [ 'numberField' => 'not a number value' ], [], true );

		$this->assertEquals( 'not a number value', get_post_meta( $updated_id, 'numberField', true ) );
	}

	public function test_update_model_entry_will_error_with_invalid_id() {
		$updated_id = update_model_entry( '4444444', [], [], true );
		$this->assertEquals( [ 'The post ID 4444444 was not found' ], $updated_id->get_error_messages( 'model_entry_not_found' ) );
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


	/**
	 * Get an array of valid data for testing insert_entry_model().
	 *
	 * Fields work with 'validation' model.
	 *
	 * @param array $overrides Optional. Override data if needed.
	 *
	 * @return array The entry model data.
	 */
	protected function get_insert_model_entry_data( $overrides = [] ) {
		return array_merge(
			[
				'textField'                 => 'Text field value',
				'repeatableTextField'       => [
					'Repeatable Text Field Value 1',
					'Repeatable Text Field Value 2',
				],
				'richTextField'             => '<p>Rich Text Field Value</p>',
				'repeatableRichTextField'   => [
					'<p>Repeatable Rich Text Field Value 1</p>',
					'<p>Repeatable Rich Text Field Value 2</p>',
				],
				'numberField'               => 200,
				'repeatableNumberField'     => [
					9,
					10,
					11,
				],
				'dateField'                 => '2022-02-22',
				'repeatableDateField'       => [
					'2022-02-22',
					'2022-02-23',
				],
				'singleMultipleChoiceField' => 'choice2',
				'multiMultipleChoiceField'  => [ 'choice1', 'choice3' ],
			],
			$overrides
		);
	}
	/**
	 * Get an array of valid data for testing insert_entry_model().
	 *
	 * @param array $overrides Optional. Override data if needed.
	 *
	 * @return array The entry model data.
	 */
	protected function get_insert_model_entry_update_data( $overrides = [] ) {
		return array_merge(
			[
				'textField'                 => 'New text field value',
				'repeatableTextField'       => [
					'Repeatable Text Field Value 1',
					'Repeatable Text Field Value 2',
				],
				'richTextField'             => '<p>New Rich Text Field Value</p>',
				'repeatableRichTextField'   => [
					'<p>New Repeatable Rich Text Field Value 1</p>',
					'<p>New Repeatable Rich Text Field Value 2</p>',
				],
				'numberField'               => 300,
				'repeatableNumberField'     => [
					10,
					11,
					12,
				],
				'dateField'                 => '2022-02-23',
				'repeatableDateField'       => [
					'2022-02-23',
					'2022-02-24',
				],
				'singleMultipleChoiceField' => 'choice3',
				'multiMultipleChoiceField'  => [ 'choice2' ],
			],
			$overrides
		);
	}
}
