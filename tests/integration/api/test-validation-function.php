<?php

use WPE\AtlasContentModeler\ContentConnect\Plugin;
use WPE\AtlasContentModeler\Validation_Exception;

use function WPE\AtlasContentModeler\API\validation\validate_model_field_data;
use function WPE\AtlasContentModeler\API\validation\validate_multiple_choice_field;
use function WPE\AtlasContentModeler\ContentRegistration\update_registered_content_types;
use function WPE\AtlasContentModeler\API\validation\validate_max;
use function WPE\AtlasContentModeler\API\validation\validate_min;
use function WPE\AtlasContentModeler\API\validation\validate_in_array;
use function WPE\AtlasContentModeler\API\validation\validate_array;
use function WPE\AtlasContentModeler\API\validation\validate_string;
use function WPE\AtlasContentModeler\API\validation\validate_number;
use function WPE\AtlasContentModeler\API\validation\validate_date;

class TestValidationFunctions extends Integration_TestCase {
	public function test_validate_model_field_data_will_return_WP_Error_for_invalid_text_field() {
		$text_field = $this->generate_field( 'Name', 'text', [ 'required' => true ] );
		$schema     = $this->generate_model_schema( [ $text_field ] );

		$valid = validate_model_field_data( $schema, [] );
		$this->assertEquals( [ 'Name field is required' ], $valid->get_error_messages( 'invalid_model_field' ) );

		$valid = validate_model_field_data( $schema, [ 'name' => '' ] );
		$this->assertEquals( [ 'Name cannot be empty' ], $valid->get_error_messages( 'invalid_model_field' ) );

		$valid = validate_model_field_data( $schema, [ 'name' => 1 ] );
		$this->assertEquals( [ 'Name must be valid text' ], $valid->get_error_messages( 'invalid_model_field' ) );
	}

	public function test_validate_model_field_data_will_return_WP_Error_for_invalid_repeatable_text_field() {
		$text_field = $this->generate_field(
			'Name',
			'text',
			[
				'required'     => true,
				'isRepeatable' => true,
			]
		);
		$schema     = $this->generate_model_schema( [ $text_field ] );

		$valid = validate_model_field_data( $schema, [] );
		$this->assertEquals( [ 'Name field is required' ], $valid->get_error_messages( 'invalid_model_field' ) );

		$valid = validate_model_field_data( $schema, [ 'name' => '' ] );
		$this->assertEquals( [ 'Name must be an array of text' ], $valid->get_error_messages( 'invalid_model_field' ) );

		$valid = validate_model_field_data( $schema, [ 'name' => [] ] );
		$this->assertEquals( [ 'Name cannot be empty' ], $valid->get_error_messages( 'invalid_model_field' ) );
	}

	public function test_validate_model_field_data_will_return_WP_Error_for_invalid_richtext_field() {
		$text_field = $this->generate_field( 'Content', 'richtext', [ 'required' => true ] );
		$schema     = $this->generate_model_schema( [ $text_field ] );

		$valid = validate_model_field_data( $schema, [] );
		$this->assertEquals( [ 'Content field is required' ], $valid->get_error_messages( 'invalid_model_field' ) );

		$valid = validate_model_field_data( $schema, [ 'content' => '' ] );
		$this->assertEquals( [ 'Content cannot be empty' ], $valid->get_error_messages( 'invalid_model_field' ) );

		$valid = validate_model_field_data( $schema, [ 'content' => 1 ] );
		$this->assertEquals( [ 'Content must be valid richtext' ], $valid->get_error_messages( 'invalid_model_field' ) );
	}

	public function test_validate_model_field_data_will_return_WP_Error_for_invalid_repeatable_richtext_field() {
		$text_field = $this->generate_field(
			'Content',
			'richtext',
			[
				'required'     => true,
				'isRepeatable' => true,
			]
		);
		$schema     = $this->generate_model_schema( [ $text_field ] );

		$valid = validate_model_field_data( $schema, [] );
		$this->assertEquals( [ 'Content field is required' ], $valid->get_error_messages( 'invalid_model_field' ) );

		$valid = validate_model_field_data( $schema, [ 'content' => '' ] );
		$this->assertEquals( [ 'Content must be an array of richtext' ], $valid->get_error_messages( 'invalid_model_field' ) );

		$valid = validate_model_field_data( $schema, [ 'content' => [] ] );
		$this->assertEquals( [ 'Content cannot be empty' ], $valid->get_error_messages( 'invalid_model_field' ) );
	}

	public function test_validate_model_field_data_will_return_WP_Error_for_invalid_number_field() {
		$text_field = $this->generate_field( 'Total', 'number', [ 'required' => true ] );
		$schema     = $this->generate_model_schema( [ $text_field ] );

		$valid = validate_model_field_data( $schema, [] );
		$this->assertEquals( [ 'Total field is required' ], $valid->get_error_messages( 'invalid_model_field' ) );

		$valid = validate_model_field_data( $schema, [ 'total' => '' ] );
		$this->assertEquals( [ 'Total cannot be empty' ], $valid->get_error_messages( 'invalid_model_field' ) );

		$valid = validate_model_field_data( $schema, [ 'total' => 'not_a_number' ] );
		$this->assertEquals( [ 'Total must be a valid number' ], $valid->get_error_messages( 'invalid_model_field' ) );
	}

