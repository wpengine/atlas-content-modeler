<?php
class EditContentModelCest {
	public function i_can_update_an_existing_content_model( AcceptanceTester $I ) {
		$I->loginAsAdmin();
		// First, create a new model.
		$I->haveContentModel('Candy', 'Candies');
		$I->wait(1);

		// Invoke edit mode.
		$I->amOnWPEngineContentModelPage();
		$I->click( '.model-list button.options' );
		$I->click( '.dropdown-content a.edit' );
		$I->see( 'Edit Candies' );

		// Update the model data and save.
		$I->fillField(['name' => 'singular'], 'Cat');
		$I->fillField(['name' => 'plural'], 'Cats');
		$I->fillField(['name' => 'description'], 'Cats are better than candy.');
		$I->see('27/250', 'span.count');
		$I->click('Save');

		// Verify the updated data.
		$I->wait(1);
		$I->see('Cats', '.model-list');
		$I->see('Cats are better than candy.', '.model-list');
	}
}
