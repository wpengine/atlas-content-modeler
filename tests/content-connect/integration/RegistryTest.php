<?php

namespace WPE\AtlasContentModeler\ContentConnect\Tests\Integration;

use WPE\AtlasContentModeler\ContentConnect\Registry;
use WPE\AtlasContentModeler\ContentConnect\Relationships\PostToPost;

class RegistryTest extends ContentConnectTestCase {

	public function test_relationship_doesnt_exist() {
		$registry = new Registry();

		$this->assertFalse( $registry->post_to_post_relationship_exists( 'post', 'post', 'basic' ) );
	}

	public function test_relationship_can_be_added() {
		$registry = new Registry();

		$this->assertInstanceOf( PostToPost::class, $registry->define_post_to_post( 'post', 'post', 'basic' ) );
	}

	public function test_doesnt_add_duplicate_post_to_post_relationship() {
		$registry = new Registry();

		$this->expectException( \Exception::class );

		$registry->define_post_to_post( 'post', 'post', 'basic' );
		$registry->define_post_to_post( 'post', 'post', 'basic' );
	}

	public function test_can_define_different_types_for_same_cpts() {
		$registry = new Registry();

		$this->assertInstanceOf( PostToPost::class, $registry->define_post_to_post( 'post', 'post', 'type1' ) );
		$this->assertInstanceOf( PostToPost::class, $registry->define_post_to_post( 'post', 'post', 'type2' ) );
	}

	public function test_flipped_order_is_still_duplicate() {
		$registry = new Registry();

		$this->expectException( \Exception::class );

		$registry->define_post_to_post( 'post', 'car', 'basic' );
		$registry->define_post_to_post( 'car', 'post', 'basic' );
	}

	public function test_retrieval_of_post_to_post_relationships() {
		$registry = new Registry();

		// Add all the relationship types so we know we aren't just lucky in the return values.
		$pp = $registry->define_post_to_post( 'post', 'post', 'basic' );
		$pc = $registry->define_post_to_post( 'post', 'car', 'basic' );
		$pt = $registry->define_post_to_post( 'post', 'tire', 'basic' );
		$ct = $registry->define_post_to_post( 'car', 'tire', 'basic' );
		$cc = $registry->define_post_to_post( 'car', 'car', 'basic' );
		$tt = $registry->define_post_to_post( 'tire', 'tire', 'basic' );

		$tt2 = new PostToPost( 'tire', 'tire', 'basic' );

		// Verify that two separate objects are NOT the same (sanity check).
		$this->assertNotSame( $tt, $tt2 );

		$this->assertSame( $pp, $registry->get_post_to_post_relationship( 'post', 'post', 'basic' ) );

		// Check that it doesn't matter the order of args.
		$this->assertSame( $pc, $registry->get_post_to_post_relationship( 'post', 'car', 'basic' ) );
		$this->assertSame( $pc, $registry->get_post_to_post_relationship( 'car', 'post', 'basic' ) );

		// Check that calling inverse args returns the same as well (it should, based on above two tests).
		$this->assertSame( $registry->get_post_to_post_relationship( 'post', 'car', 'basic' ), $registry->get_post_to_post_relationship( 'car', 'post', 'basic' ) );
	}

	public function test_retrieval_of_unique_relationship_names_on_same_cpt() {
		$registry = new Registry();

		$pp1 = $registry->define_post_to_post( 'post', 'post', 'type1' );
		$pp2 = $registry->define_post_to_post( 'post', 'post', 'type2' );

		$this->assertSame( $pp1, $registry->get_post_to_post_relationship( 'post', 'post', 'type1' ) );
		$this->assertSame( $pp2, $registry->get_post_to_post_relationship( 'post', 'post', 'type2' ) );
	}

	public function test_defining_without_array_is_same_as_with_array() {
		$registry = new Registry();

		$this->expectException( \Exception::class );

		$registry->define_post_to_post( 'post', 'post', 'basic' );
		$registry->define_post_to_post( 'post', [ 'post' ], 'basic' );
	}

	public function test_defining_same_multi_to_is_not_allowed() {
		$registry = new Registry();

		$this->expectException( \Exception::class );

		$registry->define_post_to_post( 'post', [ 'car', 'tire' ], 'basic' );
		$registry->define_post_to_post( 'post', [ 'car', 'tire' ], 'basic' );
	}

	public function test_defining_multi_to_inverse_order_is_not_allowed() {
		$registry = new Registry();

		$this->expectException( \Exception::class );

		$registry->define_post_to_post( 'post', [ 'car', 'tire' ], 'basic' );
		$registry->define_post_to_post( 'post', [ 'tire', 'car' ], 'basic' );
	}

	public function test_retrieval_of_multi_post_type_relationships() {
		$registry = new Registry();

		$pct = $registry->define_post_to_post( 'post', [ 'car', 'tire' ], 'basic' );

		$pct2 = new PostToPost( 'post', [ 'car', 'tire' ], 'basic' );

		// Verify that two separate objects are NOT the same (sanity check).
		$this->assertNotSame( $pct, $pct2 );

		$this->assertSame( $pct, $registry->get_post_to_post_relationship( 'post', [ 'car', 'tire' ], 'basic' ) );
		$this->assertSame( $pct, $registry->get_post_to_post_relationship( 'post', [ 'tire', 'car' ], 'basic' ) );
	}

}
