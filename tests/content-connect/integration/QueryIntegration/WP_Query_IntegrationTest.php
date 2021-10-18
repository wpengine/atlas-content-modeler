<?php

namespace WPE\AtlasContentModeler\ContentConnect\Tests\Integration\QueryIntegration;

use WPE\AtlasContentModeler\ContentConnect\Plugin;
use WPE\AtlasContentModeler\ContentConnect\QueryIntegration\RelationshipQuery;
use WPE\AtlasContentModeler\ContentConnect\QueryIntegration\WPQueryIntegration;
use WPE\AtlasContentModeler\ContentConnect\Registry;
use WPE\AtlasContentModeler\ContentConnect\Relationships\PostToPost;
use WPE\AtlasContentModeler\ContentConnect\Tests\Integration\ContentConnectTestCase;

class WP_Query_IntegrationTest extends ContentConnectTestCase {

	public function setUp() {
		global $wpdb;

		$wpdb->query( "delete from {$wpdb->prefix}acm_post_to_post" );

		$plugin           = Plugin::instance();
		$plugin->registry = new Registry();
		$plugin->registry->setup();

		parent::setUp();
	}

	public function define_relationships() {
		$registry = Plugin::instance()->get_registry();
		$registry->define_post_to_post( 'post', 'post', 'basic' );
		$registry->define_post_to_post( 'post', 'post', 'complex' );
		$registry->define_post_to_post( 'post', 'post', 'page1' );
		$registry->define_post_to_post( 'post', 'post', 'page2' );
	}

	public function test_that_nothing_happens_without_relationship_defined() {
		$args = array(
			'post_type'              => 'post',
			'fields'                 => 'ids',
			'orderby'                => 'ID',
			'order'                  => 'ASC',
			'posts_per_page'         => 2,
			'paged'                  => 1,
			'acm_relationship_query' => array(
				array(
					'related_to_post' => '20',
					'name'            => 'page1',
				),
			),
		);

		$query = new \WP_Query( $args );
		$this->assertEquals( array( 1, 2 ), $query->posts );

		$args['paged'] = 2;
		$query         = new \WP_Query( $args );
		$this->assertEquals( array( 3, 4 ), $query->posts );
	}

	public function test_that_nothing_happens_without_required_params() {
		$this->define_relationships();

		$args = array(
			'post_type'      => 'post',
			'fields'         => 'ids',
			'orderby'        => 'ID',
			'order'          => 'ASC',
			'posts_per_page' => 2,
			'paged'          => 1,
		);

		$query = new \WP_Query( $args );
		$this->assertEquals( array( 1, 2 ), $query->posts );

		$args['paged'] = 2;
		$query         = new \WP_Query( $args );
		$this->assertEquals( array( 3, 4 ), $query->posts );
	}

	public function test_that_nothing_happens_without_related_to_post() {
		$this->define_relationships();

		$args = array(
			'post_type'              => 'post',
			'fields'                 => 'ids',
			'orderby'                => 'ID',
			'order'                  => 'ASC',
			'posts_per_page'         => 2,
			'paged'                  => 1,
			'acm_relationship_query' => array(
				array(
					'name' => 'page1',
				),
			),
		);

		$query = new \WP_Query( $args );
		$this->assertEquals( array( 1, 2 ), $query->posts );

		$args['paged'] = 2;
		$query         = new \WP_Query( $args );
		$this->assertEquals( array( 3, 4 ), $query->posts );
	}

	public function test_that_nothing_happens_without_relationship_name() {
		$this->define_relationships();

		$args = array(
			'post_type'              => 'post',
			'fields'                 => 'ids',
			'orderby'                => 'ID',
			'order'                  => 'ASC',
			'posts_per_page'         => 2,
			'paged'                  => 1,
			'acm_relationship_query' => array(
				array(
					'related_to_post' => '31',
				),
			),
		);

		$query = new \WP_Query( $args );
		$this->assertEquals( array( 1, 2 ), $query->posts );

		$args['paged'] = 2;
		$query         = new \WP_Query( $args );
		$this->assertEquals( array( 3, 4 ), $query->posts );
	}

