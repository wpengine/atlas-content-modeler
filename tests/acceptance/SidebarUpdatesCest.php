<?php

/**
 * Users should see their changes reflected in the WordPress admin sidebar
 * without having to refresh the page.
 */
class SidebarUpdatesCest {

	public function _before( \AcceptanceTester $i ) {
		$i->maximizeWindow();
		$i->loginAsAdmin();
	}

	/**
	 * Ensure the sidebar is updated when a model is added.
	 */
	public function the_sidebar_adds_new_post_types_upon_creation( AcceptanceTester $i ) {
		$i->dontSeeElementInDOM( '#menu-posts-goose' );
		$i->dontSeeElementInDOM( '#menu-posts-moose' );

		$i->haveContentModel( 'Goose', 'Geese' );
		$i->haveContentModel( 'Moose', 'Moose' );
		$i->amOnWPEngineContentModelPage();

		$i->seeElementInDOM( '#menu-posts-goose' );
		$i->seeElementInDOM( '#menu-posts-moose' );
	}

	/**
	 * Ensure the sidebar is updated when a model is deleted.
	 */
	public function the_sidebar_removes_post_types_upon_deletion( AcceptanceTester $i ) {
		$i->haveContentModel( 'Moose', 'Moose' );
		$i->haveContentModel( 'Goose', 'Geese' );
		$i->amOnWPEngineContentModelPage();

		$i->seeElementInDOM( '#menu-posts-goose' );
		$i->seeElementInDOM( '#menu-posts-moose' );

		// Delete Moose.
		$i->click( '.model-list button.options' );
		$i->click( '.dropdown-content a.delete' );
		$i->click( 'Delete', '.atlas-content-modeler-delete-model-modal-container' );
		$i->wait( 1 );

		$i->dontSeeElementInDOM( '#menu-posts-moose' );

		// Delete Goose.
		$i->click( '.model-list button.options' );
		$i->click( '.dropdown-content a.delete' );
		$i->click( 'Delete', '.atlas-content-modeler-delete-model-modal-container' );
		$i->wait( 1 );

		$i->dontSeeElementInDOM( '#menu-posts-goose' );
	}

	/**
	 * Ensure the sidebar is updated when taxonomies are added.
	 */
	public function the_sidebar_adds_new_taxonomies_upon_creation( AcceptanceTester $i ) {
		$i->haveContentModel( 'Moose', 'Moose' );
		$i->haveContentModel( 'Goose', 'Geese' );
		$i->haveTaxonomy( 'Breed', 'Breeds', [ 'goose' ] );
		$i->wait( 1 );

		// The new taxonomy is assigned to Goose.
		$i->seeElementInDOM( '#menu-posts-goose a', [ 'href' => 'edit-tags.php?taxonomy=breed&post_type=goose' ] );
		$i->moveMouseOver( [ 'css' => '#menu-posts-goose' ] );
		$i->see( 'Breeds', [ 'css' => '#menu-posts-goose a' ] );
		// The new taxonomy is not assigned to Moose.
		$i->dontSeeElementInDOM( '#menu-posts-moose a', [ 'href' => 'edit-tags.php?taxonomy=breed&post_type=moose' ] );

		$i->haveTaxonomy( 'Region', 'Regions', [ 'goose', 'moose' ] );
		$i->wait( 1 );

		// The new taxonomy is added to Goose and existing taxonomies are still present.
		$i->seeElementInDOM( '#menu-posts-goose a', [ 'href' => 'edit-tags.php?taxonomy=region&post_type=goose' ] );
		$i->moveMouseOver( [ 'css' => '#menu-posts-goose' ] );
		$i->see( 'Breeds', [ 'css' => '#menu-posts-goose a' ] );
		$i->see( 'Region', [ 'css' => '#menu-posts-goose a' ] );

		// Only the new taxonomy is present on Moose.
		$i->seeElementInDOM( '#menu-posts-moose a', [ 'href' => 'edit-tags.php?taxonomy=region&post_type=moose' ] );
		$i->moveMouseOver( [ 'css' => '#menu-posts-moose' ] );
		$i->dontSee( 'Breeds', [ 'css' => '#menu-posts-moose a' ] );
		$i->see( 'Region', [ 'css' => '#menu-posts-moose a' ] );
	}

