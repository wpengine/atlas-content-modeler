<?php

class EditTaxonomyCest
{
	public function _before(\AcceptanceTester $I)
	{
		$I->maximizeWindow();
		$I->loginAsAdmin();
		$I->wait(1);
		$I->haveContentModel('Goose', 'Geese');
		$I->wait(1);
		$I->haveTaxonomy('Breed', 'Breeds', ['goose']);
		$I->wait(1);
		$I->amOnTaxonomyListingsPage();
		$I->wait(1);
		$I->click('.action button.options');
		$I->see('Edit', '.dropdown-content');
		$I->click('Edit', '.action .dropdown-content');
		$I->wait(1);
	}

	public function i_can_edit_a_taxonomy(AcceptanceTester $I)
	{
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

	public function i_see_the_edit_form_reset_if_no_changes_were_made(AcceptanceTester $I)
	{
		// Just submit the edit form without making changes.
		$I->click('Update');
		$I->wait(1);
		$I->dontSee('taxonomy was updated'); // No changes were made, so no toast should appear.
		$I->seeInField('#singular', ''); // Form is reset.
	}

	public function i_see_the_edit_form_reset_if_the_form_state_is_unchanged(AcceptanceTester $I)
	{
		$I->click('#model-checklist .checkbox'); // Untick the selected model.
		$I->wait(1);
		$I->click('#model-checklist .checkbox'); // Retick the model. The form should not be dirty now.
		$I->wait(1);

		$I->click('Update');
		$I->wait(1);
		$I->dontSee('taxonomy was updated'); // No changes were made, so no toast should appear.
		$I->seeInField('#singular', ''); // Form is reset.
	}

	public function i_see_the_taxonomies_index_when_navigating_back_from_the_edit_form(AcceptanceTester $I)
	{
		$I->moveBack(); // Immediately navigate back from the edit form.
		$I->wait(1);
		$I->see('Taxonomies'); // Main taxonomies screen, not the Models screen.
		$I->see('Add New');
		$I->seeInField('#singular', ''); // Form is empty.
	}
}
