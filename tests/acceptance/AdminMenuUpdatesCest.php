<?php

/**
 * Users should see their changes reflected in the WordPress admin topbar
 * without having to refresh the page.
 */
class AdminMenuUpdatesCest {

	public function _before( \AcceptanceTester $i ) {
		$i->maximizeWindow();
		$i->loginAsAdmin();
	}

	/**
	 * Ensure the topbar is updated when a model is added.
	 */
	public function the_adminbar_adds_new_post_types_upon_creation( AcceptanceTester $i ) {
		$i->dontSeeElementInDOM( '#wp-admin-bar-new-goose' );
		$i->dontSeeElementInDOM( '#wp-admin-bar-new-moose' );

		$i->haveContentModel( 'Goose', 'Geese' );
		$i->haveContentModel( 'Moose', 'Moose' );
		$i->amOnWPEngineContentModelPage();

		$i->seeElementInDOM( '#wp-admin-bar-new-goose' );
		$i->seeElementInDOM( '#wp-admin-bar-new-moose' );
	}

	/**
	 * Ensure the topbar is updated when a model is deleted.
	 */
	public function the_adminbar_removes_post_types_upon_deletion( AcceptanceTester $i ) {
		$i->haveContentModel( 'Moose', 'Moose' );
		$i->haveContentModel( 'Goose', 'Geese' );
		$i->amOnWPEngineContentModelPage();

		$i->seeElementInDOM( '#wp-admin-bar-new-goose' );
		$i->seeElementInDOM( '#wp-admin-bar-new-moose' );

		// Delete Moose.
		$i->click( '.model-list button.options' );
		$i->click( '.dropdown-content a.delete' );
		$i->click( 'Delete', '.atlas-content-modeler-delete-model-modal-container' );
		$i->wait( 1 );

		$i->dontSeeElementInDOM( '#wp-admin-bar-new-goose' );

		// Delete Goose.
		$i->click( '.model-list button.options' );
		$i->click( '.dropdown-content a.delete' );
		$i->click( 'Delete', '.atlas-content-modeler-delete-model-modal-container' );
		$i->wait( 1 );

		$i->dontSeeElementInDOM( '#wp-admin-bar-new-moose' );
	}
}
