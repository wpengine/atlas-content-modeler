<?php
/**
 * Functionality related to stats.
 *
 * @package AtlasContentModeler
 */

namespace WPE\AtlasContentModeler\Stats;

use function WPE\AtlasContentModeler\ContentRegistration\get_registered_content_types;

/**
 * Gets count of all model entries.
 * Grouped by model slug, sorted by total entries descending.
 *
 * @return array {
 *  @type string $model The model slug
 *  @type string $label The model label.
 *  @type int    $count Total post entries.
 * }
 */
function stats_model_counts(): array {
	global $wpdb;
	$table_name = $wpdb->prefix . 'posts';
	$models     = get_registered_content_types();
	$post_types = array_keys( $models );
	if ( empty( $post_types ) ) {
		return [];
	}

	$post_types_placeholder = array_fill( 0, count( $post_types ), '%s' );
	$post_types_placeholder = implode( ', ', $post_types_placeholder );

	$results = $wpdb->get_results( // phpcs:ignore
		$wpdb->prepare(
			"SELECT post_type as model, COUNT(*) as count FROM $table_name WHERE `post_type` IN ( $post_types_placeholder ) AND `post_status` = 'publish' GROUP BY `post_type` ORDER BY count DESC;", // phpcs:ignore
			$post_types
		),
		ARRAY_A
	);

	$results ?? [];

	foreach ( $results as &$result ) {
		$result['plural'] = $models[ $result['model'] ]['plural'];
	}

	return $results;
}
