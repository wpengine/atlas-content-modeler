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

/**
 * Gets latest model entries.
 *
 * @return array
 */
function stats_recent_model_entries(): array {
	$post_types = array_keys( get_registered_content_types() );
	if ( empty( $post_types ) ) {
		return [];
	}

	return get_posts(
		[
			'post_status'    => 'publish',
			'post_type'      => $post_types,
			'posts_per_page' => 5,
		]
	);
}

/**
 * Gets relationship stats.
 *
 * @return array
 */
function stats_relationships(): array {
	/**
	 * Relationship table
	 *
	 * @var \WPE\AtlasContentModeler\ContentConnect\Tables\PostToPost $table
	 */
	$table = \WPE\AtlasContentModeler\ContentConnect\Plugin::instance()->get_table( 'p2p' );

	/* @var \wpdb wpdb instance */
	$db             = $table->get_db();
	$p2p_table_name = esc_sql( $table->get_table_name() );

	global $wpdb;
	$post_table_name = esc_sql( $wpdb->prefix . 'posts' );

	$results = [
		'totalRelationshipConnections' => (int) $db->get_results( "SELECT COUNT(*) as total_connections FROM {$p2p_table_name}", ARRAY_A )[0]['total_connections'] ?: 0,
		'mostConnectedEntries'         => $db->get_results( "SELECT p2p.id1, p2p.id2, COUNT(*) as total_connections, wp_posts.post_type, wp_posts.post_title FROM {$p2p_table_name} as p2p LEFT JOIN {$post_table_name} as wp_posts ON wp_posts.ID = p2p.id1 GROUP BY `id1` ORDER BY total_connections DESC", ARRAY_A ) ?? [],
	];

	foreach ( $results['mostConnectedEntries'] as $key => &$entry ) {
		$entry['permalink'] = get_permalink( $entry['id1'] );
	}

	return $results;
}