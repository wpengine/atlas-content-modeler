<?php

namespace WPE\AtlasContentModeler\ContentConnect\Tests\Integration\Relationships;

use WPE\AtlasContentModeler\ContentConnect\Relationships\PostToPost;
use WPE\AtlasContentModeler\ContentConnect\Tests\Integration\ContentConnectTestCase;

class PostToPostTest extends ContentConnectTestCase {

	public function setUp() {
		global $wpdb;

		$wpdb->query( "delete from {$wpdb->prefix}acm_post_to_post" );

		parent::setUp();
	}

	public function test_invalid_cpt_throws_exception() {
		$this->expectException( \Exception::class );

		new PostToPost( 'post', 'fakecpt', 'basic' );
	}

	public function test_valid_cpts_throw_no_exceptions() {
		$p2p = new PostToPost( 'post', 'post', 'basic' );

		$this->assertEquals( 'post', $p2p->from );
		$this->assertTrue( in_array( 'post', $p2p->to ) ); // phpcs:ignore
	}

	public function test_add_relationship() {
		global $wpdb;
		$p2p = new PostToPost( 'post', 'post', 'basic' );

		// Make sure we don't already have this in the DB.
		$this->assertEquals( 0, $wpdb->query( "select * from {$wpdb->prefix}acm_post_to_post where id1='1' and id2='2' and name='basic'" ) );
		$this->assertEquals( 0, $wpdb->query( "select * from {$wpdb->prefix}acm_post_to_post where id1='2' and id2='1' and name='basic'" ) );

		$p2p->add_relationship( '1', '2' );
		$this->assertEquals( 1, $wpdb->query( "select * from {$wpdb->prefix}acm_post_to_post where id1='1' and id2='2' and name='basic'" ) );
		$this->assertEquals( 1, $wpdb->query( "select * from {$wpdb->prefix}acm_post_to_post where id1='2' and id2='1' and name='basic'" ) );
	}

	public function test_adding_duplicates() {
		global $wpdb;
		$p2p = new PostToPost( 'post', 'post', 'basic' );

		// Making sure we don't add duplicates.
		$p2p->add_relationship( '1', '2' );
		$p2p->add_relationship( '1', '2' );
		$this->assertEquals( 1, $wpdb->query( "select * from {$wpdb->prefix}acm_post_to_post where id1='1' and id2='2' and name='basic'" ) );
		$this->assertEquals( 1, $wpdb->query( "select * from {$wpdb->prefix}acm_post_to_post where id1='2' and id2='1' and name='basic'" ) );

		// Making sure that order doesn't matter / duplicates.
		$p2p->add_relationship( 2, 1 );

		$this->assertEquals( 1, $wpdb->query( "select * from {$wpdb->prefix}acm_post_to_post where id1='1' and id2='2' and name='basic'" ) );
		$this->assertEquals( 1, $wpdb->query( "select * from {$wpdb->prefix}acm_post_to_post where id1='2' and id2='1' and name='basic'" ) );
	}

	public function test_delete_relationship() {
		global $wpdb;
		$p2p = new PostToPost( 'post', 'post', 'basic' );

		// Make sure we're in a known state of having a relationship in the DB.
		$p2p->add_relationship( '1', '2' );
		$this->assertEquals( 1, $wpdb->query( "select * from {$wpdb->prefix}acm_post_to_post where id1='1' and id2='2' and name='basic'" ) );
		$this->assertEquals( 1, $wpdb->query( "select * from {$wpdb->prefix}acm_post_to_post where id1='2' and id2='1' and name='basic'" ) );

		$p2p->delete_relationship( 1, 2 );
		$this->assertEquals( 0, $wpdb->query( "select * from {$wpdb->prefix}acm_post_to_post where id1='1' and id2='2' and name='basic'" ) );
		$this->assertEquals( 0, $wpdb->query( "select * from {$wpdb->prefix}acm_post_to_post where id1='2' and id2='1' and name='basic'" ) );
	}

