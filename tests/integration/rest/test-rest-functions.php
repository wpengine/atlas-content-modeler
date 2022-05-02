<?php
/**
 * Tests for rest-functions.php
 */

use function WPE\AtlasContentModeler\REST_API\format_response_data;
use function WPE\AtlasContentModeler\REST_API\create_rest_response;

/**
 * Class Rest_Functions_Test
 */
class Rest_Functions_Test extends WP_UnitTestCase {
	public function test_format_response_data_will_format_the_response_data() {
		$expected = [
			'success' => true,
			'data'    => [ 0, 1, 2, 3, 4, 5 ],
		];

		$this->assertEquals( $expected, format_response_data( true, range( 0, 5 ) ) );
	}

	public function test_create_rest_response_will_create_a_WP_REST_Response_object() {
		$expected = [
			'success' => true,
			'data'    => [ 0, 1, 2, 3, 4, 5 ],
		];

		$response = create_rest_response( true, range( 0, 5 ) );

		$this->assertInstanceOf( \WP_REST_Response::class, $response );
		$this->assertEquals( $expected, $response->get_data() );
		$this->assertEquals( 200, $response->get_status() );
		$this->assertEquals( [], $response->get_headers() );
	}

	public function test_create_rest_response_will_set_the_status_code() {
		$response = create_rest_response( true, [], 201 );

		$this->assertEquals( 201, $response->get_status() );
	}

	public function test_create_rest_response_will_set_the_headers() {
		$response = create_rest_response( true, [], 200, [ 'X_ACM_HEADER' => 'some_value' ] );

		$this->assertEquals( [ 'X_ACM_HEADER' => 'some_value' ], $response->get_headers() );
	}
}
