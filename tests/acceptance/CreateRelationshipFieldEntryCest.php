<?php
use Codeception\Util\Locator;

class CreateRelationshipFieldEntryCest
{
	public function _before(\AcceptanceTester $I)
	{
		$I->maximizeWindow();
		$I->loginAsAdmin();
		$I->haveContentModel('Company', 'Companies');
		$I->wait(1);
		$I->click('Text', '.field-buttons');
		$I->checkOption('input[name="isTitle"]');
		$I->fillField(['name' => 'name'], 'Company');
		$I->click('.open-field button.primary');

		$I->haveContentModel('Employee', 'Employees');
		$I->wait(1);
		$I->click('Relationship', '.field-buttons');
		$I->wait(1);
		$I->fillField(['name' => 'name'], 'Company');
		$I->selectOption('#reference', 'Companies');
		$I->click('.open-field button.primary');
		$I->wait(1);
		$I->click(Locator::lastElement('.add-item'));
		$I->click('Relationship', '.field-buttons');
		$I->wait(1);
		$I->fillField(['name' => 'name'], 'Many Companies');
		$I->selectOption('#reference', 'Companies');
		$I->click('input#many-to-many');
		$I->click('.open-field button.primary');
		$I->wait(1);
	}

	public function i_can_create_an_employee_and_see_many_to_one_relation_field(AcceptanceTester $I)
	{
		$I->see('Relationship', '.field-list div.type');
		$I->see('Company', '.field-list div.widest');

		$I->amOnPage('/wp-admin/post-new.php?post_type=company');
		$I->fillField(['name' => 'atlas-content-modeler[company][company]'], 'WP Engine');

		$I->click('Publish', '#publishing-action');
		$I->wait(1);

		$I->see('Post published.');
		$I->wait(1);

		$I->amOnPage('/wp-admin/post-new.php?post_type=employee');
		$I->see('Company', 'div.field.relationship');
		$I->click('#atlas-content-modeler[employee][company]');
		$I->see('Select Company', 'div.ReactModal__Content.ReactModal__Content--after-open h2');
		$I->wait(3);
		$I->click(Locator::elementAt('td.checkbox input', 1));
		$I->click('button.action-button');
		$I->wait(3);

		$I->see('WP Engine', 'div.relation-model-card');
	}

	public function i_can_create_an_employee_and_see_many_to_many_relation_field(AcceptanceTester $I)
	{
		$I->see('Relationship', '.field-list div.type');
		$I->see('Company', '.field-list div.widest');

		$I->amOnPage('/wp-admin/post-new.php?post_type=company');
		$I->fillField(['name' => 'atlas-content-modeler[company][company]'], 'WP Engine');

		$I->click('Publish', '#publishing-action');
		$I->wait(1);

		$I->amOnPage('/wp-admin/post-new.php?post_type=company');
		$I->fillField(['name' => 'atlas-content-modeler[company][company]'], 'Another Company Name');

		$I->click('Publish', '#publishing-action');
		$I->wait(1);

		$I->see('Post published.');
		$I->wait(1);

		$I->amOnPage('/wp-admin/post-new.php?post_type=employee');
		$I->see('Many Companies', 'div.field.relationship');
		$I->click('#atlas-content-modeler[employee][manyCompanies]');
		$I->see('Select Companies', 'div.ReactModal__Content.ReactModal__Content--after-open h2');
		$I->wait(3);
		$I->click(Locator::elementAt('td.checkbox input', 1));
		$I->click(Locator::elementAt('td.checkbox input', 2));
		$I->click('button.action-button');
		$I->wait(3);

		$I->see('WP Engine', 'div.relation-model-card');
		$I->see('Another Company Name', 'div.relation-model-card');
	}

	public function i_can_create_a_new_entry_from_an_empty_relationships_modal_table(AcceptanceTester $I) {
		// Try to create an employee before any companies have been entered.
		$I->amOnPage('/wp-admin/post-new.php?post_type=employee');
		$I->see('Company', 'div.field.relationship');
		$I->click('#atlas-content-modeler[employee][company]');
		$I->see('Select Company', '.atlas-content-modeler-relationship-modal-container h2');
		$I->wait(2);
		$I->see('No published entries');

		// Create a company via the link in the relationships modal.
		$I->click('Create a new Company');
		$I->wait(1);
		$I->switchToNextTab(); // Focus on the Create Company tab.
		$I->fillField(['name' => 'atlas-content-modeler[company][company]'], 'WP Engine');
		$I->click('Publish', '#publishing-action');
		$I->wait(1);

		// Check the new company appears in the updated modal.
		$I->closeTab(); // Focus on the original employee tab.

		// Test that the modal is still visible.
		$I->see('Select Company', '.atlas-content-modeler-relationship-modal-container h2');

		// This is as far as we can test in headless mode under CircleCI.
		// The visibilitychange event does not fire in headless mode,
		// so we can't check that the modal refreshed to show the
		// newly-added “WP Engine” company.
	}
}
