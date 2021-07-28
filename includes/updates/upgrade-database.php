<?php
/**
 * Database upgrade functions.
 *
 * @package AtlasContentModeler
 */

use function WPE\AtlasContentModeler\ContentRegistration\get_registered_content_types;
use function WPE\AtlasContentModeler\ContentRegistration\update_registered_content_types;

/**
 * Migrates the plugin's stored data to the latest format.
 *
 * Compares the current version number of the plugin to the
 * version number stored in the database. If the stored version
 * is less than the current version, migration routines are run
 * to bring the plugin's stored data up to the latest standards.
 *
 * @since 0.4.2
 * @return void
 */
function atlas_content_modeler_upgrade_database() {
	if ( ! function_exists( 'get_plugin_data' ) ) {
		include_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	$plugin     = get_plugin_data( ATLAS_CONTENT_MODELER_FILE );
	$db_version = get_option( 'atlas_content_modeler_version', '0.4.1' );

	if ( version_compare( $db_version, $plugin['Version'], '==' ) ) {
		return;
	}

	// Migrations start here, ordered oldest to newest.

	if ( version_compare( $db_version, '0.4.2', '<' ) ) {
		atlas_content_modeler_upgrade_042();
	}

	update_option( 'atlas_content_modeler_version', $plugin['Version'] );
}

/**
 * Database upgrade routine for version 0.4.2.
 *
 * @return void
 */
function atlas_content_modeler_upgrade_042() {
	$models = get_registered_content_types();

	foreach ( $models as $key => $model ) {
		$slug = sanitize_key( $key );

		if ( $key !== $slug ) {
			$model['slug']   = $slug;
			$models[ $slug ] = $model;
			unset( $models[ $key ] );
		}
	}

	update_registered_content_types( $models );
}
