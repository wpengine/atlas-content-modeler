<?php
/**
 * Trait to log messages or throw exceptions in contexts where WP_CLI may or
 * may not be available.
 *
 * Allows classes to `use ACM_Log;` then call `self::error( 'message' );`.
 * That class can safely be used and tested whether or not `WP_CLI::error()`
 * is available.
 *
 * For examples see `includes/wp-cli/class-model.php`.
 *
 * @package AtlasContentModeler
 */

namespace WPE\AtlasContentModeler\WP_CLI;

use WP_Error;

/**
 * ACM_Log trait to wrap WP_CLI static methods for safe use outside of WP_CLI.
 */
trait ACM_Log {
	/**
	 * Logs output with WP_CLI if available.
	 *
	 * @param string $message Message to write to STDOUT.
	 */
	public static function log( string $message ) {
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			\WP_CLI::log( $message );
		}
	}

	/**
	 * Errors and exits if WP_CLI is available, otherwise throws an exception.
	 *
	 * @param string|WP_Error $message Message to write to STDERR.
	 * @throws \Exception Exception if WP_CLI is not available.
	 */
	public static function error( $message ) {
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			\WP_CLI::error( $message );
		}

		if ( is_wp_error( $message ) ) {
			$message = $message->get_error_message();
		}

		throw new \Exception( $message );
	}

	/**
	 * Logs a success message with WP_CLI if available.
	 *
	 * @param string $message Message to write to STDOUT.
	 */
	public static function success( string $message ) {
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			\WP_CLI::success( $message );
		}
	}

	/**
	 * Prompts user to confirm an action if WP_CLI is available.
	 *
	 * @param string $question Question to display before the prompt.
	 * @param array  $args Skips prompt if 'yes' key exists and is `true`.
	 */
	public static function confirm( string $question, array $args ) {
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			\WP_CLI::confirm( $question, $args );
		}
	}
}
