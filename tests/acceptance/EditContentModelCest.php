<?php
class EditContentModelCest {
	public function i_can_update_an_existing_content_model( AcceptanceTester $i ) {
		$i->resizeWindow( 1024, 1024 );
		$i->loginAsAdmin();

		// First, create a new model.
		$i->haveContentModel( 'Candy', 'Candies' );
		$i->wait( 1 );

		// Invoke edit mode.
		$i->amOnWPEngineContentModelPage();
		$i->click( '.model-list button.options' );
		$i->click( '.dropdown-content a.edit' );
		$i->see( 'Edit Candies' );

		// Update the model data.
		$i->fillField( [ 'name' => 'singular' ], 'Cat' );
		$i->fillField( [ 'name' => 'plural' ], 'Cats' );
		$i->fillField( [ 'name' => 'description' ], 'Cats are better than candy.' );
		$i->see( '27/250', 'span.count' );

		// Change the model's icon.
		$i->click( '.dashicons-picker' );
		$i->waitForElement( '.dashicon-picker-container' );
		$i->click( '.dashicon-picker-container .dashicons-admin-media' );

		$i->click( 'Save' );

		// Verify the updated data.
		$i->wait( 1 );
		$i->see( 'Cats', '.model-list' );
		$i->see( 'Cats are better than candy.', '.model-list' );

		// Check the icon in the WP admin sidebar was updated.
		$classes = $i->grabAttributeFrom( '#menu-posts-candy .wp-menu-image', 'class' );
		$i->assertContains( 'dashicons-admin-media', $classes );
	}
}
