<?php
/**
 * Deleted Items
 *
 * @package AtlasContentModeler
 */

namespace WPE\AtlasContentModeler\ContentConnect\Relationships;

use WPE\AtlasContentModeler\ContentConnect\Plugin;

/**
 * Undocumented class
 */
class DeletedItems {

	/**
	 * Undocumented function
	 */
	public function setup() {
		add_action( 'deleted_post', array( $this, 'deleted_post' ) );
	}

	/**
	 * Fires right after a post was deleted from the database (NOT when it was moved to trash)
	 *
	 * @param int $post_id Post ID.
	 */
	public function deleted_post( $post_id ) {
		// phpcs:ignore
		/** @var \WPE\AtlasContentModeler\ContentConnect\Tables\PostToPost $p2p_table */
		$p2p_table = Plugin::instance()->get_table( 'p2p' );

		$p2p_table->delete(
			array( 'id1' => $post_id ),
			array( '%d' )
		);
		$p2p_table->delete(
			array( 'id2' => $post_id ),
			array( '%d' )
		);
	}

}
