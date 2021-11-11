<?php
/**
 * Tests version updater.
 */

use \WPE\AtlasContentModeler\VersionUpdater;

/**
 * Class UpdaterTestCases
 *
 * @package AtlasContentModeler
 */
class UpdaterTestCases extends WP_UnitTestCase {

	protected $versions;

	public function set_up(): void {
		parent::set_up();

		$file_data      = get_file_data( ATLAS_CONTENT_MODELER_FILE, array( 'Version' => 'Version' ) );
		$plugin_version = $file_data['Version'];

		$this->versions = array(
			'old'     => '0.5.0',
			'current' => $plugin_version,
			'new'     => $this->new_version( $plugin_version ),
		);
	}

	public function tear_down(): void {
		parent::tear_down();

		delete_option( 'atlas_content_modeler_current_version' );
	}

	/**
	 * @covers ::\WPE\AtlasContentModeler\VersionUpdater\update_plugin()
	 */
	public function test_update_plugin_installed_new_version(): void {
		update_option( 'atlas_content_modeler_current_version', $this->versions['old'] );

		self::assertTrue( VersionUpdater\update_plugin() );
		self::assertEquals( get_option( 'atlas_content_modeler_current_version' ), $this->versions['current'] );
	}

	/**
	 * @covers ::\WPE\AtlasContentModeler\VersionUpdater\update_plugin()
	 */
	public function test_update_plugin_no_saved_version(): void {
		self::assertTrue( VersionUpdater\update_plugin() );
		self::assertEquals( get_option( 'atlas_content_modeler_current_version' ), $this->versions['current'] );
	}

	/**
	 * @covers ::\WPE\AtlasContentModeler\VersionUpdater\update_plugin()
	 */
	public function test_update_plugin_current_version(): void {
		update_option( 'atlas_content_modeler_current_version', $this->versions['current'] );

		self::assertFalse( VersionUpdater\update_plugin() );
		self::assertEquals( get_option( 'atlas_content_modeler_current_version' ), $this->versions['current'] );
	}



	/**
	 * @covers ::\WPE\AtlasContentModeler\VersionUpdater\update_plugin()
	 */
	public function test_update_plugin_installed_old_version(): void {
		update_option( 'atlas_content_modeler_current_version', $this->versions['new'] );

		self::assertFalse( VersionUpdater\update_plugin() );
		self::assertEquals( get_option( 'atlas_content_modeler_current_version' ), $this->versions['new'] );
	}

	/**
	 * Increments the patch version supplied for easier testing
	 *
	 * @param string $old_version The version to increment.
	 *
	 * @return string
	 */
	protected function new_version( $old_version ) {
		$version_parts = explode( '.', $old_version );

		$version_parts[2] = strval( intval( $version_parts[2] ) + 1 );

		return implode( '.', $version_parts );
	}
}
