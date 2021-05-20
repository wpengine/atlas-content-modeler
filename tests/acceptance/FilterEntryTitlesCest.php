<?php
use Codeception\Util\Locator;

class FilterEntryTitlesCest
{
	/**
	 * If the user specifies a text field to use as the entry title, check that
	 * the value of that field appears as the title in the list of entries.
	 *
	 * @param AcceptanceTester $i
	 */
	public function i_see_the_defined_entry_title_field_value_in_the_entry_list(AcceptanceTester $i)
	{
		$i->maximizeWindow();

		$i->loginAsAdmin();
		$i->haveContentModel('goose', 'geese', 'Geese go honk');
		$i->wait(1);

		$i->click('Text', '.field-buttons');
		$i->fillField(['name' => 'name'], 'Fave Foods');
		$i->click('.open-field button.primary');
		$i->wait(1);

		$i->click(Locator::lastElement('.add-item'));
		$i->click('Text', '.field-buttons');
		$i->fillField(['name' => 'name'], 'Name');
		// Set the 'name' field as the entry title field.
		$i->click('.open-field .checkbox.is-title');
		$i->click('.open-field button.primary');
		$i->wait(1);

		// Create an entry for the model.
		$i->amOnPage('/wp-admin/edit.php?post_type=goose');
		$i->click('Add New', '.wrap');
		$i->wait(1);

		$i->fillField(['name' => 'wpe-content-model[goose][faveFoods]'], 'Gumdrops');
		$i->fillField(['name' => 'wpe-content-model[goose][name]'], 'Lucy');

		$i->click('Publish', '#publishing-action');
		$i->wait(2);

		$i->see('Post published.');

		// Check that the admin page displays the “Lucy” title.
		$i->amOnPage('/wp-admin/edit.php?post_type=goose');
		$i->see('Lucy');
	}

	/**
	 * If no field is set as the entry title, use an auto-generated title.
	 *
	 * @param AcceptanceTester $i
	 */
	public function i_see_a_fallback_title_if_there_is_no_entry_title_field(AcceptanceTester $i)
	{
		$i->maximizeWindow();

		$i->loginAsAdmin();
		$i->haveContentModel('goose', 'geese', 'Geese go honk');
		$i->wait(1);

		$i->click('Text', '.field-buttons');
		$i->fillField(['name' => 'name'], 'Fave Foods');
		$i->click('.open-field button.primary');
		$i->wait(1);

		$i->click(Locator::lastElement('.add-item'));
		$i->click('Text', '.field-buttons');
		$i->fillField(['name' => 'name'], 'Name');
		$i->click('.open-field button.primary');
		$i->wait(1);

		// Create an entry for the model.
		$i->amOnPage('/wp-admin/edit.php?post_type=goose');
		$i->click('Add New', '.wrap');
		$i->wait(1);

		$i->fillField(['name' => 'wpe-content-model[goose][faveFoods]'], 'Gumdrops');
		$i->fillField(['name' => 'wpe-content-model[goose][name]'], 'Lucy');

		$i->click('Publish', '#publishing-action');
		$i->wait(2);

		$i->see('Post published.');

		// Check that the admin page displays a title based on the post type singular name.
		$i->amOnPage('/wp-admin/edit.php?post_type=goose');
		$i->see('geese');
	}

	/**
	 * If the entry title field has no user-entered data, the fallback title should also be used.
	 *
	 * @param AcceptanceTester $i
	 */
	public function i_see_a_fallback_title_if_the_title_field_is_empty(AcceptanceTester $i)
	{
		$i->maximizeWindow();

		$i->loginAsAdmin();
		$i->haveContentModel('goose', 'geese', 'Geese go honk');
		$i->wait(1);

		$i->click('Text', '.field-buttons');
		$i->fillField(['name' => 'name'], 'Name');
		$i->click('.open-field button.primary');
		// Set the 'name' field as the entry title field.
		$i->click('.open-field .checkbox.is-title');
		$i->wait(1);

		// Create an entry for the model.
		$i->amOnPage('/wp-admin/edit.php?post_type=goose');
		$i->click('Add New', '.wrap');
		$i->wait(1);

		// Don't fill the name field here — we want to test that a default
		// value is used for the title if the entry title field is empty.

		$i->click('Publish', '#publishing-action');
		$i->wait(2);

		$i->see('Post published.');

		// Check that the admin page displays the fallback entry title.
		// (The actual title will be 'goose [post ID]', but we can just
		// check for 'goose' because it does not appear elsewhere on the
		// entry listings page.)
		$i->amOnPage('/wp-admin/edit.php?post_type=goose');
		$i->see('geese');
	}
}
