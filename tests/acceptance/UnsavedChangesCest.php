<?php
use Codeception\Util\Locator;
class UnsavedChangesCest
{
	public function _before(\AcceptanceTester $I)
	{
		$I->resizeWindow(1024, 1024);
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
		$I->wait(1);

		// Start to create a second field.
		$I->click(Locator::lastElement('.add-item'));
		$I->click('Text', '.field-buttons');
		$I->wait(1);
		$I->fillField(['name' => 'name'], 'Hobbies');

		// Click the first field's edit button before completing the second field.
		// Offsets are used here to prevent “other element would receive the click”
		// due to the “add field” button overlapping the edit button in the center.
		$I->clickWithLeftButton('button.edit', 10, 10);

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

		// Start to edit the first field again.
		$I->clickWithLeftButton('button.edit', 10, 10);

		// Make a change to a field.
		$I->fillField(['name' => 'name'], 'Name Edited');

		// Attempt to navigate away via the breadcrumb.
		$I->click('Content Models', '.heading');

		// Check the modal appears, but discard changes.
		$I->see('Unsaved Changes');
		$I->click('.ReactModal__Content button.tertiary');
		$I->dontSee('Name Edited');
		$I->see('Name');

		// Start to edit the first field again.
		$I->clickWithLeftButton('button.edit', 10, 10);

		// Attempt to navigate away via the breadcrumb.
		$I->click('Content Models', '.heading');

		// Confirm the modal does *not* appear, because there are no unsaved changes.
		$I->dontSee('Unsaved Changes');
		$I->see('New Model'); // Model index page.
	}
}
