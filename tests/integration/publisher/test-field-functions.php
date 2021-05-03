<?php
/**
 * Tests for field functions.
 *
 * @package WPE_Content_Model
 */

use function WPE\ContentModel\order_fields;
use function WPE\ContentModel\get_top_level_fields;
use function WPE\ContentModel\get_first_field_of_type;
use function WPE\ContentModel\get_entry_title_field;

/**
 * Class FieldFunctionTestCases
 */
class FieldFunctionTestCases extends WP_UnitTestCase {

	public function test_order_fields(): void {
		$fields = [
			[ 'id' => 123, 'position' => 10000 ],
			[ 'id' => 456, 'position' => 0 ],
		];

		$expected = [
			[ 'id' => 456, 'position' => 0 ],
			[ 'id' => 123, 'position' => 10000 ],
		];

		$this->assertSame(
			$expected,
			order_fields( $fields )
		);
	}

	public function test_get_top_level_fields(): void {
		$fields = [
			[ 'id' => 123, 'parent' => 456 ],
			[ 'id' => 456 ],
		];

		$expected = [
			[ 'id' => 456 ]
		];

		$this->assertSame(
			$expected,
			get_top_level_fields( $fields )
		);
	}

	public function test_get_first_field_of_type(): void {
		$fields = [
			[ 'id' => 123, 'type' => 'text', 'position' => 2 ],
			[ 'id' => 456, 'type' => 'text', 'position' => 0 ],
			[ 'id' => 789, 'type' => 'text', 'position' => 1 ],
		];

		$expected = [ 'id' => 456, 'type' => 'text', 'position' => 0 ];

		$this->assertSame(
			$expected,
			get_first_field_of_type( $fields, 'text' )
		);
	}

	public function test_get_first_field_of_type_with_no_such_type(): void {
		$fields = [
			[ 'id' => 123, 'type' => 'boolean', 'position' => 2 ],
			[ 'id' => 456, 'type' => 'boolean', 'position' => 0 ],
			[ 'id' => 789, 'type' => 'boolean', 'position' => 1 ],
		];

		$expected = [];

		$this->assertSame(
			$expected,
			get_first_field_of_type( $fields, 'text' )
		);
	}

	public function test_get_entry_title_field(): void {
		$fields = [
			[ 'id' => 123 ],
			[ 'id' => 456, 'isTitle' => true ],
		];

		$expected = [ 'id' => 456, 'isTitle' => true ];

		$this->assertSame(
			$expected,
			get_entry_title_field( $fields )
		);
	}

	public function test_get_entry_title_field_with_no_titles(): void {
		$fields = [
			[ 'id' => 123, 'position' => 0 ],
			[ 'id' => 456, 'position' => 1 ],
		];

		$expected = [];

		$this->assertSame(
			$expected,
			get_entry_title_field( $fields )
		);
	}
}
