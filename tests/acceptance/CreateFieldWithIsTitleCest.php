<?php

class CreateFieldWithIsTitleCest
{
	/**
	 * Ensure a user can add a text field and set it as the entry title.
	 */
	public function i_can_create_a_content_model_text_field_with_is_title(AcceptanceTester $I)
	{
		$I->loginAsAdmin();
		$I->haveContentModel('Candy', 'Candies');
		$I->amOnWPEngineEditContentModelPage('candies');
		$I->wait(1);

		$I->click('Text', '.field-buttons');
		$I->wait(1);
		$I->fillField(['name' => 'name'], 'Name');
		$I->seeInField('#slug','name');
		$I->click('.open-field label.checkbox');
		$I->click('.open-field button.primary');
		$I->wait(1);

		$I->see('Text', '.field-list span.type');
		$I->see('Name', '.field-list span.widest');
		$I->see('entry title', '.field-list span.tags');
	}
}
