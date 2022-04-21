<?php
/**
 * Test case class for integration testing.
 */

/**
 * Integration test case.
 */
class Integration_TestCase extends \WP_UnitTestCase {
	/**
	 * Get an array of content models.
	 *
	 * @return array Array of content model data.
	 */
	protected function get_models( $file_path ): array {
		return include $file_path;
	}
}
