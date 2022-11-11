<?php

class EditContentModelCest {

	public function _before( \AcceptanceTester $i ) {
		$i->resizeWindow( 1024, 1024 );
		$i->maximizeWindow();
		$i->loginAsAdmin();

		// First, create a new model.
		$content_model = $i->haveContentModel( 'Candy', 'Candies' );
		$i->amOnWPEngineEditContentModelPage( $content_model['slug'] );

		// Invoke edit mode.
		$i->amOnWPEngineContentModelPage();
		$i->click( '.model-list button.options' );
		$i->click( '.dropdown-content a.edit' );
		$i->see( 'Edit Candies' );
	}

	public function i_can_update_an_existing_content_model( AcceptanceTester $i ) {
		// Update the model data.
		$i->fillField( [ 'name' => 'singular' ], 'Cat' );
		$i->fillField( [ 'name' => 'plural' ], 'Cats' );
		$i->fillField( [ 'name' => 'description' ], 'Cats are better than candy.' );
		$i->see( '27/250', 'span.count' );
		$i->uncheckOption( [ 'name' => 'with_front' ] );
		$i->dontSeeCheckboxIsChecked( [ 'name' => 'has_archive' ] );
		$i->checkOption( [ 'name' => 'has_archive' ] );

		// Change the model's icon.
		$i->click( '.dashicons-picker' );
		$i->waitForElement( '.dashicon-picker-container' );
		$i->click( '.dashicon-picker-container .dashicons-admin-media' );

		$i->click( 'Save' );

		// Verify the updated data.
		$i->wait( 1 );
		$i->see( 'Cats', '.model-list' );
		$i->see( 'Cats are better than candy.', '.model-list' );

		// Check the icon in the WP admin sidebar was updated without refreshing the page.
		$classes = $i->grabAttributeFrom( '#menu-posts-candy .wp-menu-image', 'class' );
		$i->assertContains( 'dashicons-admin-media', $classes );

		// Check the label in the WP admin sidebar was updated without refreshing the page.
		$menu_label = $i->grabTextFrom( '#menu-posts-candy .wp-menu-name' );
		$i->assertEquals( 'Cats', $menu_label );

		// Check the label in the WP admin topbar was updated without refreshing the page.
		$i->moveMouseOver( '#wp-admin-bar-new-content' );
		$menu_label = $i->grabTextFrom( '#wp-admin-bar-new-candy .ab-item' );
		$i->assertEquals( 'Cat', $menu_label );

		// Check updated data persists in the edit modal when reopened.
		$i->click( '.model-list button.options' );
		$i->click( '.dropdown-content a.edit' );
		$i->dontSeeCheckboxIsChecked( [ 'name' => 'with_front' ] );
		$i->seeCheckboxIsChecked( [ 'name' => 'has_archive' ] );
		$i->seeInField( [ 'name' => 'singular' ], 'Cat' );
		$i->seeInField( [ 'name' => 'plural' ], 'Cats' );
	}

	public function i_see_a_warning_if_the_model_singular_name_is_reserved( AcceptanceTester $i ) {
		// Update the model data.
		$i->fillField( [ 'name' => 'singular' ], 'Post' ); // 'post' is in use.
		$i->click( 'Save' );
		$i->wait( 1 );

		$i->see( 'singular name is in use', '.ReactModal__Content' );
	}

	public function i_see_a_warning_if_the_model_plural_name_is_reserved( AcceptanceTester $i ) {
		$i->fillField( [ 'name' => 'plural' ], 'Posts' ); // 'posts' is in use.
		$i->click( 'Save' );
		$i->wait( 1 );

		$i->see( 'plural name is in use', '.ReactModal__Content' );
	}

}
