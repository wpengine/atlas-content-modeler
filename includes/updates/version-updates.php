<?php
/**
 * Handles version updates needed to modify data already stored in the database.
 *
 * @package AtlasContentModeler
 */

declare(strict_types=1);
namespace WPE\AtlasContentModeler\VersionUpdater;

/**
 * Checks plugin version for update and calls update function where appropriate.
 */
function update_plugin() {
	delete_option( 'wpe_atlas_current_version' );
	$current_version = get_option( 'wpe_atlas_current_version', '0.0.0' );
	$file_data       = get_file_data( ATLAS_CONTENT_MODELER_FILE, array( 'Version' => 'Version' ) );
	$plugin_version  = $file_data['Version'];

	if ( 1 === version_compare( $plugin_version, $current_version ) ) {

		// Array of versions requiring update and their callbacks.
		// Note these do not have to exactly match plugin version.
		$update_versions = array(
			'0.6.1' => 'update_0_6_1',
		);

		foreach ( $update_versions as $version => $callback ) {
			if ( 1 === version_compare( $version, $current_version ) ) {
				call_user_func( __NAMESPACE__ . '\\' . $callback );
			}
		}

		// Save the last updated version.
		$file_data = get_file_data( ATLAS_CONTENT_MODELER_FILE, array( 'Version' => 'Version' ) );
		update_option( 'wpe_atlas_current_version', $plugin_version );
	}
}

/**
 * Upgrade field cardinality for versions prior to 0.6.1.
 *
 * After version 0.6.0 we had to modify existing relationship fields to properly
 * reflect their cardinality. This script updates the fields accordingly.
 */
function update_0_6_1() {
	$models = get_option( 'atlas_content_modeler_post_types', array() );

	foreach ( $models as $model_index => $model ) {
		foreach ( $model['fields'] as $field_index => $field ) {
			if ( isset( $field['cardinality'] ) ) {
				if ( 'one-to-one' === $field['cardinality'] ) {
					$models[ $model_index ]['fields'][ $field_index ]['cardinality'] = 'many-to-one';
				}
				if ( 'one-to-many' === $field['cardinality'] ) {
					$models[ $model_index ]['fields'][ $field_index ]['cardinality'] = 'many-to-many';
				}
			}
		}
	}

	update_option( 'atlas_content_modeler_post_types', $models );
}