	public function test_basic_post_to_post_query_integration() {
		$this->add_post_relations();
		$this->define_relationships();

		$args = array(
			'post_type'              => 'post',
			'fields'                 => 'ids',
			'orderby'                => 'ID',
			'order'                  => 'ASC',
			'posts_per_page'         => 2,
			'paged'                  => 1,
			'acm_relationship_query' => array(
				array(
					'related_to_post' => '31',
					'name'            => 'page1',
				),
			),

		);

		$query = new \WP_Query( $args );
		$this->assertEquals( array( 36, 40 ), $query->posts );

		$args['paged'] = 2;
		$query         = new \WP_Query( $args );
		$this->assertEquals( array( 44, 48 ), $query->posts );

		$args['acm_relationship_query'][0]['related_to_post'] = 32;
		$args['paged']                                        = 1;
		$query = new \WP_Query( $args );
		$this->assertEquals( array( 37, 41 ), $query->posts );

		$args['paged'] = 2;
		$query         = new \WP_Query( $args );
		$this->assertEquals( array( 45, 49 ), $query->posts );

		// Different name, so should come back empty.
		$args['acm_relationship_query'][0]['related_to_post'] = 33;
		$args['paged']                                        = 1;
		$query = new \WP_Query( $args );
		$this->assertEquals( array(), $query->posts );

		$args['acm_relationship_query'][0]['name'] = 'page2';
		$query                                     = new \WP_Query( $args );
		$this->assertEquals( array( 38, 42 ), $query->posts );

		$args['paged'] = '2';
		$query         = new \WP_Query( $args );
		$this->assertEquals( array( 46, 50 ), $query->posts );
	}

	public function test_compound_post_to_post_queries() {
		$this->add_post_relations();
		$this->define_relationships();

		$args = array(
			'post_type'      => 'post',
			'fields'         => 'ids',
			'orderby'        => 'ID',
			'order'          => 'ASC',
			'posts_per_page' => 3,
			'paged'          => 1,
		);

		$args['acm_relationship_query'] = array(
			'relation' => 'OR',
			array(
				'related_to_post' => 1,
				'name'            => 'basic',
			),
			array(
				'related_to_post' => 1,
				'name'            => 'complex',
			),
		);
		$query                          = new \WP_Query( $args );
		$this->assertEquals( array( 2, 3, 4 ), $query->posts );

		$args['acm_relationship_query']['relation'] = 'AND';
		$query                                      = new \WP_Query( $args );
		$this->assertEquals( array( 3 ), $query->posts );
	}

	public function test_orderby_only_works_with_one_segment() {
		$this->define_relationships();

		$query = new \stdClass();

		$query->query_vars = array(
			'orderby' => 'relationship',
		);

		$query->acm_relationship_query = new RelationshipQuery(
			array(
				array(
					'related_to_post' => 1,
					'name'            => 'basic',
				),
			)
		);

		// The other function does nothing without a where.
		$query->acm_relationship_query->where = 'WHERE';

		$orderby = 'default';

		$integration = new WPQueryIntegration();

		$this->assertEquals( 'p2p1.order = 0, p2p1.order ASC', $integration->posts_orderby( $orderby, $query ) );

		$query->acm_relationship_query = new RelationshipQuery(
			array(
				array(
					'related_to_post' => 1,
					'name'            => 'basic',
				),
				array(
					'related_to_post' => 2,
					'name'            => 'basic',
				),
			)
		);

		// The other function does nothing without a where.
		$query->acm_relationship_query->where = 'WHERE';

		$this->assertEquals( 'default', $integration->posts_orderby( $orderby, $query ) );
	}

	public function test_post_to_post_sorting_queries() {
		$this->add_post_relations();
		$this->define_relationships();

		$p2p = new PostToPost( 'post', 'post', 'page1' );
		$p2p->save_sort_data( 31, array( 40, 48, 44, 36 ) );

		$args = array(
			'post_type'              => 'post',
			'fields'                 => 'ids',
			'orderby'                => 'relationship',
			'posts_per_page'         => 2,
			'paged'                  => 1,
			'acm_relationship_query' => array(
				array(
					'related_to_post' => '31',
					'name'            => 'page1',
				),
			),

		);

		$query = new \WP_Query( $args );
		$this->assertEquals( array( 40, 48 ), $query->posts );

		$args['paged'] = 2;
		$query         = new \WP_Query( $args );
		$this->assertEquals( array( 44, 36 ), $query->posts );
	}

}
