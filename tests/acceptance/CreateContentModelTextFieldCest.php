<?php

class CreateContentModelTextFieldCest
{
    /**
     * Ensure a user can add a text field to the model and see it within the list.
     */
    public function i_can_create_a_content_model_text_field(AcceptanceTester $I)
    {
        $I->loginAsAdmin();
        $I->haveContentModel('Candy', 'Candies');
        $I->wait(1);

        $I->click('Text', '.field-buttons');
		$I->wait(1);
		$I->fillField(['name' => 'name'], 'Color');
        $I->see('5/50', 'span.count');
        $I->seeInField('#slug','color');
        $I->click('.open-field button.primary');
        $I->wait(1);

        $I->see('Text', '.field-list div.type');
        $I->see('Color', '.field-list div.widest');
    }

	public function i_can_create_a_content_model_text_field_as_a_textarea(AcceptanceTester $I)
	{
		$I->loginAsAdmin();
		$I->haveContentModel('Candy', 'Candies');
		$I->wait(1);

		$I->click('Text', '.field-buttons');
		$I->wait(1);
		$I->fillField(['name' => 'name'], 'Color');
		$I->seeInField('#slug','color');

		// Set the Input Type to “multiple lines” instead of “Single line”.
		$I->click('#multi');

		// Save the field.
		$I->click('.open-field button.primary');
		$I->wait(1);

		// Create a new Candies entry.
		$I->amOnPage('/wp-admin/edit.php?post_type=candy');
		$I->click('Add New', '.wrap');
		$I->wait(1);

		// Check that the “Color” field uses a textarea instead of an input field.
		$I->seeElement(
			'textarea',
			[ 'name' => 'atlas-content-modeler[candy][color]' ]
		);
	}
}
