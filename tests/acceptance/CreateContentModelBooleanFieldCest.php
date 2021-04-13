<?php

class CreateContentModelBooleanFieldCest
{
    /**
     * Ensure a user can add a boolean field to the model and see it within the list.
     */
    public function i_can_create_a_content_model_boolean_field(AcceptanceTester $I)
    {
        $I->loginAsAdmin();
        $I->haveContentModel('Candy', 'Candies');
        $I->amOnWPEngineEditContentModelPage('candies');
        $I->wait(1);

        $I->click('Boolean', '.field-buttons');
		$I->wait(1);
		$I->fillField(['name' => 'name'], 'Accept Terms');
        $I->see('12/50', 'span.count');
        $I->seeInField('#slug','acceptTerms');
        $I->click('.open-field button.primary');
        $I->wait(1);

        $I->see('Boolean', '.field-list span.type');
        $I->see('Accept Terms', '.field-list span.widest');
    }
}
