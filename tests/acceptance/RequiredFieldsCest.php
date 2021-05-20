<?php
use Codeception\Util\Locator;
class RequiredFieldsCest
{
	public function i_can_set_required_fields_and_see_submission_errors(AcceptanceTester $I)
	{
		$I->maximizeWindow();

		// Create a model with a required 'name' field.
		$I->loginAsAdmin();
		$I->haveContentModel('goose', 'geese');
		$I->amOnWPEngineEditContentModelPage('geese');
		$I->wait(1);

		$I->click('Text', '.field-buttons');
		$I->wait(1);
		$I->fillField(['name' => 'name'], 'Name');
		$I->seeInField('#slug','name');
		$I->click('.open-field label.checkbox.is-required');
		$I->click('.open-field button.primary');
		$I->wait(1);

		// Create an entry for the new model.
		$I->amOnPage('/wp-admin/edit.php?post_type=geese');
		$I->click('Add New', '.wrap');
		$I->wait(1);

		// Do not fill the 'name' field here.
		// We want to check we're prompted to fill the required field.

		$I->click('Publish', '#publishing-action');
		$I->wait(1);

		$I->see('field is required');

		// Fill the field as prompted.
		$I->fillField(['name' => 'wpe-content-model[geese][name]'], 'Goosey goose');

		$I->click('Publish', '#publishing-action');
		$I->wait(2);

		$I->see('Post published.');
		$I->seeInField('wpe-content-model[geese][name]', 'Goosey goose');
	}
}
