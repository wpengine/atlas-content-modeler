<?php

class DeleteContentModelCest
{
	public function _before(\AcceptanceTester $I)
	{
		$I->maximizeWindow();
		$I->loginAsAdmin();
		$I->wait(1);
		$I->haveContentModel('Goose', 'Geese');
		$I->wait(1);
	}

	/**
	 * Ensure a user can delete models.
	 */
	public function i_can_delete_a_model(AcceptanceTester $I)
	{
		$I->amOnWPEngineContentModelPage();
		$I->wait(1);

		$I->click('.model-list button.options');
		$I->see('Delete', '.dropdown-content');
        $I->click('Delete', '.model-list .dropdown-content');

		$I->see("Are you sure you want to delete");
		$I->click('Delete', '.atlas-content-modeler-delete-model-modal-container');
		$I->wait(1);
		$I->see("You currently have no Content Models.");
	}

	/**
	 * Ensure that any taxonomies associated with a deleted model are updated properly.
	 *
	 * We expect that the deleted model's post type slug will be removed from the 'types'
	 * array of any taxonomies it is associated with.
	 */
	public function i_see_that_associated_taxonomies_are_updated_when_model_is_deleted(AcceptanceTester $I)
	{
		$I->haveContentModel('Moose', 'Moose');
		$I->wait(1);
		$I->haveTaxonomy('Breed', 'Breeds', [ 'goose', 'moose' ]);
		$I->wait(1);

		$I->amOnWPEngineContentModelPage();
		$I->click('.model-list button.options');
        $I->click('Delete', '.model-list .dropdown-content');
		$I->click('Delete', '.atlas-content-modeler-delete-model-modal-container');

		$I->dontSee('Goose', '.model-list');
		$I->wait(1);

		// Test that React state was updated without reloading the page.
		$I->click('button.taxonomies');
		$I->see('moose', '.taxonomy-list');
		$I->dontSee('goose', '.taxonomy-list');

		/**
		 * We have tested the React state update above, but we
		 * should also test a fresh copy of the data from the
		 * server.
		 */
		$I->reloadPage();

		$I->see('moose', '.taxonomy-list');
		$I->dontSee('goose', '.taxonomy-list');
	}
}
