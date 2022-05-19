<?php

use function WPE\AtlasContentModeler\API\array_extract_by_keys;
use function WPE\AtlasContentModeler\API\array_remove_by_keys;

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
}
