<?php
/**
 * Validation exception.
 *
 * @package AtlasContentModeler
 */

declare(strict_types=1);

namespace WPE\AtlasContentModeler;

use WPE\AtlasContentModeler\WP_Error;

/**
 * Class Validation_Exception
 */
class Validation_Exception extends \Exception {
	/**
	 * Array of additional exception messages.
	 *
	 * @var array<int, string>
	 */
	protected $additional_messages = [];

	/**
	 * Get the exception message as a WP_Error.
	 *
	 * @param string $code Optional. The WP_Error code.
	 *
	 * @return \WP_Error The exception as a WP_Error.
	 */
	public function as_wp_error( $code = 'invalid_value' ): \WP_Error {
		$wp_error = new WP_Error();

		if ( ! empty( $this->additional_messages ) ) {
			$wp_error->add_multiple( $code, $this->additional_messages );
		}

		if ( ! empty( $this->message ) ) {
			$wp_error->add( $code, $this->getMessage() );
		}

		return $wp_error;
	}

	/**
	 * Add an array of additional messages.
	 *
	 * @param array<int,string> $messages Array of exception messages.
	 *
	 * @return void
	 */
	public function add_messages( array $messages ): void {
		foreach ( $messages as $index => $message ) {
			$this->add_message( $message, $index );
		}
	}

	/**
	 * Add an additional message.
	 *
	 * @param string   $message An exception message.
	 * @param int|null $index Optional array index.
	 *
	 * @return void
	 */
	public function add_message( string $message, ?int $index = null ): void {
		if ( is_null( $index ) ) {
			$this->additional_messages[] = $message;
		} else {
			$this->additional_messages[ $index ] = $message;
		}
	}
}
