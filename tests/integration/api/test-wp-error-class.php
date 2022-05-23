<?php
/**
 * Test WP_Error subclass.
 */

use WPE\AtlasContentModeler\WP_Error;

class Test_WP_Error extends WP_UnitTestCase {
	/**
	 * @var \WPE\AtlasContentModeler\WP_Error
	 */
	protected $wp_error;

	/**
	 * Override of parent::set_up().
	 */
	public function set_up() {
		parent::set_up();

		$this->wp_error = new WP_Error();
	}

	public function test_add_at_index_will_maintain_the_array_index() {
		$this->wp_error->add_at_index( 'error_code', 'Error message', 2 );
		$this->wp_error->add_at_index( 'error_code', 'Another message', 5 );

		$this->assertEquals(
			[
				2 => 'Error message',
				5 => 'Another message',
			],
			$this->wp_error->get_error_messages( 'error_code' )
		);
		$this->assertEquals( '', $this->wp_error->get_error_data( 'error_code' ) );
	}

	public function test_add_at_index_will_override_an_existing_index() {
		$this->wp_error->add( 'error_code', 'The first error message' );
		$this->wp_error->add( 'error_code', 'The second error message' );
		$this->wp_error->add( 'error_code', 'The third error message' );

		$this->wp_error->add_at_index( 'error_code', 'Override error message', 1 );

		$this->assertEquals(
			[
				0 => 'The first error message',
				1 => 'Override error message',
				2 => 'The third error message',
			],
			$this->wp_error->get_error_messages( 'error_code' )
		);
	}

	public function test_add_at_index_will_add_the_data_if_given() {
		$this->wp_error->add_at_index( 'error_code', 'An error message', 1, 'Some data' );

		$this->assertEquals(
			'Some data',
			$this->wp_error->get_error_data( 'error_code' )
		);
	}

	public function test_add_at_index_will_trigger_the_action() {
		add_action(
			'wp_error_added',
			function ( $code, $message, $data, $wp_error ) {
				$this->assertEquals( 'error_code', $code );
				$this->assertEquals( 'An error message', $message );
				$this->assertEquals( 'Some data', $data );
				$this->assertInstanceOf( WP_Error::class, $wp_error );
			},
			10,
			4
		);

		$this->wp_error->add_at_index( 'error_code', 'An error message', 1, 'Some data' );
	}

	public function test_add_multiple_will_add_multiple_error_messages() {
		$this->wp_error->add_multiple(
			'error_code',
			[
				2 => 'Error message index 2',
				3 => 'Error message index 3',
				6 => 'Error message index 6',
			],
			'error data'
		);

		$this->assertEquals(
			[
				2 => 'Error message index 2',
				3 => 'Error message index 3',
				6 => 'Error message index 6',
			],
			$this->wp_error->get_error_messages( 'error_code' )
		);

		$this->assertEquals(
			'error data',
			$this->wp_error->get_error_data( 'error_code' )
		);
	}

	public function test_static_copy_errors_will_maintain_array_index() {
		$second_wp_error = new WP_Error();
		$messages        = [
			2 => 'Error message index 2',
			3 => 'Error message index 3',
			6 => 'Error message index 6',
		];

		$this->wp_error->add_multiple( 'error_code', $messages );
		$this->wp_error->export_to( $second_wp_error );

		$this->assertEquals(
			$messages,
			$second_wp_error->get_error_messages( 'error_code' )
		);
	}
}
