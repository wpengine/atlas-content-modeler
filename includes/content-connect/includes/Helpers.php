<?php
/**
 * Helpers
 *
 * @package AtlasContentModeler
 */

namespace WPE\AtlasContentModeler\ContentConnect\Helpers;

use WPE\AtlasContentModeler\ContentConnect\Plugin;

if ( ! function_exists( __NAMESPACE__ . '\get_registry' ) ) :

	/**
	 * Returns the instance of the relationship registry
	 *
	 * @return \WPE\AtlasContentModeler\ContentConnect\Registry
	 */
	function get_registry() {
		return Plugin::instance()->registry;
	}

endif;

if ( ! function_exists( __NAMESPACE__ . '\get_related_ids_by_name' ) ) :

	/**
	 * Returns all related posts for a given relationship name, without restricting by post type.
	 *
	 * Useful when you have many relationships between different post types with the same name, and you want to return
	 * ALL related posts by relationship name.
	 *
	 * @param int    $post_id Post ID.
	 * @param string $relationship_name Relationship Name.
	 *
	 * @return Array IDs of posts related to the post with the named relationship
	 */
	function get_related_ids_by_name( $post_id, $relationship_name ) {
		// phpcs:ignore
		/** @var \WPE\AtlasContentModeler\ContentConnect\Tables\PostToPost $table */
		$table = Plugin::instance()->get_table( 'p2p' );
		$db    = $table->get_db();

		$table_name = esc_sql( $table->get_table_name() );

		$query = $db->prepare( "SELECT p2p.id1 as ID FROM {$table_name} AS p2p WHERE p2p.id2 = %d and p2p.name = %s", $post_id, $relationship_name );

		$objects = $db->get_results( $query );

		return wp_list_pluck( $objects, 'ID' );
	}

endif;
