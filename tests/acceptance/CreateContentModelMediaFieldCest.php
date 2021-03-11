<?php

class CreateContentModelMediaFieldCest
{
    /**
     * Ensure a user can add a media field to the model and see it within the list.
     */
    public function i_can_add_a_media_field_to_a_content_model(AcceptanceTester $I)
    {
        $I->loginAsAdmin();
        $I->haveContentModel('Candy', 'Candies');
        $I->amOnWPEngineEditContentModelPage('candies');
        $I->wait(1);

        $I->click('li.add-item button');
        $I->wait(1);

        $I->click('Media', '.open-field .field-buttons');
        $I->fillField(['name' => 'name'], 'Product Photo');
        $I->see('13/50', 'span.count');
        $I->seeInField('#slug','productPhoto');
        $I->click('.open-field button.primary');
        $I->wait(1);

        $I->see('Media', '.field-list span.type');
        $I->see('Product Photo', '.field-list span.widest');
    }
}
