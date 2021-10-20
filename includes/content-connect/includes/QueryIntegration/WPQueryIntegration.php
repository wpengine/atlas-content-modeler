<?php
/**
 * WPQuery Integration
 *
 * @package AtlasContentModeler
 */

namespace WPE\AtlasContentModeler\ContentConnect\QueryIntegration;

use WPE\AtlasContentModeler\ContentConnect\Relationships\PostToPost;

/**
 * Undocumented class
 */
class WPQueryIntegration {

	/**
	 * Undocumented function
	 */
	public function setup() {
		// Posts_where is first, posts_join is after.
		add_filter( 'posts_where', array( $this, 'posts_where' ), 10, 2 );
		add_filter( 'posts_join', array( $this, 'posts_join' ), 10, 2 );
		add_filter( 'posts_groupby', array( $this, 'posts_groupby' ), 10, 2 );
		add_filter( 'posts_orderby', array( $this, 'posts_orderby' ), 10, 2 );
	}

	/**
	 * Undocumented function
	 *
	 * @param string   $where Where clause.
	 * @param WP_Query $query WP Query.
	 */
	public function posts_where( $where, $query ) {
		if ( isset( $query->query_vars['acm_relationship_query'] ) ) {
			$post_type = isset( $query->query_vars['post_type'] ) ? $query->query_vars['post_type'] : '';

			// Adding to the query, so that we can fetch it from the other filter methods below and be dealing with the same data.
			$query->acm_relationship_query = new RelationshipQuery( $query->query_vars['acm_relationship_query'], $post_type );

			$where .= $query->acm_relationship_query->where;
		}

		return $where;
	}

	/**
	 * Undocumented function
	 *
	 * @param string   $join Join Clause.
	 * @param WP_Query $query WP_Query.
	 */
	public function posts_join( $join, $query ) {
		if ( isset( $query->acm_relationship_query ) ) {
			$join .= $query->acm_relationship_query->join;
		}

		return $join;
	}

	/**
	 * Undocumented function
	 *
	 * @param string   $groupby Group By.
	 * @param WP_Query $query WP_Query.
	 */
	public function posts_groupby( $groupby, $query ) {
		global $wpdb;

		if ( isset( $query->acm_relationship_query ) && ! empty( $query->acm_relationship_query->where ) ) {
			$groupby = "{$wpdb->posts}.ID";
		}

		return $groupby;
	}

	/**
	 * Undocumented function
	 *
	 * @param string   $orderby Order By.
	 * @param WP_Query $query WP_Query.
	 */
	public function posts_orderby( $orderby, $query ) {
		if ( ! isset( $query->acm_relationship_query ) || empty( $query->acm_relationship_query->where ) ) {
			return $orderby;
		}

		/*
		 * If orderby is anything other than relationship (array, etc) we don't allow it.
		 * Trying to allow multiple order by statements would likely end in confusing results
		 */
		if ( ! isset( $query->query_vars['orderby'] ) || $query->query_vars['orderby'] !== 'relationship' ) {
			return $orderby;
		}

		/*
		 * Since each component of the relationship query could have its OWN order, and there is not a good way to
		 * reconcile those, we just don't allow this and default to default ordering on WP_Query
		 */
		if ( count( $query->acm_relationship_query->segments ) > 1 ) {
			return $orderby;
		}

		$segment      = $query->acm_relationship_query->segments[0];
		$relationship = $query->acm_relationship_query->get_relationship_for_segment( $segment );

		// The order = 0 part puts any zero values (defaults) last to account for cases when they were adding from the other side of the relationship.
		if ( $relationship instanceof PostToPost ) {
			$orderby = 'p2p1.order = 0, p2p1.order ASC';
		}

		return $orderby;
	}
}
