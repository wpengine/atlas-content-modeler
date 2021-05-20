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
        $I->amOnWPEngineEditContentModelPage('candies');
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
}
