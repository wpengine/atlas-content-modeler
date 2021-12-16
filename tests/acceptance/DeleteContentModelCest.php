<?php

class DeleteContentModelCest {

	public function _before( \AcceptanceTester $i ) {
		$i->resizeWindow( 1024, 1024 );
		$i->maximizeWindow();
		$i->loginAsAdmin();
		$i->haveContentModel( 'Goose', 'Geese' );
	}

	/**
	 * Ensure a user can delete models.
	 */
	public function i_can_delete_a_model( AcceptanceTester $i ) {
		$i->amOnWPEngineContentModelPage();
		$i->wait( 1 );

		$i->click( '.model-list button.options' );
		$i->see( 'Delete', '.dropdown-content' );
		$i->click( 'Delete', '.model-list .dropdown-content' );

		$i->see( 'Are you sure you want to delete' );
		$i->click( 'Delete', '.atlas-content-modeler-delete-model-modal-container' );
		$i->wait( 1 );
		$i->see( 'You currently have no Content Models.' );
	}

	/**
	 * Ensure that any taxonomies associated with a deleted model are updated properly.
	 *
	 * We expect that the deleted model's post type slug will be removed from the 'types'
	 * array of any taxonomies it is associated with.
	 */
	public function i_see_that_associated_taxonomies_are_updated_when_model_is_deleted( AcceptanceTester $i ) {
		$i->haveContentModel( 'Moose', 'Moose' );
		$i->haveTaxonomy( 'Breed', 'Breeds', [ 'goose', 'moose' ] );
		$i->wait( 1 );

		$i->amOnWPEngineContentModelPage();
		$i->click( '.model-list button.options' );
		$i->click( 'Delete', '.model-list .dropdown-content' );
		$i->click( 'Delete', '.atlas-content-modeler-delete-model-modal-container' );

		$i->dontSee( 'Goose', '.model-list' );
		$i->wait( 1 );

		// Test that React state was updated without reloading the page.
		$i->click( 'Taxonomies', '#toplevel_page_atlas-content-modeler .wp-submenu' );
		$i->see( 'moose', '.taxonomy-list' );
		$i->dontSee( 'goose', '.taxonomy-list' );

		/**
		 * We have tested the React state update above, but we
		 * should also test a fresh copy of the data from the
		 * server.
		 */
		$i->reloadPage();

		$i->see( 'moose', '.taxonomy-list' );
		$i->dontSee( 'goose', '.taxonomy-list' );
	}

	public function i_see_relationship_fields_for_a_deleted_model_are_automatically_removed( AcceptanceTester $i ) {
		// Create a Mouse model with a “Geese Friends” relationship field.
		$content_model = $i->haveContentModel( 'Mouse', 'Mice' );
		$i->amOnWPEngineEditContentModelPage( $content_model['slug'] );
		$i->click( 'Relation', '.field-buttons' );
		$i->wait( 1 );
		$i->fillField( [ 'name' => 'name' ], 'Geese Friends' );
		$i->selectOption( '#reference', 'Geese' );
		$i->click( '#many-to-many' );
		$i->click( '.open-field button.primary' );
		$i->wait( 1 );
		$i->see( 'Geese Friends' );

		// Delete the Geese model (first model on the page).
		$i->amOnWPEngineContentModelPage();
		$i->wait( 1 );
		$i->click( '.model-list button.options' );
		$i->see( 'Delete', '.dropdown-content' );
		$i->click( 'Delete', '.model-list .dropdown-content' );

		// Confirm we see a warning that relationship fields will be deleted.
		$i->see( 'Are you sure you want to delete' );
		$i->see( 'Relationship fields' ); // Warning that relationship fields will be deleted.
		$i->click( 'Delete', '.atlas-content-modeler-delete-model-modal-container' );
		$i->wait( 1 );

		// Confirm the relationship field has been removed.
		$i->click( 'Mice', '.model-list' );
		$i->wait( 1 );
		$i->dontSee( 'Geese Friends' );
	}
}
