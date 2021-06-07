<?php
use Codeception\Util\Locator;
class AdvancedSettingsCest
{
	public function _before(\AcceptanceTester $I)
	{
		$I->maximizeWindow();

		$I->loginAsAdmin();
		$I->haveContentModel('goose', 'geese');
		$I->wait(1);
	}

	public function i_can_set_min_max_character_counts_for_text_fields(\AcceptanceTester $I)
	{
		$I->click('Text', '.field-buttons');
		$I->wait(1);
		$I->fillField(['name' => 'name'], 'Name');
		$I->seeInField('#slug','name');

		// Open and fill Advanced Settings.
		$I->click('button.settings');
		$I->fillField(['name' => 'minChars'], '1');
		$I->fillField(['name' => 'maxChars'], '10');
		$I->click('.ReactModal__Content button.primary');
		$I->wait(1);

		// Open Advanced Settings again and check the options persisted.
		$I->click('button.settings');
		$I->seeInField('minChars','1');
		$I->seeInField('maxChars','10');
	}

	public function i_can_see_errors_if_max_is_lower_than_min(\AcceptanceTester $I) {
		$I->click('Text', '.field-buttons');
		$I->wait(1);
		$I->fillField(['name' => 'name'], 'Name');
		$I->seeInField('#slug','name');

		// Open and fill Advanced Settings.
		$I->click('button.settings');
		$I->fillField(['name' => 'maxChars'], '10');
		$I->fillField(['name' => 'minChars'], '11');
		$I->see('Max must be more than min');
	}

	public function i_can_see_errors_if_min_is_negative(\AcceptanceTester $I) {
		$I->click('Text', '.field-buttons');
		$I->wait(1);
		$I->fillField(['name' => 'name'], 'Name');
		$I->seeInField('#slug','name');

		// Open and fill Advanced Settings.
		$I->click('button.settings');
		$I->fillField(['name' => 'minChars'], '-1');
		$I->see('The minimum value is');
	}

	public function i_can_see_errors_if_publisher_text_entry_does_not_exceed_min_chars(AcceptanceTester $I) {
		$I->click('Text', '.field-buttons');
		$I->wait(1);
		$I->fillField(['name' => 'name'], 'Name');
		$I->seeInField('#slug','name');

		// Open and fill Advanced Settings.
		$I->click('button.settings');
		$I->fillField(['name' => 'minChars'], '10');
		$I->click('.ReactModal__Content button.primary'); // Save Advanced Settings.
		$I->wait(1);
		$I->click('button.primary'); // Save the field.

		// Create an entry in the publisher app.
		$I->amOnPage('/wp-admin/edit.php?post_type=goose');
		$I->click('Add New', '.wrap');
		$I->wait(1);

		// Fill the field with a value that does not meet the 10 character minimum we set in minChars.
		$I->fillField(['name' => 'atlas-content-modeler[goose][name]'], 'goose');

		$I->click('Publish', '#publishing-action');
		$I->wait(1);

		$I->see('Minimum length is 10');
	}
}
