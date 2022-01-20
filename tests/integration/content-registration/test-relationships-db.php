<?php
use WPE\AtlasContentModeler\ContentConnect\Tables\PostToPost;

class TestRelationshipsDB extends WP_UnitTestCase {
	public function tear_down(): void {
		global $wpdb;
		parent::tear_down();
		$table = $wpdb->prefix . 'acm_post_to_post';
		$wpdb->query( "LOCK TABLES $table WRITE" );
		$wpdb->query( "DROP TABLE IF EXISTS $table" );
	}

	public function test_relationships_db_exists(): void {
		/**
		 * @var \wpdb $wpdb
		 */
		global $wpdb;
		$exists = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->prefix . 'acm_post_to_post' ) );
		self::assertSame( $exists, 'wptests_acm_post_to_post' );
	}
}
