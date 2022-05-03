<?php

use WPE\AtlasContentModeler\ContentConnect\Plugin;
use WPE\AtlasContentModeler\Validation_Exception;

use function WPE\AtlasContentModeler\API\validation\validate_model_field_data;
use function WPE\AtlasContentModeler\API\validation\validate_multiple_choice_field;
use function WPE\AtlasContentModeler\ContentRegistration\update_registered_content_types;
use function WPE\AtlasContentModeler\API\validation\validate_in_array;
use function WPE\AtlasContentModeler\API\validation\validate_array;
use function WPE\AtlasContentModeler\API\validation\validate_string;
use function WPE\AtlasContentModeler\API\validation\validate_number;
use function WPE\AtlasContentModeler\API\validation\validate_date;
use function WPE\AtlasContentModeler\API\validation\validate_min;
use function WPE\AtlasContentModeler\API\validation\validate_max;
use function WPE\AtlasContentModeler\API\validation\validate_post_exists;
use function WPE\AtlasContentModeler\API\validation\validate_post_type;
use function WPE\AtlasContentModeler\API\validation\validate_post_is_attachment;

class TestValidationFunctions extends Integration_TestCase {
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
	}

	public function test_validate_model_field_data_will_return_true_for_valid_data() {
		$model_schema = $this->content_models['validation'];
		$data         = [
			'textField'                 => 'John Doe',
			'repeatableTextField'       => [ 'John', 'Doe' ],
			'richTextField'             => '<p>This is a description</p>',
			'repeatableRichTextField'   => [ '<p>This is excerpt one</p>', '<p>This is excerpt two</p>' ],
			'numberField'               => '21',
			'repeatableNumberField'     => [ 1, 2, 3, 4, 5 ],
			'dateField'                 => '2001-04-01',
			'repeatableDateField'       => [ '2022-07-04', '2022-10-31' ],
			'singleMultipleChoiceField' => [ 'choice1' ],
			'multiMultipleChoiceField'  => [ 'choice1', 'choice2' ],
		];

		$this->assertTrue( validate_model_field_data( $model_schema, $data ) );
	}

	public function test_validate_model_field_data_will_return_true_if_only_required_model_data_given() {
		$model_schema = $this->content_models['validation'];

		$model_schema['fields'][1649787479673]['required'] = true;

		$this->assertTrue( validate_model_field_data( $model_schema, [ 'textField' => 'John Doe' ] ) );
	}

	public function test_validate_model_field_data_will_return_true_if_no_data_given_without_required_fields() {
		$model_schema = $this->content_models['validation'];

		$this->assertTrue( validate_model_field_data( $model_schema, [] ) );
	}

	public function test_validate_model_field_data_will_return_true_with_non_field_data_given() {
		$model_schema = $this->content_models['validation'];
		$data         = [
			'sku' => '2083947523',
			'id'  => 'q3p90874hfq3p984fhjqn3',
		];

		$this->assertTrue( validate_model_field_data( $model_schema, $data ) );
	}

	public function test_validate_model_field_data_will_return_WP_Error_for_invalid_text_field() {
		$model_schema = $this->content_models['validation'];

		$model_schema['fields'][1649787479673]['required'] = true;

		$valid = validate_model_field_data( $model_schema, [] );
		$this->assertEquals( [ 'Text Field field is required' ], $valid->get_error_messages( 'invalid_model_field' ) );

		$valid = validate_model_field_data( $model_schema, [ 'textField' => '' ] );
		$this->assertEquals( [ 'Text Field cannot be empty' ], $valid->get_error_messages( 'invalid_model_field' ) );

		$valid = validate_model_field_data( $model_schema, [ 'textField' => 1 ] );
		$this->assertEquals( [ 'Text Field must be valid text' ], $valid->get_error_messages( 'invalid_model_field' ) );
	}

	public function test_validate_model_field_data_will_return_WP_Error_for_invalid_repeatable_text_field() {
		$model_schema = $this->content_models['validation'];

		$model_schema['fields'][1649787498608]['required'] = true;

		$valid = validate_model_field_data( $model_schema, [] );
		$this->assertEquals( [ 'Repeatable Text Field field is required' ], $valid->get_error_messages( 'invalid_model_field' ) );

		$valid = validate_model_field_data( $model_schema, [ 'repeatableTextField' => '' ] );
		$this->assertEquals( [ 'Repeatable Text Field must be an array of text' ], $valid->get_error_messages( 'invalid_model_field' ) );

		$valid = validate_model_field_data( $model_schema, [ 'repeatableTextField' => [] ] );
		$this->assertEquals( [ 'Repeatable Text Field cannot be empty' ], $valid->get_error_messages( 'invalid_model_field' ) );
	}

	public function test_validate_model_field_data_will_return_WP_Error_for_invalid_richtext_field() {
		$model_schema = $this->content_models['validation'];

		$model_schema['fields'][1649787509847]['required'] = true;

		$valid = validate_model_field_data( $model_schema, [] );
		$this->assertEquals( [ 'Rich Text Field field is required' ], $valid->get_error_messages( 'invalid_model_field' ) );

		$valid = validate_model_field_data( $model_schema, [ 'richTextField' => '' ] );
		$this->assertEquals( [ 'Rich Text Field cannot be empty' ], $valid->get_error_messages( 'invalid_model_field' ) );

		$valid = validate_model_field_data( $model_schema, [ 'richTextField' => 1 ] );
		$this->assertEquals( [ 'Rich Text Field must be valid richtext' ], $valid->get_error_messages( 'invalid_model_field' ) );
	}

	public function test_validate_model_field_data_will_return_WP_Error_for_invalid_repeatable_richtext_field() {
		$model_schema = $this->content_models['validation'];

		$model_schema['fields'][1649787528544]['required'] = true;

		$valid = validate_model_field_data( $model_schema, [] );
		$this->assertEquals( [ 'Repeatable Rich Text Field field is required' ], $valid->get_error_messages( 'invalid_model_field' ) );

		$valid = validate_model_field_data( $model_schema, [ 'repeatableRichTextField' => '' ] );
		$this->assertEquals( [ 'Repeatable Rich Text Field must be an array of richtext' ], $valid->get_error_messages( 'invalid_model_field' ) );

		$valid = validate_model_field_data( $model_schema, [ 'repeatableRichTextField' => [] ] );
		$this->assertEquals( [ 'Repeatable Rich Text Field cannot be empty' ], $valid->get_error_messages( 'invalid_model_field' ) );
	}

	public function test_validate_model_field_data_will_return_WP_Error_for_invalid_number_field() {
		$model_schema = $this->content_models['validation'];

		$model_schema['fields'][1649787543496]['required'] = true;

		$valid = validate_model_field_data( $model_schema, [] );
		$this->assertEquals( [ 'Number Field field is required' ], $valid->get_error_messages( 'invalid_model_field' ) );

		$valid = validate_model_field_data( $model_schema, [ 'numberField' => '' ] );
		$this->assertEquals( [ 'Number Field cannot be empty' ], $valid->get_error_messages( 'invalid_model_field' ) );

		$valid = validate_model_field_data( $model_schema, [ 'numberField' => 'not_a_number' ] );
		$this->assertEquals( [ 'Number Field must be a valid number' ], $valid->get_error_messages( 'invalid_model_field' ) );
	}

	public function test_validate_model_field_data_will_return_WP_Error_for_invalid_repeatable_number_field() {
		$model_schema = $this->content_models['validation'];

		$model_schema['fields'][1649787560968]['required'] = true;

		$valid = validate_model_field_data( $model_schema, [] );
		$this->assertEquals( [ 'Repeatable Number Field field is required' ], $valid->get_error_messages( 'invalid_model_field' ) );

		$valid = validate_model_field_data( $model_schema, [ 'repeatableNumberField' => 1 ] );
		$this->assertEquals( [ 'Repeatable Number Field must be an array of number' ], $valid->get_error_messages( 'invalid_model_field' ) );

		$valid = validate_model_field_data( $model_schema, [ 'repeatableNumberField' => [] ] );
		$this->assertEquals( [ 'Repeatable Number Field cannot be empty' ], $valid->get_error_messages( 'invalid_model_field' ) );
	}

	public function test_validate_model_field_data_will_return_WP_Error_for_invalid_date_field() {
		$model_schema = $this->content_models['validation'];

		$model_schema['fields'][1649787611492]['required'] = true;

		$valid = validate_model_field_data( $model_schema, [] );
		$this->assertEquals( [ 'Date Field field is required' ], $valid->get_error_messages( 'invalid_model_field' ) );

		$valid = validate_model_field_data( $model_schema, [ 'dateField' => '' ] );
		$this->assertEquals( [ 'Date Field must be a valid date' ], $valid->get_error_messages( 'invalid_model_field' ) );

		$valid = validate_model_field_data( $model_schema, [ 'dateField' => '1/1/2021' ] );
		$this->assertEquals( [ 'Date Field must be a valid date' ], $valid->get_error_messages( 'invalid_model_field' ) );
	}

	public function test_validate_model_field_data_will_return_WP_Error_for_invalid_repeatable_date_field() {
		$model_schema = $this->content_models['validation'];

		$model_schema['fields'][1649787623430]['required'] = true;

		$valid = validate_model_field_data( $model_schema, [] );
		$this->assertEquals( [ 'Repeatable Date Field field is required' ], $valid->get_error_messages( 'invalid_model_field' ) );

		$valid = validate_model_field_data( $model_schema, [ 'repeatableDateField' => '2022-04-08' ] );
		$this->assertEquals( [ 'Repeatable Date Field must be an array of date' ], $valid->get_error_messages( 'invalid_model_field' ) );

		$valid = validate_model_field_data( $model_schema, [ 'repeatableDateField' => [] ] );
		$this->assertEquals( [ 'Repeatable Date Field cannot be empty' ], $valid->get_error_messages( 'invalid_model_field' ) );
	}

	public function test_validate_multiple_choice_field_will_return_WP_Error_for_invalid_single_choice_field() {
		$model_schema = $this->content_models['validation'];

		$model_schema['fields'][1649787666652]['required'] = true;

		$valid = validate_model_field_data( $model_schema, [] );
		$this->assertEquals( [ 'Single Multiple Choice Field field is required' ], $valid->get_error_messages( 'invalid_model_field' ) );

		$valid = validate_model_field_data( $model_schema, [ 'singleMultipleChoiceField' => [] ] );
		$this->assertEquals( [ 'Single Multiple Choice Field cannot be empty' ], $valid->get_error_messages( 'invalid_model_field' ) );

		$valid = validate_model_field_data( $model_schema, [ 'singleMultipleChoiceField' => 'choice1' ] );
		$this->assertEquals( [ 'Single Multiple Choice Field must be an array of choices' ], $valid->get_error_messages( 'invalid_model_field' ) );

		$valid = validate_model_field_data( $model_schema, [ 'singleMultipleChoiceField' => [ 'choice1', 'choice2' ] ] );
		$this->assertEquals( [ 'Single Multiple Choice Field cannot have more than one choice' ], $valid->get_error_messages( 'invalid_model_field' ) );

		$valid = validate_model_field_data( $model_schema, [ 'singleMultipleChoiceField' => [ 'not_a_choice' ] ] );
		$this->assertEquals( [ 'Single Multiple Choice Field must only contain choice values' ], $valid->get_error_messages( 'invalid_model_field' ) );
	}

	public function test_validate_multiple_choice_field_will_return_WP_Error_for_invalid_multiple_choice_field() {
		$model_schema = $this->content_models['validation'];

		$model_schema['fields'][1649787701753]['required'] = true;

		$valid = validate_model_field_data( $model_schema, [] );
		$this->assertEquals( [ 'Multi Multiple Choice Field field is required' ], $valid->get_error_messages( 'invalid_model_field' ) );

		$valid = validate_model_field_data( $model_schema, [ 'multiMultipleChoiceField' => [] ] );
		$this->assertEquals( [ 'Multi Multiple Choice Field cannot be empty' ], $valid->get_error_messages( 'invalid_model_field' ) );

		$valid = validate_model_field_data( $model_schema, [ 'multiMultipleChoiceField' => [ 'purple' ] ] );
		$this->assertEquals( [ 'Multi Multiple Choice Field must only contain choice values' ], $valid->get_error_messages( 'invalid_model_field' ) );
	}

	/**
	 * @testWith
	 * [ "0000-01-01" ]
	 * [ "9999-12-31" ]
	 */
	public function test_validate_date_will_return_null_if_valid_date_format( $value ) {
		$this->assertNull( validate_date( $value ) );
	}

	/**
	 * @testWith
	 * [ "0000-00-00" ]
	 * [ "4/8/2022" ]
	 * [ "not_a_date" ]
	 * [ 290834752098 ]
	 * [ "" ]
	 * [ null ]
	 */
	public function test_validate_date_will_throw_an_exception_if_invalid_date_format( $value ) {
		$this->expectException( Validation_Exception::class );
		$this->expectExceptionMessage( 'Value must be of format YYYY-MM-DD' );

		validate_date( $value );
	}

	public function test_validate_date_will_use_the_custom_message() {
		$message = 'That value is not a date';
		$this->expectExceptionMessage( $message );

		validate_date( null, $message );
	}

	/**
	 * @testWith
	 * [ 0 ]
	 * [ 0.1 ]
	 * [ 1.0 ]
	 * [ 1 ]
	 */
	public function test_validate_number_will_return_null_if_valid_number( $value ) {
		$this->assertNull( validate_number( $value ) );
	}

	/**
	 * @testWith
	 * [ "" ]
	 * [ [] ]
	 * [ true ]
	 * [ null ]
	 */
	public function test_validate_number_will_throw_an_exception_if_value_is_not_an_number( $value ) {
		$this->expectException( Validation_Exception::class );
		$this->expectExceptionMessage( 'Value must be a valid number' );

		validate_number( $value );
	}

	public function test_validate_number_will_use_the_custom_message() {
		$message = 'That value is not a number';
		$this->expectExceptionMessage( $message );

		validate_number( null, $message );
	}

	public function test_validate_string_will_return_null_if_valid_string() {
		$this->assertNull( validate_string( '' ) );
	}

	/**
	 * @testWith
	 * [ 0 ]
	 * [ [] ]
	 * [ true ]
	 * [ null ]
	 */
	public function test_validate_string_will_throw_an_exception_if_value_is_not_an_string( $value ) {
		$this->expectException( Validation_Exception::class );
		$this->expectExceptionMessage( 'Value is not of type string' );

		validate_string( $value );
	}

	public function test_validate_string_will_use_the_custom_message() {
		$message = 'That value is not a string';
		$this->expectExceptionMessage( $message );

		validate_string( null, $message );
	}

	public function test_validate_array_will_return_null_if_valid_array() {
		$this->assertNull( validate_array( [] ) );
	}

	/**
	 * @testWith
	 * [ 0 ]
	 * [ "" ]
	 * [ true ]
	 * [ null ]
	 */
	public function test_validate_array_will_throw_an_exception_if_value_is_not_an_array( $value ) {
		$this->expectException( Validation_Exception::class );
		$this->expectExceptionMessage( 'Value is not of type array' );

		validate_array( $value );
	}

	public function test_validate_array_will_use_the_custom_message() {
		$message = 'That value is not an array';
		$this->expectExceptionMessage( $message );

		validate_array( null, $message );
	}

	/**
	 * @testWith
	 * [ 1, [ 1, 2 ] ]
	 * [ [ 1 ], [ 1, 2 ] ]
	 * [ [ 1, 2 ], [ 1, 2 ] ]
	 */
	public function test_validate_in_array_will_return_null_if_values_exist_in_array( $value, $array ) {
		$this->assertNull( validate_in_array( $value, $array ) );
	}

	/**
	 * @testWith
	 * [ 4, [ 1, 2, 3 ] ]
	 * [ [ 4 ], [ 1, 2, 3 ] ]
	 */
	public function test_validate_in_array_will_throw_an_exception_if_values_do_not_exist_in_array( $values, $array ) {
		$this->expectException( Validation_Exception::class );
		$this->expectExceptionMessage( 'Values not found within array' );

		validate_in_array( $values, $array );
	}

	public function test_validate_in_array_will_use_the_custom_message() {
		$message = 'That value is not in the array';
		$this->expectExceptionMessage( $message );

		validate_in_array( 'test', [], $message );
	}

	/**
	 * @testWith
	 * [ "", 1 ]
	 * [ "a", 2 ]
	 * [ [], 1 ]
	 * [ [ "item" ], 2 ]
	 * [ 0, 1 ]
	 * [ 1, 2 ]
	 */
	public function test_validate_min_will_throw_an_exception_if_invalid( $value, $min ) {
		$this->expectException( Validation_Exception::class );
		$this->expectExceptionMessage( 'The field must be at least the minimum' );

		validate_min( $value, $min );
	}

	/**
	 * @testWith
	 * [ "", 0 ]
	 * [ "a", 1 ]
	 * [ [], 0 ]
	 * [ [ "item" ], 1 ]
	 * [ 0, 0 ]
	 * [ 1, 1 ]
	 */
	public function test_validate_min_will_not_throw_an_exception_if_valid( $value, $min ) {
		$this->assertNull( validate_min( $value, $min ) );
	}

	/**
	 * @testWith
	 * [ "", 1, "The field must be at least 1 character" ]
	 * [ [], 1, "The field must contain at least 1 item" ]
	 * [ 0, 1, "The value must equal 1 or greater" ]
	 */
	public function test_validate_min_will_use_a_custom_exception_message( $value, $min, $message ) {
		$this->expectExceptionMessage( $message );

		validate_min( $value, $min, $message );
	}

	/**
	 * @testWith
	 * [ "22", 1 ]
	 * [ [ "item", "item2" ], 1 ]
	 * [ 2, 1 ]
	 */
	public function test_validate_max_will_throw_an_exception_if_invalid( $value, $max ) {
		$this->expectException( Validation_Exception::class );
		$this->expectExceptionMessage( 'The field cannot exceed the maximum' );

		validate_max( $value, $max );
	}

	/**
	 * @testWith
	 * [ "", 1 ]
	 * [ "a", 1 ]
	 * [ [], 1 ]
	 * [ [ "item" ], 1 ]
	 * [ 0, 1 ]
	 * [ 1, 1 ]
	 */
	public function test_validate_max_will_not_throw_an_exception_if_valid( $value, $max ) {
		$this->assertNull( validate_max( $value, $max ) );
	}

	/**
	 * @testWith
	 * [ "aa", 1, "The field cannot be greater than 1 character" ]
	 * [ [ "item", "item2" ], 1, "The field cannot contain more than 1 items" ]
	 * [ 2, 1, "The value cannot exceed 1" ]
	 */
	public function test_validate_max_will_use_a_custom_exception_message( $value, $max, $message ) {
		$this->expectExceptionMessage( $message );

		validate_max( $value, $max, $message );
	}

	public function test_validate_post_exists_will_throw_an_exception_if_post_does_not_exist() {
		$this->expectException( Validation_Exception::class );
		$this->expectExceptionMessage( 'The post object was not found' );

		validate_post_exists( 0 );
	}

	/**
	 * @testWith
	 * [ "A post object could not be found" ]
	 */
	public function test_validate_post_exists_will_use_a_custom_exception_message( $message ) {
		$this->expectExceptionMessage( $message );

		validate_post_exists( 0, $message );
	}

	public function test_validate_post_exists_will_return_null_if_valid_post_object() {
		$post_id = $this->factory->post->create();

		$this->assertNull(
			validate_post_exists( $post_id )
		);
	}

	public function test_validate_post_type_will_throw_an_exception_if_invalid_post_type() {
		$this->expectException( Validation_Exception::class );
		$this->expectExceptionMessage( 'Invalid post type' );

		$post_id = $this->factory->post->create();

		validate_post_type( $post_id, 'page' );
	}

	/**
	 * @testWith
	 * [ "Post type must be a page" ]
	 */
	public function test_validate_post_type_will_use_a_custom_exception_message( $message ) {
		$this->expectExceptionMessage( $message );

		validate_post_type( 0, 'page', $message );
	}

	public function test_validate_post_type_will_return_null_if_valid_post_type() {
		$wp_post = $this->factory->post->create_and_get();

		$this->assertNull(
			validate_post_type( $wp_post->ID, $wp_post->post_type )
		);
	}

	public function test_validate_post_is_attachment_will_throw_an_exception_if_post_not_an_attachment() {
		$this->expectException( Validation_Exception::class );
		$this->expectExceptionMessage( 'Post is not an attachment post type' );

		$post_id = $this->factory->post->create();

		validate_post_is_attachment( $post_id );
	}

	/**
	 * @testWith
	 * [ "This is not an attachment" ]
	 */
	public function test_validate_post_is_attachment_will_use_a_custom_exception_message( $message ) {
		$this->expectExceptionMessage( $message );

		$post_id = $this->factory->post->create();

		validate_post_is_attachment( $post_id, $message );
	}

	public function test_validate_post_is_attachment_will_return_null_if_valid_attachment() {
		$post_id = $this->factory->post->create( [ 'post_type' => 'attachment' ] );

		$this->assertNull(
			validate_post_is_attachment( $post_id )
		);
	}
}
