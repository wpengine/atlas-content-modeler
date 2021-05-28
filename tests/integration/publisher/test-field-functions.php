<?php
/**
 * Tests for field functions.
 *
 * @package AtlasContentModeler
 */

use function WPE\AtlasContentModeler\order_fields;
use function WPE\AtlasContentModeler\get_top_level_fields;
use function WPE\AtlasContentModeler\get_entry_title_field;
use function WPE\AtlasContentModeler\sanitize_field;
use function WPE\AtlasContentModeler\get_field_type_from_slug;

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

	public function test_get_field_type_from_slug(): void {
		$slug_to_find = 'findme';

		$fields = [
			[ 'type' => 'text', 'slug' => 'test', ],
			[ 'type' => 'media', 'slug' => $slug_to_find ],
		];

		$expected = 'media';

		$this->assertSame(
			$expected,
			get_field_type_from_slug( $slug_to_find, $fields )
		);
	}

	public function test_get_field_type_from_slug_missing_type(): void {
		$slug_to_find = 'findme';

		$fields = [
			[ 'slug' => 'test', ],
			[ 'slug' => $slug_to_find ],
		];

		$expected = 'unknown';

		$this->assertSame(
			$expected,
			get_field_type_from_slug( $slug_to_find, $fields )
		);
	}

	public function test_get_field_type_from_slug_no_slug_matches(): void {
		$slug_to_find = 'findme';

		$fields = [
			[ 'slug' => 'test', ],
			[ 'slug' => 'test2' ],
			[ 'slug' => 'test3' ],
		];

		$expected = 'unknown';

		$this->assertSame(
			$expected,
			get_field_type_from_slug( $slug_to_find, $fields )
		);
	}

	public function test_sanitize_field(): void {
		$test_cases = [
			[ 'text', '<p>Test</p>', 'Test' ],
			[ 'richtext', '<em>Test</em><script></script>', '<em>Test</em>' ],
			[ 'number', '123', '123' ],
			[ 'number', '-123', '-123' ],
			[ 'number', '1.23', '1.23' ],
			[ 'number', '123abc', '123' ],
			[ 'number', '1.23abc', '1.23' ],
			[ 'number', '1,000.00', '1000.00' ],
			[ 'number', '1.000,00', '1.00000' ],
			[ 'date', '2021-12-31', '2021-12-31' ],
			[ 'date', '2021-31-12', '' ],
			[ 'date', '12-31-2021', '' ],
			[ 'date', 'not-a-date', '' ],
			[ 'media', '123', '123' ],
			[ 'media', 'not-a-number', '' ],
			[ 'boolean', 'on', 'on' ],
			[ 'boolean', 'other', 'off' ],
			[ 'unknown-type', 'unaffected', 'unaffected' ],
		];

		foreach ( $test_cases as $test ) {
			[ $type, $input, $expected ] = $test;

			$this->assertSame(
				$expected,
				sanitize_field( $type, $input )
			);
		}
	}
}
