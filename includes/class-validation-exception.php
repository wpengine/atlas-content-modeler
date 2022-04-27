<?php
/**
 * Validation exception.
 *
 * @package AtlasContentModeler
 */

declare(strict_types=1);

namespace WPE\AtlasContentModeler;

/**
 * Class Validation_Exception
 */
class Validation_Exception extends \Exception {
	/**
	 * Get the exception message as a WP_Error.
	 *
	 * @param string $code Optional. The WP_Error code.
	 *
	 * @return \WP_Error The exception as a WP_Error.
	 */
	public function as_wp_error( $code = 'invalid_value' ): \WP_Error {
		return new \WP_Error( $code, $this->getMessage() );
	}
}
