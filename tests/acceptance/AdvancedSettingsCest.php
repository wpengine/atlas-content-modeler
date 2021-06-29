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

	public function i_can_cancel_advanced_settings_edits_without_losing_other_field_changes(\AcceptanceTester $I) {
		$I->click('Text', '.field-buttons');
		$I->wait(1);
		$I->fillField(['name' => 'name'], 'Name');
		$I->seeInField('#slug','name');

		// Open and fill Advanced Settings.
		$I->click('button.settings');
		$I->fillField(['name' => 'minChars'], '1');

		// Cancel the Advanced Settings changes.
		$I->click('.ReactModal__Content button.tertiary');
		$I->wait(1);

		// Check the name field still contains the original text.
		$I->seeInField('#slug','name');

		// Check the minChars field in Advanced Settings is now cleared.
		$I->click('button.settings');
		$I->seeInField('#minChars','');
	}

	public function i_can_cancel_advanced_settings_edits_without_losing_existing_dirty_changes(\AcceptanceTester $I) {
		$I->click('Text', '.field-buttons');
		$I->wait(1);

		// Open and fill Advanced Settings.
		$I->click('button.settings');
		$I->fillField(['name' => 'minChars'], '111');

		// Save the Advanced Settings change but do not save the field yet.
		$I->click('.ReactModal__Content button.primary');
		$I->wait(1);

		// Open Advanced Settings again and update the same field.
		$I->click('button.settings');
		$I->fillField(['name' => 'minChars'], '999');

		// This time, cancel the Advanced Settings changes.
		$I->click('.ReactModal__Content button.tertiary');
		$I->wait(1);

		// Open Advanced Settings a final time.
		$I->click('button.settings');

		// Expect to see the original saved value (previous state when
		// “Done” was clicked), not an empty field (initial form state).
		$I->seeInField('#minChars','111');
	}

	public function i_can_set_allowed_file_types_for_media_fields( \AcceptanceTester $I ) {
		$I->click('Media', '.field-buttons');
		$I->wait(1);

		$I->fillField(['name' => 'name'], 'Photo');

		// Open and fill Advanced Settings.
		$I->click('button.settings');
		$I->fillField(['name' => 'allowedTypes'], 'jpg,jpeg,pdf');

		$I->click('.ReactModal__Content button.primary'); // Save Advanced Settings.
		$I->wait(1);
		$I->click('button.primary'); // Save the field.
		$I->wait(1);

		// Offsets are used here to prevent “other element would receive the click”
		// due to the “add field” button overlapping the edit button in the center.
		$I->clickWithLeftButton('.field-list button.edit', -5, -5);

		// Open Advanced Settings again.
		$I->click('button.settings');

		// Expect to see saved values.
		$I->seeInField('#allowedTypes', 'jpg,jpeg,pdf');

		$I->amOnPage('/wp-admin/post-new.php?post_type=goose');
		$I->wait(1);
		$I->see('Accepts file types: JPG, JPEG, PDF');
	}
}
