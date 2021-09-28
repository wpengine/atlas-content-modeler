<?php
use Codeception\Util\Locator;

class CreateContentModelRelationFieldCest
{
	public function _before(\AcceptanceTester $I)
	{
		$I->maximizeWindow();
		$I->loginAsAdmin();
		$I->haveContentModel('Employee', 'Employees');
		$I->haveContentModel('Company', 'Companies');
		$I->wait(1);
		$I->click('Relation', '.field-buttons');
		$I->wait(1);
		$I->fillField(['name' => 'name'], 'Company Employees');
	}

	public function i_can_create_a_relation_field(AcceptanceTester $I)
	{
		$I->selectOption('#reference', 'Employees');
		$I->click('#many-to-many');
		$I->click('.open-field button.primary');
		$I->wait(1);

		$I->see('Relationship', '.field-list div.type');
		$I->see('Company Employees', '.field-list div.widest');
	}

	public function i_must_select_a_model_to_create_a_relation_field(AcceptanceTester $I)
	{
		$I->click('.open-field button.primary');
		$I->wait(1);

		$I->see('Please choose a related model');
	}

	public function i_can_not_edit_reference_or_cardinality(AcceptanceTester $I)
	{
		$this->i_can_create_a_relation_field($I);
		// Offsets from the center are used here to prevent “other element would
		// receive the click” due to the “add field” button overlapping the edit
		// button in the center.
		$I->clickWithLeftButton('.field-list button.edit', -5, -5);
		$I->wait(1);

		$reference_disabled_state   = $I->grabAttributeFrom('#reference', 'disabled');
		$cardinality_disabled_state = $I->grabAttributeFrom('#many-to-many', 'disabled');

		$I->assertEquals('true', $reference_disabled_state);
		$I->assertEquals('true', $cardinality_disabled_state);
	}

	public function i_can_update_an_existing_relationship_field(AcceptanceTester $I)
	{
		$this->i_can_create_a_relation_field($I);
		// Offsets from the center are used here to prevent “other element would
		// receive the click” due to the “add field” button overlapping the edit
		// button in the center.
		$I->clickWithLeftButton('.field-list button.edit', -5, -5);
		$I->wait(1);

		$I->fillField(['name' => 'name'], 'Updated Name');

		$I->click('.open-field button.primary');
		$I->wait(1);

		$I->see('Updated Name', '.field-list div.widest');
	}

	public function i_can_set_a_relationship_field_description_shorter_than_the_character_limit(AcceptanceTester $I)
	{
		$I->selectOption('#reference', 'Employees');
		$I->click('#many-to-many');

		// The field cannot be submitted with a description exceeding the maximum length.
		$I->fillField(['name' => 'description'], str_repeat('a', 251));
		$I->see('251/250', Locator::lastElement('span.count'));
		$I->click('.open-field button.primary');
		$I->wait(1);
		$I->see('Exceeds max length');

		// The description saves when corrected.
		$I->fillField(['name' => 'description'], 'This text is under the character limit.');
		$I->see('39/250', Locator::lastElement('span.count'));
		$I->click('.open-field button.primary');
		$I->wait(1);
		$I->see('Relationship', '.field-list div.type');
		$I->see('Company Employees', '.field-list div.widest');

		// The description and count are correct when reopening the field.
		$I->clickWithLeftButton('.field-list button.edit', -5, -5);
		$I->seeInField('description', 'This text is under the character limit.');
		$I->see('39/250', Locator::lastElement('span.count'));
		$I->wait(1);
	}
}
