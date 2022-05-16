<?php
/**
 * Test Validation_Exception.
 */

use WPE\AtlasContentModeler\WP_Error;
use WPE\AtlasContentModeler\Validation_Exception;

class Test_Validation_Exception extends WP_UnitTestCase {
	/**
	 * @var \WPE\AtlasContentModeler\Validation_Exception
	 */
	protected $validation_exception;

	public function set_up() {
		parent::set_up();

		$this->validation_exception = new Validation_Exception();
	}

	public function test_as_wp_error_will_create_a_WP_Error() {
		$validation_exception = new Validation_Exception( 'Exception message' );
		$wp_error             = $validation_exception->as_wp_error( 'error_code' );

		$this->assertInstanceOf( WP_Error::class, $wp_error );
		$this->assertEquals( 'Exception message', $wp_error->get_error_message( 'error_code' ) );
	}

	public function test_as_wp_error_will_add_additional_error_messages_as_well_as_initial_message() {
		$validation_exception = new Validation_Exception( 'Initial exception message' );
		$validation_exception->add_message( 'Another error message' );
		$validation_exception->add_message( 'Indexed error message', 3 );
		$wp_error = $validation_exception->as_wp_error( 'error_code' );

		$this->assertEquals(
			[
				0 => 'Another error message',
				3 => 'Indexed error message',
				4 => 'Initial exception message',
			],
			$wp_error->get_error_messages( 'error_code' )
		);
	}

	public function test_add_message_will_add_an_addition_exception_message() {
		$this->validation_exception->add_message( 'Another error message' );
		$this->validation_exception->add_message( 'Indexed error message', 3 );
		$wp_error = $this->validation_exception->as_wp_error( 'error_code' );

		$this->assertEquals(
			[
				0 => 'Another error message',
				3 => 'Indexed error message',
			],
			$wp_error->get_error_messages( 'error_code' )
		);
	}

	public function test_add_messages_will_add_an_array_of_messages() {
		$messages = [
			2 => 'Index 2 error message',
			4 => 'Index 4 error message',
			6 => 'Index 6 error message',
		];

		$this->validation_exception->add_messages( $messages );
		$wp_error = $this->validation_exception->as_wp_error( 'error_code' );

		$this->assertEquals( $messages, $wp_error->get_error_messages( 'error_code' ) );
	}
}
