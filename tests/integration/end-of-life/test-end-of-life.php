<?php
/**
 * Class TestEndOfLife
 */
class TestEndOfLife extends WP_UnitTestCase {
	public function test_admin_notices_hook_has_deprecation_notice_callback_added(): void {
		self::assertSame( 10, has_action( 'admin_notices', 'acm_deprecation_notice' ) );
	}
}
