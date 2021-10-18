<?php

namespace WPE\AtlasContentModeler\ContentConnect\Tests\Integration;

use WPE\AtlasContentModeler\ContentConnect\Relationships\PostToPost;

class ContentConnectTestCase extends \PHPUnit_Framework_TestCase {

	public static function setupBeforeClass() {
		self::insert_dummy_data();
		self::register_post_types();

		parent::setUpBeforeClass();
	}

	public static function insert_dummy_data() {
		global $wpdb;

		$wpdb->query( "DELETE FROM {$wpdb->posts}" );
		$wpdb->query( "INSERT INTO `{$wpdb->posts}` " . file_get_contents( __DIR__ . '/data/posts.sql' ) ); // phpcs:ignore
	}

	public static function register_post_types() {
		$post_types = array(
			'car',
			'tire',
		);

		foreach ( $post_types as $post_type ) {
			if ( ! post_type_exists( $post_type ) ) {
				register_post_type( $post_type );
			}
		}
	}

	/**
	 * Adds known relationships that we can then test against
	 *
	 * Post Type to Post ID Mapping:
	 *
	 * Post Type Post: 1-10
	 * Post Type Car:  11-20
	 * Post Type Tire: 21-30
	 */
	public function add_post_relations() {
		global $wpdb;

		$wpdb->query( "DELETE FROM {$wpdb->prefix}acm_post_to_post;" );

		// post to post "basic" name.
		$ppb = new PostToPost( 'post', 'post', 'basic' );
		// post to post "complex" name.
		$ppc = new PostToPost( 'post', 'post', 'complex' );
		$pcb = new PostToPost( 'post', 'car', 'basic' );
		$pcc = new PostToPost( 'post', 'car', 'complex' );
		$ptb = new PostToPost( 'post', 'tire', 'basic' );
		$ptc = new PostToPost( 'post', 'tire', 'complex' );
		$ctb = new PostToPost( 'car', 'tire', 'basic' );
		$ctc = new PostToPost( 'car', 'tire', 'complex' );

		$ppb->add_relationship( 1, 2 );
		$ppb->add_relationship( 1, 3 );
		$ppc->add_relationship( 1, 3 );
		$ppc->add_relationship( 1, 4 );
		$pcb->add_relationship( 1, 11 );
		$pcb->add_relationship( 1, 12 );
		$pcc->add_relationship( 1, 13 );
		$pcc->add_relationship( 1, 14 );
		$ptb->add_relationship( 1, 21 );
		$ptb->add_relationship( 1, 22 );
		$ptc->add_relationship( 1, 23 );
		$ptc->add_relationship( 1, 24 );
		$ctb->add_relationship( 11, 21 );
		$ctc->add_relationship( 13, 23 );

		// for pagination tests, we'll use "page1" and "page2" names to make sure we have different names.
		$p1 = new PostToPost( 'post', 'post', 'page1' );
		$p2 = new PostToPost( 'post', 'post', 'page2' );

		for ( $i = 35; $i <= 90; $i++ ) {
			switch ( $i % 4 ) {
				case 0:
					$p1->add_relationship( 31, $i );
					break;
				case 1:
					$p1->add_relationship( 32, $i );
					break;
				case 2:
					$p2->add_relationship( 33, $i );
					break;
				case 3:
					$p2->add_relationship( 34, $i );
					break;
			}
		}
	}

}
