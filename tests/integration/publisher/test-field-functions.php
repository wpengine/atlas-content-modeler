<?php
/**
 * Tests for field functions.
 *
 * @package AtlasContentModeler
 */

use function WPE\AtlasContentModeler\order_fields;
use function WPE\AtlasContentModeler\get_entry_title_field;
use function WPE\AtlasContentModeler\sanitize_field;
use function WPE\AtlasContentModeler\sanitize_fields;
use function WPE\AtlasContentModeler\get_field_type_from_slug;
use function WPE\AtlasContentModeler\append_reverse_relationship_fields;
use function WPE\AtlasContentModeler\get_fields_by_type;

/**
 * Class FieldFunctionTestCases
 */
class FieldFunctionTestCases extends WP_UnitTestCase {

	public function test_order_fields(): void {
		$fields = [
			[
				'id'       => 123,
				'position' => 10000,
			],
			[
				'id'       => 456,
				'position' => 0,
			],
		];

		$expected = [
			[
				'id'       => 456,
				'position' => 0,
			],
			[
				'id'       => 123,
				'position' => 10000,
			],
		];

		$this->assertSame(
			$expected,
			order_fields( $fields )
		);
	}

	public function test_get_entry_title_field(): void {
		$fields = [
			[ 'id' => 123 ],
			[
				'id'      => 456,
				'isTitle' => true,
			],
		];

		$expected = [
			'id'      => 456,
			'isTitle' => true,
		];

		$this->assertSame(
			$expected,
			get_entry_title_field( $fields )
		);
	}

	public function test_get_entry_title_field_with_no_titles(): void {
		$fields = [
			[
				'id'       => 123,
				'position' => 0,
			],
			[
				'id'       => 456,
				'position' => 1,
			],
		];

		$expected = [];

		$this->assertSame(
			$expected,
			get_entry_title_field( $fields )
		);
	}

	public function test_get_field_type_from_slug(): void {
		$slug_to_find = 'findme';
		$post_type    = 'cats';

		$models = [
			$post_type => [
				'fields' => [
					[
						'type' => 'text',
						'slug' => 'test',
					],
					[
						'type' => 'media',
						'slug' => $slug_to_find,
					],
				],
			],
		];

		$expected = 'media';

		$this->assertSame(
			$expected,
			get_field_type_from_slug( $slug_to_find, $models, $post_type )
		);
	}

	public function test_get_field_type_from_slug_missing_type(): void {
		$slug_to_find = 'findme';
		$post_type    = 'cats';

		$models = [
			$post_type => [
				'fields' => [
					[ 'slug' => 'test' ],
					[ 'slug' => $slug_to_find ],
				],
			],
		];

		$expected = 'unknown';

		$this->assertSame(
			$expected,
			get_field_type_from_slug( $slug_to_find, $models, $post_type )
		);
	}

	public function test_get_field_type_from_slug_no_slug_matches(): void {
		$slug_to_find = 'findme';
		$post_type    = 'cats';

		$models = [
			$post_type => [
				'fields' => [
					[ 'slug' => 'test' ],
					[ 'slug' => 'test2' ],
					[ 'slug' => 'test3' ],
				],
			],
		];

		$expected = 'unknown';

		$this->assertSame(
			$expected,
			get_field_type_from_slug( $slug_to_find, $models, $post_type )
		);
	}