	public function test_delete_flipped_order() {
		global $wpdb;
		$p2p = new PostToPost( 'post', 'post', 'basic' );

		// Make sure we're in a known state of having a relationship in the DB.
		$p2p->add_relationship( '1', '2' );
		$this->assertEquals( 1, $wpdb->query( "select * from {$wpdb->prefix}acm_post_to_post where id1='1' and id2='2' and name='basic'" ) );
		$this->assertEquals( 1, $wpdb->query( "select * from {$wpdb->prefix}acm_post_to_post where id1='2' and id2='1' and name='basic'" ) );

		$p2p->delete_relationship( 2, 1 );
		$this->assertEquals( 0, $wpdb->query( "select * from {$wpdb->prefix}acm_post_to_post where id1='1' and id2='2' and name='basic'" ) );
		$this->assertEquals( 0, $wpdb->query( "select * from {$wpdb->prefix}acm_post_to_post where id1='2' and id2='1' and name='basic'" ) );
	}

	public function test_delete_only_deletes_correct_records() {
		global $wpdb;
		$pp = new PostToPost( 'post', 'post', 'basic' );

		$keep_pairs = array(
			array( 1, 2 ),
			array( 1, 5 ),
			array( 2, 8 ),
			array( 2, 9 ),
		);

		$delete_pairs = array(
			array( 1, 9 ),
		);

		$pairs = array_merge( $keep_pairs, $delete_pairs );

		foreach ( $pairs as $pair ) {
			$pp->add_relationship( $pair[0], $pair[1] );
		}

		foreach ( $pairs as $pair ) {
			$this->assertEquals( 1, $wpdb->query( "select * from {$wpdb->prefix}acm_post_to_post where id1='{$pair[0]}' and id2='{$pair[1]}' and name='basic'" ) );
			$this->assertEquals( 1, $wpdb->query( "select * from {$wpdb->prefix}acm_post_to_post where id1='{$pair[1]}' and id2='{$pair[0]}' and name='basic'" ) );
		}

		$pp->delete_relationship( 1, 9 );

		foreach ( $keep_pairs as $pair ) {
			$this->assertEquals( 1, $wpdb->query( "select * from {$wpdb->prefix}acm_post_to_post where id1='{$pair[0]}' and id2='{$pair[1]}' and name='basic'" ) );
			$this->assertEquals( 1, $wpdb->query( "select * from {$wpdb->prefix}acm_post_to_post where id1='{$pair[1]}' and id2='{$pair[0]}' and name='basic'" ) );
		}

		foreach ( $delete_pairs as $pair ) {
			$this->assertEquals( 0, $wpdb->query( "select * from {$wpdb->prefix}acm_post_to_post where id1='{$pair[0]}' and id2='{$pair[1]}' and name='basic'" ) );
			$this->assertEquals( 0, $wpdb->query( "select * from {$wpdb->prefix}acm_post_to_post where id1='{$pair[1]}' and id2='{$pair[0]}' and name='basic'" ) );
		}
	}

	public function test_that_posts_relate_to_posts() {
		$this->add_post_relations();

		$ppb = new PostToPost( 'post', 'post', 'basic' );
		$ppc = new PostToPost( 'post', 'post', 'complex' );

		$this->assertEquals( array( 2, 3 ), $ppb->get_related_object_ids( 1 ) );
		$this->assertEquals( array( 3, 4 ), $ppc->get_related_object_ids( 1 ) );
		$this->assertEquals( array( 1 ), $ppb->get_related_object_ids( 2 ) );
		$this->assertEquals( array( 1 ), $ppb->get_related_object_ids( 3 ) );
		$this->assertEquals( array( 1 ), $ppc->get_related_object_ids( 3 ) );
		$this->assertEquals( array( 1 ), $ppc->get_related_object_ids( 4 ) );
	}

