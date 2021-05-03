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
	public function i_can_see_the_defined_entry_title_in_the_entry_list(AcceptanceTester $i)
	{
		$i->maximizeWindow();

		/**
		 * Create a “Goose” model with one “Name” text field
		 * created with “Use this field as the entry title”.
		 */
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
		$i->amOnPage('/wp-admin/edit.php?post_type=geese');
		$i->click('Add New', '.wrap');
		$i->wait(1);

		$i->fillField(['name' => 'wpe-content-model[geese][faveFoods]'], 'Gumdrops');
		$i->fillField(['name' => 'wpe-content-model[geese][name]'], 'Lucy');

		$i->click('Publish', '#publishing-action');
		$i->wait(2);

		$i->see('Post published.');

		// Check that the admin page displays the “Lucy” title.
		$i->amOnPage('/wp-admin/edit.php?post_type=geese');
		$i->see('Lucy');
	}

	/**
	 * If no field is set as the entry title, the first text field is used.
	 *
	 * @param AcceptanceTester $i
	 */
	public function i_can_see_the_first_text_field_value_as_the_entry_title(AcceptanceTester $i)
	{
		$i->maximizeWindow();

		/**
		 * Create a “Goose” model with a “Name” text field
		 * created with “Use this field as the entry title”.
		 */
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
		$i->amOnPage('/wp-admin/edit.php?post_type=geese');
		$i->click('Add New', '.wrap');
		$i->wait(1);

		$i->fillField(['name' => 'wpe-content-model[geese][faveFoods]'], 'Gumdrops');
		$i->fillField(['name' => 'wpe-content-model[geese][name]'], 'Lucy');

		$i->click('Publish', '#publishing-action');
		$i->wait(2);

		$i->see('Post published.');

		// Check that the admin page displays the “Lucy” title.
		$i->amOnPage('/wp-admin/edit.php?post_type=geese');
		$i->see('Gumdrops');
	}

	/**
	 * If the entry title field has no user-entered data, the singular name of
	 * the post type plus the post ID is used as the title.
	 *
	 * @param AcceptanceTester $i
	 */
	public function i_can_see_a_generated_entry_title_for_empty_title_field_values(AcceptanceTester $i)
	{
		$i->maximizeWindow();

		/**
		 * Create a “Goose” model with a text field that will be used
		 * as the entry title.
		 */
		$i->loginAsAdmin();
		$i->haveContentModel('goose', 'geese', 'Geese go honk');
		$i->wait(1);

		$i->click('Text', '.field-buttons');
		$i->fillField(['name' => 'name'], 'Name');
		$i->click('.open-field button.primary');
		$i->wait(1);

		// Create an entry for the model.
		$i->amOnPage('/wp-admin/edit.php?post_type=geese');
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
		$i->amOnPage('/wp-admin/edit.php?post_type=geese');
		$i->see('goose');
	}

	/**
	 * If the model has no text fields, check that we fall back to a
	 * title generated from the singular model name and post ID.
	 *
	 * @param AcceptanceTester $i
	 */
	public function i_can_see_the_fallback_entry_title_in_the_entry_list(AcceptanceTester $i)
	{
		$i->maximizeWindow();

		/**
		 * Create a “Goose” model with a number field.
		 */
		$i->loginAsAdmin();
		$i->haveContentModel('goose', 'geese', 'Geese go honk');
		$i->wait(1);

		$i->click('Number', '.field-buttons');
		$i->fillField(['name' => 'name'], 'Teeth');
		$i->click('.open-field button.primary');
		$i->wait(1);

		// Create an entry for the model.
		$i->amOnPage('/wp-admin/edit.php?post_type=geese');
		$i->click('Add New', '.wrap');
		$i->wait(1);

		$i->fillField(['name' => 'wpe-content-model[geese][teeth]'], '2');

		$i->click('Publish', '#publishing-action');
		$i->wait(2);

		$i->see('Post published.');

		// Check that the admin page displays the fallback entry title.
		// (The actual title will be 'goose [post ID]', but we can just
		// check for 'goose' because it does not appear elsewhere on the
		// entry listings page.
		$i->amOnPage('/wp-admin/edit.php?post_type=geese');
		$i->see('goose');
	}
}
