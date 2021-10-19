<?php
/**
 * Post to Post
 *
 * @package AtlasContentModeler
 */

namespace WPE\AtlasContentModeler\ContentConnect\Relationships;

use WPE\AtlasContentModeler\ContentConnect\Plugin;

/**
 * Undocumented class
 */
class PostToPost extends Relationship {

	/**
	 * CPT Name of the first post type in the relationship
	 *
	 * @var string
	 */
	public $from;

	/**
	 * CPT Name of the second post type in the relationship
	 *
	 * @var string|array
	 */
	public $to;

	/**
	 * Undocumented function
	 *
	 * @param string $from From.
	 * @param string $to To.
	 * @param string $name Name.
	 * @param array  $args Args.
	 *
	 * @throws \Exception Exception.
	 */
	public function __construct( $from, $to, $name, $args = array() ) {
		if ( ! post_type_exists( $from ) ) {
			throw new \Exception( "Post Type {$from} does not exist. Post types must exist to create a relationship" );
		}

		$to = (array) $to;
		foreach ( $to as $to_post_type ) {
			if ( ! post_type_exists( $to_post_type ) ) {
				throw new \Exception( "Post Type {$to_post_type} does not exist. Post types must exist to create a relationship" );
			}
		}

		$this->from = $from;
		$this->to   = $to;
		$this->id   = strtolower( get_class( $this ) ) . "-{$name}-{$from}-" . implode( '.', $to );

		if ( $from === $to ) {
			$args['is_bidirectional'] = true;
		}

		parent::__construct( $name, $args );
	}

	/**
	 * Undocumented function
	 */
	public function setup() {}

	/**
	 * Gets the IDs that are related to the supplied post ID in the context of the current relationship
	 *
	 * @param int  $post_id Post ID.
	 * @param bool $order_by_relationship Order By Relationship.
	 *
	 * @return array
	 */
	public function get_related_object_ids( $post_id, $order_by_relationship = false ) {
		// phpcs:ignore
		/** @var \WPE\AtlasContentModeler\ContentConnect\Tables\PostToPost $table */
		$table = Plugin::instance()->get_table( 'p2p' );
		$db    = $table->get_db();

		$table_name = esc_sql( $table->get_table_name() );

		// Query either to or from, depending on the post type of the ID we're finding relationships for.
		$post_type = get_post_type( $post_id );
		if ( $post_type !== $this->from && ! in_array( $post_type, $this->to, true ) ) {
			return array();
		}

		if ( $post_type == $this->from ) { // phpcs:ignore
			$where_post_types = array_map(
				function( $value ) {
					return "'" . esc_sql( $value ) . "'";
				},
				$this->to
			);
			$where_post_types = implode( ', ', $where_post_types );
			$query            = $db->prepare( "SELECT p2p.id1 as ID, p.post_type FROM {$table_name} AS p2p INNER JOIN {$db->posts} as p on p2p.id1 = p.ID WHERE p2p.id2 = %d and p2p.name = %s and p.post_type IN ({$where_post_types})", $post_id, $this->name );
		} else {
			$query = $db->prepare( "SELECT p2p.id1 as ID, p.post_type FROM {$table_name} AS p2p INNER JOIN {$db->posts} as p on p2p.id1 = p.ID WHERE p2p.id2 = %d and p2p.name = %s and p.post_type = %s", $post_id, $this->name, $this->from );
		}

		if ( $order_by_relationship ) {
			$query .= ' ORDER BY p2p.order = 0, p2p.order ASC';
		}

		$objects = $db->get_results( $query );

		return wp_list_pluck( $objects, 'ID' );
	}

	/**
	 * Since we are joining on the same tables, its rather difficult to always know which order the relationship will be
	 * ESPECIALLY when joining the same post type to itself. To work around this, we just store both combinations of
	 * the relationship if the relationship is bidirectional. Adds a tiny bit of data to the DB, but greatly simplifies
	 * queries to find related posts.
	 *
	 * Coincidentally, this also allows us to store directional sort order information
	 *
	 * `order` corresponds to the order of id2, when viewed from id1
	 *
	 * @param int $pid1 Post ID 1.
	 * @param int $pid2 Post ID 2.
	 *
	 * @return boolean True on success or false if relationship would violate cardinality.
	 */
	public function add_relationship( $pid1, $pid2 ) {
		// phpcs:ignore
		/** @var \WPE\AtlasContentModeler\ContentConnect\Tables\PostToPost $table */
		$table = Plugin::instance()->get_table( 'p2p' );

		if ( $this->can_relate_post_ids( $pid1, $pid2 ) ) {
			if ( ! $this->check_cardinality( intval( $pid1 ), intval( $pid2 ) ) ) {
				return false;
			}

			// For one way relationships, $pid1 must be the "from" post type.
			if ( ! $this->is_bidirectional && get_post_type( $pid1 ) !== $this->from ) {
				$tmp  = $pid2;
				$pid2 = $pid1;
				$pid1 = $tmp;
			}

			/**
			 * $pid2 is first because one way queries execute on the "to" post type,
			 * which means we join "to" posts on id1 and return "from" posts as id2.
			 */
			$table->replace(
				array(
					'id1'  => $pid2,
					'id2'  => $pid1,
					'name' => $this->name,
				),
				array( '%d', '%d', '%s' )
			);

			if ( $this->is_bidirectional ) {
				$table->replace(
					array(
						'id1'  => $pid1,
						'id2'  => $pid2,
						'name' => $this->name,
					),
					array( '%d', '%d', '%s' )
				);
			}
		}

		return true;
	}

