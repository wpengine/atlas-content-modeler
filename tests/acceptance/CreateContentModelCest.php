<?php

class CreateContentModelCest
{
	/**
	 * Ensure user sees a message when no content models are present.
	 */
	public function i_see_a_message_when_i_have_no_content_models(AcceptanceTester $I)
	{
		$I->loginAsAdmin();
    	$I->amOnWPEngineContentModelPage();
    	$I->wait(1);
		$I->see('have no Content Models');
	}

    /**
     * Ensure a content model can be created.
     */
    public function i_can_create_a_content_model(AcceptanceTester $I)
    {
    	$I->loginAsAdmin();
        $I->amOnWPEngineCreateContentModelPage();
        $I->wait(1);

        $I->fillField(['name' => 'singular'], 'Candy');
        $I->fillField(['name' => 'plural'], 'Candies');
        $I->fillField(['name' => 'description'], 'My candy content model');
        $I->see('22/250', 'span.count');
        $I->click('.card-content button.primary');
        $I->wait(1);

        $I->amOnWPEngineContentModelPage();
        $I->wait(1);
        $I->see('Candies', '.model-list');
        $I->see('My candy content model', '.model-list');
    }
}
