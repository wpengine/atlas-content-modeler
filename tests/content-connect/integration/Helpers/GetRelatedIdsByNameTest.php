<?php

namespace WPE\AtlasContentModeler\ContentConnect\Tests\Integration\Helpers;

use function WPE\AtlasContentModeler\ContentConnect\Helpers\get_registry;
use function WPE\AtlasContentModeler\ContentConnect\Helpers\get_related_ids_by_name;
use WPE\AtlasContentModeler\ContentConnect\Tests\Integration\ContentConnectTestCase;

class GetRelatedIdsByNameTest extends ContentConnectTestCase {

	public function test_returns_all_post_types() {
		$registry = get_registry();

		$post_car  = $registry->define_post_to_post( 'car', 'post', 'same-name' );
		$post_tire = $registry->define_post_to_post( 'tire', 'post', 'same-name' );

		$post_car->add_relationship( 1, 11 );
		$post_tire->add_relationship( 1, 21 );

		// Sanity check (restrict by specific relationship first).
		$this->assertEquals( array( 11 ), $post_car->get_related_object_ids( 1 ) );
		$this->assertEquals( array( 21 ), $post_tire->get_related_object_ids( 1 ) );

		$this->assertEquals( array( 11, 21 ), get_related_ids_by_name( 1, 'same-name' ) );
	}

}
