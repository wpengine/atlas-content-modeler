<?php

class EditTaxonomyCest
{
	public function _before(\AcceptanceTester $I)
	{
		$I->maximizeWindow();
		$I->loginAsAdmin();
		$I->wait(1);
		$I->haveContentModel('Goose', 'Geese');
		$I->haveTaxonomy('Breed', 'Breeds', ['goose']);
		$I->wait(1);
		$I->amOnTaxonomyListingsPage();
		$I->wait(1);
	}

	public function i_can_edit_a_taxonomy(AcceptanceTester $I)
	{
		$I->click('.action button.options');
		$I->see('Edit', '.dropdown-content');
        $I->click('Edit', '.action .dropdown-content');

		$I->wait(1);
		$I->seeInField('#singular','Breed');

		$I->fillField(['name' => 'singular'], 'Br33d');
		$I->fillField(['name' => 'plural'], 'Br33ds');
		$I->wait(1);
		$I->seeInField('#slug','breed'); // Editing the singular field does not change the slug.

		$I->click('Update');
		$I->wait(1);
		$I->see('taxonomy was updated');
		$I->see('Br33ds', '.taxonomy-list');
		$I->seeInField('#singular', ''); // Form is reset.
	}
}
