<?php
/**
 * Class AdminMenuPageTest
 */
class AdminMenuPageTest extends WP_UnitTestCase {
	public function test_admin_menu_hook_has_action_added(): void {
		self::assertSame( 10, has_action( 'admin_menu', 'WPE\AtlasContentModeler\Settings\register_admin_menu_page' ) );
	}

	public function test_parent_file_hook_has_filter_added(): void {
		self::assertSame( 10, has_action( 'parent_file', 'WPE\AtlasContentModeler\Settings\maybe_override_submenu_file' ) );
	}
}
