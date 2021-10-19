<?php

namespace WPE\AtlasContentModeler\ContentConnect\Tests\Integration\Tables;

class PostToPostTest extends \PHPUnit_Framework_TestCase {

	public function test_table_is_created() {
		global $wpdb;

		// @ suppresses headers already sent errors.
		@do_action( 'init' ); // phpcs:ignore

		$result = $wpdb->query( "SHOW TABLES LIKE '{$wpdb->prefix}acm_post_to_post'" );

		$this->assertEquals( 1, $result );
	}

}
