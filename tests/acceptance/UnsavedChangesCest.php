<?php
use Codeception\Util\Locator;
class UnsavedChangesCest
{
	public function _before(\AcceptanceTester $I)
	{
		$I->maximizeWindow();

		$I->loginAsAdmin();
		$I->haveContentModel('goose', 'geese');
		$I->wait(1);
	}

	public function i_see_an_unsaved_changes_prompt_when_opening_another_field(\AcceptanceTester $I)
	{
		// Create the first field.
		$I->click('Text', '.field-buttons');
		$I->wait(1);
		$I->fillField(['name' => 'name'], 'Name');
		$I->click('button.primary');

		// Start to create a second field.
		$I->click(Locator::lastElement('.add-item'));
		$I->click('Text', '.field-buttons');
		$I->wait(1);
		$I->fillField(['name' => 'name'], 'Hobbies');

		// Click the first field's edit button before completing the second field.
		// Offsets are used here to prevent “other element would receive the click”
		// due to the “add field” button overlapping the edit button in the center.
		$I->clickWithLeftButton('button.edit', 10, 10); //

		// Confirm that the Unsaved Changes modal appears.
		$I->see('Unsaved Changes');

		// Click “Continue Editing” and confirm our changes were preserved.
		$I->click('.ReactModal__Content button.primary');
		$I->seeInField('#name', 'Hobbies');

		// Now try adding a new field without saving changes to the current field.
		$I->click('.add-item button');
		$I->see('Unsaved Changes');

		// Click “Discard Changes” and confirm our incomplete Hobbies field is gone.
		$I->click('.ReactModal__Content button.tertiary');
		$I->dontSee('Hobbies');
	}
}
