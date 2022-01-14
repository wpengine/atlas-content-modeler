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

		do_action( 'init' );

		set_current_screen( 'admin.php' );

		do_action( 'admin_enqueue_scripts', 'toplevel_page_atlas-content-modeler' );
	}

	public function tear_down(): void {
		parent::tear_down();
		wp_set_current_user( null );
	}

	public function test_wp_data_script_is_enqueued_on_the_settings_view(): void {
		self::assertTrue( wp_script_is( 'wp-core-data', 'enqueued' ) );
	}
}
