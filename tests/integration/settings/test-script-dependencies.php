<?php
/**
 * Tests for script dependencies in the settings app.
 *
 * @package AtlasContentModeler
 */

/**
 * Class TestSettingsScriptDependencies
 */
class TestSettingsScriptDependencies extends WP_UnitTestCase {
	public function set_up(): void {
		parent::set_up();

		wp_set_current_user( 1 );

//		do_action( 'init' );

		set_current_screen( 'admin.php' );

//		do_action( 'admin_enqueue_scripts', 'toplevel_page_atlas-content-modeler' );
	}

	public function tear_down(): void {
		parent::tear_down();
		wp_set_current_user( null );
	}

//	public function test_dependency_scripts_are_enqueued_on_the_settings_view(): void {
//		self::assertTrue( wp_script_is( 'wp-api', 'enqueued' ) );
//		self::assertTrue( wp_script_is( 'wp-api-fetch', 'enqueued' ) );
//		self::assertTrue( wp_script_is( 'react', 'enqueued' ) );
//		self::assertTrue( wp_script_is( 'react-dom', 'enqueued' ) );
//		self::assertTrue( wp_script_is( 'lodash', 'enqueued' ) );
//		self::assertTrue( wp_script_is( 'wp-i18n', 'enqueued' ) );
//		self::assertTrue( wp_script_is( 'wp-core-data', 'enqueued' ) );
//	}
}
