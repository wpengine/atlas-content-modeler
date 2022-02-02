<?php

class CreateTaxonomyCest {

	public function _before( \AcceptanceTester $i ) {
		$i->resizeWindow( 1024, 1024 );
		$i->maximizeWindow();
		$i->loginAsAdmin();
		$i->haveContentModel( 'goose', 'geese' );
	}

	public function i_can_navigate_to_the_taxonomies_page( AcceptanceTester $i ) {
		$i->amOnWPEngineContentModelPage();
		$i->wait( 1 );
		$i->see( 'Taxonomies', '#toplevel_page_atlas-content-modeler .wp-submenu' );
		$i->click( 'Taxonomies', '#toplevel_page_atlas-content-modeler .wp-submenu' );
		$i->wait( 1 );
		$i->see( 'Taxonomies', 'section.heading h2' );
	}

	public function i_can_see_the_no_taxonomies_message_if_none_exist( AcceptanceTester $i ) {
		$i->amOnTaxonomyListingsPage();
		$i->see( 'You currently have no taxonomies', '.taxonomy-list' );
	}

	public function i_can_create_a_taxonomy( AcceptanceTester $i ) {
		$i->amOnTaxonomyListingsPage();
		$i->wait( 1 );

		$i->fillField( [ 'name' => 'singular' ], 'Breed' );
		$i->fillField( [ 'name' => 'plural' ], 'Breeds' );
		$i->click( '.checklist .checkbox' ); // The “goose” model.
		$i->click( '.card-content button.primary' );
		$i->wait( 2 );
		$i->see( 'taxonomy was created', '#success' );
		$i->see( 'Breeds', '.taxonomy-list' );
		$i->see( 'goose', '.taxonomy-list' );

		// Form fields should reset when a submission was successful.
		$i->seeInField( '#singular', '' );
		$i->seeInField( '#plural', '' );
		$i->seeInField( '#slug', '' );

		// Character counts should reset.
		$i->see( '0/50', '.field .count' );
	}

	public function i_can_not_create_a_taxonomy_without_ticking_a_model( AcceptanceTester $i ) {
		$i->amOnTaxonomyListingsPage();
		$i->wait( 1 );

		$i->fillField( [ 'name' => 'singular' ], 'Breed' );
		$i->fillField( [ 'name' => 'plural' ], 'Breeds' );
		$i->click( '.card-content button.primary' );
		$i->wait( 1 );
		$i->see( 'Please choose at least one model' );
	}

	public function i_can_not_create_a_taxonomy_without_filling_the_singular_name( AcceptanceTester $i ) {
		$i->amOnTaxonomyListingsPage();
		$i->wait( 1 );

		$i->fillField( [ 'name' => 'plural' ], 'Breeds' );
		$i->click( '.checklist .checkbox' ); // The “goose” model.
		$i->click( '.card-content button.primary' );
		$i->wait( 1 );
		$i->see( 'This field is required' );
	}

	public function i_can_not_create_a_taxonomy_without_filling_the_plural_name( AcceptanceTester $i ) {
		$i->amOnTaxonomyListingsPage();
		$i->wait( 1 );

		$i->fillField( [ 'name' => 'singular' ], 'Breed' );
		$i->click( '.checklist .checkbox' ); // The “goose” model.
		$i->click( '.card-content button.primary' );
		$i->wait( 1 );
		$i->see( 'This field is required' );
	}

	public function i_can_not_create_a_taxonomy_if_the_slug_already_exists( AcceptanceTester $i ) {
		$i->amOnTaxonomyListingsPage();
		$i->wait( 1 );

		$i->fillField( [ 'name' => 'singular' ], 'Breed' );
		$i->fillField( [ 'name' => 'plural' ], 'Breeds' );
		$i->click( '.checklist .checkbox' ); // The “goose” model.
		$i->click( '.card-content button.primary' );
		$i->wait( 1 );

		// Create another taxonomy with the same info.
		$i->fillField( [ 'name' => 'singular' ], 'Breed' );
		$i->fillField( [ 'name' => 'plural' ], 'Breeds' );
		$i->click( '.checklist .checkbox' );
		$i->click( '.card-content button.primary' );
		$i->wait( 1 );

		$i->see( 'already exists' );
	}

	public function i_can_not_create_a_taxonomy_with_a_reserved_singular_name( AcceptanceTester $i ) {
		$i->amOnTaxonomyListingsPage();
		$i->wait( 1 );

		$i->fillField( [ 'name' => 'singular' ], 'Post' ); // 'Post' is a reserved name.
		$i->fillField( [ 'name' => 'plural' ], 'Breeds' );
		$i->fillField( [ 'name' => 'slug' ], 'breed' );
		$i->click( '.checklist .checkbox' ); // The “goose” model.
		$i->click( '.card-content button.primary' );
		$i->wait( 1 );
		$i->see( 'singular name is in use' );
	}

	public function i_can_not_create_a_taxonomy_with_a_reserved_plural_name( AcceptanceTester $i ) {
		$i->amOnTaxonomyListingsPage();
		$i->wait( 1 );

		$i->fillField( [ 'name' => 'singular' ], 'Breed' );
		$i->fillField( [ 'name' => 'plural' ], 'Posts' ); // 'Posts' is a reserved name.
		$i->click( '.checklist .checkbox' ); // The “goose” model.
		$i->click( '.card-content button.primary' );
		$i->wait( 1 );
		$i->see( 'plural name is in use' );
	}

	public function i_can_not_create_a_taxonomy_if_the_slug_is_a_reserved_term( AcceptanceTester $i ) {
		$i->amOnTaxonomyListingsPage();
		$i->wait( 1 );

		$i->fillField( [ 'name' => 'singular' ], 'Author' );
		$i->fillField( [ 'name' => 'plural' ], 'Authors' );
		$i->click( '.checklist .checkbox' ); // The “goose” model.
		$i->click( '.card-content button.primary' );
		$i->wait( 1 );

		$i->see( 'Taxonomy slug is reserved.' );
	}

	public function i_can_see_a_generated_slug_when_creating_a_second_taxonomy_after_editing_the_slug_in_the_first( AcceptanceTester $i ) {
		$i->amOnTaxonomyListingsPage();
		$i->wait( 1 );

		$i->fillField( [ 'name' => 'singular' ], 'First' );
		$i->fillField( [ 'name' => 'plural' ], 'Firsts' );
		// Edit the slug manually to break the “link” with the singular field.
		$i->fillField( [ 'name' => 'slug' ], 'myFirst' );
		$i->click( '.checklist .checkbox' ); // The “goose” model.
		$i->click( '.card-content button.primary' );
		$i->wait( 1 );
		$i->see( 'taxonomy was created', '#success' );
		$i->see( 'Firsts', '.taxonomy-list' );

		// A successful submission should relink the Singular and Taxonomy ID fields
		// so they are linked for the next entry. Confirm the fields were
		// relinked: filling "singular" should auto-generate a slug.
		$i->fillField( [ 'name' => 'singular' ], 'Second' );
		$i->seeInField( '#slug', 'second' );
	}
}
