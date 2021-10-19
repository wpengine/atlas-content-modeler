<?php

namespace WPE\AtlasContentModeler\ContentConnect\Tests\Integration\Relationships;

use WPE\AtlasContentModeler\ContentConnect\Relationships\PostToPost;
use WPE\AtlasContentModeler\ContentConnect\Tests\Integration\ContentConnectTestCase;

class DeletedItemsTest extends ContentConnectTestCase {

	public function setUp() {
		global $wpdb;

		// Start out with known empty slate.
		$wpdb->query( "delete from {$wpdb->prefix}acm_post_to_post" );

		$wpdb->query( "delete from {$wpdb->posts}" );
		self::insert_dummy_data();

		parent::setUp();
	}

	/*
	 * Direct DB queries, because get_related_*_id functions are smart enough to not return the IDs (because of the join)
	 * even if the record still exists in the join table
	 */
	public function test_deleted_posts_are_removed_from_post_to_post_table() {
		global $wpdb;

		$relationship = new PostToPost( 'car', 'tire', 'test' );

		// 11 (car) to 21 (tire).
		$this->assertEquals( 0, $wpdb->get_var( "select count(id1) from {$wpdb->prefix}acm_post_to_post where id1=11;" ) );
		$this->assertEquals( 0, $wpdb->get_var( "select count(id2) from {$wpdb->prefix}acm_post_to_post where id2=11;" ) );
		$this->assertEquals( 0, $wpdb->get_var( "select count(id1) from {$wpdb->prefix}acm_post_to_post where id1=21;" ) );
		$this->assertEquals( 0, $wpdb->get_var( "select count(id2) from {$wpdb->prefix}acm_post_to_post where id2=21;" ) );

		$relationship->add_relationship( 11, 21 );
		$this->assertEquals( 1, $wpdb->get_var( "select count(id1) from {$wpdb->prefix}acm_post_to_post where id1=11;" ) );
		$this->assertEquals( 1, $wpdb->get_var( "select count(id2) from {$wpdb->prefix}acm_post_to_post where id2=11;" ) );
		$this->assertEquals( 1, $wpdb->get_var( "select count(id1) from {$wpdb->prefix}acm_post_to_post where id1=21;" ) );
		$this->assertEquals( 1, $wpdb->get_var( "select count(id2) from {$wpdb->prefix}acm_post_to_post where id2=21;" ) );

		// Test that relationships persist when trashing posts (in case they are untrashed).
		wp_trash_post( 11 );
		$this->assertEquals( 1, $wpdb->get_var( "select count(id1) from {$wpdb->prefix}acm_post_to_post where id1=11;" ) );
		$this->assertEquals( 1, $wpdb->get_var( "select count(id2) from {$wpdb->prefix}acm_post_to_post where id2=11;" ) );
		$this->assertEquals( 1, $wpdb->get_var( "select count(id1) from {$wpdb->prefix}acm_post_to_post where id1=21;" ) );
		$this->assertEquals( 1, $wpdb->get_var( "select count(id2) from {$wpdb->prefix}acm_post_to_post where id2=21;" ) );

		wp_delete_post( 11 );
		$this->assertEquals( 0, $wpdb->get_var( "select count(id1) from {$wpdb->prefix}acm_post_to_post where id1=11;" ) );
		$this->assertEquals( 0, $wpdb->get_var( "select count(id2) from {$wpdb->prefix}acm_post_to_post where id2=11;" ) );
		$this->assertEquals( 0, $wpdb->get_var( "select count(id1) from {$wpdb->prefix}acm_post_to_post where id1=21;" ) );
		$this->assertEquals( 0, $wpdb->get_var( "select count(id2) from {$wpdb->prefix}acm_post_to_post where id2=21;" ) );
	}

}
