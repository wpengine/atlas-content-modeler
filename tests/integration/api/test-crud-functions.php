<?php

use WPE\AtlasContentModeler\ContentConnect\Plugin;
use function WPE\AtlasContentModeler\API\replace_relationship;
use function WPE\AtlasContentModeler\ContentRegistration\update_registered_content_types;

class TestApiFunctions extends Integration_TestCase {
	/**
	 * Content models.
	 *
	 * @var array
	 */
	protected $content_models;

	/**
	 * Override of parent::set_up.
	 *
	 * @return void
	 */
	public function set_up(): void {
		parent::set_up();

		$this->content_models = $this->get_models( __DIR__ . '/test-data/content-models.php' );

		update_registered_content_types( $this->content_models );
		Plugin::instance()->setup();
		do_action( 'init' );
	}

	public function test_replace_relationship_will_associate_relation_ids() {
		global $wpdb;

		$post_id      = $this->factory->post->create( [ 'post_type' => 'person' ] );
		$relation_ids = $this->factory->post->create_many( 3, [ 'post_type' => 'car' ] );

		replace_relationship( $post_id, 'cars', $relation_ids );

		$result = $wpdb->get_results( "SELECT id2 FROM {$wpdb->prefix}acm_post_to_post WHERE id1 IN( {$post_id} );" );
		$result = array_map( 'intval', wp_list_pluck( $result, 'id2' ) );

		$this->assertEquals( $relation_ids, $result );
	}
}
