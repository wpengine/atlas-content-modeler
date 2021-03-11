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

        $I->click('li.add-item button');
        $I->wait(1);

        $I->click('.field-list button:first-of-type');
        $I->fillField(['name' => 'name'], 'Color');
        $I->click('.open-field button.primary');
        $I->wait(1);

        $I->see('Text', '.field-list span.type');
        $I->see('Color', '.field-list span.widest');
    }
}