	public function test_validate_model_field_data_will_return_WP_Error_for_invalid_repeatable_number_field() {
		$text_field = $this->generate_field(
			'Quantity',
			'number',
			[
				'required'     => true,
				'isRepeatable' => true,
			]
		);
		$schema     = $this->generate_model_schema( [ $text_field ] );

		$valid = validate_model_field_data( $schema, [] );
		$this->assertEquals( [ 'Quantity field is required' ], $valid->get_error_messages( 'invalid_model_field' ) );

		$valid = validate_model_field_data( $schema, [ 'quantity' => 1 ] );
		$this->assertEquals( [ 'Quantity must be an array of number' ], $valid->get_error_messages( 'invalid_model_field' ) );

		$valid = validate_model_field_data( $schema, [ 'quantity' => [] ] );
		$this->assertEquals( [ 'Quantity cannot be empty' ], $valid->get_error_messages( 'invalid_model_field' ) );
	}

	public function test_validate_model_field_data_will_return_WP_Error_for_invalid_date_field() {
		$text_field = $this->generate_field( 'Birthday', 'date', [ 'required' => true ] );
		$schema     = $this->generate_model_schema( [ $text_field ] );

		$valid = validate_model_field_data( $schema, [] );
		$this->assertEquals( [ 'Birthday field is required' ], $valid->get_error_messages( 'invalid_model_field' ) );

		$valid = validate_model_field_data( $schema, [ 'birthday' => '' ] );
		$this->assertEquals( [ 'Birthday must be a valid date' ], $valid->get_error_messages( 'invalid_model_field' ) );

		$valid = validate_model_field_data( $schema, [ 'birthday' => '1/1/2021' ] );
		$this->assertEquals( [ 'Birthday must be a valid date' ], $valid->get_error_messages( 'invalid_model_field' ) );
	}

	public function test_validate_model_field_data_will_return_WP_Error_for_invalid_repeatable_date_field() {
		$text_field = $this->generate_field(
			'Birthday',
			'date',
			[
				'required'     => true,
				'isRepeatable' => true,
			]
		);
		$schema     = $this->generate_model_schema( [ $text_field ] );

		$valid = validate_model_field_data( $schema, [] );
		$this->assertEquals( [ 'Birthday field is required' ], $valid->get_error_messages( 'invalid_model_field' ) );

		$valid = validate_model_field_data( $schema, [ 'birthday' => '2022-04-08' ] );
		$this->assertEquals( [ 'Birthday must be an array of date' ], $valid->get_error_messages( 'invalid_model_field' ) );

		$valid = validate_model_field_data( $schema, [ 'birthday' => [] ] );
		$this->assertEquals( [ 'Birthday cannot be empty' ], $valid->get_error_messages( 'invalid_model_field' ) );
	}

	public function test_validate_multiple_choice_field_will_return_WP_Error_for_invalid_multiple_choice_field() {
		$text_field = $this->generate_field(
			'Color',
			'multipleChoice',
			[
				'required' => true,
				'listType' => 'single',
				'choices'  => [
					0 => [
						'name' => 'Red',
						'slug' => 'red',
					],
					1 => [
						'name' => 'Blue',
						'slug' => 'blue',
					],
					2 => [
						'name' => 'Green',
						'slug' => 'green',
					],
				],
			]
		);
		$schema     = $this->generate_model_schema( [ $text_field ] );

		$valid = validate_model_field_data( $schema, [] );
		$this->assertEquals( [ 'Color field is required' ], $valid->get_error_messages( 'invalid_model_field' ) );

		$valid = validate_model_field_data( $schema, [ 'color' => [] ] );
		$this->assertEquals( [ 'Color cannot be empty' ], $valid->get_error_messages( 'invalid_model_field' ) );

		$valid = validate_model_field_data( $schema, [ 'color' => 'red' ] );
		$this->assertEquals( [ 'Color must be an array of choices' ], $valid->get_error_messages( 'invalid_model_field' ) );

		$valid = validate_model_field_data( $schema, [ 'color' => [ 'red', 'blue' ] ] );
		$this->assertEquals( [ 'Color cannot have more than one choice' ], $valid->get_error_messages( 'invalid_model_field' ) );

		$valid = validate_model_field_data( $schema, [ 'color' => [ 'purple' ] ] );
		$this->assertEquals( [ 'Color must only contain choice values' ], $valid->get_error_messages( 'invalid_model_field' ) );

		$schema['fields'][0]['listType'] = 'multiple';

		$valid = validate_model_field_data( $schema, [ 'color' => [ 'red', 'blue', 'purple' ] ] );
		$this->assertEquals( [ 'Color must only contain choice values' ], $valid->get_error_messages( 'invalid_model_field' ) );
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

	protected function generate_model_schema( array $fields ): array {
		return [
			'fields' => $fields,
		];
	}

	/**
	 * Generate a model field.
	 *
	 * @param string $name The field name.
	 * @param array  $attributes Optional field attributes. Override default attributes.
	 *
	 * @return array The model field schema.
	 */
	protected function generate_field( string $name, string $type, array $attributes ): array {
		return array_merge(
			[
				'name'         => $name,
				'slug'         => sanitize_title( $name ),
				'type'         => $type,
				'required'     => false,
				'isRepeatable' => false,
			],
			$attributes
		);
	}
}
