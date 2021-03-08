<?php

class CreateContentModelCest
{
	/**
	 * Ensure user sees a message when no content models are present.
	 */
	public function i_see_a_message_when_i_have_no_content_models(AcceptanceTester $I)
	{
		$I->loginAsAdmin();
    	$I->amOnPage('/wp-admin/admin.php?page=wpe-content-model');
    	$I->wait(1);
		$I->see('You have no Content Models. It might be a good idea to create one now.');
	}

    /**
     * Ensure a content model can be created.
     */
    public function i_can_create_a_content_model(AcceptanceTester $I)
    {
    	$I->loginAsAdmin();
        $I->amOnPage('/wp-admin/admin.php?page=wpe-content-model&view=create-model');
        $I->wait(1);

        $I->fillField(['name' => 'singular'], 'Candy');
        $I->fillField(['name' => 'plural'], 'Candies');
        $I->fillField(['name' => 'description'], 'My candy content model');
        $I->see('22/250', 'p.limit');
        $I->click('.card-content button.primary');

        $I->amOnPage('/wp-admin/admin.php?page=wpe-content-model');
        $I->wait(1);
        $I->see('Candies', '.model-list');
        $I->see('My candy content model', '.model-list');
    }
}
