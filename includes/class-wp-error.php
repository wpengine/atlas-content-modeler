<?php
/**
 * Atlas Content Modeler Error API.
 *
 * Extended fromWP_Error
 *
 * @package AtlasContentModeler
 */

declare(strict_types=1);

namespace WPE\AtlasContentModeler;

use \WP_Error as Base_Error;

/**
 * Override of WordPress Error class WP_Error.
 *
 * Added additional methods to account for validation logic.
 */
class WP_Error extends Base_Error {
	/**
	 * Add multiple error messages for a given code.
	 *
	 * Maintains array index when adding errors. Will override
	 * existing error messages with the same numeric index.
	 *
	 * @param string|int $code     Error code.
	 * @param array      $messages Array of error messages.
	 * @param mixed      $data     Optional. Error data.
	 */
	public function add_multiple( $code, array $messages, $data = '' ): void {
		foreach ( $messages as $index => $message ) {
			$this->add_at_index( $code, $message, $index, $data );
		}
	}

	/**
	 * Adds an error at the given index or appends an additional message to an existing error.
	 *
	 * @param string|int $code    Error code.
	 * @param string     $message Error message.
	 * @param string|int $index   The desired array index.
	 * @param mixed      $data    Optional. Error data.
	 *
	 * @return void
	 */
	public function add_at_index( $code, $message, $index, $data = '' ): void {
		$this->errors[ $code ][ $index ] = $message;

		if ( ! empty( $data ) ) {
			$this->add_data( $data, $code );
		}

		/**
		 * Fires when an error is added to a WP_Error object.
		 *
		 * @param string|int $code     Error code.
		 * @param string     $message  Error message.
		 * @param mixed      $data     Error data. Might be empty.
		 * @param WP_Error   $wp_error The WP_Error object.
		 */
		\do_action( 'wp_error_added', $code, $message, $data, $this ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
	}

	/**
	 * Override of WP_Error::copy_errors().
	 *
	 * Copies errors from one WP_Error instance to another.
	 *
	 * @param \WP_Error $from The WP_Error to copy from.
	 * @param \WP_Error $to   The WP_Error to copy to.
	 */
	protected static function copy_errors( \WP_Error $from, \WP_Error $to ) {
		foreach ( $from->get_error_codes() as $code ) {
			$to->add_multiple( $code, $from->get_error_messages( $code ) );

			foreach ( $from->get_all_error_data( $code ) as $data ) {
				$to->add_data( $data, $code );
			}
		}
	}
}
