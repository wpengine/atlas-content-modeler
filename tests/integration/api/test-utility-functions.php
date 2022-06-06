<?php

use function WPE\AtlasContentModeler\API\array_extract_by_keys;
use function WPE\AtlasContentModeler\API\array_remove_by_keys;
use function WPE\AtlasContentModeler\API\trim_space;

class TestUtilityFunctions extends WP_UnitTestCase {
	public function test_array_extract_by_keys_will_extract_assoc_keys_as_array_given_keys() {
		$data = [
			'username' => 1,
			'first'    => 2,
			'last'     => 3,
			'email'    => 4,
			3          => 'some value',
		];

		$this->assertEquals(
			[ 'username' => 1 ],
			array_extract_by_keys( $data, [ 'username' ] )
		);

		$this->assertEquals(
			[
				'username' => 1,
				'email'    => 4,
				3          => 'some value',
			],
			array_extract_by_keys( $data, [ 'username', 'email', 'does_not_exist', 3 ] )
		);

		$this->assertEquals( [], array_extract_by_keys( $data, [ 'does_not_exist' ] ) );
	}

	public function test_array_remove_by_keys_will_return_an_array_without_given_keys() {
		$data = [
			'first' => 2,
			'last'  => 3,
			3       => 'some value',
		];

		$this->assertEquals(
			[ 'first' => 2 ],
			array_remove_by_keys( $data, [ 'last', 3 ] )
		);

		$this->assertEquals(
			$data,
			array_remove_by_keys( $data, [ 'does_not_exist' ] )
		);

		$this->assertEquals(
			$data,
			array_remove_by_keys( $data, [] )
		);

		$this->assertEquals(
			[],
			array_remove_by_keys( $data, [ 'first', 'last', 3 ] )
		);
	}

	/** @test */
	public function trim_space_will_trim_space_from_a_string() {
		$this->assertEquals( 'test', trim_space( '   test   ' ) );
	}

	/** @test */
	public function trim_space_will_recursively_trim_space_within_an_array() {
		$data = [
			[
				' a test string  ',
				true,
				null,
				[
					'  nested within an array ',
					[],
					1,
				],
			],
			'  example   ',
		];

		$this->assertEquals(
			[
				[
					'a test string',
					true,
					null,
					[
						'nested within an array',
						[],
						1,
					],
				],
				'example',
			],
			trim_space( $data )
		);
	}

	/** @test */
	public function trim_space_will_ignore_non_string_or_array_values() {
		$this->assertEquals( null, trim_space( null ) );
		$this->assertEquals( 1, trim_space( 1 ) );
		$this->assertEquals( 2.13, trim_space( 2.13 ) );
		$this->assertEquals( false, trim_space( false ) );
		$this->assertEquals( new stdClass(), trim_space( new stdClass() ) );
	}
}