	/**
	 * Undocumented function
	 *
	 * @param int $pid1 Post ID 1.
	 * @param int $pid2 Post ID 2.
	 */
	public function delete_relationship( $pid1, $pid2 ) {
		// phpcs:ignore
		/** @var \WPE\AtlasContentModeler\ContentConnect\Tables\PostToPost $table */
		$table = Plugin::instance()->get_table( 'p2p' );

		if ( $this->can_relate_post_ids( $pid1, $pid2 ) ) {
			// For one way relationships, $pid1 must be the "from" post type.
			if ( ! $this->is_bidirectional && get_post_type( $pid1 ) !== $this->from ) {
				$tmp  = $pid2;
				$pid2 = $pid1;
				$pid1 = $tmp;
			}

			$table->delete(
				array(
					'id1'  => $pid2,
					'id2'  => $pid1,
					'name' => $this->name,
				),
				array( '%d', '%d', '%s' )
			);

			if ( $this->is_bidirectional ) {
				$table->delete(
					array(
						'id1'  => $pid1,
						'id2'  => $pid2,
						'name' => $this->name,
					),
					array( '%d', '%d', '%s' )
				);
			}
		}
	}

	/**
	 * Replaces existing relationships for the post with this set.
	 *
	 * Any relationship that is present in the database but not in $related_ids will no longer be related
	 *
	 * @param int   $post_id Post ID.
	 * @param array $related_ids Related IDs.
	 *
	 * @return boolean True on success of false if the relationship would break cardinality.
	 */
	public function replace_relationships( $post_id, $related_ids ) {
		$current_ids = $this->get_related_object_ids( $post_id );

		$delete_ids = array_diff( $current_ids, $related_ids );
		$add_ids    = array_diff( $related_ids, $current_ids );

		foreach ( $delete_ids as $delete ) {
			$this->delete_relationship( $post_id, $delete );
		}

		foreach ( $add_ids as $add ) {
			if ( ! $this->add_relationship( $post_id, $add ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Updates all the rows with order information.
	 *
	 * This function ONLY modifies ONE direction of the query:
	 *      - id2 is the post we're ordering on (we're on this edit screen)
	 *      - id1 is the post being ordered
	 * The inverse is managed from the other end of the relationship
	 *
	 * @param int   $object_id Object ID.
	 * @param array $ordered_ids Ordered IDs.
	 */
	public function save_sort_data( $object_id, $ordered_ids ) {
		if ( empty( $ordered_ids ) ) {
			return;
		}

		$order = 0;

		$data = array();

		foreach ( $ordered_ids as $id ) {
			$order++;

			$data[] = array(
				'id1'   => $id,
				'id2'   => $object_id,
				'name'  => $this->name,
				'order' => $order,
			);
		}

		$fields = array(
			'id1'   => '%d',
			'id2'   => '%d',
			'name'  => '%s',
			'order' => '%d',
		);

		// phpcs:ignore
		/** @var \WPE\AtlasContentModeler\ContentConnect\Tables\PostToPost $table */
		$table = Plugin::instance()->get_table( 'p2p' );
		$table->replace_bulk( $fields, $data );
	}

	/**
	 * Test the post types of two post IDs to make sure they belong to this
	 * relationship.
	 *
	 * @param int $pid1 A post ID.
	 * @param int $pid2 A second post ID.
	 * @return boolean True if both IDs represent post types that belong to the
	 *                 relationship.
	 */
	public function can_relate_post_ids( $pid1, $pid2 ) {
		$ids = [ $pid1, $pid2 ];

		foreach ( $ids as $id ) {
			$post_type = get_post_type( $id );

			if ( $post_type !== $this->from && ! in_array( $post_type, $this->to ) ) { // phpcs:ignore
				return false;
			}
		}

		return true;
	}

	/**
	 * Checks for cardinality
	 *
	 * Ensures a given pair of posts would respect the given relationship's cardinality.
	 *
	 * @param int $post_id_1 The first post to add to the relationship.
	 * @param int $post_id_2 The 2nd post to add to the relationship.
	 *
	 * @return boolean True if cardinality is correct or false if the relationship would break cardinality.
	 */
	protected function check_cardinality( $post_id_1, $post_id_2 ) {
		// We need to make sure post_id_1 is always the "from" type of the relationship to ensure cardinality is checked in the correct direction.
		if ( $this->from === get_post_type( $post_id_1 ) ) {
			$forward_relationships = $this->get_related_object_ids( $post_id_1 );
			$reverse_relationships = $this->get_related_object_ids( $post_id_2 );
		} else {
			$forward_relationships = $this->get_related_object_ids( $post_id_2 );
			$reverse_relationships = $this->get_related_object_ids( $post_id_1 );
		}

		switch ( $this->cardinality ) {
			case 'one-to-one':
				if ( ! empty( $forward_relationships ) || ! empty( $reverse_relationships ) ) {
					return false;
				}
				break;
			case 'many-to-one':
				if ( ! empty( $forward_relationships ) ) {
					return false;
				}
				break;
			case 'one-to-many':
				if ( ! empty( $reverse_relationships ) ) {
					return false;
				}
		};

		return true;
	}
}
