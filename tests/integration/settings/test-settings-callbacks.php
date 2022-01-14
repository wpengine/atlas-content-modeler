<?php
/**
 * Class SettingsCallbacksTestCases
 *
 * @package AtlasContentModeler
 */

/**
 * Settings callbacks test cases.
 */
class SettingsCallbacksTestCases extends WP_UnitTestCase {
	public function set_up() {
		parent::set_up();
		wp_set_current_user( 1 );
	}

	public function tear_down() {
		parent::tear_down();
		wp_set_current_user( null );
	}

	/**
	 * @covers ::\WPE\AtlasContentModeler\Settings\register_plugin_settings
	 * @covers ::\WPE\AtlasContentModeler\Settings\register_settings_fields
	 */
	public function test_settings_registration_functions_are_hooked(): void {
		self::assertSame( 10, has_action( 'init', 'WPE\AtlasContentModeler\Settings\register_plugin_settings' ) );
		self::assertSame( 10, has_action( 'admin_init', 'WPE\AtlasContentModeler\Settings\register_settings_fields' ) );
	}
}
