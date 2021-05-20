<?php

class CreateContentModelNumberFieldCest
{
    /**
     * Ensure a user can add a number field to the model and see it within the list.
     */
    public function i_can_add_a_number_field_to_a_content_model(AcceptanceTester $I)
    {
        $I->loginAsAdmin();
        $I->haveContentModel('Candy', 'Candies');
        $I->amOnWPEngineEditContentModelPage('candies');
        $I->wait(1);

        $I->click('Number', '.field-buttons');
		$I->wait(1);
		$I->fillField(['name' => 'name'], 'Count');
        $I->see('5/50', 'span.count');
        $I->seeInField('#slug','count');
        $I->click('.open-field button.primary');
        $I->wait(1);

        $I->see('Number', '.field-list div.type');
        $I->see('Count', '.field-list span.widest');
    }
}
