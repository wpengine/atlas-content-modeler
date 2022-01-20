<?php
/**
 * Tests for global settings-related functions.
 */
class GlobalSettingsFunctionsTestCases extends WP_UnitTestCase {
	/**
	 * @covers ::\acm_usage_tracking_enabled()
	 */
	public function test_acm_usage_tracking_returns_false_when_no_value_exists_in_the_database(): void {
		self::assertFalse( acm_usage_tracking_enabled() );
	}

	/**
	 * @covers ::\acm_usage_tracking_enabled()
	 */
	public function test_acm_usage_tracking_returns_true_when_truthy_value_exists_in_the_database(): void {
		update_option( 'atlas_content_modeler_usage_tracking', 'moo' );
		self::assertTrue( acm_usage_tracking_enabled() );
	}

}