	/**
	 * Ensure the sidebar is updated when taxonomies are edited.
	 */
	public function the_sidebar_updates_taxonomies_upon_editing( AcceptanceTester $i ) {
		$i->haveContentModel( 'Moose', 'Moose' );
		$i->haveContentModel( 'Goose', 'Geese' );
		$i->haveTaxonomy( 'Breed', 'Breeds', [ 'goose' ] );
		$i->wait( 1 );

		$i->moveMouseOver( [ 'css' => '#menu-posts-goose' ] );
		$i->see( 'Breeds', [ 'css' => '#menu-posts-goose a' ] );
		$i->moveMouseOver( [ 'css' => '#menu-posts-moose' ] );
		$i->dontSee( 'Breeds', [ 'css' => '#menu-posts-moose a' ] );

		// Make some edits.
		$i->click( '.action button.options' );
		$i->click( 'Edit', '.action .dropdown-content' );
		$i->wait( 1 );
		$i->fillField( [ 'name' => 'plural' ], 'Br33ds' );
		$i->click( '.checklist .checkbox input[value=moose]' );
		$i->click( '.card-content button.primary' );
		$i->wait( 1 );

		// The edited taxonomy should be updated on both models.
		$i->moveMouseOver( [ 'css' => '#menu-posts-goose' ] );
		$i->see( 'Br33ds', [ 'css' => '#menu-posts-goose a' ] );
		$i->moveMouseOver( [ 'css' => '#menu-posts-moose' ] );
		$i->see( 'Br33ds', [ 'css' => '#menu-posts-moose a' ] );

		// Make edits to test "type" removal.
		$i->click( '.action button.options' );
		$i->click( 'Edit', '.action .dropdown-content' );
		$i->wait( 1 );
		$i->click( '.checklist .checkbox input[value=goose]' ); // Remove from goose.
		$i->click( '.card-content button.primary' );
		$i->wait( 1 );

		$i->moveMouseOver( [ 'css' => '#menu-posts-goose' ] );
		$i->dontSee( 'Br33ds', [ 'css' => '#menu-posts-goose a' ] );
		$i->moveMouseOver( [ 'css' => '#menu-posts-moose' ] );
		$i->see( 'Br33ds', [ 'css' => '#menu-posts-moose a' ] );
	}

	/**
	 * Ensure the sidebar is updated when taxonomies are deleted.
	 */
	public function the_sidebar_removes_taxonomies_upon_deletion( AcceptanceTester $i ) {
		$i->haveContentModel( 'Moose', 'Moose' );
		$i->haveContentModel( 'Goose', 'Geese' );
		$i->haveTaxonomy( 'Breed', 'Breeds', [ 'goose', 'moose' ] );
		$i->wait( 1 );

		$i->moveMouseOver( [ 'css' => '#menu-posts-goose' ] );
		$i->see( 'Breeds', [ 'css' => '#menu-posts-goose a' ] );
		$i->moveMouseOver( [ 'css' => '#menu-posts-moose' ] );
		$i->See( 'Breeds', [ 'css' => '#menu-posts-moose a' ] );

		// Delete the Breeds taxonomy.
		$i->click( '.action button.options' );
		$i->see( 'Delete', '.dropdown-content' );
		$i->click( 'Delete', '.action .dropdown-content' );
		$i->click( 'Delete', '.atlas-content-modeler-delete-field-modal-container' );
		$i->wait( 1 );

		// The edited taxonomy should be removed from both models.
		$i->moveMouseOver( [ 'css' => '#menu-posts-goose' ] );
		$i->dontSee( 'Breeds', [ 'css' => '#menu-posts-goose a' ] );
		$i->moveMouseOver( [ 'css' => '#menu-posts-moose' ] );
		$i->dontSee( 'Breeds', [ 'css' => '#menu-posts-moose a' ] );
	}
}