	public function test_that_posts_relate_to_cars() {
		$this->add_post_relations();

		$pcb = new PostToPost( 'post', 'car', 'basic' );
		$pcc = new PostToPost( 'post', 'car', 'complex' );

		$this->assertEquals( array( 11, 12 ), $pcb->get_related_object_ids( 1 ) );
		$this->assertEquals( array( 13, 14 ), $pcc->get_related_object_ids( 1 ) );
		$this->assertEquals( array( 1 ), $pcb->get_related_object_ids( 11 ) );
		$this->assertEquals( array( 1 ), $pcb->get_related_object_ids( 12 ) );
		$this->assertEquals( array( 1 ), $pcc->get_related_object_ids( 13 ) );
		$this->assertEquals( array( 1 ), $pcc->get_related_object_ids( 14 ) );
	}

	public function test_that_posts_relate_to_tires() {
		$this->add_post_relations();

		$ptb = new PostToPost( 'post', 'tire', 'basic' );
		$ptc = new PostToPost( 'post', 'tire', 'complex' );

		$this->assertEquals( array( 21, 22 ), $ptb->get_related_object_ids( 1 ) );
		$this->assertEquals( array( 23, 24 ), $ptc->get_related_object_ids( 1 ) );
		$this->assertEquals( array( 1 ), $ptb->get_related_object_ids( 21 ) );
		$this->assertEquals( array( 1 ), $ptb->get_related_object_ids( 22 ) );
		$this->assertEquals( array( 1 ), $ptc->get_related_object_ids( 23 ) );
		$this->assertEquals( array( 1 ), $ptc->get_related_object_ids( 24 ) );
	}

	public function test_that_cars_relate_to_tires() {
		$this->add_post_relations();

		$ctb = new PostToPost( 'car', 'tire', 'basic' );
		$ctc = new PostToPost( 'car', 'tire', 'complex' );

		// even though 11 is related to array( 1, 21 ) - that is wrong post type. When JUST these post types, its just array( 21 ).
		$this->assertEquals( array( 21 ), $ctb->get_related_object_ids( 11 ) );
		$this->assertEquals( array( 23 ), $ctc->get_related_object_ids( 13 ) );
		$this->assertEquals( array( 11 ), $ctb->get_related_object_ids( 21 ) );
		$this->assertEquals( array( 13 ), $ctc->get_related_object_ids( 23 ) );
	}

	/**
	 * Makes sure that even if a relationship for a post that no longer exists (or never existed) is still present in the
	 * relationship table, it isn't returned as part of the related ID list
	 */
	public function test_that_only_real_post_ids_are_returned() {
		global $wpdb;

		$this->add_post_relations();

		// Add a really high ID (1000) to the relationships table that shouldn't exist in our valid test data.
		$wpdb->insert(
			"{$wpdb->prefix}acm_post_to_post",
			array(
				'id1'  => '1',
				'id2'  => '1000',
				'name' => 'basic',
			)
		);
		$wpdb->insert(
			"{$wpdb->prefix}acm_post_to_post",
			array(
				'id1'  => '1000',
				'id2'  => '1',
				'name' => 'basic',
			)
		);

		// Make sure post ID 1000 doesn't exist (sanity check).
		$this->assertNull( get_post( 1000 ) );

		$ppb = new PostToPost( 'post', 'post', 'basic' );

		$related = $ppb->get_related_object_ids( 1 );
		$this->assertEquals( array( 2, 3 ), $related );
	}

	public function test_sort_data_is_saved() {
		global $wpdb;

		$this->add_post_relations();

		$rel = new PostToPost( 'post', 'post', 'basic' );
		$rel->save_sort_data( 1, array( 2, 3 ) );

		$this->assertEquals( 1, $wpdb->get_var( "select `order` from {$wpdb->prefix}acm_post_to_post where id2=1 and name='basic' and id1=2;" ) );
		$this->assertEquals( 2, $wpdb->get_var( "select `order` from {$wpdb->prefix}acm_post_to_post where id2=1 and name='basic' and id1=3;" ) );

		$rel->save_sort_data( 1, array( 3, 2 ) );

		$this->assertEquals( 2, $wpdb->get_var( "select `order` from {$wpdb->prefix}acm_post_to_post where id2=1 and name='basic' and id1=2;" ) );
		$this->assertEquals( 1, $wpdb->get_var( "select `order` from {$wpdb->prefix}acm_post_to_post where id2=1 and name='basic' and id1=3;" ) );
	}

