<?php

class DeleteTaxonomyCest {

	/**
	 * Ensure a user can delete ACM taxonomies.
	 */
	public function i_can_delete_a_taxonomy( AcceptanceTester $i ) {
		$i->maximizeWindow();
		$i->loginAsAdmin();
		$i->haveContentModel( 'Goose', 'Geese' );
		$i->haveTaxonomy( 'Breed', 'Breeds', [ 'types' => [ 'goose' ] ] );
		$i->amOnTaxonomyListingsPage();
		$i->wait( 1 );

		$i->click( '.action button.options' );
		$i->see( 'Delete', '.dropdown-content' );
		$i->click( 'Delete', '.action .dropdown-content' );

		$i->see( 'Are you sure you want to delete the Breeds taxonomy?' );
		$i->click( 'Delete', '.atlas-content-modeler-delete-field-modal-container' );
		$i->wait( 1 );
		$i->see( 'You currently have no taxonomies.' );
	}
}
