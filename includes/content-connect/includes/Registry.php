<?php
/**
 * Registry
 *
 * @package AtlasContentModeler
 */

namespace WPE\AtlasContentModeler\ContentConnect;

use WPE\AtlasContentModeler\ContentConnect\Relationships\PostToPost;
use WPE\AtlasContentModeler\ContentConnect\Relationships\Relationship;

/**
 * Creates and Tracks any relationships between post types
 */
class Registry {

	/**
	 * Undocumented variable
	 *
	 * @var array
	 */
	protected $post_post_relationships = array();

	/**
	 * Undocumented function
	 */
	public function setup() {}

	/**
	 * Gets a key that uniquely identifies a relationship between two CPTs
	 *
	 * @param string $from From.
	 * @param string $to To.
	 * @param string $name Name.
	 *
	 * @return string
	 */
	public function get_relationship_key( $from, $to, $name ) {
		$from = (array) $from;
		sort( $from );
		$from = implode( '.', $from );

		$to = (array) $to;
		sort( $to );
		$to = implode( '.', $to );

		return "{$from}_{$to}_{$name}";
	}

	/**
	 * Checks if a relationship exists between two post types.
	 *
	 * Order of post type doesn't matter when checking if the relationship already exists.
	 *
	 * @param string $cpt1 CPT1.
	 * @param string $cpt2 CPT2.
	 * @param string $name Name.
	 *
	 * @return bool
	 */
	public function post_to_post_relationship_exists( $cpt1, $cpt2, $name ) {
		$relationship = $this->get_post_to_post_relationship( $cpt1, $cpt2, $name );

		if ( ! $relationship ) {
			return false;
		}

		return true;
	}

	/**
	 * Undocumented function
	 *
	 * @param string $key Key.
	 * @return mixed
	 */
	public function get_post_to_post_relationship_by_key( $key ) {
		if ( isset( $this->post_post_relationships[ $key ] ) ) {
			return $this->post_post_relationships[ $key ];
		}

		return false;
	}

	/**
	 * Returns the relationship object for the post types provided. Order of CPT args is only important
	 * for one way relationships, where the first argument must be the "from" post type.
	 *
	 * @param string $cpt1 CPT1.
	 * @param string $cpt2 CPT2.
	 * @param string $name Name.
	 *
	 * @return bool|Relationship Returns relationship object if relationship exists, otherwise false
	 */
	public function get_post_to_post_relationship( $cpt1, $cpt2, $name ) {
		$key = $this->get_relationship_key( $cpt1, $cpt2, $name );

		$relationship = $this->get_post_to_post_relationship_by_key( $key );

		if ( $relationship && ( $relationship->is_bidirectional || $relationship->from === $cpt1 ) ) {
			return $relationship;
		}

		// Try the inverse, only if "cpt2" isn't an array.
		if ( is_array( $cpt2 ) ) {
			return false;
		}
		$key = $this->get_relationship_key( $cpt2, $cpt1, $name );

		$relationship = $this->get_post_to_post_relationship_by_key( $key );

		if ( $relationship && $relationship->is_bidirectional ) {
			return $relationship;
		}

		return false;
	}

	/**
	 * Defines a new many to many relationship between two post types
	 *
	 * @param string $from From.
	 * @param string $to To.
	 * @param string $name Name.
	 * @param array  $args Args.
	 *
	 * @throws \Exception Exception.
	 *
	 * @return Relationship
	 */
	public function define_post_to_post( $from, $to, $name, $args = array() ) {
		if ( $this->post_to_post_relationship_exists( $from, $to, $name ) ) {
			$to = implode( ', ', (array) $to );
			throw new \Exception( "A relationship already exists between {$from} and {$to} with name {$name}" );
		}

		$key = $this->get_relationship_key( $from, $to, $name );

		$this->post_post_relationships[ $key ] = new PostToPost( $from, $to, $name, $args );
		$this->post_post_relationships[ $key ]->setup();

		return $this->post_post_relationships[ $key ];
	}
}