	public function test_relationship_ids_are_returned_in_order() {
		$this->add_post_relations();

		$rel = new PostToPost( 'post', 'post', 'basic' );
		$rel->save_sort_data( 1, array( 2, 3 ) );

		$this->assertEquals( array( 2, 3 ), $rel->get_related_object_ids( 1, false ) );
		$this->assertEquals( array( 2, 3 ), $rel->get_related_object_ids( 1, true ) );

		$rel->save_sort_data( 1, array( 3, 2 ) );

		$this->assertEquals( array( 2, 3 ), $rel->get_related_object_ids( 1, false ) );
		$this->assertEquals( array( 3, 2 ), $rel->get_related_object_ids( 1, true ) );
	}

	public function test_relationships_added_with_no_order_go_to_end() {
		// post to post "basic" name.
		$rel = new PostToPost( 'post', 'post', 'basic' );

		$rel->add_relationship( 1, 2 );
		$rel->add_relationship( 1, 3 );
		$rel->add_relationship( 1, 4 );
		$rel->add_relationship( 1, 5 );
		$rel->add_relationship( 1, 6 );

		$rel->save_sort_data( 1, array( 2, 3, 4, 6 ) );

		$this->assertEquals( array( 2, 3, 4, 5, 6 ), $rel->get_related_object_ids( 1, false ) );
		$this->assertEquals( array( 2, 3, 4, 6, 5 ), $rel->get_related_object_ids( 1, true ) );
	}

	public function test_replace_relationships() {
		global $wpdb;

		$rel = new PostToPost( 'post', 'post', 'basic' );

		$this->assertEquals( 0, $wpdb->get_var( "select count(id1) from {$wpdb->prefix}acm_post_to_post where id1=1 and `name`='basic';" ) );

		// Add some known relationships, and make sure they get written to DB.
		$rel->add_relationship( 1, 2 );
		$rel->add_relationship( 1, 3 );
		$rel->add_relationship( 1, 4 );
		$rel->add_relationship( 1, 5 );

		$this->assertEquals( 1, $wpdb->get_var( "select count(id1) from {$wpdb->prefix}acm_post_to_post where id1=1 and id2=2 and `name`='basic';" ) );
		$this->assertEquals( 1, $wpdb->get_var( "select count(id1) from {$wpdb->prefix}acm_post_to_post where id1=1 and id2=3 and `name`='basic';" ) );
		$this->assertEquals( 1, $wpdb->get_var( "select count(id1) from {$wpdb->prefix}acm_post_to_post where id1=1 and id2=4 and `name`='basic';" ) );
		$this->assertEquals( 1, $wpdb->get_var( "select count(id1) from {$wpdb->prefix}acm_post_to_post where id1=1 and id2=5 and `name`='basic';" ) );

		// Should remove 2 and 5 and add 6.
		$rel->replace_relationships( 1, array( 3, 4, 6 ) );
		$this->assertEquals( 0, $wpdb->get_var( "select count(id1) from {$wpdb->prefix}acm_post_to_post where id1=1 and id2=2 and `name`='basic';" ) );
		$this->assertEquals( 1, $wpdb->get_var( "select count(id1) from {$wpdb->prefix}acm_post_to_post where id1=1 and id2=3 and `name`='basic';" ) );
		$this->assertEquals( 1, $wpdb->get_var( "select count(id1) from {$wpdb->prefix}acm_post_to_post where id1=1 and id2=4 and `name`='basic';" ) );
		$this->assertEquals( 0, $wpdb->get_var( "select count(id1) from {$wpdb->prefix}acm_post_to_post where id1=1 and id2=5 and `name`='basic';" ) );
		$this->assertEquals( 1, $wpdb->get_var( "select count(id1) from {$wpdb->prefix}acm_post_to_post where id1=1 and id2=6 and `name`='basic';" ) );
	}



}
