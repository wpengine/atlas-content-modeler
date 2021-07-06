<?php

class DeleteTaxonomyCest
{
	public function _before(\AcceptanceTester $I)
	{
		$I->maximizeWindow();
		$I->loginAsAdmin();
		$I->wait(1);
		$I->haveContentModel('Goose', 'Geese');
	}

	/**
	 * Ensure a user can delete ACM taxonomies.
	 */
	public function i_can_delete_a_taxonomy(AcceptanceTester $I)
	{
		$I->haveTaxonomy('Breed', 'Breeds', [ 'goose' ]);
		$I->wait(1);
		$I->amOnTaxonomyListingsPage();
		$I->wait(1);

		$I->click('.action button.options');
		$I->see('Delete', '.dropdown-content');
        $I->click('Delete', '.action .dropdown-content');

		$I->see("Are you sure you want to delete the Breeds taxonomy?");
		$I->click('Delete', '.atlas-content-modeler-delete-field-modal-container');
		$I->wait(1);
		$I->see("You currently have no taxonomies.");
	}
}
