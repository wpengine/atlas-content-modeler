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
 *
 * @return bool True if updates were carried out or false.
 */
function update_plugin(): bool {
	$current_version = get_option( 'atlas_content_modeler_current_version', '0.0.0' );
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
		update_option( 'atlas_content_modeler_current_version', $plugin_version );
		return true;
	}

	return false;
}

/**
 * Upgrade field cardinality for versions prior to 0.6.1.
 *
 * After version 0.6.0 we had to modify existing relationship fields to properly
 * reflect their cardinality. This script updates the fields accordingly.
 *
 * @return bool True if the database was updated or false.
 */
function update_0_6_1(): bool {
	global $wpdb;

	$models = get_option( 'atlas_content_modeler_post_types', array() );

	$acm_table        = $wpdb->base_prefix . 'acm_post_to_post';
	$acm_table_exists = $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $acm_table ) );

	foreach ( $models as $model_index => $model ) {
		foreach ( $model['fields'] as $field_index => $field ) {
			if ( isset( $field['cardinality'] ) ) {
				if ( 'one-to-one' === $field['cardinality'] ) {
					$models[ $model_index ]['fields'][ $field_index ]['cardinality'] = 'many-to-one';
				}
				if ( 'one-to-many' === $field['cardinality'] ) {
					$models[ $model_index ]['fields'][ $field_index ]['cardinality'] = 'many-to-many';
				}

				if ( $acm_table_exists ) {
					$query = "
						SELECT
							acm.name,
							acm.id1,
							posts.post_type AS t1,
							acm.id2,
							posts2.post_type AS t2
						FROM
							{$acm_table} AS acm
							JOIN {$wpdb->posts} AS posts ON id1 = posts.ID
							JOIN {$wpdb->posts} AS posts2 ON id2 = posts2.ID
						WHERE
							name=%s;
						";

					$relationships = $wpdb->get_results( $wpdb->prepare( $query, $field['slug'] ), ARRAY_A ); // phpcs:ignore

					foreach ( $relationships as $relationship ) {
						if (
							$relationship['t1'] === sanitize_key( $models[ $field['reference'] ]['singular'] ) &&
							$relationship['t2'] === sanitize_key( $model['singular'] ) &&
							$field['slug'] === $relationship['name']
							) {
								$wpdb->update( // phpcs:ignore
									$acm_table,
									array(
										'name' => $field['id'],
									),
									array(
										'name' => $field['slug'],
										'id1'  => $relationship['id1'],
										'id2'  => $relationship['id2'],
									),
									array(
										'%s',
									)
								);
								$wpdb->insert( // phpcs:ignore
									$acm_table,
									array(
										'name' => $field['id'],
										'id1'  => $relationship['id2'],
										'id2'  => $relationship['id1'],
									),
									array(
										'%s',
										'%d',
										'%d',
									)
								);
						}
					}
				}
			}
		}
	}

	return update_option( 'atlas_content_modeler_post_types', $models );
}
