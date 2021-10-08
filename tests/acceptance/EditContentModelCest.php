<?php
class EditContentModelCest {
	public function i_can_update_an_existing_content_model( AcceptanceTester $I ) {
		$I->resizeWindow(1024, 1024);
		$I->loginAsAdmin();

		// First, create a new model.
		$I->haveContentModel('Candy', 'Candies');
		$I->wait(1);

		// Invoke edit mode.
		$I->amOnWPEngineContentModelPage();
		$I->click( '.model-list button.options' );
		$I->click( '.dropdown-content a.edit' );
		$I->see( 'Edit Candies' );

		// Update the model data.
		$I->fillField(['name' => 'singular'], 'Cat');
		$I->fillField(['name' => 'plural'], 'Cats');
		$I->fillField(['name' => 'description'], 'Cats are better than candy.');
		$I->see('27/250', 'span.count');

		// Change the model's icon.
		$I->click('.dashicons-picker');
		$I->waitForElement('.dashicon-picker-container');
		$I->click('.dashicon-picker-container .dashicons-admin-media');

		$I->click('Save');

		// Verify the updated data.
		$I->wait(1);
		$I->see('Cats', '.model-list');
		$I->see('Cats are better than candy.', '.model-list');

		// Check the icon in the WP admin sidebar was updated.
		$classes = $I->grabAttributeFrom('#menu-posts-candy .wp-menu-image', 'class');
		$I->assertContains('dashicons-admin-media', $classes);
	}
}