	/**
	 * Checks that the 'relationship' type is returned for relationship fields
	 * that are stored in another model with a reference to the current model.
	 */
	public function test_get_field_type_from_slug_reverse_relationship(): void {
		$slug_to_find = 'findme';

		$models = [
			'left'  => [
				'fields' => [
					/**
					 * This field will also appear in the 'right' model on
					 * publisher entry screens as a reverse relationship,
					 * even though it is stored with 'left'.
					 */
					[
						'id'            => 123,
						'slug'          => $slug_to_find,
						'type'          => 'relationship',
						'reference'     => 'right',
						'enableReverse' => true,
						'cardinality'   => 'many-to-many',
					],
				],
			],
			/**
			 * This model has no fields, but will display the relationship
			 * field from the 'left' model on publisher screens, because that
			 * field references the 'right' model and has 'enableReverse'.
			 */
			'right' => [],
		];

		/**
		 * Searching for fields in the 'right' model should include reverse
		 * relationship fields from other models that reference 'right'.
		 */
		$expected = 'relationship';

		$this->assertSame(
			$expected,
			get_field_type_from_slug( $slug_to_find, $models, 'right' )
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
			// Multiple choice fields with a singular choice should be wrapped as arrays for consistency.
			[ 'multipleChoice', 'choice1', [ 'choice1' ] ],
			// "Multi" multiple choice fields submitted with keys and values should retain only the keys.
			[
				'multipleChoice',
				[
					[ 'choice1' => 'Choice 1' ],
					[ 'choice2' => 'Choice 2' ],
				],
				[ 'choice1', 'choice2' ],
			],
			// "Multi" multiple choice fields submitted with only keys should be unaltered.
			[ 'multipleChoice', [ 'choice1', 'choice2' ], [ 'choice1', 'choice2' ] ],
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

	public function test_sanitize_fields(): void {
		$model = [
			'person' => [
				'fields' => [
					'123' => [
						'id'   => 123,
						'slug' => 'name',
						'type' => 'text',
					],
					'456' => [
						'id'   => 456,
						'slug' => 'bio',
						'type' => 'text',
					],
				],
			],
		];

		$test_data = [
			'name'    => 'text',
			'bio'     => 'value',
			'unknown' => 'value',
		];

		$this->assertSame(
			$test_data,
			sanitize_fields( $model['person'], $test_data )
		);
	}

	public function test_sanitize_fields_empty(): void {
		$model = [
			'person' => [
				'fields' => [
					'123' => [
						'id'   => 123,
						'slug' => 'name',
						'type' => 'text',
					],
					'456' => [
						'id'   => 456,
						'slug' => 'bio',
						'type' => 'text',
					],
				],
			],
		];

		$test_data = [];

		$this->assertSame(
			$test_data,
			sanitize_fields( $model['person'], $test_data )
		);
	}

	public function test_append_reverse_relationship_fields(): void {
		$expected_reverse_name = 'Test Reverse Name';

		$left_model = [
			'fields' => [
				/**
				 * This relationship field should be appended to the 'right'
				 * model's fields because it refers to that model and is
				 * enabled as a back reference.
				 */
				'456' => [
					'id'            => 456,
					'name'          => 'Original Name',
					'reference'     => 'right',
					'slug'          => 'refersToRight',
					'type'          => 'relationship',
					'enableReverse' => true,
					'reverseName'   => $expected_reverse_name,
					'cardinality'   => 'one-to-many',
				],
			],
		];

		$right_model = [
			'fields' => [
				'123' => [
					'id'   => 123,
					'slug' => 'name',
					'type' => 'text',
				],
			],
		];

		$models = [
			'left'  => $left_model,
			'right' => $right_model,
		];

		$expected = [
			'left'  => $left_model,
			'right' => [
				'fields' => [
					'123' => [
						'id'   => 123,
						'slug' => 'name',
						'type' => 'text',
					],
					'456' => [
						'id'            => 456,
						'name'          => $expected_reverse_name, // Changed from 'Original Name'.
						'reference'     => 'left', // Changed from 'right'.
						'slug'          => 'refersToRight',
						'type'          => 'relationship',
						'enableReverse' => true,
						'reverseName'   => $expected_reverse_name,
						'cardinality'   => 'many-to-one', // Cardinality is reversed.
					],
				],
			],
		];

		$this->assertSame(
			$expected,
			append_reverse_relationship_fields( $models, 'right' )
		);
	}

	/**
	 * An edge-case test to check that relationship fields with a back reference
	 * to their own model will never be added to the field list for that model.
	 * Doing so could either make them appear twice or overwrite the name with
	 * the reverseName in error.
	 */
	public function test_append_reverse_relationship_fields_do_not_append_to_self(): void {
		$models = [
			'left' => [
				'fields' => [
					/**
					 * This relationship field has a backreference to the same
					 * model it is stored on. It should not be appended to that
					 * model because this would duplicate the fields.
					 */
					'456' => [
						'id'            => 456,
						'name'          => 'Original Name',
						'reverseName'   => 'Reverse Name',
						'reference'     => 'left',
						'slug'          => 'refersToRight',
						'type'          => 'relationship',
						'enableReverse' => true,
						'cardinality'   => 'many-to-many',
					],
				],
			],
		];

		$this->assertSame(
			$models, // Should be unchanged, ignoring the back reference.
			append_reverse_relationship_fields( $models, 'left' )
		);
	}

	public function test_get_fields_by_type_will_return_fields_for_a_given_type() {
		$models = [
			'person' => [
				'fields' => [
					454189 => [
						'type' => 'text',
					],
					684759 => [
						'type' => 'number',
					],
					965422 => [
						'type' => 'text',
					],
				],
			],
		];

		update_option( 'atlas_content_modeler_post_types', $models );
		$fields = get_fields_by_type( 'text', 'person' );

		$this->assertEquals(
			[
				454189 => [
					'type' => 'text',
				],
				965422 => [
					'type' => 'text',
				],
			],
			$fields
		);

		$this->assertEmpty( get_fields_by_type( 'does_not_exist', 'person' ) );
	}
}
