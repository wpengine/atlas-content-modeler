<?php

class EditTaxonomyCest {

	public function _before( \AcceptanceTester $i ) {
		$i->maximizeWindow();
		$i->loginAsAdmin();
		$content_model = $i->haveContentModel( 'Goose', 'Geese' );
		$i->amOnWPEngineEditContentModelPage( $content_model['slug'] );
		$i->haveTaxonomy( 'Breed', 'Breeds', [ 'goose' ] );
		$i->wait( 1 );
		$i->amOnTaxonomyListingsPage();
		$i->wait( 1 );
		$i->click( '.action button.options' );
		$i->see( 'Edit', '.dropdown-content' );
		$i->click( 'Edit', '.action .dropdown-content' );
		$i->wait( 1 );
	}

	public function i_can_edit_a_taxonomy( AcceptanceTester $i ) {
		$i->seeInField( '#singular', 'Breed' );

		$i->fillField( [ 'name' => 'singular' ], 'Br33d' );
		$i->fillField( [ 'name' => 'plural' ], 'Br33ds' );
		$i->wait( 1 );
		$i->seeInField( '#slug', 'breed' ); // Editing the singular field does not change the slug.

		$i->click( 'Update' );
		$i->wait( 1 );
		$i->see( 'taxonomy was updated' );
		$i->see( 'Br33ds', '.taxonomy-list' );
		$i->seeInField( '#singular', '' ); // Form is reset.
	}

	public function i_see_the_edit_form_reset_if_no_changes_were_made( AcceptanceTester $i ) {
		// Just submit the edit form without making changes.
		$i->click( 'Update' );
		$i->wait( 1 );
		$i->dontSee( 'taxonomy was updated' ); // No changes were made, so no toast should appear.
		$i->seeInField( '#singular', '' ); // Form is reset.
	}

	public function i_see_the_edit_form_reset_if_the_form_state_is_unchanged( AcceptanceTester $i ) {
		$i->click( '#model-checklist .checkbox' ); // Untick the selected model.
		$i->wait( 1 );
		$i->click( '#model-checklist .checkbox' ); // Retick the model. The form should not be dirty now.
		$i->wait( 1 );

		$i->click( 'Update' );
		$i->wait( 1 );
		$i->dontSee( 'taxonomy was updated' ); // No changes were made, so no toast should appear.
		$i->seeInField( '#singular', '' ); // Form is reset.
	}

	public function i_see_the_taxonomies_index_when_navigating_back_from_the_edit_form( AcceptanceTester $i ) {
		$i->moveBack(); // Immediately navigate back from the edit form.
		$i->wait( 1 );
		$i->see( 'Taxonomies' ); // Main taxonomies screen, not the Models screen.
		$i->see( 'Add New' );
		$i->seeInField( '#singular', '' ); // Form is empty.
	}
}
