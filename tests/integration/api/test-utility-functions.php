<?php

use function WPE\AtlasContentModeler\API\Utility\get_data_for_fields;

class TestUtilityFunctions extends Integration_TestCase {
	public function test_get_data_for_fields_returns_array_with_keys_matching_model_field_slugs() {
		$model_fields = [
			0 => [ 'slug' => 'name' ],
			1 => [ 'slug' => 'color' ],
			2 => [ 'slug' => 'material' ],
		];

		$data = [
			'id'             => '12345670',
			'name'           => 'MT-123',
			'color'          => 'blue',
			'material'       => 'metal',
			'does_not_exist' => null,
		];

		$this->assertEquals(
			[
				'name'     => 'MT-123',
				'color'    => 'blue',
				'material' => 'metal',
			],
			get_data_for_fields( $model_fields, $data )
		);
	}

	public function test_get_data_for_fields_returns_empty_array_if_data_keys_do_not_match_any_model_field_slugs() {
		$model_fields = [
			0 => [ 'slug' => 'name' ],
			1 => [ 'slug' => 'color' ],
			2 => [ 'slug' => 'material' ],
		];

		$data = [
			0                => null,
			12               => 'nothing',
			'id'             => '12345670',
			'test'           => '009123',
			'887'            => 'blue',
			'does_not_exist' => null,
		];

		$this->assertEmpty( get_data_for_fields( $model_fields, $data ) );
	}

	public function test_get_data_for_fields_returns_empty_array_if_data_is_empty() {
		$model_fields = [
			0 => [ 'slug' => 'name' ],
			1 => [ 'slug' => 'color' ],
			2 => [ 'slug' => 'material' ],
		];

		$this->assertEmpty( get_data_for_fields( $model_fields, [] ) );
	}

	public function test_get_data_for_fields_returns_empty_array_if_model_fields_is_empty() {
		$data = [
			'id'             => '12345670',
			'name'           => 'MT-123',
			'color'          => 'blue',
			'material'       => 'metal',
			'does_not_exist' => null,
		];

		$this->assertEmpty( get_data_for_fields( [], $data ) );
	}
}
