<?php

class CreateContentModelMediaFieldCest {

	public function _before( \AcceptanceTester $i ) {
		$i->loginAsAdmin();
		$content_model = $i->haveContentModel( 'Candy', 'Candies' );
		$i->amOnWPEngineEditContentModelPage( $content_model['slug'] );
	}

	/**
	 * Ensure a user can add a Media field to the model with a repeatable property.
	 */
	public function i_can_create_a_content_model_media_repeatable_field( AcceptanceTester $i ) {
		$i->click( 'Media', '.field-buttons' );
		$i->wait( 1 );
		$i->fillField( [ 'name' => 'name' ], 'positionxyz' );

		// Set the Input Type to “multiple lines” instead of “Single line”.
		$i->click( '.is-repeatable' );

		// Save the field.
		$i->click( 'button[data-testid="edit-model-update-create-button"]' );
		$i->wait( 1 );

		// Create a new Candies entry.
		$i->amOnPage( '/wp-admin/edit.php?post_type=candy' );
		$i->click( 'Add New', '.wrap' );
		$i->wait( 1 );

		$i->see( 'Manage Media', 'button[data-testid="media-uploader-manage-media-button"]' );

		// The media button should be in focus as the first field on the page.
		$active_element = $i->executeJS( "return document.activeElement.getAttribute('data-testid');" );
		$i->assertEquals( 'media-uploader-manage-media-button', $active_element );
	}

	/**
	 * Ensure a user cannot save a Media field with a required repeatable property.
	 */
	public function i_cannot_save_empty_required_media_repeatable_field( AcceptanceTester $i ) {
		$i->click( 'Media', '.field-buttons' );
		$i->wait( 1 );
		$i->fillField( [ 'name' => 'name' ], 'positionxyz' );

		$i->click( '.is-repeatable' );
		$i->click( '.is-required' );
		$i->click( 'button[data-testid="edit-model-update-create-button"]' );
		$i->wait( 1 );

		// Create a new Candies entry.
		$i->amOnPage( '/wp-admin/edit.php?post_type=candy' );
		$i->click( 'Add New', '.wrap' );
		$i->wait( 1 );

		$i->seeElement( 'input[name="atlas-content-modeler[candy][positionxyz]"]:invalid' );
	}

	/**
	 * Ensure a user can add a media field to the model and see it within the list.
	 */
	public function i_can_add_a_media_field_to_a_content_model( AcceptanceTester $i ) {
		$i->click( 'Media', '.field-buttons' );
		$i->wait( 1 );
		$i->fillField( [ 'name' => 'name' ], 'Product Photo' );
		$i->see( '13/50', 'span.count' );
		$i->seeInField( '#slug', 'productPhoto' );
		$i->click( 'button[data-testid="edit-model-update-create-button"]' );
		$i->wait( 1 );

		$i->see( 'Media', '.field-list div.type' );
		$i->see( 'Product Photo', '.field-list div.widest' );
	}

	/**
	 * Ensure a user can add a featured image media field to the model and see it within the list.
	 */
	public function i_can_add_a_featured_image_media_field_to_a_content_model( AcceptanceTester $i ) {
		$i->click( 'Media', '.field-buttons' );
		$i->wait( 1 );
		$i->fillField( [ 'name' => 'name' ], 'The Primary Image' );
		$i->see( '17/50', 'span.count' );
		$i->seeInField( '#slug', 'thePrimaryImage' );
		$i->checkOption( 'isFeatured' );
		$i->click( 'button[data-testid="edit-model-update-create-button"]' );
		$i->wait( 1 );

		$i->see( 'Media', '.field-list div.type' );
		$i->see( 'The Primary Image', '.field-list div.widest' );
	}

	/**
	 * Ensure a user can only add one featured image media field to the model.
	 */
	public function i_can_only_add_one_featured_image_media_field_to_a_content_model( AcceptanceTester $i ) {
		$i->click( 'Media', '.field-buttons' );
		$i->wait( 1 );
		$i->fillField( [ 'name' => 'name' ], 'The Primary Image' );
		$i->see( '17/50', 'span.count' );
		$i->seeInField( '#slug', 'thePrimaryImage' );
		$i->checkOption( 'isFeatured' );
		$i->click( 'button[data-testid="edit-model-update-create-button"]' );
		$i->wait( 1 );

		$i->see( 'Media', '.field-list div.type' );
		$i->see( 'The Primary Image', '.field-list div.widest' );

		$i->click( '.add-item' );
		$i->wait( 1 );

		$i->click( 'Media', '.field-buttons' );
		$i->wait( 1 );
		$i->fillField( [ 'name' => 'name' ], 'The Second Image' );
		$i->see( '16/50', 'span.count' );
		$i->seeInField( '#slug', 'theSecondImage' );
		$i->checkOption( 'isFeatured' );
		$i->click( 'button[data-testid="edit-model-update-create-button"]' );
		$i->wait( 1 );

		$i->clickWithLeftButton( '.field-list button.edit', -5, -5 );
		$i->wait( 1 );
		$i->seeInField( '#slug', 'thePrimaryImage' );
		$i->dontSeeCheckboxIsChecked( 'isFeatured ' );
	}

	/**
	 * Ensure a featured image field is properly denoted in publisher.
	 */
	public function i_can_denote_a_featured_image_field_in_publisher( AcceptanceTester $i ) {
		$i->click( 'Media', '.field-buttons' );
		$i->wait( 1 );
		$i->fillField( [ 'name' => 'name' ], 'The Primary Image' );
		$i->see( '17/50', 'span.count' );
		$i->seeInField( '#slug', 'thePrimaryImage' );
		$i->checkOption( 'isFeatured' );
		$i->click( 'button[data-testid="edit-model-update-create-button"]' );
		$i->wait( 1 );

		$i->see( 'Media', '.field-list div.type' );
		$i->see( 'The Primary Image', '.field-list div.widest' );

		$i->amOnPage( '/wp-admin/edit.php?post_type=candy' );
		$i->click( 'Add New', '.wrap' );
		$i->wait( 2 );
		$i->see( 'Add Featured Image', 'div.media-btns' );
	}
}
